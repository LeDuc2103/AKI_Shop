<?php
session_start();
require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();

// Lấy số lượng giỏ hàng
include_once 'includes/cart_count.php';

// Lấy ID tin tức từ URL
$news_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Lấy chi tiết tin tức
$news_detail = null;
if ($news_id > 0) {
    try {
        $stmt = $conn->prepare("SELECT * FROM tin_tuc WHERE ma_tintuc = ?");
        $stmt->execute(array($news_id));
        $news_detail = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching news detail: " . $e->getMessage());
    }
}

// Nếu không tìm thấy tin tức, redirect về trang blog
if (!$news_detail) {
    header('Location: blog.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <title><?php echo htmlspecialchars($news_detail['tieu_de']); ?> - KLTN Shop</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        .news-detail-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }
        
        .news-detail-header {
            margin-bottom: 30px;
        }
        
        .news-detail-header h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 15px;
            line-height: 1.3;
        }
        
        .news-meta {
            display: flex;
            gap: 20px;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .news-meta i {
            margin-right: 5px;
        }
        
        .news-featured-image {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .news-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #444;
        }
        
        .news-content img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            margin: 20px 0;
        }
        
        .news-content p {
            margin-bottom: 15px;
        }
        
        .news-content h2,
        .news-content h3,
        .news-content h4 {
            margin-top: 25px;
            margin-bottom: 15px;
            color: #333;
        }
        
        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background-color: #088178;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: 0.3s;
        }
        
        .back-button:hover {
            background-color: #066963;
        }
        
        .back-button i {
            margin-right: 5px;
        }
        
        @media (max-width: 768px) {
            .news-detail-header h1 {
                font-size: 1.8rem;
            }
            
            .news-content {
                font-size: 1rem;
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
                <li><a class="active" href="blog.php">Tin tức</a></li>
                <li><a href="about.php">Về chúng tôi</a></li>
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

    <div class="news-detail-container">
        <a href="blog.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Quay lại danh sách tin tức
        </a>
        
        <div class="news-detail-header">
            <h1><?php echo htmlspecialchars($news_detail['tieu_de']); ?></h1>
            <div class="news-meta">
                <span><i class="far fa-calendar"></i> 
                    <?php 
                    if (!empty($news_detail['ngay_tao']) && $news_detail['ngay_tao'] != '0000-00-00') {
                        echo date('d/m/Y', strtotime($news_detail['ngay_tao']));
                    }
                    ?>
                </span>
                <span><i class="far fa-user"></i> <?php echo htmlspecialchars($news_detail['nguoi_tao']); ?></span>
            </div>
        </div>
        
        <?php if (!empty($news_detail['hinh_anh'])): ?>
            <img src="<?php echo htmlspecialchars($news_detail['hinh_anh']); ?>" 
                 alt="<?php echo htmlspecialchars($news_detail['tieu_de']); ?>" 
                 class="news-featured-image">
        <?php endif; ?>
        
        <div class="news-content">
            <?php echo $news_detail['noi_dung']; ?>
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
