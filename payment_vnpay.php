<?php
session_start();
require_once 'config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header('Location: login.php');
    exit();
}

// Khởi tạo kết nối DB
$db = new Database();
$conn = $db->getConnection();

$user_id = $_SESSION['user_id'];

// Lấy thông tin user
$stmtUser = $conn->prepare("SELECT * FROM user WHERE ma_user = ?");
$stmtUser->execute(array($user_id));
$user = $stmtUser->fetch();

// Lấy giỏ hàng hiện tại
$cart_items = array();
$tong_tien_hang = 0;
$tien_ship = 30000;
$discount_amount = 0;
$promo_code = '';

// Lấy thông tin từ session nếu có
if (isset($_SESSION['cart_summary'])) {
    $cart_summary = $_SESSION['cart_summary'];
    $tong_tien_hang = $cart_summary['subtotal'];
    $tien_ship = $cart_summary['shipping_fee'];
    $discount_amount = $cart_summary['discount_amount'];
    $promo_code = $cart_summary['promo_code'];
}

$sqlCart = "SELECT 
                g.id_giohang,
                g.id_sanpham,
                g.so_luong,
                g.thanh_tien,
                s.ten_sanpham,
                s.gia,
                s.gia_khuyen_mai
            FROM gio_hang g
            INNER JOIN san_pham s ON g.id_sanpham = s.id_sanpham
            WHERE g.ma_user = ?
            ORDER BY g.created_at DESC";

$stmtCart = $conn->prepare($sqlCart);
$stmtCart->execute(array($user_id));
$cart_items = $stmtCart->fetchAll();

if (!isset($_SESSION['cart_summary'])) {
    foreach ($cart_items as $item) {
        $tong_tien_hang += $item['thanh_tien'];
    }
}

// Nếu giỏ hàng trống thì quay lại trang giỏ hàng
if (empty($cart_items)) {
    header('Location: cart.php');
    exit();
}

// Tính tổng tiền (đã trừ giảm giá)
$tong_tien = $tong_tien_hang + $tien_ship - $discount_amount;

// Tạo đơn hàng trong bảng don_hang
$ten_nguoinhan   = $user ? $user['ho_ten'] : 'Khách hàng';
$diachi_nhan     = $user ? $user['dia_chi'] : '';
$email_nguoinhan = $user ? $user['email'] : '';
$so_dienthoai    = $user ? $user['phone'] : '';

$sqlOrder = "INSERT INTO don_hang 
    (ten_nguoinhan, diachi_nhan, email_nguoinhan, so_dienthoai, 
     trangthai_thanhtoan, phuongthuc_thanhtoan, thanh_toan,
     tien_hang, tien_ship, tong_tien, ma_user, trang_thai)
    VALUES
    (:ten_nguoinhan, :diachi_nhan, :email_nguoinhan, :so_dienthoai,
     'chua_thanh_toan', 'vnpay', 'chưa thanh toán',
     :tien_hang, :tien_ship, :tong_tien, :ma_user, 'cho_xu_ly')";

$stmtOrder = $conn->prepare($sqlOrder);
$stmtOrder->execute(array(
    ':ten_nguoinhan'   => $ten_nguoinhan,
    ':diachi_nhan'     => $diachi_nhan,
    ':email_nguoinhan' => $email_nguoinhan,
    ':so_dienthoai'    => $so_dienthoai,
    ':tien_hang'       => $tong_tien_hang,
    ':tien_ship'       => $tien_ship,
    ':tong_tien'       => $tong_tien,
    ':ma_user'         => $user_id
));

// Lưu ghi chú về mã khuyến mãi đã áp dụng (nếu có)
if (!empty($promo_code)) {
    // Có thể thêm vào ghi chú hoặc bảng riêng nếu cần
    // Tạm thời lưu vào session để tracking
    $_SESSION['order_promo_applied'] = $promo_code;
}

// Lấy ma_donhang (primary key của bảng don_hang) từ lastInsertId
$ma_donhang = $conn->lastInsertId();

// Lưu chi tiết đơn hàng vào bảng chitiet_donhang và trừ số lượng sản phẩm
$sqlDetail = "INSERT INTO chitiet_donhang (ma_donhang, id_sanpham, so_luong, don_gia)
              VALUES (:ma_donhang, :id_sanpham, :so_luong, :don_gia)";
$stmtDetail = $conn->prepare($sqlDetail);

// Cập nhật số lượng sản phẩm
$updateStock = $conn->prepare("UPDATE san_pham SET so_luong = so_luong - ? WHERE id_sanpham = ?");

foreach ($cart_items as $item) {
    $don_gia = $item['so_luong'] > 0 ? ($item['thanh_tien'] / $item['so_luong']) : 0;
    $stmtDetail->execute(array(
        ':ma_donhang' => $ma_donhang,
        ':id_sanpham' => $item['id_sanpham'],
        ':so_luong'   => $item['so_luong'],
        ':don_gia'    => $don_gia
    ));
    
    // Trừ số lượng sản phẩm trong kho
    $updateStock->execute(array($item['so_luong'], $item['id_sanpham']));
}

// KHÔNG xóa giỏ hàng ngay - chỉ xóa khi thanh toán thành công trong vnpay_return.php
// Nếu khách hàng không thanh toán, họ có thể quay lại giỏ hàng

// Chuẩn bị dữ liệu gửi sang VNPay
// VNPay yêu cầu vnp_TxnRef là string, nên convert ma_donhang sang string
$order_id = (string)$ma_donhang; // order_id để gửi sang VNPay (phải là string)
$order_desc = "Thanh toan don hang #" . $ma_donhang;
$order_type = "other";
$amount     = (int)$tong_tien; // VNĐ - Đảm bảo là số nguyên
$language   = "vn";
$bank_code  = "";
$expire     = date('YmdHis', strtotime('+30 minutes')); // Tăng lên 30 phút

// Thông tin billing / hóa đơn gửi tối thiểu
$billing_fullname = $ten_nguoinhan;
$billing_mobile   = $so_dienthoai;
$billing_email    = $email_nguoinhan;
$billing_address  = $diachi_nhan;

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đang chuyển tới cổng thanh toán VNPay...</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/responsive.css?v=1765636814">
</head>
<body>
    <p>Đang chuyển tới cổng thanh toán VNPay, vui lòng chờ trong giây lát...</p>

    <form id="vnpay_form" method="post" action="vnpay_php/vnpay_create_payment.php">
        <!-- order_id = ma_donhang (bigint) từ bảng don_hang, convert sang string cho VNPay -->
        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($ma_donhang); ?>">
        <input type="hidden" name="order_desc" value="<?php echo htmlspecialchars($order_desc); ?>">
        <input type="hidden" name="order_type" value="<?php echo htmlspecialchars($order_type); ?>">
        <input type="hidden" name="amount" value="<?php echo htmlspecialchars($amount); ?>">
        <input type="hidden" name="language" value="<?php echo htmlspecialchars($language); ?>">
        <input type="hidden" name="bank_code" value="<?php echo htmlspecialchars($bank_code); ?>">
        <input type="hidden" name="txtexpire" value="<?php echo htmlspecialchars($expire); ?>">

        <input type="hidden" name="txt_billing_fullname" value="<?php echo htmlspecialchars($billing_fullname); ?>">
        <input type="hidden" name="txt_billing_mobile" value="<?php echo htmlspecialchars($billing_mobile); ?>">
        <input type="hidden" name="txt_billing_email" value="<?php echo htmlspecialchars($billing_email); ?>">
        <input type="hidden" name="txt_inv_addr1" value="<?php echo htmlspecialchars($billing_address); ?>">
        <input type="hidden" name="txt_bill_city" value="Ho Chi Minh">
        <input type="hidden" name="txt_bill_country" value="VN">
        <input type="hidden" name="txt_bill_state" value="">

        <input type="hidden" name="txt_inv_mobile" value="<?php echo htmlspecialchars($billing_mobile); ?>">
        <input type="hidden" name="txt_inv_email" value="<?php echo htmlspecialchars($billing_email); ?>">
        <input type="hidden" name="txt_inv_customer" value="<?php echo htmlspecialchars($billing_fullname); ?>">
        <input type="hidden" name="txt_inv_company" value="KLTN Shop">
        <input type="hidden" name="txt_inv_taxcode" value="">
        <input type="hidden" name="cbo_inv_type" value="I">

        <input type="hidden" name="redirect" value="1">
    </form>

    <script>
        document.getElementById('vnpay_form').submit();
    </script>
    <script src="js/mobile-responsive.js?v=1765636814"></script>
</body>
</html>
