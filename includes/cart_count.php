<?php
// File: includes/cart_count.php
// Lấy số lượng sản phẩm trong giỏ hàng

$cart_count = 0;

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] && isset($_SESSION['user_id'])) {
    try {
        if (!isset($conn)) {
            require_once 'config/database.php';
            $db = new Database();
            $conn = $db->getConnection();
        }
        
        $stmt = $conn->prepare("SELECT SUM(so_luong) as total FROM gio_hang WHERE ma_user = ?");
        $stmt->execute(array($_SESSION['user_id']));
        $result = $stmt->fetch();
        $cart_count = $result['total'] ? (int)$result['total'] : 0;
    } catch (PDOException $e) {
        $cart_count = 0;
    }
}
?>
