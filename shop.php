<?php
session_start();

// Kết nối database
require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();

// Lấy các tham số lọc
$selected_category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search_keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : '';
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;

// Thiết lập phân trang
$items_per_page = 16; // 4 sản phẩm x 4 dòng
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
    // Tạo câu query dựa vào điều kiện tìm kiếm
    $where_conditions = array();
    $params = array();
    
    // Nếu có từ khóa tìm kiếm
    if (!empty($search_keyword)) {
        $where_conditions[] = "(sp.ten_sanpham LIKE :search OR dm.ten_danhmuc LIKE :search)";
        $params[':search'] = '%' . $search_keyword . '%';
    }
    
    // Nếu có danh mục được chọn
    if ($selected_category > 0) {
        $where_conditions[] = "sp.id_danhmuc = :category";
        $params[':category'] = $selected_category;
    }
    
    // Lọc theo giá
    if ($min_price !== null) {
        $where_conditions[] = "(CASE WHEN sp.gia_khuyen_mai > 0 THEN sp.gia_khuyen_mai ELSE sp.gia END) >= :min_price";
        $params[':min_price'] = $min_price;
    }
    
    if ($max_price !== null) {
        $where_conditions[] = "(CASE WHEN sp.gia_khuyen_mai > 0 THEN sp.gia_khuyen_mai ELSE sp.gia END) <= :max_price";
        $params[':max_price'] = $max_price;
    }
    
    // Tạo WHERE clause
    $where_clause = '';
    if (count($where_conditions) > 0) {
        $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
    }
    
    // Tạo ORDER BY clause
    $order_clause = "ORDER BY sp.created_at DESC";
    if ($sort_by == 'price_asc') {
        $order_clause = "ORDER BY (CASE WHEN sp.gia_khuyen_mai > 0 THEN sp.gia_khuyen_mai ELSE sp.gia END) ASC";
    } elseif ($sort_by == 'price_desc') {
        $order_clause = "ORDER BY (CASE WHEN sp.gia_khuyen_mai > 0 THEN sp.gia_khuyen_mai ELSE sp.gia END) DESC";
    } elseif ($sort_by == 'name_asc') {
        $order_clause = "ORDER BY sp.ten_sanpham ASC";
    } elseif ($sort_by == 'name_desc') {
        $order_clause = "ORDER BY sp.ten_sanpham DESC";
    }
    
    // Query đếm tổng số sản phẩm
    $count_sql = "SELECT COUNT(*) as total 
                  FROM san_pham sp 
                  LEFT JOIN danh_muc dm ON sp.id_danhmuc = dm.id_danhmuc 
                  $where_clause";
    
    // Query lấy sản phẩm
    $product_sql = "SELECT sp.*, dm.ten_danhmuc 
                    FROM san_pham sp 
                    LEFT JOIN danh_muc dm ON sp.id_danhmuc = dm.id_danhmuc 
                    $where_clause 
                    $order_clause 
                    LIMIT :limit OFFSET :offset";
    
    // Đếm tổng số sản phẩm
    $stmt = $conn->prepare($count_sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $result = $stmt->fetch();
    $total_products = $result['total'];
    $total_pages = ceil($total_products / $items_per_page);
    
    // Lấy sản phẩm với phân trang
    $stmt = $conn->prepare($product_sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    // Nếu lỗi database, $products sẽ là array rỗng
    error_log("Database error: " . $e->getMessage());
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
        <a href="index.php"><img src="img/logo7.png" width="150px" class="logo" alt="KLTN Logo"></a>
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
    <!--Shop page-->
    <section id="page-header">
        <h2>SẢN PHẨM</h2>
        <p>Tiết kiệm nhiều hơn với phiếu giảm giá lên tới 5% - 50%.</p>
    </section>
    
    <!-- Shop Container with Filter -->
    <div class="shop-wrapper section-p1">
        <!-- Sidebar Filter -->
        <aside class="shop-sidebar">
            <div class="filter-section">
                <h3><i class="fas fa-filter"></i> Bộ Lọc Tìm Kiếm</h3>
                
                <form method="GET" action="shop.php" id="filterForm">
                    <!-- Tìm kiếm theo tên -->
                    <div class="filter-group">
                        <label><i class="fas fa-search"></i> Tìm theo tên</label>
                        <input type="text" name="search" class="filter-input" placeholder="Nhập tên sản phẩm..." value="<?php echo htmlspecialchars($search_keyword); ?>">
                    </div>
                    
                    <!-- Danh mục -->
                    <div class="filter-group">
                        <label><i class="fas fa-list"></i> Danh mục</label>
                        <select name="category" class="filter-select">
                            <option value="0">Tất cả danh mục</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id_danhmuc']; ?>" 
                                        <?php echo ($selected_category == $category['id_danhmuc']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['ten_danhmuc']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Khoảng giá -->
                    <div class="filter-group">
                        <label><i class="fas fa-dollar-sign"></i> Khoảng giá</label>
                        <div class="price-inputs">
                            <input type="number" name="min_price" class="filter-input" placeholder="Từ (VNĐ)" 
                                   value="<?php echo $min_price !== null ? $min_price : ''; ?>" min="0" step="1000">
                            <span style="margin: 0 5px;">-</span>
                            <input type="number" name="max_price" class="filter-input" placeholder="Đến (VNĐ)" 
                                   value="<?php echo $max_price !== null ? $max_price : ''; ?>" min="0" step="1000">
                        </div>
                    </div>
                    
                    <!-- Sắp xếp -->
                    <div class="filter-group">
                        <label><i class="fas fa-sort"></i> Sắp xếp theo</label>
                        <select name="sort" class="filter-select">
                            <option value="">Mới nhất</option>
                            <option value="price_asc" <?php echo ($sort_by == 'price_asc') ? 'selected' : ''; ?>>Giá: Thấp đến Cao</option>
                            <option value="price_desc" <?php echo ($sort_by == 'price_desc') ? 'selected' : ''; ?>>Giá: Cao đến Thấp</option>
                            <option value="name_asc" <?php echo ($sort_by == 'name_asc') ? 'selected' : ''; ?>>Tên: A-Z</option>
                            <option value="name_desc" <?php echo ($sort_by == 'name_desc') ? 'selected' : ''; ?>>Tên: Z-A</option>
                        </select>
                    </div>
                    
                    <!-- Buttons -->
                    <div class="filter-buttons">
                        <button type="submit" class="btn-apply-filter">
                            <i class="fas fa-check"></i> Áp dụng
                        </button>
                        <a href="shop.php" class="btn-reset-filter">
                            <i class="fas fa-redo"></i> Đặt lại
                        </a>
                    </div>
                </form>
                
                <!-- Active Filters Display -->
                <?php 
                $active_filters = array();
                if (!empty($search_keyword)) $active_filters[] = "Tìm kiếm: \"$search_keyword\"";
                if ($selected_category > 0) {
                    foreach ($categories as $cat) {
                        if ($cat['id_danhmuc'] == $selected_category) {
                            $active_filters[] = "Danh mục: " . $cat['ten_danhmuc'];
                            break;
                        }
                    }
                }
                if ($min_price !== null) $active_filters[] = "Từ: " . number_format($min_price, 0, ',', '.') . " VNĐ";
                if ($max_price !== null) $active_filters[] = "Đến: " . number_format($max_price, 0, ',', '.') . " VNĐ";
                if (!empty($sort_by)) {
                    $sort_labels = array(
                        'price_asc' => 'Giá tăng dần',
                        'price_desc' => 'Giá giảm dần',
                        'name_asc' => 'Tên A-Z',
                        'name_desc' => 'Tên Z-A'
                    );
                    if (isset($sort_labels[$sort_by])) {
                        $active_filters[] = "Sắp xếp: " . $sort_labels[$sort_by];
                    }
                }
                
                if (!empty($active_filters)):
                ?>
                <div class="active-filters">
                    <h4>Bộ lọc đang áp dụng:</h4>
                    <ul>
                        <?php foreach ($active_filters as $filter): ?>
                            <li><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($filter); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </aside>
        
        <!-- Main Product Area -->
        <div class="shop-main">
            <!-- Result Summary -->
            <div class="result-summary">
                <h4>Hiển thị <?php echo count($products); ?> / <?php echo $total_products; ?> sản phẩm</h4>
            </div>
    
    <?php if (!empty($search_keyword) && $total_products == 0): ?>
    <!-- Search No Result -->
        <div class="search-no-result" style="margin-bottom: 20px;">
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; border-radius: 8px; text-align: center;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 48px; color: #ffc107; margin-bottom: 15px;"></i>
                <h3 style="color: #856404; margin-bottom: 10px;">Không tìm thấy sản phẩm</h3>
                <p style="color: #856404; margin-bottom: 15px;">Không tìm thấy sản phẩm phù hợp với bộ lọc của bạn</p>
                <p style="color: #856404; margin-bottom: 20px;">Vui lòng thử lại với bộ lọc khác.</p>
                <a href="shop.php" class="normal" style="display: inline-block; text-decoration: none; background: #088178; color: white; font-size: 16px; padding: 15px 30px;">Xem tất cả sản phẩm</a>
            </div>
        </div>
    <?php endif; ?>
    
     <!-- Products -->
      <?php if (!(!empty($search_keyword) && $total_products == 0)): ?>
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
                    <div class="col-12 text-center" style="width: 100%; padding: 40px; text-align: center;">
                        <?php if (!empty($search_keyword)): ?>
                            <h3 style="color: #666; margin-bottom: 10px;">Không tìm thấy sản phẩm phù hợp</h3>
                            <p style="color: #999; margin-bottom: 20px;">Không có sản phẩm nào phù hợp với từ khóa "<?php echo htmlspecialchars($search_keyword, ENT_QUOTES, 'UTF-8'); ?>"</p>
                            <a href="shop.php" class="normal" style="display: inline-block; text-decoration: none;">Xem tất cả sản phẩm</a>
                        <?php else: ?>
                            <p style="color: #999;">Không có sản phẩm nào.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
             </div>
        </section>
        <?php endif; ?>
         <!-- Pagination -->
     <?php if ($total_products > 0): ?>
     <section id="pagination" class="section-p1" id="shop-pagination">
        <?php 
        // Tạo query string cho pagination
        $pagination_params = array();
        if ($selected_category > 0) {
            $pagination_params[] = 'category='.$selected_category;
        }
        if (!empty($search_keyword)) {
            $pagination_params[] = 'search='.urlencode($search_keyword);
        }
        $pagination_query = !empty($pagination_params) ? implode('&', $pagination_params).'&' : '';
        ?>
        
        <?php if ($current_page > 1): ?>
            <a href="?<?php echo $pagination_query; ?>page=<?php echo $current_page - 1; ?>"><i class="fa fa-long-arrow-alt-left"></i></a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?<?php echo $pagination_query; ?>page=<?php echo $i; ?>" class="<?php echo ($i == $current_page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        
        <?php if ($current_page < $total_pages): ?>
            <a href="?<?php echo $pagination_query; ?>page=<?php echo $current_page + 1; ?>"><i class="fa fa-long-arrow-alt-right"></i></a>
        <?php endif; ?>
     </section>
     <?php endif; ?>
     
        </div><!-- end shop-main -->
    </div><!-- end shop-wrapper -->
    
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

         <!-- Search Box -->
         <div class="search-box" id="search-box">
             <div class="search-container">
                 <form action="shop.php" method="GET" id="search-form">
                     <div class="search-input-wrapper">
                         <input type="text" id="search-input" name="search" placeholder="Tìm kiếm sản phẩm..." autocomplete="off" value="<?php echo htmlspecialchars($search_keyword, ENT_QUOTES, 'UTF-8'); ?>">
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

         <script src="script.js?v=<?php echo time(); ?>"></script></script>
         <script src="https://cdn.botpress.cloud/webchat/v3.3/inject.js" defer></script>
         <script src="https://files.bpcontent.cloud/2025/11/26/16/20251126163853-AFN0KSEV.js" defer></script>
</body>
</html>