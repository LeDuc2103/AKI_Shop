<?php
session_start();
require_once 'config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$db = new Database();
$conn = $db->getConnection();

// Lấy mã đơn hàng từ POST
$ma_donhang = isset($_POST['ma_donhang']) ? (int)$_POST['ma_donhang'] : 0;

if ($ma_donhang <= 0) {
    $_SESSION['error_message'] = 'Mã đơn hàng không hợp lệ.';
    header('Location: my_orders.php');
    exit();
}

$transaction_started = false;
$use_transaction = method_exists($conn, 'beginTransaction');

try {
    // Kiểm tra đơn hàng có thuộc về user này không
    $stmt = $conn->prepare("SELECT * FROM don_hang WHERE ma_donhang = ? AND ma_user = ?");
    $stmt->execute(array($ma_donhang, $user_id));
    $order = $stmt->fetch();

    if (!$order) {
        $_SESSION['error_message'] = 'Không tìm thấy đơn hàng hoặc bạn không có quyền hủy đơn hàng này.';
        header('Location: my_orders.php');
        exit();
    }

    // Chỉ cho phép hủy đơn hàng COD chưa thanh toán và chưa bị hủy
    if ($order['phuongthuc_thanhtoan'] != 'cod') {
        $_SESSION['error_message'] = 'Chỉ có thể hủy đơn hàng thanh toán khi nhận hàng (COD).';
        header('Location: my_orders.php');
        exit();
    }

    if ($order['trangthai_thanhtoan'] == 'da_thanh_toan') {
        $_SESSION['error_message'] = 'Không thể hủy đơn hàng đã thanh toán.';
        header('Location: my_orders.php');
        exit();
    }

    if ($order['trang_thai'] == 'huy') {
        $_SESSION['error_message'] = 'Đơn hàng này đã bị hủy trước đó.';
        header('Location: my_orders.php');
        exit();
    }

    // Kiểm tra trạng thái đơn hàng - chỉ cho hủy nếu đang ở trạng thái chờ xử lý hoặc đã xác nhận
    if (!in_array($order['trang_thai'], array('cho_xu_ly', 'xac_nhan'))) {
        $_SESSION['error_message'] = 'Không thể hủy đơn hàng đã xuất kho hoặc đã hoàn thành.';
        header('Location: my_orders.php');
        exit();
    }

    // Bắt đầu transaction (nếu hỗ trợ)
    if ($use_transaction) {
        $conn->beginTransaction();
        $transaction_started = true;
    }

    // Cập nhật trạng thái đơn hàng thành 'huy'
    $updateOrder = $conn->prepare("UPDATE don_hang 
                                   SET trang_thai = 'huy',
                                       trangthai_thanhtoan = 'chua_thanh_toan',
                                       thanh_toan = 'đã hủy'
                                   WHERE ma_donhang = ?");
    $updateOrder->execute(array($ma_donhang));

    // Hoàn lại số lượng sản phẩm vào kho
    $stmtDetails = $conn->prepare("SELECT id_sanpham, so_luong FROM chitiet_donhang WHERE ma_donhang = ?");
    $stmtDetails->execute(array($ma_donhang));
    $order_details = $stmtDetails->fetchAll();

    foreach ($order_details as $detail) {
        // Cập nhật số lượng tồn kho (cột so_luong trong bảng san_pham)
        $updateStock = $conn->prepare("UPDATE san_pham 
                                       SET so_luong = so_luong + ? 
                                       WHERE id_sanpham = ?");
        $updateStock->execute(array($detail['so_luong'], $detail['id_sanpham']));
    }

    // Commit transaction (nếu có)
    if ($transaction_started) {
        $conn->commit();
        $transaction_started = false;
    }

    $_SESSION['success_message'] = 'Đơn hàng #' . $ma_donhang . ' đã được hủy thành công.';
    header('Location: my_orders.php');
    exit();

} catch (PDOException $e) {
    // Rollback nếu có lỗi và transaction đã bắt đầu
    if ($transaction_started && $use_transaction) {
        try {
            $conn->rollBack();
        } catch (Exception $rollbackException) {
            error_log('Rollback error: ' . $rollbackException->getMessage());
        }
    }
    
    // Log lỗi chi tiết
    $error_msg = 'Cancel order error: ' . $e->getMessage() . ' | Code: ' . $e->getCode();
    error_log($error_msg);
    
    // Hiển thị lỗi chi tiết trong development (có thể bật display_errors)
    if (ini_get('display_errors')) {
        $_SESSION['error_message'] = 'Lỗi: ' . $e->getMessage() . ' (Code: ' . $e->getCode() . ')';
    } else {
        $_SESSION['error_message'] = 'Có lỗi xảy ra khi hủy đơn hàng. Vui lòng thử lại sau.';
    }
    
    header('Location: my_orders.php');
    exit();
} catch (Exception $e) {
    // Rollback nếu có lỗi và transaction đã bắt đầu
    if ($transaction_started && $use_transaction) {
        try {
            $conn->rollBack();
        } catch (Exception $rollbackException) {
            error_log('Rollback error: ' . $rollbackException->getMessage());
        }
    }
    
    error_log('Cancel order error: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Có lỗi xảy ra khi hủy đơn hàng. Vui lòng thử lại sau.';
    header('Location: my_orders.php');
    exit();
}
?>

