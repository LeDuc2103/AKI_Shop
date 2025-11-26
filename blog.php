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
    <title>KLTN Shop - Tin tức</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <section id="header">
        <a href="#"><img src="img/logo1.png" width="150px" class="logo" alt="KLTN Logo"></a>
        <div>
            <ul id="navbar">
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="shop.php">Sản phẩm</a></li>
                <li><a class="active" href="blog.php">Tin tức</a></li>
                <li><a href="about.php">Về chúng tôi</a></li>
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

    <section id="page-header" class="blog-header">
        <h2>#tintuc</h2>
        <p>Đọc tất cả các trường hợp về sản phẩm của chúng tôi!</p>
    </section>

    <section id="blog">
        <div class="blog-box">
            <div class="blog-img">
                <img src="img/blog/b1.jpg" alt="">
            </div>
            <div class="blog-details">
                <h4>Review Kindle Paperwhite 2024: Máy đọc sách tốt nhất?</h4>
                <p>Kindle Paperwhite 2024 mang đến nhiều cải tiến đáng kể so với thế hệ trước, từ màn hình sắc nét hơn đến thời lượng pin lâu hơn...</p>
                <a href="#">ĐỌC THÊM</a>
            </div>
            <h1>13/01</h1>
        </div>
        <div class="blog-box">
            <div class="blog-img">
                <img src="img/blog/b2.jpg" alt="">
            </div>
            <div class="blog-details">
                <h4>So sánh Boox vs Kindle: Nên chọn máy nào?</h4>
                <p>Cùng tìm hiểu sự khác biệt giữa Boox và Kindle để chọn được chiếc máy đọc sách phù hợp nhất với nhu cầu của bạn...</p>
                <a href="#">ĐỌC THÊM</a>
            </div>
            <h1>10/01</h1>
        </div>
        <div class="blog-box">
            <div class="blog-img">
                <img src="img/blog/b3.jpg" alt="">
            </div>
            <div class="blog-details">
                <h4>Cách chọn máy đọc sách phù hợp cho người mới bắt đầu</h4>
                <p>Bài viết hướng dẫn chi tiết cách chọn máy đọc sách cho người mới, từ kích thước màn hình đến các tính năng cần thiết...</p>
                <a href="#">ĐỌC THÊM</a>
            </div>
            <h1>05/01</h1>
        </div>
        <div class="blog-box">
            <div class="blog-img">
                <img src="img/blog/b4.jpg" alt="">
            </div>
            <div class="blog-details">
                <h4>Xu hướng đọc sách điện tử năm 2025</h4>
                <p>Khám phá những xu hướng mới trong việc đọc sách điện tử và cách công nghệ đang thay đổi thói quen đọc của chúng ta...</p>
                <a href="#">ĐỌC THÊM</a>
            </div>
            <h1>01/01</h1>
        </div>
        <div class="blog-box">
            <div class="blog-img">
                <img src="img/blog/b6.jpg" alt="">
            </div>
            <div class="blog-details">
                <h4>Tips bảo quản máy đọc sách để sử dụng lâu dài</h4>
                <p>Những mẹo hay giúp bạn bảo quản máy đọc sách đúng cách, kéo dài tuổi thọ thiết bị và duy trì chất lượng hiển thị...</p>
                <a href="#">ĐỌC THÊM</a>
            </div>
            <h1>28/12</h1>
        </div>
    </section>

    <section id="pagination" class="section-p1">
        <a href="#">1</a>
        <a href="#">2</a>
        <a href="#"><i class="fa fa-long-arrow-alt-right"></i></a>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="script.js"></script>
</body>
</html>