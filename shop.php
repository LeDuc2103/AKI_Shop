<?php
session_start();

// Kết nối database
require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();

// Lấy danh mục được chọn (nếu có)
$selected_category = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Thiết lập phân trang
$items_per_page = 16;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Lấy danh sách danh mục
$categories = array();
try {
    $stmt = $conn->prepare("SELECT * FROM danh_muc ORDER BY id_danhmuc ASC");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    // Nếu lỗi database, $categories sẽ là array rỗng
}

// Lấy danh sách sản phẩm
$products = array();
$total_products = 0;
$total_pages = 0;

try {
    // Tạo câu query dựa vào danh mục được chọn
    if ($selected_category > 0) {
        // Lọc theo danh mục
        $count_sql = "SELECT COUNT(*) as total FROM san_pham WHERE id_danhmuc = :category";
        $product_sql = "SELECT * FROM san_pham WHERE id_danhmuc = :category ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    } else {
        // Hiển thị tất cả sản phẩm
        $count_sql = "SELECT COUNT(*) as total FROM san_pham";
        $product_sql = "SELECT * FROM san_pham ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    }
    
    // Đếm tổng số sản phẩm
    $stmt = $conn->prepare($count_sql);
    if ($selected_category > 0) {
        $stmt->bindValue(':category', $selected_category, PDO::PARAM_INT);
    }
    $stmt->execute();
    $result = $stmt->fetch();
    $total_products = $result['total'];
    $total_pages = ceil($total_products / $items_per_page);
    
    // Lấy sản phẩm với phân trang
    $stmt = $conn->prepare($product_sql);
    if ($selected_category > 0) {
        $stmt->bindValue(':category', $selected_category, PDO::PARAM_INT);
    }
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    // Nếu lỗi database, $products sẽ là array rỗng
}

// Lấy số lượng giỏ hàng
include_once 'includes/cart_count.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <title>KLTN Shop - Sản phẩm</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="pagination-style.css?v=<?php echo time(); ?>">
</head>
<body>
    <section id="header">
        <a href="#"><img src="img/logo1.png" width="150px" class="logo" alt="KLTN Logo"></a>
        <div>
            <ul id="navbar">
                <li><a href="index.php">Trang chủ</a></li>
                <li class="dropdown-menu">
                    <a class="active" href="shop.php">Sản phẩm</a>
                    <div class="category-dropdown">
                        <a href="shop.php">Tất cả sản phẩm</a>
                        <?php foreach ($categories as $category): ?>
                            <a href="shop.php?category=<?php echo $category['id_danhmuc']; ?>"><?php echo htmlspecialchars($category['ten_danhmuc']); ?></a>
                        <?php endforeach; ?>
                    </div>
                </li>
                <li><a href="blog.php">Tin tức</a></li>
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
    <!--Shop page-->
    <section id="page-header">
        <h2>#sanpham</h2>
        <p>Tiết kiệm nhiều hơn với phiếu giảm giá lên tới 30%.</p>
    </section>
    
     <!-- Products -->
      <section id="product1" class="section-p1">
             <div class="pro-container" id="products-container">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <?php
                            // Tính phần trăm giảm giá
                            $discount_percent = 0;
                            if ($product['gia'] > 0 && $product['gia_khuyen_mai'] > 0) {
                                $discount_percent = round((($product['gia'] - $product['gia_khuyen_mai']) / $product['gia']) * 100);
                            }
                        ?>
                        <div class="pro" onclick="window.location.href='sproduct.php?id=<?php echo $product['id_sanpham']; ?>';">
                            <?php if ($discount_percent > 0): ?>
                                <div class="discount-badge discount-<?php echo $discount_percent; ?>">
                                    <div class="percent">-<?php echo $discount_percent; ?>%</div>
                                    <div class="text">OFF</div>
                                </div>
                            <?php endif; ?>
                            <div class="product-image-container">
                                <img src="<?php echo htmlspecialchars($product['hinh_anh'] ? $product['hinh_anh'] : 'img/products/f1.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($product['ten_sanpham']); ?>">
                                <div class="view-details-overlay">
                                    <a href="sproduct.php?id=<?php echo $product['id_sanpham']; ?>" class="view-details-btn">
                                        <i class="fa-solid fa-eye"></i> Xem chi tiết
                                    </a>
                                </div>
                            </div>
                            <div class="des">
                                <span>Máy đọc sách</span>
                                <h5><?php echo htmlspecialchars($product['ten_sanpham']); ?></h5>
                                <div class="star">
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                </div>
                                <?php if ($discount_percent > 0): ?>
                                    <h4><del style="color: #999; font-size: 0.9em;"><?php echo number_format($product['gia'], 0, ',', '.'); ?>VNĐ</del> <span style="color: #e74c3c; font-weight: bold;"><?php echo number_format($product['gia_khuyen_mai'], 0, ',', '.'); ?>VNĐ</span></h4>
                                <?php else: ?>
                                    <h4><?php echo number_format($product['gia'], 0, ',', '.'); ?>VNĐ</h4>
                                <?php endif; ?>
                            </div>
                            <a href="#"><i class="fa-solid fa-cart-plus cart"></i></a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p>Không có sản phẩm nào.</p>
                    </div>
                <?php endif; ?>
             </div>
        </section>
         <!-- Pagination -->
     <?php if ($total_products > 0): ?>
     <section id="pagination" class="section-p1" id="shop-pagination">
        <?php if ($current_page > 1): ?>
            <a href="?<?php echo $selected_category > 0 ? 'category='.$selected_category.'&' : ''; ?>page=<?php echo $current_page - 1; ?>"><i class="fa fa-long-arrow-alt-left"></i></a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?<?php echo $selected_category > 0 ? 'category='.$selected_category.'&' : ''; ?>page=<?php echo $i; ?>" class="<?php echo ($i == $current_page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        
        <?php if ($current_page < $total_pages): ?>
            <a href="?<?php echo $selected_category > 0 ? 'category='.$selected_category.'&' : ''; ?>page=<?php echo $current_page + 1; ?>"><i class="fa fa-long-arrow-alt-right"></i></a>
        <?php endif; ?>
     </section>
     <?php endif; ?>
        <!-- Newsletter -->
         <section id="newletter" class="section-p1 section-m1">
            <div class="newtext">
                <h4>Đăng ký nhận bản tin</h4>
                <p>Nhận cập nhật qua Email về <span>các ưu đãi mới nhất </span>và sản phẩm mới nhất từ KLTN Shop.</p>
            </div>
            <div class="form">
                <input type="text" placeholder="Nhập email của bạn...">
                <button class="normal">Đăng ký</button>
            </div>
         </section>
        <!-- Footer -->
         <?php include 'includes/footer.php'; ?>
         <script src="script.js"></script>
         <script src="shop-ajax-pagination.js"></script>
</body>
</html>