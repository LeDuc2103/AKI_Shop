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
    <title>AKISTORE - Về chúng tôi</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        /* About Page Styles */
        .about-hero {
            background-image: url('img/banner/abbner.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            /* color: white; */
            text-align: center;
            padding: 80px 20px;
            margin-bottom: 60px;
            position: relative;
            height: 300px;;
        }
        
        .about-hero h1,
        .about-hero p {
            position: relative;
            z-index: 2;
        }
        
        .about-hero h1 {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            color: white;
        }
        
        .about-hero p {
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.8;
            color: white;
        }
        
        .about-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 60px;
        }
        
        .about-section {
            margin-bottom: 60px;
        }
        
        .about-section h2 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #667eea;
            display: inline-block;
        }
        
        .about-section p {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #555;
            margin-bottom: 20px;
        }
        
        .video-container {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
            height: 0;
            margin: 30px 0;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        
        .about-image {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            margin: 20px 0;
            transition: transform 0.3s ease;
        }
        
        .about-image:hover {
            transform: scale(1.02);
        }
        
        .image-caption {
            text-align: center;
            font-style: italic;
            color: #666;
            margin-top: 10px;
            margin-bottom: 30px;
            font-size: 0.95rem;
        }
        
        .timeline {
            position: relative;
            padding: 20px 0;
        }
        
        .timeline-item {
            padding-left: 40px;
            margin-bottom: 30px;
            position: relative;
        }
        
        .timeline-item:before {
            content: '';
            position: absolute;
            left: 10px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #667eea;
        }
        
        .timeline-item:after {
            content: '';
            position: absolute;
            left: 15px;
            top: 17px;
            width: 2px;
            height: calc(100% + 10px);
            background: #e0e0e0;
        }
        
        .timeline-item:last-child:after {
            display: none;
        }
        
        .product-brands {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }
        
        .brand-card {
            text-align: center;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .brand-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .brand-card img {
            max-width: 200px;
            height: auto;
            margin-bottom: 15px;
        }
        
        .contact-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 10px;
            margin: 40px 0;
        }
        
        .contact-info h3 {
            font-size: 1.8rem;
            margin-bottom: 20px;
        }
        
        .contact-info p {
            font-size: 1.1rem;
            margin-bottom: 15px;
            color: white;
        }
        
        .contact-info a {
            color: #fff;
            text-decoration: none;
            border-bottom: 1px solid rgba(255,255,255,0.5);
            transition: border-color 0.3s;
        }
        
        .contact-info a:hover {
            border-color: #fff;
        }
        
        .links-section {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            margin: 30px 0;
        }
        
        .links-section h3 {
            color: #333;
            margin-bottom: 20px;
        }
        
        .links-section ul {
            list-style: none;
            padding: 0;
        }
        
        .links-section ul li {
            margin-bottom: 10px;
        }
        
        .links-section ul li a {
            color: #667eea;
            text-decoration: none;
            font-size: 1.05rem;
            transition: color 0.3s;
        }
        
        .links-section ul li a:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        .tagline {
            text-align: center;
            font-size: 1.8rem;
            font-weight: 700;
            color: #667eea;
            margin: 50px 0 30px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        @media (max-width: 768px) {
            .about-hero h1 {
                font-size: 2rem;
            }
            
            .about-hero p {
                font-size: 1rem;
            }
            
            .about-section h2 {
                font-size: 1.5rem;
            }
            
            .product-brands {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <section id="header">
        <a href="index.php"><img src="img/logo1.png" width="150px" class="logo" alt="KLTN Logo"></a>
        <div>
            <ul id="navbar">
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="shop.php">Sản phẩm</a></li>
                <li><a href="blog.php">Tin tức</a></li>
                <li><a class="active" href="about.php">Về chúng tôi</a></li>
                <li><a href="contact.php">Liên hệ</a></li>
                <li id="search-icon"><a href="#" onclick="toggleSearch(event)"><i class="fa-solid fa-search"></i></a></li>
                <li id="user-icon" tabindex="0">
                    <a href="#" tabindex="-1"><i class="fa-solid fa-user"></i></a>
                    <div class="user-dropdown">
                        <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                            <a href="#">Xin chào, <?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
                            <a href="my_orders.php">Đơn hàng của tôi</a>
                            <?php 
                            $user_role = isset($_SESSION['user_role']) ? strtolower(trim($_SESSION['user_role'])) : '';
                            if ($user_role === 'quanly'): 
                            ?>
                                <a href="admin.php">Quản trị viên</a>
                            <?php elseif ($user_role === 'nhanvien'): ?>
                                <a href="nhanvienbanhang.php">Quản trị viên</a>
                            <?php elseif ($user_role === 'nhanvienkho'): ?>
                                <a href="nhanvienkho.php">Quản trị viên</a>
                            <?php endif; ?>
                            <a href="logout.php">Đăng xuất</a>
                        <?php else: ?>
                            <a href="login.php">Đăng Nhập</a>
                            <a href="register.php">Đăng Ký</a>
                        <?php endif; ?>
                    </div>
                </li>
                <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                <?php endif; ?>
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

    <!-- Hero Section -->
    <div class="about-hero">
        
    </div>

    <!-- Main Content -->
    <div class="about-container">
        
        <!-- Intro Text -->
        <div class="about-section">
            <h2>AKISTORE - HỆ THỐNG MÁY ĐỌC SÁCH SỐ 1 VIỆT NAM</h2>
            <p style="font-size: 1.1rem; line-height: 1.8; color: #555; margin-bottom: 20px;">Chào mừng bạn đến với Akishop, đơn vị tiên phong trong việc đưa công nghệ E-ink vào thị trường Việt Nam. Với hơn 8 năm hoạt động, Akishop đã trở thành nhà phân phối uy tín hàng đầu cho các sản phẩm máy đọc sách chính hãng, góp phần nâng cao văn hóa đọc cho người Việt thông qua việc cung cấp các sản phẩm công nghệ tiên tiến, thân thiện với mắt.</p>
        </div>
        
        <!-- Video Section -->
        <div class="about-section">
            <div class="video-container">
                <iframe src="https://www.youtube.com/embed/WJ_982Szg9o" 
                        title="AKISTORE Video" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                </iframe>
            </div>
        </div>

        <!-- Lịch Sử Section -->
        <div class="about-section">
            <h2>Lịch Sử Hình Thành và Phát Triển</h2>
            
            <div class="timeline">
                <div class="timeline-item">
                    <p><strong>2016:</strong> Akishop được thành lập vào năm 2016, và chỉ sau 2 năm, chúng tôi đã trở thành nhà phân phối độc quyền của Boox tại Việt Nam.</p>
                </div>
            </div>
            
            <img src="img/banner/about1.jpg" alt="Akishop History" class="about-image">
            
            <div class="timeline">
                <div class="timeline-item">
                    <p><strong>2021:</strong> Năm 2021 đánh dấu bước phát triển mạnh mẽ khi Akishop mở rộng mạng lưới phân phối với hai cửa hàng mới tại Hà Nội và TP. Hồ Chí Minh. Cũng trong năm này, Akishop tự hào trở thành đơn vị cung cấp quà tặng cho Đại hội Kỷ niệm 90 năm ngày thành lập Đoàn Thanh niên Cộng sản Hồ Chí Minh.</p>
                </div>
                
                <div class="timeline-item">
                    <p><strong>2023:</strong> Đến năm 2023, Akishop tổ chức thành công hai sự kiện ra mắt sản phẩm mới của Boox "Super Refresh" giới thiệu các sản phẩm như Boox Palma, Boox Tab Ultra C Pro, Boox Note Air3 C. Từ cuối năm 2023, Akishop bắt đầu chính thức có mặt nhiều hơn trong các sự kiện dành cho các doanh nghiệp uy tín trên toàn quốc.</p>
                </div>
            </div>
            
            <img src="img/banner/about2.jpg" alt="Super Refresh Event" class="about-image">
            <p class="image-caption">Sự kiện trải nghiệm "Super Refresh" do Akishop tổ chức tại TP.HCM</p>
            
            <img src="img/banner/about3.jpg" alt="Akishop at Event" class="about-image">
            <p class="image-caption">Akishop tại Đại hội Đại biểu Hội Doanh nhân trẻ TP. HCM</p>
        </div>

        <!-- Giá Trị & Triết Lý Section -->
        <div class="about-section">
            <h2>Giá Trị & Triết Lý Thương Hiệu</h2>
            <p>Akishop không chỉ là người dẫn đầu trong lĩnh vực phân phối máy đọc sách, mà còn là người bạn đồng hành đáng tin cậy, góp phần nâng cao văn hóa đọc và bảo vệ đôi mắt của người Việt. Chúng tôi cung cấp những sản phẩm máy đọc sách, thiết bị hiển thị thân thiện với mắt, và các ứng dụng đọc sách trực tuyến tích hợp công nghệ AI, giúp người dùng dễ dàng tiếp cận kiến thức mới một cách thoải mái nhất.</p>
        </div>

        <!-- Sản Phẩm và Dịch Vụ Section -->
        <div class="about-section">
            <h2>Sản Phẩm và Dịch Vụ Của Akishop</h2>
            <p>Máy đọc sách với màn hình giấy điện tử eink thân thiện với mắt, không chỉ giúp bạn có trải nghiệm y như đọc sách giấy mà giúp tiết kiệm thời gian tìm, tải sách, thông minh, tiện lợi hơn. Mang cả kho sách hơn 10 ngàn cuốn đi theo mọi lúc mọi nơi trong thiết bị nhỏ gọn này.</p>
            
            <p>Akishop với các sản phẩm máy đọc sách chất lượng cao đến từ những thương hiệu hàng đầu thế giới như Kindle, Boox, Kobo với đa dạng mẫu mã, kích thước khác nhau phù hợp với nhu cầu của người dùng từ 6 inch đến những máy màn hình lớn hơn 13,3 inch.</p>
            
            <div class="product-brands">
                <div class="brand-card">
                    <img src="img/banner/abkindle.png" alt="Kindle">
                    <h4>Máy đọc sách Kindle</h4>
                </div>
                <div class="brand-card">
                    <img src="img/banner/abboox.png" alt="Boox">
                    <h4>Máy đọc sách Boox</h4>
                </div>
                <div class="brand-card">
                    <img src="img/banner/abkobo.png" alt="Kobo">
                    <h4>Máy đọc sách Kobo</h4>
                </div>
            </div>
        </div>

        <!-- Lời Cảm Ơn -->
        <div class="about-section">
            <p>Akishop xin gửi lời cảm ơn chân thành nhất tới tất cả các khách hàng đã tin tưởng và đồng hành cùng Aki trong suốt thời gian qua. Chúng tôi sẽ tiếp tục nỗ lực để cải thiện, đưa sản phẩm máy đọc sách đến nhiều khách hàng hơn nữa và thực hiện sứ mệnh nâng cao văn hóa đọc cho người Việt với giải pháp đọc thông minh, hiện đại.</p>
        </div>

        <!-- Contact Info -->
        <div class="contact-info">
            <h3>Mọi thông tin đóng góp vui lòng liên hệ:</h3>
            <p><i class="fas fa-envelope"></i> Email: <a href="mailto:leduc2103@gmail.com">leduc2103@gmail.com</a></p>
            <p><i class="fas fa-phone"></i> Hotline: <a href="tel:098152222">098152222</a></p>
        </div>

        <!-- Tagline -->
        <div class="tagline">
            AKISTORE - ĐỌC SÁCH ĐỂ THÀNH CÔNG
        </div>

        <!-- Links Section -->
        <div class="links-section">
            <h3>Xem thêm các sản phẩm của Akishop:</h3>
            <ul>
                <li><a href="shop.php?search=Boox"><i class="fas fa-book-reader"></i> Máy đọc sách Boox</a></li>
                <li><a href="shop.php?search=Kindle"><i class="fas fa-book-reader"></i> Máy đọc sách Kindle</a></li>
                <li><a href="shop.php?search=Kobo"><i class="fas fa-book-reader"></i> Máy đọc sách Kobo</a></li>
            </ul>
            
            <h3 style="margin-top: 30px;">Akishop với các sự kiện:</h3>
            <ul>
                <li><a href="blog.php"><i class="fas fa-calendar-alt"></i> Sự kiện ra mắt máy đọc sách Boox Go</a></li>
                <li><a href="blog.php"><i class="fas fa-handshake"></i> Akishop cùng Nhà xuất bản Chính trị Quốc gia Sự Thật</a></li>
            </ul>
        </div>

    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Search Box -->
    <div class="search-box" id="search-box">
        <div class="search-container">
            <form action="shop.php" method="GET" id="search-form">
                <div class="search-input-wrapper">
                    <input type="text" id="search-input" name="search" placeholder="Tìm kiếm sản phẩm..." autocomplete="off">
                    <button type="submit" class="search-btn">
                        <i class="fa-solid fa-search"></i>
                    </button>
                    <div class="search-suggestions" id="search-suggestions"></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Scroll to Top Button -->
    <button id="scrollToTop" title="Trở về đầu trang">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script src="script.js?v=<?php echo time(); ?>"></script>
    <script src="https://cdn.botpress.cloud/webchat/v3.3/inject.js" defer></script>
    <script src="https://files.bpcontent.cloud/2025/11/26/16/20251126163853-AFN0KSEV.js" defer></script>
</body>
</html>