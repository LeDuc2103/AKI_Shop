<?php
header('Content-Type: application/json');
require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get orders with status 'chua_thanh_toan'
    $stmt = $conn->prepare("
        SELECT ma_donhang, tong_tien, trangthai_thanhtoan, created_at 
        FROM don_hang 
        WHERE phuongthuc_thanhtoan = 'SePay'
        ORDER BY ma_donhang DESC 
        LIMIT 20
    ");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($orders);
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
