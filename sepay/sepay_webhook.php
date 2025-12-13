<?php

    /* 
    File sepay_webhook.php
    File này dùng làm endpoint nhận webhook từ SePay. Mỗi khi có giao dịch SePay sẽ bắn webhook về và chúng ta sẽ lưu thông tin giao dịch vào CSDL. Đồng thời bóc tách ID đơn hàng từ nội dung thanh toán. Sau khi tìm được ID đơn hàng thì cập nhật trạng thái thanh toán của đơn hàng thành đã thanh toán (payment_status=Paid).
     Endpoint nhận webhook sẽ là https://nodose-jamika-astylar.ngrok-free.dev/sepay/sepay_webhook.php
    */
    
    // API Key verification
    define('SEPAY_API_KEY', '7O2MCQT0UISAX1BNW3KGZFHKESPOJOC4HRUE1MEBXDBABIELFARZWUL68FNYV2MD');
    
    // Log file
    $log_file = '../logs/sepay_webhook.log';
    if(!file_exists('../logs')) {
        mkdir('../logs', 0777, true);
    }
    
    // Log webhook received
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Webhook received from IP: " . $_SERVER['REMOTE_ADDR'] . "\n", FILE_APPEND);
    
    // Get Authorization header
    $headers = getallheaders();
    $auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : '';
    
    // Log headers for debugging
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Authorization header: " . $auth_header . "\n", FILE_APPEND);
    
    // Verify API Key - chỉ verify nếu không phải local testing
    $is_local = ($_SERVER['REMOTE_ADDR'] === '::1' || $_SERVER['REMOTE_ADDR'] === '127.0.0.1');
    
    if (!$is_local && $auth_header !== 'Apikey ' . SEPAY_API_KEY) {
        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] UNAUTHORIZED - Invalid API Key\n", FILE_APPEND);
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        die();
    }
    
    if ($is_local) {
        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] LOCAL REQUEST - Skipping API Key verification\n", FILE_APPEND);
    }
    
     // Include file db_connect.php, file chứa toàn bộ kết nối CSDL
    require('../config/database.php');
    
    // Khởi tạo database
    $db = new Database();
    $conn = $db->getConnection();
   
    // Lay du lieu tu webhooks, xem cac truong du lieu tai https://docs.sepay.vn/tich-hop-webhooks.html#du-lieu
    $data = json_decode(file_get_contents('php://input'));
    if(!is_object($data)) {
        echo json_encode(['success'=>FALSE, 'message' => 'No data']);
        die('No data found!');
    }
    
    // Khoi tao cac bien
    $gateway = $data->gateway;
    $transaction_date = $data->transactionDate;
    $account_number = $data->accountNumber;
    $sub_account = $data->subAccount;

    $transfer_type = $data->transferType;
    $transfer_amount = $data->transferAmount;
    $accumulated = $data->accumulated;

    $code = $data->code;
    $transaction_content = $data->content;
    $reference_number = $data->referenceCode;
    $body = $data->description;

    $amount_in = 0;
    $amount_out = 0;

    // Kiem tra giao dich tien vao hay tien ra
    if($transfer_type == "in")
        $amount_in = $transfer_amount;
    else if($transfer_type == "out")
        $amount_out = $transfer_amount;

    // Log webhook data received
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] === PROCESSING WEBHOOK ===\n", FILE_APPEND);
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Gateway: $gateway | Amount: $transfer_amount | Type: $transfer_type\n", FILE_APPEND);
    
    // Lưu giao dịch vào CSDL
    try {
        $sql = "INSERT INTO tb_transactions (gateway, transaction_date, account_number, sub_account, amount_in, amount_out, accumulated, code, transaction_content, reference_number, body, created_at) VALUES (:gateway, :transaction_date, :account_number, :sub_account, :amount_in, :amount_out, :accumulated, :code, :transaction_content, :reference_number, :body, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':gateway', $gateway);
        $stmt->bindParam(':transaction_date', $transaction_date);
        $stmt->bindParam(':account_number', $account_number);
        $stmt->bindParam(':sub_account', $sub_account);
        $stmt->bindParam(':amount_in', $amount_in);
        $stmt->bindParam(':amount_out', $amount_out);
        $stmt->bindParam(':accumulated', $accumulated);
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':transaction_content', $transaction_content);
        $stmt->bindParam(':reference_number', $reference_number);
        $stmt->bindParam(':body', $body);
        
        if($stmt->execute()) {
            $transaction_id = $conn->lastInsertId();
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] ✓ Transaction saved to tb_transactions (ID: $transaction_id)\n", FILE_APPEND);
        }
    } catch(PDOException $e) {
        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] ✗ Failed to save transaction: " . $e->getMessage() . "\n", FILE_APPEND);
        http_response_code(200);
        echo json_encode(['success'=>FALSE, 'message' => 'Cannot insert transaction: ' . $e->getMessage()]);
        die();
    }
    
    // Tách mã đơn hàng
    
    // Biểu thức regex để khớp với mã đơn hàng
    $regex = '/DH(\d+)/';
    
    // Sử dụng preg_match để khớp regex với chuỗi nội dung chuyển tiền
    preg_match($regex, $transaction_content, $matches);
    
    // Lấy mã đơn hàng từ kết quả khớp
    $pay_order_id = isset($matches[1]) ? $matches[1] : null;
    
    // Log transaction content
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Transaction content: " . $transaction_content . "\n", FILE_APPEND);
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Extracted order ID: " . $pay_order_id . "\n", FILE_APPEND);

    // Nếu không tìm thấy mã đơn hàng từ nội dung thanh toán thì trả về kết quả lỗi
    if(!is_numeric($pay_order_id)) {
        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] ERROR - Order ID not found or invalid\n", FILE_APPEND);
        http_response_code(200); // Vẫn trả về 200 để SePay không retry
        echo json_encode(['success' => false, 'message' => 'Order not found. ma_donhang ' . $pay_order_id]);
        die();
    }
    
    // Tìm đơn hàng với mã đơn hàng và số tiền tương ứng với giao dịch thanh toán trên
    try {
        // Tìm đơn hàng - không quan tâm trạng thái thanh toán để tránh lỗi
        $stmt = $conn->prepare("SELECT * FROM don_hang WHERE ma_donhang = ? AND tong_tien = ?");
        $stmt->execute([$pay_order_id, $amount_in]);
        $order = $stmt->fetch(PDO::FETCH_OBJ);
        
        // Nếu không tìm thấy đơn hàng
        if(!$order) {
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] ERROR - Order not found: DH" . str_pad($pay_order_id, 4, '0', STR_PAD_LEFT) . " with amount: " . $amount_in . "\n", FILE_APPEND);
            http_response_code(200); // Vẫn trả về 200 để SePay không retry
            echo json_encode(['success' => false, 'message' => 'Order not found. ma_donhang ' . $pay_order_id]);
            die();
        }
        
        // Kiểm tra nếu đơn hàng đã thanh toán rồi thì không cần cập nhật lại
        if($order->trangthai_thanhtoan === 'da_thanh_toan') {
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] ⚠️ Order already paid: DH" . str_pad($pay_order_id, 4, '0', STR_PAD_LEFT) . "\n", FILE_APPEND);
            http_response_code(200);
            echo json_encode(['success'=>TRUE, 'message' => 'Order already paid: DH' . str_pad($pay_order_id, 4, '0', STR_PAD_LEFT)]);
            die();
        }
        
        // Tìm thấy đơn hàng, update trạng thái
        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Updating order DH" . str_pad($pay_order_id, 4, '0', STR_PAD_LEFT) . " to 'da_thanh_toan'\n", FILE_APPEND);
        
        $update_stmt = $conn->prepare("UPDATE don_hang SET trangthai_thanhtoan = 'da_thanh_toan', update_at = NOW() WHERE ma_donhang = ?");
        $update_stmt->execute([$pay_order_id]);
        
        // Xóa giỏ hàng sau khi thanh toán thành công
        $ma_user = $order->ma_user;
        $deleteCart = $conn->prepare("DELETE FROM gio_hang WHERE ma_user = ?");
        $deleteCart->execute([$ma_user]);
        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] ✓ Cart cleared for user: " . $ma_user . "\n", FILE_APPEND);
        
        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] SUCCESS - Payment confirmed for order DH" . str_pad($pay_order_id, 4, '0', STR_PAD_LEFT) . "\n", FILE_APPEND);
        
        http_response_code(200);
        echo json_encode(['success'=>TRUE, 'message' => 'Payment confirmed for order DH' . str_pad($pay_order_id, 4, '0', STR_PAD_LEFT)]);
        
    } catch(PDOException $e) {
        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] DATABASE ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
        http_response_code(200); // Vẫn trả về 200 để SePay không retry
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        die();
    }
    
    
    

?>