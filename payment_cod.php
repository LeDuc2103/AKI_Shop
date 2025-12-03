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

foreach ($cart_items as $item) {
    $tong_tien_hang += $item['thanh_tien'];
}

// Nếu giỏ hàng trống thì quay lại trang giỏ hàng
if (empty($cart_items)) {
    header('Location: cart.php');
    exit();
}

// Thiết lập phí ship và tổng tiền
$tien_ship = 15000; // có thể cấu hình sau
$tong_tien = $tong_tien_hang + $tien_ship;

// Tạo đơn hàng trong bảng don_hang
// Lấy thông tin từ user (có thể mở rộng để lấy từ form invoice sau)
$ten_nguoinhan   = $user ? $user['ho_ten'] : 'Khách hàng';
$diachi_nhan     = $user ? $user['dia_chi'] : '';
$email_nguoinhan = $user ? $user['email'] : '';
$so_dienthoai    = $user ? $user['phone'] : '';

// Tạo đơn hàng trong bảng don_hang
$sqlOrder = "INSERT INTO don_hang 
    (ten_nguoinhan, diachi_nhan, email_nguoinhan, so_dienthoai, 
     trangthai_thanhtoan, phuongthuc_thanhtoan, thanh_toan,
     tien_hang, tien_ship, tong_tien, ma_user, trang_thai)
    VALUES
    (:ten_nguoinhan, :diachi_nhan, :email_nguoinhan, :so_dienthoai,
     'chua_thanh_toan', 'cod', 'chưa thanh toán',
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

$ma_donhang = $conn->lastInsertId();

// Lưu chi tiết đơn hàng vào bảng chitiet_donhang
$sqlDetail = "INSERT INTO chitiet_donhang (ma_donhang, id_sanpham, so_luong, don_gia)
              VALUES (:ma_donhang, :id_sanpham, :so_luong, :don_gia)";
$stmtDetail = $conn->prepare($sqlDetail);

foreach ($cart_items as $item) {
    $don_gia = $item['so_luong'] > 0 ? ($item['thanh_tien'] / $item['so_luong']) : 0;
    $stmtDetail->execute(array(
        ':ma_donhang' => $ma_donhang,
        ':id_sanpham' => $item['id_sanpham'],
        ':so_luong'   => $item['so_luong'],
        ':don_gia'    => $don_gia
    ));
}

// Xóa giỏ hàng sau khi tạo đơn hàng thành công
$deleteCart = $conn->prepare("DELETE FROM gio_hang WHERE ma_user = ?");
$deleteCart->execute(array($user_id));

// Lấy số lượng giỏ hàng (sẽ = 0 sau khi xóa)
include_once 'includes/cart_count.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt hàng thành công - KLTN Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .success-container {
            max-width: 600px;
            margin: 100px auto;
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
        }
        
        .success-container h1 {
            color: #088178;
            margin-bottom: 15px;
        }
        
        .success-container p {
            font-size: 18px;
            line-height: 1.8;
            color: #666;
            margin-bottom: 30px;
        }
        
        .order-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: left;
        }
        
        .order-info p {
            margin: 10px 0;
            font-size: 16px;
        }
        
        .btn-group {
            margin-top: 30px;
        }
        
        .btn-group a {
            display: inline-block;
            padding: 12px 30px;
            margin: 10px;
            background: #088178;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .btn-group a:hover {
            background: #066d63;
        }
        
        .btn-secondary {
            background: #6c757d !important;
        }
        
        .btn-secondary:hover {
            background: #5a6268 !important;
        }
    </style>
</head>
<body>
    <section id="header">
        <a href="index.php"><img src="img/logo1.png" width="150px" class="logo" alt=""></a>
        <div>
            <ul id="navbar">
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="shop.php">Sản phẩm</a></li>
                <li><a href="blog.php">Tin tức</a></li>
                <li><a href="about.php">Về chúng tôi</a></li>
                <li><a href="contact.php">Liên hệ</a></li>
                <li id="search-icon"><a href="#"><i class="fa-solid fa-search"></i></a></li>
                <li id="user-icon" tabindex="0">
                    <a href="#" tabindex="-1"><i class="fa-solid fa-user"></i></a>
                    <div class="user-dropdown">
                        <a href="#">Xin chào, <?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
                        <a href="my_orders.php">Đơn hàng của tôi</a>
                        <?php if (in_array($_SESSION['user_role'], array('admin', 'quanly', 'nhanvien', 'nhanvienkho'))): ?>
                            <a href="admin.php">Quản trị viên</a>
                        <?php endif; ?>
                        <a href="logout.php">Đăng xuất</a>
                    </div>
                </li>
                <li id="lg-bag"><a href="cart.php"><i class="fa-solid fa-cart-shopping"></i></a></li>
                <a href="#" id="close"><i class="fa-solid fa-xmark"></i></a>    
            </ul> 
            <div id="mobile">
                <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i></a>
                <i id="bar" class="fas fa-outdent"></i>
            </div>  
        </div>
    </section>

    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1>Đặt hàng thành công!</h1>
        
        <p>Cảm ơn bạn đã đặt hàng tại KLTN Shop.<br>
        Đơn hàng của bạn đã được tiếp nhận và đang được xử lý.</p>
        
        <div class="order-info">
            <p><strong>Mã đơn hàng:</strong> #<?php echo $ma_donhang; ?></p>
            <p><strong>Phương thức thanh toán:</strong> Thanh toán khi nhận hàng (COD)</p>
            <p><strong>Trạng thái:</strong> Đang xử lý</p>
            <p><strong>Tổng tiền:</strong> <?php echo number_format($tong_tien, 0, ',', '.'); ?> VNĐ</p>
            <p><strong>Thời gian giao hàng dự kiến:</strong> 3-5 ngày làm việc</p>
        </div>
        
        <p>Chúng tôi sẽ liên hệ với bạn qua email hoặc số điện thoại<br>
        để xác nhận đơn hàng trong thời gian sớm nhất.</p>
        
        <div class="btn-group">
            <a href="my_orders.php">Xem đơn hàng của tôi</a>
            <a href="index.php" class="btn-secondary">Về trang chủ</a>
            <a href="shop.php" class="btn-secondary">Tiếp tục mua sắm</a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="script.js"></script>
</body>
</html>
