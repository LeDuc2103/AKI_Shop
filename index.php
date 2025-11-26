<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
require_once 'config/database.php';

// Kết nối database và lấy sản phẩm
$featured_products = array();
$new_products = array();

// Thiết lập phân trang
$items_per_page = 8;
$featured_page = isset($_GET['featured_page']) ? max(1, (int)$_GET['featured_page']) : 1;
$new_page = isset($_GET['new_page']) ? max(1, (int)$_GET['new_page']) : 1;
$featured_offset = ($featured_page - 1) * $items_per_page;
$new_offset = ($new_page - 1) * $items_per_page;

try {
    // Khởi tạo database
    $db = new Database();
    $conn = $db->getConnection();
    
    // Đếm tổng số sản phẩm khuyến mãi
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM san_pham WHERE gia_khuyen_mai > 0");
    $stmt->execute();
    $result = $stmt->fetch();
    $featured_total = $result['total'];
    $featured_total_pages = ceil($featured_total / $items_per_page);
    
    // Lấy sản phẩm khuyến mãi (gia_khuyen_mai > 0) với phân trang
    $stmt = $conn->prepare("SELECT * FROM san_pham WHERE gia_khuyen_mai > 0 ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $featured_offset, PDO::PARAM_INT);
    $stmt->execute();
    $featured_products = $stmt->fetchAll();
    
    // Đếm tổng số sản phẩm mới
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM san_pham WHERE gia_khuyen_mai = 0 OR gia_khuyen_mai IS NULL");
    $stmt->execute();
    $result = $stmt->fetch();
    $new_total = $result['total'];
    $new_total_pages = ceil($new_total / $items_per_page);
    
    // Lấy sản phẩm mới (gia_khuyen_mai = 0 hoặc NULL) với phân trang
    $stmt = $conn->prepare("SELECT * FROM san_pham WHERE gia_khuyen_mai = 0 OR gia_khuyen_mai IS NULL ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $new_offset, PDO::PARAM_INT);
    $stmt->execute();
    $new_products = $stmt->fetchAll();
    
} catch(Exception $e) {
    error_log("Lỗi database: " . $e->getMessage());
    // Nếu lỗi database, $featured_products và $new_products sẽ là array rỗng
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
    <title>KLTN Shop - Trang chủ</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="performance-fix.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="pagination-style.css?v=<?php echo time(); ?>">
</head>
<body>
    <section id="header">
        <a href="#"><img src="img/logo1.png" width="150px" class="logo" alt="KLTN Logo"></a>
        <div>
            <ul id="navbar">
                <li><a class="active" href="index.php">Trang chủ</a></li>
                <li><a href="shop.php">Sản phẩm</a></li>
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
    <!--Home page-->
    <section id="hero">
        <button>MUA NGAY</button>
    </section>
    <!-- Featured -->
     <section id="feature" class="section-p1">
        <div class="fe-box">
            <img src="img/features/f1.png" alt="">
            <h6>Đặt hàng trực tuyến</h6>
        </div>
        <div class="fe-box">
            <img src="img/features/f2.png" alt="">
             <h6>Miễn phí vận chuyển</h6>
        </div>
        <div class="fe-box">
            <img src="img/features/f3.png" alt="">
            <h6>Tiết kiệm tiền</h6>
        </div>
        <div class="fe-box">
            <img src="img/features/f4.png" alt="">
            <h6>Khuyến mãi</h6>
        </div>
        <div class="fe-box">
            <img src="img/features/f5.png" alt="">
            <h6>Giá cả hợp lý</h6>
        </div>
        <div class="fe-box">
            <img src="img/features/f6.png" alt="">
            <h6>Hỗ trợ 24/7</h6>
        </div>

     </section>
     <!-- New Product -->
      <section id="product1" class="section-p1" id="featured-section">
            <h2>KHUYẾN MÃI HOT</h2>
            <p>Hàng ngàn khuyến mãi đang chờ bạn mua.</p>
             <div class="pro-container">
                <?php if (!empty($featured_products)): ?>
                    <?php foreach ($featured_products as $product): ?>
                        <?php
                            // Tính phần trăm giảm giá
                            $discount_percent = 0;
                            if ($product['gia'] > 0 && $product['gia_khuyen_mai'] > 0) {
                                $discount_percent = round((($product['gia'] - $product['gia_khuyen_mai']) / $product['gia']) * 100);
                            }
                        ?>
                        <div class="pro" onclick="window.location.href='sproduct.php?id=<?php echo $product['id_sanpham']; ?>';" style="cursor: pointer;">
                            <?php if ($discount_percent > 0): ?>
                                <div class="discount-badge discount-<?php echo $discount_percent; ?>">
                                    <div class="percent">-<?php echo $discount_percent; ?>%</div>
                                    <div class="text">OFF</div>
                                </div>
                            <?php endif; ?>
                            <div class="product-image-container">
                                <img src="<?php echo htmlspecialchars($product['hinh_anh'] ? $product['hinh_anh'] : 'img/products/f1.jpg', ENT_QUOTES, 'UTF-8'); ?>" 
                                     alt="<?php echo htmlspecialchars($product['ten_sanpham'], ENT_QUOTES, 'UTF-8'); ?>"
                                     onerror="this.src='img/products/f1.jpg'">
                                <div class="view-details-overlay">
                                    <a href="sproduct.php?id=<?php echo $product['id_sanpham']; ?>" class="view-details-btn">
                                        <i class="fa-solid fa-eye"></i> Xem chi tiết
                                    </a>
                                </div>
                            </div>
                            <div class="des">
                                <span>Máy đọc sách</span>
                                <h5><?php echo htmlspecialchars($product['ten_sanpham'], ENT_QUOTES, 'UTF-8'); ?></h5>
                                <div class="star">
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                </div>
                                <h4><del style="color: #999; font-size: 0.9em;"><?php echo number_format($product['gia'], 0, ',', '.'); ?>VNĐ</del> <span style="color: #e74c3c; font-weight: bold;"><?php echo number_format($product['gia_khuyen_mai'], 0, ',', '.'); ?>VNĐ</span></h4>
                            </div>
                            <a href="sproduct.php?id=<?php echo $product['id_sanpham']; ?>" onclick="event.stopPropagation();"><i class="fa-solid fa-cart-plus cart"></i></a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; width: 100%; padding: 40px;">Chưa có sản phẩm khuyến mãi. Vui lòng thêm sản phẩm vào database.</p>
                <?php endif; ?>
            </div>
            
            <!-- Phân trang cho Khuyến mãi hot -->
            <?php if ($featured_total > 0): ?>
            <section id="pagination" class="section-p1">
                <?php if ($featured_page > 1): ?>
                    <a href="?featured_page=<?php echo $featured_page - 1; ?><?php echo isset($_GET['new_page']) ? '&new_page='.$_GET['new_page'] : ''; ?>#featured-section"><i class="fa fa-long-arrow-alt-left"></i></a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $featured_total_pages; $i++): ?>
                    <a href="?featured_page=<?php echo $i; ?><?php echo isset($_GET['new_page']) ? '&new_page='.$_GET['new_page'] : ''; ?>#featured-section" 
                       class="<?php echo ($i == $featured_page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                
                <?php if ($featured_page < $featured_total_pages): ?>
                    <a href="?featured_page=<?php echo $featured_page + 1; ?><?php echo isset($_GET['new_page']) ? '&new_page='.$_GET['new_page'] : ''; ?>#featured-section"><i class="fa fa-long-arrow-alt-right"></i></a>
                <?php endif; ?>
            </section>
            <?php endif; ?>
        </section>
        <!-- banner mid -->
         <section id="banner" class="section-m1">
            <h4>DỊCH VỤ SỬA CHỮA</h4>
            <h2>Lên đến <span>Giảm từ 10%-30%</span>-Tất cả các dòng máy đọc sách.</h2>
            <button class="normal">Tìm hiểu thêm</button>

         </section>
        <!-- Product -->
         <section id="product1" class="section-p1" id="new-section">
            <h2>Sản phẩm mới</h2>
            <p>Khám phá các sản phẩm mới nhất của chúng tôi</p>
            <div class="pro-container">
                <?php if (!empty($new_products)): ?>
                    <?php foreach ($new_products as $product): ?>
                        <div class="pro" onclick="window.location.href='sproduct.php?id=<?php echo $product['id_sanpham']; ?>';" style="cursor: pointer;">
                            <div class="product-image-container">
                                <img src="<?php echo htmlspecialchars($product['hinh_anh'] ? $product['hinh_anh'] : 'img/products/f2.jpg', ENT_QUOTES, 'UTF-8'); ?>" 
                                     alt="<?php echo htmlspecialchars($product['ten_sanpham'], ENT_QUOTES, 'UTF-8'); ?>"
                                     onerror="this.src='img/products/f2.jpg'">
                                <div class="view-details-overlay">
                                    <a href="sproduct.php?id=<?php echo $product['id_sanpham']; ?>" class="view-details-btn">
                                        <i class="fa-solid fa-eye"></i> Xem chi tiết
                                    </a>
                                </div>
                            </div>
                            <div class="des">
                                <span>Máy đọc sách</span>
                                <h5><?php echo htmlspecialchars($product['ten_sanpham'], ENT_QUOTES, 'UTF-8'); ?></h5>
                                <div class="star">
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                </div>
                                <h4><?php echo number_format($product['gia'], 0, ',', '.'); ?>VNĐ</h4>
                            </div>
                            <a href="sproduct.php?id=<?php echo $product['id_sanpham']; ?>" onclick="event.stopPropagation();"><i class="fa-solid fa-cart-plus cart"></i></a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; width: 100%; padding: 40px;">Chưa có sản phẩm mới. Vui lòng thêm sản phẩm vào database.</p>
                <?php endif; ?>
            </div>
            
            <!-- Phân trang cho Sản phẩm mới -->
            <?php if ($new_total > 0): ?>
            <section id="pagination" class="section-p1">
                <?php if ($new_page > 1): ?>
                    <a href="?new_page=<?php echo $new_page - 1; ?><?php echo isset($_GET['featured_page']) ? '&featured_page='.$_GET['featured_page'] : ''; ?>#new-section"><i class="fa fa-long-arrow-alt-left"></i></a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $new_total_pages; $i++): ?>
                    <a href="?new_page=<?php echo $i; ?><?php echo isset($_GET['featured_page']) ? '&featured_page='.$_GET['featured_page'] : ''; ?>#new-section" 
                       class="<?php echo ($i == $new_page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                
                <?php if ($new_page < $new_total_pages): ?>
                    <a href="?new_page=<?php echo $new_page + 1; ?><?php echo isset($_GET['featured_page']) ? '&featured_page='.$_GET['featured_page'] : ''; ?>#new-section"><i class="fa fa-long-arrow-alt-right"></i></a>
                <?php endif; ?>
            </section>
            <?php endif; ?>
        </section>
        <!-- New letter -->
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
            <!-- footer -->
             <footer id="section-p1">
                <div class="col">
                    <img class="logo" src="img/logo1.png" width="120px" alt="">
                    <h4>Thông Tin Liên Hệ</h4>
                    <p><Strong>Địa chỉ: </Strong>124 Lê Quang Định, phường Bình Thạnh, TP.HỒ CHÍ MINH, VIỆT NAM</p>
                    <p><Strong>Điện thoại: </Strong>+84 123 456 789</p>
                    <p><Strong>Giờ làm việc: </Strong> 8:00 - 17:00, Thứ 2 - Thứ 7</p>
                    <div class="follow">
                        <h4>Theo dõi chúng tôi</h4>
                        <div class="icon">
                            <i class="fa-brands fa-facebook-f"></i>
                            <i class="fa-brands fa-telegram"></i>
                            <i class="fa-brands fa-instagram"></i>
                            <i class="fa-brands fa-youtube"></i>
                        </div>
                </div>
                </div>
                <div class="col">
                    <h4>Liên hệ</h4>
                    <a href="#">Về chúng tôi</a>
                    <a href="#">Thông tin giao hàng</a>
                    <a href="#">Chính sách bảo mật</a>
                    <a href="#">Điều khoản & Điều kiện</a>
                    <a href="contact.php">Liên hệ chúng tôi</a>
                </div>
                <div class="col">
                    <h4>Tài khoản của tôi</h4>
                    <a href="login.php">Đăng nhập</a>
                    <a href="register.php">Đăng ký</a>
                    <a href="cart.php">Xem giỏ hàng</a>
                    <a href="#">Yêu thích</a>
                    <a href="#">Theo dõi đơn hàng</a>
                </div>
                <div class="col install">
                    <p>Thanh toán an toàn cho các giao dịch trực tuyến</p>
                    <img src="img/pay/pay.png" alt="">
                </div>
                <div class="copyright">
                    <p>© 2025 KLTN Project by Le Van Tuc - Huynh Dinh Chieu</p>
                </div>
             </footer>
             <script src="script.js"></script>

</body>
</html>