<?php
/**
 * SePay Payment Handler - Tạo đơn hàng và redirect đến trang QR
 */
session_start();
require_once 'config/database.php';

// Check login
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$ma_user = $_SESSION['user_id'];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get cart items
    $stmt = $conn->prepare("
        SELECT gh.*, sp.ten_sanpham, sp.gia, sp.hinh_anh, sp.so_luong as stock
        FROM gio_hang gh
        JOIN san_pham sp ON gh.id_sanpham = sp.id_sanpham
        WHERE gh.ma_user = :ma_user
    ");
    $stmt->execute(array(':ma_user' => $ma_user));
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($cart_items)) {
        header("Location: cart.php");
        exit();
    }
    
    // Calculate total
    $tong_thanhtoan = 0;
    foreach ($cart_items as $item) {
        $tong_thanhtoan += $item['gia'] * $item['so_luong'];
    }
    
    // Get user info
    $stmt_user = $conn->prepare("SELECT * FROM user WHERE ma_user = :ma_user");
    $stmt_user->execute(array(':ma_user' => $ma_user));
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        die("Không tìm thấy thông tin người dùng!");
    }
    
    // Generate unique order code (4 digits)
    do {
        $order_code = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM don_hang WHERE order_code = :order_code");
        $check_stmt->execute(array(':order_code' => $order_code));
        $exists = $check_stmt->fetchColumn();
    } while ($exists > 0);
    
    // Create order
    $stmt_order = $conn->prepare("
        INSERT INTO don_hang (
            ma_user, ten_nguoinhan, email_nguoinhan, so_dienthoai, diachi_nhan, 
            tong_tien, phuongthuc_thanhtoan, trangthai_thanhtoan, 
            trang_thai, thanh_toan, order_code, created_at, tien_hang, tien_ship
        ) VALUES (
            :ma_user, :ten_nguoinhan, :email_nguoinhan, :so_dienthoai, :diachi_nhan,
            :tong_tien, 'SePay QR', 'chua_thanh_toan',
            'cho_xu_ly', 'chưa thanh toán', :order_code, NOW(), :tien_hang, 15000
        )
    ");
    
    $stmt_order->execute(array(
        ':ma_user' => $ma_user,
        ':ten_nguoinhan' => $user['ho_ten'],
        ':email_nguoinhan' => $user['email'],
        ':so_dienthoai' => isset($user['phone']) ? $user['phone'] : '',
        ':diachi_nhan' => isset($user['dia_chi']) ? $user['dia_chi'] : '',
        ':tong_tien' => $tong_thanhtoan + 15000,
        ':tien_hang' => $tong_thanhtoan,
        ':order_code' => $order_code
    ));
    
    $ma_donhang = $conn->lastInsertId();
    
    // Create order details and deduct stock
    foreach ($cart_items as $item) {
        // Insert order detail
        $stmt_detail = $conn->prepare("
            INSERT INTO chitiet_donhang (ma_donhang, id_sanpham, so_luong, don_gia)
            VALUES (:ma_donhang, :id_sanpham, :so_luong, :don_gia)
        ");
        $stmt_detail->execute(array(
            ':ma_donhang' => $ma_donhang,
            ':id_sanpham' => $item['id_sanpham'],
            ':so_luong' => $item['so_luong'],
            ':don_gia' => $item['gia']
        ));
        
        // Deduct stock
        $stmt_stock = $conn->prepare("
            UPDATE san_pham 
            SET so_luong = so_luong - :quantity 
            WHERE id_sanpham = :id_sanpham
        ");
        $stmt_stock->execute(array(
            ':quantity' => $item['so_luong'],
            ':id_sanpham' => $item['id_sanpham']
        ));
    }
    
    // Create transaction record - Format: "DH" + order_code
    $transaction_content = "DH" . $order_code;
    $stmt_trans = $conn->prepare("
        INSERT INTO transactions (
            ma_donhang, account_number, transaction_content, 
            created_at, is_processed
        ) VALUES (
            :ma_donhang, '0981523130', :content, NOW(), 0
        )
    ");
    $stmt_trans->execute(array(
        ':ma_donhang' => $ma_donhang,
        ':content' => $transaction_content
    ));
    
    // Redirect to SePay payment page
    header("Location: sepay-php-main/payment_page.php?order_code=" . $order_code . "&amount=" . $tong_thanhtoan);
    exit();
    
} catch (Exception $e) {
    die("Lỗi: " . $e->getMessage());
}
?>
