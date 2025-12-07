<?php
/**
 * Check Payment Status - Gọi SePay API và kiểm tra thanh toán
 */
header('Content-Type: application/json');
require_once 'config.php';

$order_code = isset($_GET['order_code']) ? trim($_GET['order_code']) : '';

if (empty($order_code)) {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'Missing order_code',
        'paid' => false
    )));
}

try {
    $conn = getDB();
    
    // Get order info
    $stmt = $conn->prepare("
        SELECT ma_donhang, tong_tien, trangthai_thanhtoan, ma_user, order_code
        FROM don_hang 
        WHERE order_code = :order_code
    ");
    $stmt->execute(array(':order_code' => $order_code));
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        exit(json_encode(array(
            'status' => 'error',
            'message' => 'Order not found',
            'paid' => false
        )));
    }
    
    // Already paid
    if ($order['trangthai_thanhtoan'] == 'da_thanh_toan') {
        exit(json_encode(array(
            'status' => 'success',
            'paid' => true,
            'message' => 'Already paid'
        )));
    }
    
    // Call SePay API
    $api_url = 'https://my.sepay.vn/userapi/transactions/list?account_number=' . SEPAY_ACCOUNT_NUMBER . '&limit=20';
    
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $api_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer ' . SEPAY_API_KEY,
            'Content-Type: application/json'
        ),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 15
    ));
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code != 200 || !$response) {
        logSePay("API Error - HTTP {$http_code} - {$curl_error}");
        exit(json_encode(array(
            'status' => 'error',
            'message' => 'API connection failed',
            'paid' => false,
            'http_code' => $http_code
        )));
    }
    
    $data = json_decode($response, true);
    
    if (!isset($data['transactions']) || !is_array($data['transactions'])) {
        logSePay("Invalid API response");
        exit(json_encode(array(
            'status' => 'error',
            'message' => 'Invalid API response',
            'paid' => false
        )));
    }
    
    $transactions = $data['transactions'];
    logSePay("Checking order {$order_code} - Found " . count($transactions) . " transactions");
    
    // Match pattern
    $pattern = '/(?:Thanh\s*Toan\s*)?Don\s*Hang\s*' . $order_code . '/i';
    $order_amount = floatval($order['tong_tien']);
    $matched_transaction = null;
    
    foreach ($transactions as $trans) {
        $content = isset($trans['transaction_content']) ? $trans['transaction_content'] : '';
        $amount_in = isset($trans['amount_in']) ? floatval($trans['amount_in']) : 0;
        
        if (preg_match($pattern, $content) && $amount_in >= $order_amount) {
            $matched_transaction = $trans;
            logSePay("✅ MATCHED! Order {$order_code} - {$content} - {$amount_in} VND");
            break;
        }
    }
    
    // If matched, update database
    if ($matched_transaction) {
        // Update don_hang
        $update_order = $conn->prepare("
            UPDATE don_hang 
            SET trangthai_thanhtoan = 'da_thanh_toan',
                trang_thai = 'xac_nhan',
                thanh_toan = 'đã thanh toán',
                update_at = NOW()
            WHERE ma_donhang = :ma_donhang
        ");
        $update_order->execute(array(':ma_donhang' => $order['ma_donhang']));
        
        // Update transactions table
        $trans_id = isset($matched_transaction['id']) ? $matched_transaction['id'] : '';
        $account_number = isset($matched_transaction['account_number']) ? $matched_transaction['account_number'] : '';
        $amount_in = isset($matched_transaction['amount_in']) ? floatval($matched_transaction['amount_in']) : 0;
        $content = isset($matched_transaction['transaction_content']) ? $matched_transaction['transaction_content'] : '';
        $bank_brand_name = isset($matched_transaction['bank_brand_name']) ? $matched_transaction['bank_brand_name'] : '';
        $transaction_date = isset($matched_transaction['transaction_date']) ? $matched_transaction['transaction_date'] : date('Y-m-d H:i:s');
        
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
        
        // Delete cart
        $delete_cart = $conn->prepare("DELETE FROM gio_hang WHERE ma_user = :ma_user");
        $delete_cart->execute(array(':ma_user' => $order['ma_user']));
        
        logSePay("Database updated for order {$order_code}");
        
        exit(json_encode(array(
            'status' => 'success',
            'paid' => true,
            'message' => 'Payment confirmed',
            'transaction' => $matched_transaction,
            'transactions' => array_slice($transactions, 0, 5) // Return recent 5
        )));
    }
    
    // Not paid yet, return transactions for display
    exit(json_encode(array(
        'status' => 'success',
        'paid' => false,
        'message' => 'No matching transaction',
        'transactions' => array_slice($transactions, 0, 5),
        'checked_count' => count($transactions)
    )));
    
} catch (Exception $e) {
    logSePay("ERROR: " . $e->getMessage());
    exit(json_encode(array(
        'status' => 'error',
        'message' => $e->getMessage(),
        'paid' => false
    )));
}
?>
