<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

// Lấy order_id từ GET
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid order ID']);
    exit();
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Kiểm tra trạng thái đơn hàng và transaction
    $sql = "SELECT 
                dh.ma_donhang,
                dh.trangthai_thanhtoan,
                dh.trang_thai,
                dh.tong_tien,
                t.id as transaction_id,
                t.is_processed,
                t.transaction_date,
                t.amount_in
            FROM don_hang dh
            LEFT JOIN transactions t ON dh.ma_donhang = t.ma_donhang
            WHERE dh.ma_donhang = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute(array($order_id));
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(['status' => 'error', 'message' => 'Order not found']);
        exit();
    }

    // Kiểm tra xem đã thanh toán chưa
    $isPaid = false;
    if ($order['trangthai_thanhtoan'] === 'da_thanh_toan' || $order['is_processed'] == 1) {
        $isPaid = true;
    }

    echo json_encode([
        'status' => 'success',
        'paid' => $isPaid,
        'order_status' => $order['trang_thai'],
        'payment_status' => $order['trangthai_thanhtoan'],
        'amount' => $order['tong_tien'],
        'transaction_processed' => $order['is_processed']
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
