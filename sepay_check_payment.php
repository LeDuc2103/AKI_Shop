<?php
/**
 * Check Payment - Gọi SePay API từ PHP backend (tránh CORS)
 */

header('Content-Type: application/json');
require_once 'config/database.php';

$order_code = isset($_GET['order_code']) ? $_GET['order_code'] : '';

if (empty($order_code)) {
    exit(json_encode(array('status' => 'error', 'message' => 'Missing order_code')));
}

// Log function
function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    $log_file = 'sepay_worker.log';
    $log_entry = "[{$timestamp}] {$message}\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Lấy thông tin đơn hàng
    $stmt = $conn->prepare("
        SELECT ma_donhang, tong_tien, trangthai_thanhtoan, ma_user
        FROM don_hang 
        WHERE order_code = :order_code
    ");
    $stmt->execute(array(':order_code' => $order_code));
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        exit(json_encode(array('status' => 'error', 'message' => 'Order not found')));
    }
    
    // Nếu đã thanh toán rồi thì return ngay
    if ($order['trangthai_thanhtoan'] == 'da_thanh_toan') {
        exit(json_encode(array(
            'status' => 'success',
            'paid' => true,
            'message' => 'Already paid'
        )));
    }
    
    // Gọi SePay API từ server-side
    $ch = curl_init();
    $api_url = 'https://my.sepay.vn/userapi/transactions/list?account_number=0981523130&limit=20';
    
    curl_setopt_array($ch, array(
        CURLOPT_URL => $api_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer 7O2MCQT0UISAX1BNW3KGZFHKESPOJOC4HRUE1MEBXDBABIELFARZWUL68FNYV2MD',
            'Content-Type: application/json'
        ),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 10
    ));
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code != 200 || !$response) {
        logMessage("API Error - HTTP {$http_code}");
        exit(json_encode(array(
            'status' => 'error',
            'message' => 'API Error',
            'paid' => false
        )));
    }
    
    $data = json_decode($response, true);
    $transactions = isset($data['transactions']) ? $data['transactions'] : array();
    
    logMessage("Checking order {$order_code} - Found " . count($transactions) . " transactions");
    
    // Tìm giao dịch khớp
    $pattern = '/(?:Thanh\s*Toan\s*)?Don\s*Hang\s*' . $order_code . '/i';
    $order_amount = floatval($order['tong_tien']);
    
    foreach ($transactions as $trans) {
        $content = isset($trans['transaction_content']) ? $trans['transaction_content'] : '';
        $amount_in = isset($trans['amount_in']) ? floatval($trans['amount_in']) : 0;
        
        if (preg_match($pattern, $content) && $amount_in >= $order_amount) {
            // Tìm thấy thanh toán!
            logMessage("✅ MATCH! Order {$order_code} - Transaction: {$content} - Amount: {$amount_in}");
            
            // Cập nhật database
            $update_order = $conn->prepare("
                UPDATE don_hang 
                SET trangthai_thanhtoan = 'da_thanh_toan',
                    trang_thai = 'xac_nhan',
                    thanh_toan = 'đã thanh toán',
                    update_at = NOW()
                WHERE ma_donhang = :ma_donhang
            ");
            $update_order->execute(array(':ma_donhang' => $order['ma_donhang']));
            
            // Cập nhật transaction
            $trans_id = isset($trans['id']) ? $trans['id'] : '';
            $account_number = isset($trans['account_number']) ? $trans['account_number'] : '';
            $bank_brand_name = isset($trans['bank_brand_name']) ? $trans['bank_brand_name'] : '';
            $transaction_date = isset($trans['transaction_date']) ? $trans['transaction_date'] : date('Y-m-d H:i:s');
            
            $update_trans = $conn->prepare("
                UPDATE transactions 
                SET id = :trans_id,
                    account_number = :account_number,
                    amount_in = :amount_in,
                    transaction_content = :content,
                    bank_brand_name = :bank_brand_name,
                    transaction_date = :transaction_date,
                    is_processed = 1
                WHERE ma_donhang = :ma_donhang
            ");
            $update_trans->execute(array(
                ':trans_id' => $trans_id,
                ':account_number' => $account_number,
                ':amount_in' => $amount_in,
                ':content' => $content,
                ':bank_brand_name' => $bank_brand_name,
                ':transaction_date' => $transaction_date,
                ':ma_donhang' => $order['ma_donhang']
            ));
            
            // Xóa giỏ hàng
            $delete_cart = $conn->prepare("DELETE FROM gio_hang WHERE ma_user = :ma_user");
            $delete_cart->execute(array(':ma_user' => $order['ma_user']));
            
            exit(json_encode(array(
                'status' => 'success',
                'paid' => true,
                'message' => 'Payment confirmed',
                'amount' => $amount_in
            )));
        }
    }
    
    // Chưa tìm thấy
    exit(json_encode(array(
        'status' => 'success',
        'paid' => false,
        'message' => 'No matching transaction',
        'transactions_checked' => count($transactions)
    )));
    
} catch (Exception $e) {
    logMessage("ERROR: " . $e->getMessage());
    exit(json_encode(array(
        'status' => 'error',
        'message' => $e->getMessage(),
        'paid' => false
    )));
}
?>
