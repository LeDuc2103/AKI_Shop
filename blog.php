<?php
session_start();
require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();

// Lấy số lượng giỏ hàng
include_once 'includes/cart_count.php';

// Phân trang
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$items_per_page = 5;
$offset = ($page - 1) * $items_per_page;

// Lấy tổng số tin tức
$total_news = 0;
try {
    $stmt_count = $conn->query("SELECT COUNT(*) as total FROM tin_tuc");
    $count_result = $stmt_count->fetch(PDO::FETCH_ASSOC);
    $total_news = $count_result['total'];
} catch (PDOException $e) {
    error_log("Error counting news: " . $e->getMessage());
}

$total_pages = ceil($total_news / $items_per_page);

// Lấy danh sách tin tức
$news_list = array();
try {
    $stmt = $conn->prepare("SELECT * FROM tin_tuc ORDER BY ngay_tao DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $news_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching news: " . $e->getMessage());
}
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

    <section id="page-header" class="blog-header">
        <h2>#tintuc</h2>
        <p>Đọc tất cả các trường hợp về sản phẩm của chúng tôi!</p>
    </section>

    <section id="blog">
        <?php if (empty($news_list)): ?>
            <div style="text-align: center; padding: 50px 0; color: #666;">
                <i class="fas fa-newspaper fa-3x" style="margin-bottom: 20px;"></i>
                <h3>Chưa có tin tức nào</h3>
                <p>Hãy quay lại sau để xem những tin tức mới nhất!</p>
            </div>
        <?php else: ?>
            <?php foreach ($news_list as $news): ?>
                <div class="blog-box">
                    <div class="blog-img">
                        <?php if (!empty($news['hinh_anh'])): ?>
                            <img src="<?php echo htmlspecialchars($news['hinh_anh']); ?>" 
                                 alt="<?php echo htmlspecialchars($news['tieu_de']); ?>">
                        <?php else: ?>
                            <img src="img/blog/default.jpg" alt="No image">
                        <?php endif; ?>
                    </div>
                    <div class="blog-details">
                        <h4><?php echo htmlspecialchars($news['tieu_de']); ?></h4>
                        <p>
                            <?php 
                            // Lấy 150 ký tự đầu của nội dung (loại bỏ HTML tags)
                            $content = strip_tags($news['noi_dung']);
                            echo htmlspecialchars(mb_substr($content, 0, 150, 'UTF-8')) . '...';
                            ?>
                        </p>
                        <a href="blog_detail.php?id=<?php echo $news['ma_tintuc']; ?>">ĐỌC THÊM</a>
                    </div>
                    <h1>
                        <?php 
                        if (!empty($news['ngay_tao']) && $news['ngay_tao'] != '0000-00-00') {
                            echo date('d/m', strtotime($news['ngay_tao']));
                        } else {
                            echo '--/--';
                        }
                        ?>
                    </h1>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section id="pagination" class="section-p1">
        <?php if ($total_pages > 1): ?>
            <?php if ($page > 1): ?>
                <a href="blog.php?page=1"><i class="fa fa-angle-double-left"></i></a>
                <a href="blog.php?page=<?php echo ($page - 1); ?>"><i class="fa fa-long-arrow-alt-left"></i></a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="blog.php?page=<?php echo $i; ?>" 
                   class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="blog.php?page=<?php echo ($page + 1); ?>"><i class="fa fa-long-arrow-alt-right"></i></a>
                <a href="blog.php?page=<?php echo $total_pages; ?>"><i class="fa fa-angle-double-right"></i></a>
            <?php endif; ?>
        <?php endif; ?>
    </section>

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