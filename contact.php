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
    <title>KLTN Shop - Liên hệ</title>
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
                <li><a href="about.php">Về chúng tôi</a></li>
                <li><a class="active" href="contact.php">Liên hệ</a></li>
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
        <h2>#lienhe</h2>
        <p>Liên hệ với chúng tôi để được hỗ trợ</p>
    </section>

    <section id="contact-details" class="section-p1">
        <div class="details">
            <span>LIÊN HỆ VỚI CHÚNG TÔI</span>
            <h2>Ghé thăm một trong các cửa hàng hoặc liên hệ ngay hôm nay</h2>
            <h3>Trụ sở chính</h3>
            <div>
                <li>
                    <i class="fal fa-map"></i>
                    <p>124 Lê Quang Định, phường Bình Thạnh, TP.HCM, Việt Nam</p>
                </li>
                <li>
                    <i class="far fa-envelope"></i>
                    <p>contact@kltnshop.com</p>
                </li>
                <li>
                    <i class="fas fa-phone-alt"></i>
                    <p>+84 123 456 789</p>
                </li>
                <li>
                    <i class="far fa-clock"></i>
                    <p>Thứ 2 đến Thứ 7: 8:00 - 17:00</p>
                </li>
            </div>
        </div>
        <div class="map">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3918.858131704436!2d106.69174081533292!3d10.798859561797!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317529292e8d3dd1%3A0xf15f5aad773c112b!2zVHLGsOG7nW5nIMSR4bqhaSBo4buNYyDEkGnhu4duIEzhu7Fj!5e0!3m2!1svi!2s!4v1609765766860!5m2!1svi!2s" width="600" height="450" frameborder="0" style="border:0;" allowfullscreen="" aria-hidden="false" tabindex="0"></iframe>
        </div>
    </section>

    <section id="form-details">
        <form action="">
            <span>GỬI TIN NHẮN</span>
            <h2>Chúng tôi rất mong nhận được ý kiến từ bạn</h2>
            <input type="text" placeholder="Họ và tên của bạn">
            <input type="text" placeholder="E-mail">
            <input type="text" placeholder="Tiêu đề">
            <textarea name="" id="" cols="30" rows="10" placeholder="Nội dung tin nhắn của bạn"></textarea>
            <button class="normal">Gửi</button>
        </form>

        <div class="people">
            <div>
                <img src="img/people/1.png" alt="">
                <p><span>Lê Văn Túc</span> Senior Marketing Manager <br> Điện thoại: +84 123 456 789 <br> Email: tuc@kltnshop.com</p>
            </div>
            <div>
                <img src="img/people/2.png" alt="">
                <p><span>Huỳnh Đình Chiểu</span> Senior Marketing Manager <br> Điện thoại: +84 987 654 321 <br> Email: chieu@kltnshop.com</p>
            </div>
            <div>
                <img src="img/people/3.png" alt="">
                <p><span>Nguyễn Văn A</span> Senior Marketing Manager <br> Điện thoại: +84 555 666 777 <br> Email: a@kltnshop.com</p>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="script.js"></script>
</body>
</html>