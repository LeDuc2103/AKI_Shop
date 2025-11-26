<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../config/database.php';

$response = array('status' => 'error', 'message' => 'Có lỗi xảy ra');

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response['message'] = 'Phương thức không hợp lệ';
        echo json_encode($response);
        exit;
    }
    
    $cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
    
    if ($cart_id <= 0) {
        $response['message'] = 'ID giỏ hàng không hợp lệ';
        echo json_encode($response);
        exit;
    }
    
    // Kiểm tra user_id từ session
    if (!isset($_SESSION['user_id'])) {
        $response['message'] = 'Vui lòng đăng nhập';
        echo json_encode($response);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Xóa sản phẩm (chỉ xóa sản phẩm của user hiện tại)
    $sql_delete = "DELETE FROM gio_hang WHERE id_giohang = ? AND ma_user = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->execute(array($cart_id, $user_id));
    
    if ($stmt->rowCount() > 0) {
        $response['status'] = 'success';
        $response['message'] = 'Đã xóa sản phẩm khỏi giỏ hàng';
    } else {
        $response['message'] = 'Không tìm thấy sản phẩm hoặc bạn không có quyền xóa';
    }
    
} catch (PDOException $e) {
    $response['message'] = 'Lỗi database: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = 'Lỗi: ' . $e->getMessage();
}

echo json_encode($response);
?>
