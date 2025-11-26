<?php
session_start();
require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();

// Lấy số lượng giỏ hàng
include_once 'includes/cart_count.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <title>KLTN Shop - Về chúng tôi</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <section id="header">
        <a href="#"><img src="img/logo1.png" width="150px" class="logo" alt="KLTN Logo"></a>
        <div>
            <ul id="navbar">
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="shop.php">Sản phẩm</a></li>
                <li><a href="blog.php">Tin tức</a></li>
                <li><a class="active" href="about.php">Về chúng tôi</a></li>
                <li><a href="contact.php">Liên hệ</a></li>
                <li id="search-icon"><a href="#"><i class="fa-solid fa-search"></i></a></li>
                <li id="user-icon" tabindex="0">
                    <a href="#" tabindex="-1"><i class="fa-solid fa-user"></i></a>
                    <div class="user-dropdown">
                        <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                            <a href="#">Xin chào, <?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
                            <?php if (in_array($_SESSION['user_role'], array('admin', 'quanly', 'nhanvien', 'nhanvienkho'))): ?>
                                <a href="admin.php">Quản trị viên</a>
                            <?php endif; ?>
                            <a href="logout.php">Đăng xuất</a>
                        <?php else: ?>
                            <a href="login.php">Đăng Nhập</a>
                            <a href="register.php">Đăng Ký</a>
                        <?php endif; ?>
                    </div>
                </li>
                <li id="lg-bag">
                    <a href="cart.php" style="position: relative;">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-badge"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <a href="#" id="close"><i class="fa-solid fa-xmark"></i></a>    
            </ul> 
        <div id="mobile">
            <a href="cart.php" style="position: relative;">
                <i class="fa-solid fa-cart-shopping"></i>
                <?php if ($cart_count > 0): ?>
                    <span class="cart-badge"><?php echo $cart_count; ?></span>
                <?php endif; ?>
            </a>
            <i id="bar" class="fas fa-outdent"></i>
        </div>  
        </section>

    <section id="page-header" class="about-header">
        <h2>#vechungtoi</h2>
        <p>Tìm hiểu thêm về KLTN Shop</p>
    </section>

    <section id="about-head" class="section-p1">
        <img src="img/about/a6.jpg" alt="">
        <div>
            <h2>Chúng tôi là ai?</h2>
            <p>KLTN Shop là cửa hàng chuyên bán các thiết bị đọc sách điện tử hàng đầu tại Việt Nam. Chúng tôi cam kết mang đến cho khách hàng những sản phẩm chất lượng cao với giá cả hợp lý.</p>
            
            <abbr title="">Tạo ra trải nghiệm mua sắm tuyệt vời nhất cho khách hàng là sứ mệnh của chúng tôi.</abbr>
            
            <br><br>
            
            <marquee bgcolor="#ccc" loop="-1" scrollamount="5" width="100%">
                Chúng tôi luôn đặt khách hàng lên hàng đầu và không ngừng cải thiện chất lượng dịch vụ.
            </marquee>
        </div>
    </section>

    <section id="about-app" class="section-p1">
        <h1>Tải ứng dụng <a href="#">KLTN Shop</a></h1>
        <div class="video">
            <video autoplay muted loop src="img/about/1.mp4"></video>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="script.js"></script>
</body>
</html>