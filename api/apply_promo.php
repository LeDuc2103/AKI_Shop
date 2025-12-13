<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../cart.php');
    exit;
}

$promo_code = isset($_POST['promo_code']) ? trim($_POST['promo_code']) : '';

if (empty($promo_code)) {
    // Xóa mã giảm giá
    unset($_SESSION['promo_code']);
    header('Location: ../cart.php');
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Kiểm tra mã giảm giá có tồn tại và còn hiệu lực
    $stmt = $conn->prepare("SELECT id, ten_km, phan_tram_km, ngay_bat_dau, ngay_ket_thuc FROM khuyen_mai WHERE ten_km = ?");
    $stmt->execute(array($promo_code));
    $promo = $stmt->fetch();
    
    if ($promo && (empty($promo['ngay_ket_thuc']) || strtotime($promo['ngay_ket_thuc']) >= strtotime('today'))) {
        // Lưu mã giảm giá vào session
        $_SESSION['promo_code'] = $promo_code;
        $_SESSION['promo_message'] = 'Áp dụng mã giảm giá thành công!';
    } else {
        // Mã không hợp lệ
        unset($_SESSION['promo_code']);
        $_SESSION['promo_error'] = 'Mã giảm giá không hợp lệ hoặc đã hết hạn!';
    }
    
} catch (PDOException $e) {
    $_SESSION['promo_error'] = 'Lỗi: ' . $e->getMessage();
}

header('Location: ../cart.php');
exit;
?>
