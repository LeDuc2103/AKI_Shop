<?php
session_start();

// Kết nối database
require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();

// Lấy thông tin sản phẩm từ ID
$product = null;
$related_products = array();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = intval($_GET['id']);
    
    try {
        $stmt = $conn->prepare("SELECT p.*, dm.ten_danhmuc 
                               FROM san_pham p 
                               LEFT JOIN danh_muc dm ON p.id_danhmuc = dm.id_danhmuc 
                               WHERE p.id_sanpham = ?");
        $stmt->execute(array($product_id));
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Lấy sản phẩm liên quan cùng danh mục
        if ($product && isset($product['id_danhmuc'])) {
            $stmt = $conn->prepare("SELECT * FROM san_pham 
                                   WHERE id_danhmuc = ? AND id_sanpham != ? 
                                   ORDER BY created_at DESC LIMIT 4");
            $stmt->execute(array($product['id_danhmuc'], $product_id));
            $related_products = $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        error_log("Lỗi database: " . $e->getMessage());
    }
}

// Redirect về trang shop nếu không tìm thấy sản phẩm
if (!$product) {
    header('Location: shop.php');
    exit();
}

// Lấy danh sách đánh giá
$reviews = array();
$total_reviews = 0;
$average_rating = 0;

try {
    // Đếm tổng và tính trung bình
    $stmt = $conn->prepare("SELECT COUNT(*) as total, AVG(so_sao) as avg_rating 
                           FROM comments 
                           WHERE id_sanpham = ? AND trang_thai = 'hien'");
    $stmt->execute(array($product_id));
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_reviews = $stats['total'];
    $average_rating = $stats['avg_rating'] ? round($stats['avg_rating'], 1) : 0;
    
    // Lấy danh sách đánh giá với thông tin user
    $stmt = $conn->prepare("SELECT c.*, u.ho_ten as ten_nguoi_danh_gia,
                           DATE_FORMAT(c.ngay_danh_gia, '%d/%m/%Y %H:%i') as ngay_formatted
                           FROM comments c
                           LEFT JOIN user u ON c.ma_user = u.ma_user
                           WHERE c.id_sanpham = ? AND c.trang_thai = 'hien'
                           ORDER BY c.ngay_danh_gia DESC");
    $stmt->execute(array($product_id));
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Lỗi lấy đánh giá: " . $e->getMessage());
}

// Đếm số lượng đã bán
$sold_count = 0;
try {
    $sold_stmt = $conn->prepare("SELECT SUM(ct.so_luong) as total_sold 
                                 FROM chitiet_donhang ct
                                 INNER JOIN don_hang dh ON ct.ma_donhang = dh.ma_donhang
                                 WHERE ct.id_sanpham = ? 
                                 AND dh.trang_thai IN ('hoan_thanh', 'da_xuat_kho')");
    $sold_stmt->execute(array($product_id));
    $sold_result = $sold_stmt->fetch(PDO::FETCH_ASSOC);
    $sold_count = $sold_result['total_sold'] ? intval($sold_result['total_sold']) : 0;
} catch (PDOException $e) {
    error_log("Lỗi đếm đã bán: " . $e->getMessage());
}

// Xử lý submit đánh giá
$review_success = '';
$review_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';
    $ma_user = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    // Kiểm tra đã đăng nhập chưa
    if (empty($ma_user)) {
        $review_error = 'Vui lòng đăng nhập để đánh giá sản phẩm!';
    } elseif ($rating < 1 || $rating > 5) {
        $review_error = 'Vui lòng chọn đánh giá từ 1 đến 5 sao!';
    } elseif (strlen($review_text) < 10) {
        $review_error = 'Nội dung đánh giá phải có ít nhất 10 ký tự!';
    } else {
        try {
            // Lấy tên user từ database
            $user_stmt = $conn->prepare("SELECT ho_ten FROM user WHERE ma_user = ?");
            $user_stmt->execute(array($ma_user));
            $user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);
            $ho_ten = $user_data ? $user_data['ho_ten'] : 'Khách hàng';
            
            $stmt = $conn->prepare("INSERT INTO comments (id_sanpham, ma_user, ho_ten, so_sao, noi_dung, ngay_danh_gia, created_at, trang_thai) 
                                   VALUES (?, ?, ?, ?, ?, NOW(), NOW(), 'hien')");
            $result = $stmt->execute(array($product_id, $ma_user, $ho_ten, $rating, $review_text));
            
            $_SESSION['review_success'] = "Cảm ơn bạn đã đánh giá!";
            header("Location: sproduct.php?id=" . $product_id);
            exit();
        } catch (PDOException $e) {
            $review_error = "Có lỗi xảy ra. Vui lòng thử lại!";
        }
    }
}

// Tính phần trăm giảm giá
$discount_percent = 0;
$final_price = $product['gia'];
if ($product['gia'] > 0 && $product['gia_khuyen_mai'] > 0) {
    $discount_percent = round((($product['gia'] - $product['gia_khuyen_mai']) / $product['gia']) * 100);
    $final_price = $product['gia_khuyen_mai'];
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title><?php echo htmlspecialchars($product['ten_sanpham']); ?> - KLTN Shop</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>
    <link rel="stylesheet" href="css/responsive.css?v=1765636811">">
</head>
<body>
    <section id="header">
        <a href="index.php"><img src="img/logo7.png" width="150px" class="logo" alt="KLTN Logo"></a>
        <div>
            <ul id="navbar">
                <li><a href="index.php">Trang chủ</a></li>
                <li><a class="active" href="shop.php">Sản phẩm</a></li>
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

    <!-- Product Details Section -->
    <section id="prodetails" class="section-p1">
        <div class="single-pro-img">
            <img src="<?php echo htmlspecialchars(!empty($product['hinh_anh']) ? $product['hinh_anh'] : 'img/products/f1.jpg'); ?>" width="100%" id="MainImg" alt="<?php echo htmlspecialchars($product['ten_sanpham']); ?>">
            
            <div class="small-img-group">
                <?php
                // Tạo mảng chứa tất cả ảnh (ảnh chính + ảnh phụ)
                $all_images = array();
                
                // Thêm ảnh chính
                if (!empty($product['hinh_anh'])) {
                    $all_images[] = $product['hinh_anh'];
                }
                
                // Thêm các ảnh phụ
                if (!empty($product['anh_con'])) {
                    $anh_con_array = explode('|', $product['anh_con']);
                    foreach ($anh_con_array as $anh_con_item) {
                        $trimmed = trim($anh_con_item);
                        if (!empty($trimmed)) {
                            $all_images[] = $trimmed;
                        }
                    }
                }
                
                // Hiển thị 4 ảnh đầu tiên trong small-img-group
                for ($i = 0; $i < 4; $i++):
                    $img_src = isset($all_images[$i]) ? $all_images[$i] : (!empty($product['hinh_anh']) ? $product['hinh_anh'] : 'img/products/f1.jpg');
                ?>
                <div class="small-img-col">
                    <img src="<?php echo htmlspecialchars($img_src); ?>" width="100%" class="small-img" alt="">
                </div>
                <?php endfor; ?>
            </div>
        </div>
        
        <div class="single-pro-details">
            <h6><a href="index.php">Trang chủ</a> / <a href="shop.php">Sản phẩm</a> / <?php echo htmlspecialchars(isset($product['ten_danhmuc']) ? $product['ten_danhmuc'] : 'Chi tiết'); ?></h6>
            <h4><?php echo htmlspecialchars($product['ten_sanpham']); ?></h4>
            
            <div class="rating-display">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fa-solid fa-star <?php echo $i <= $average_rating ? 'filled' : ''; ?>"></i>
                <?php endfor; ?>
                <span class="review-count">(<?php echo count($reviews); ?> đánh giá)</span>
            </div>
            
            <div class="sold-info" style="margin: 10px 0; color: #666; font-size: 14px;">
                <i class="fa-solid fa-box"></i> Đã bán: <strong style="color: #088178;"><?php echo number_format($sold_count); ?></strong> sản phẩm
            </div>
            
            <div class="price-section">
                <?php if ($discount_percent > 0): ?>
                    <h2 class="current-price"><?php echo number_format($final_price, 0, ',', '.'); ?>Đ</h2>
                    <div class="price-info">
                        <span class="original-price">Giá cũ: <del><?php echo number_format($product['gia'], 0, ',', '.'); ?>Đ</del></span>
                        <span class="save-percent">Tiết kiệm: <?php echo $discount_percent; ?> %</span>
                    </div>
                <?php else: ?>
                    <h2 class="current-price"><?php echo number_format($product['gia'], 0, ',', '.'); ?>Đ</h2>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($product['mau_sac'])): ?>
            <div class="color-selection">
                <label>Màu sắc:</label>
                <div class="color-options">
                    <?php 
                    $colors = explode('|', $product['mau_sac']);
                    foreach($colors as $color): 
                        $color = trim($color);
                        $color_code = '';
                        // Map tên màu sang mã màu
                        $color_map = array(
                            'Đen' => '#000000',
                            'Trắng' => '#FFFFFF',
                            'Xám' => '#808080',
                            'Đỏ' => '#FF0000',
                            'Xanh dương' => '#0000FF',
                            'Xanh lá' => '#00FF00',
                            'Vàng' => '#FFD700',
                            'Cam' => '#FFA500',
                            'Hồng' => '#FFC0CB',
                            'Nâu' => '#8B4513',
                            'Tím' => '#800080',
                            'Xanh lam' => '#87CEEB'
                        );
                        $color_code = isset($color_map[$color]) ? $color_map[$color] : '#CCCCCC';
                    ?>
                    <div class="color-box" data-color="<?php echo htmlspecialchars($color); ?>" style="background-color: <?php echo $color_code; ?>;" title="<?php echo htmlspecialchars($color); ?>">
                        <?php if ($color_code == '#FFFFFF'): ?>
                        <span class="color-border"></span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="color-select" value="">
            </div>
            <?php endif; ?>
            <div class="product-description">
                <h4>Mô tả</h4>
                <div class="description-content"><?php echo isset($product['mo_ta']) && !empty($product['mo_ta']) ? $product['mo_ta'] : 'Chưa có mô tả cho sản phẩm này.'; ?></div>
            </div>
            
            <div class="quantity-section">
                <label>Số lượng:</label>
                <?php if (isset($product['so_luong']) && $product['so_luong'] > 0): ?>
                <div class="quantity-control">
                    <button type="button" class="qty-btn qty-minus" onclick="decreaseQty()">-</button>
                    <input type="number" id="quantity-input" value="1" min="1" max="<?php echo $product['so_luong']; ?>" data-max="<?php echo $product['so_luong']; ?>" oninput="validateQuantity()">
                    <button type="button" class="qty-btn qty-plus" onclick="increaseQty()">+</button>
                </div>
                <div id="quantity-error" class="quantity-error" style="display: none;">
                    <i class="fa-solid fa-exclamation-circle"></i> Không đủ hàng trong kho!
                </div>
                <?php else: ?>
                <div class="out-of-stock-message" style="color: #e74c3c; font-weight: 600; margin-top: 10px;">
                    <i class="fa-solid fa-times-circle"></i> Sản phẩm hiện tại đã hết hàng!
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (isset($product['so_luong']) && $product['so_luong'] > 0): ?>
            <button class="add-to-cart-main-btn" onclick="addToCartDetail();">
                <i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ hàng
            </button>
            <?php else: ?>
            <button class="add-to-cart-main-btn" disabled style="background-color: #ccc; cursor: not-allowed; opacity: 0.6;">
                <i class="fa-solid fa-ban"></i> Hết hàng
            </button>
            <?php endif; ?>
        </div>
    </section>

    <!-- Product Tabs Section (Thông số & Đánh giá) -->
    <section id="product-tabs-section" class="section-p1">
        <div class="container">
            <div class="tabs-wrapper">
                <ul class="nav nav-tabs" id="productTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs" type="button" role="tab" aria-controls="specs" aria-selected="true">
                            <i class="fa-solid fa-list-check"></i> Thông số sản phẩm
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab" aria-controls="reviews" aria-selected="false">
                            <i class="fa-solid fa-star"></i> Đánh giá (<?php echo $total_reviews; ?>)
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="productTabsContent">
                    <!-- Tab Thông số sản phẩm -->
                    <div class="tab-pane fade show active" id="specs" role="tabpanel" aria-labelledby="specs-tab">
                        <div class="specs-content">
                            <?php if (!empty($product['ct_sp'])): ?>
                                <?php echo $product['ct_sp']; ?>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fa-solid fa-info-circle"></i> Thông số sản phẩm đang được cập nhật.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Tab Đánh giá -->
                    <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                        <div class="reviews-content">

    <!-- Reviews Section -->
    <div class="reviews-container">
        <h2>Đánh giá sản phẩm</h2>
        
        <?php if (isset($_SESSION['review_success'])): ?>
            <div class="success-message">
                <i class="fa-solid fa-check-circle"></i> <?php echo $_SESSION['review_success']; unset($_SESSION['review_success']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Review Form -->
        <div class="review-form-container">
            <h3>Viết đánh giá của bạn</h3>
            <?php if (isset($_SESSION['user_id'])): ?>
            <form method="POST" action="" class="review-form">
                
                <div class="form-group">
                    <label>Đánh giá <span class="required">*</span></label>
                    <div class="star-rating">
                        <input type="radio" id="star5" name="rating" value="5" required>
                        <label for="star5"><i class="fa-solid fa-star"></i></label>
                        <input type="radio" id="star4" name="rating" value="4">
                        <label for="star4"><i class="fa-solid fa-star"></i></label>
                        <input type="radio" id="star3" name="rating" value="3">
                        <label for="star3"><i class="fa-solid fa-star"></i></label>
                        <input type="radio" id="star2" name="rating" value="2">
                        <label for="star2"><i class="fa-solid fa-star"></i></label>
                        <input type="radio" id="star1" name="rating" value="1">
                        <label for="star1"><i class="fa-solid fa-star"></i></label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="review_text">Nội dung đánh giá <span class="required">*</span></label>
                    <textarea id="review_text" name="review_text" rows="5" required placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm..."></textarea>
                </div>
                
                <button type="submit" name="submit_review" class="submit-review-btn">
                    <i class="fa-solid fa-paper-plane"></i> Gửi đánh giá
                </button>
            </form>
            <?php else: ?>
            <div class="alert alert-warning">
                <i class="fa-solid fa-info-circle"></i> 
                Vui lòng <a href="login.php" style="color: #856404; font-weight: bold;">đăng nhập</a> để đánh giá sản phẩm.
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Display Reviews -->
        <div class="reviews-list">
            <h3>Đánh giá từ khách hàng (<?php echo $total_reviews; ?>)</h3>
            
            <?php if ($total_reviews > 0): ?>
                <div class="rating-summary">
                    <span class="avg-rating">⭐ <?php echo $average_rating; ?>/5</span>
                    <span class="total-reviews">(<?php echo $total_reviews; ?> đánh giá)</span>
                </div>
            <?php endif; ?>
            <?php if (count($reviews) > 0): ?>
                <?php foreach ($reviews as $review): ?>
                <div class="review-item">
                    <div class="review-header">
                        <div class="reviewer-info">
                            <i class="fa-solid fa-user-circle"></i>
                            <strong><?php echo htmlspecialchars($review['ten_nguoi_danh_gia']); ?></strong>
                        </div>
                        <div class="review-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fa-solid fa-star <?php echo $i <= $review['so_sao'] ? 'filled' : ''; ?>"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="review-body">
                        <p><?php echo nl2br(htmlspecialchars($review['noi_dung'])); ?></p>
                    </div>
                    
                    <?php if (!empty($review['phan_hoi'])): ?>
                    <div class="staff-reply">
                        <div class="reply-header">
                            <i class="fa-solid fa-headset"></i>
                            <strong><?php echo htmlspecialchars($review['nguoi_phan_hoi'] ? $review['nguoi_phan_hoi'] : 'Nhân viên AKI-Store'); ?></strong>
                        </div>
                        <div class="reply-body">
                            <p><?php echo nl2br(htmlspecialchars($review['phan_hoi'])); ?></p>
                        </div>
                        <div class="reply-footer">
                            <span class="reply-date"><i class="fa-regular fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($review['ngay_phan_hoi'])); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="review-footer">
                        <span class="review-date"><i class="fa-regular fa-clock"></i> <?php echo $review['ngay_formatted']; ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-reviews">Chưa có đánh giá nào. Hãy là người đầu tiên đánh giá sản phẩm này!</p>
            <?php endif; ?>
        </div>
    </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .tabs-wrapper {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 20px;
        }
        
        .nav-tabs {
            border-bottom: 2px solid #088178;
            margin-bottom: 20px;
        }
        
        .nav-tabs .nav-link {
            color: #666;
            border: none;
            border-bottom: 3px solid transparent;
            padding: 12px 24px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .nav-tabs .nav-link:hover {
            color: #088178;
            border-color: transparent;
        }
        
        .nav-tabs .nav-link.active {
            color: #088178;
            background: transparent;
            border-color: transparent transparent #088178 transparent;
        }
        
        .tab-content {
            padding: 20px 0;
        }
        
        .specs-content {
            line-height: 1.8;
        }
        
        .specs-content table {
            width: 100%;
            margin: 15px 0;
        }
        
        .specs-content img {
            max-width: 100%;
            height: auto;
            margin: 15px 0;
        }
        
        .specs-content iframe {
            max-width: 100%;
        }

        /* Lightbox Styles */
        .image-lightbox {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s;
        }

        .image-lightbox.active {
            display: flex;
        }

        .lightbox-content {
            position: relative;
            max-width: 90%;
            max-height: 90%;
            animation: zoomIn 0.3s;
        }

        .lightbox-content img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            max-height: 90vh;
            border-radius: 8px;
        }

        .lightbox-close {
            position: absolute;
            top: -40px;
            right: 0;
            font-size: 40px;
            color: white;
            background: transparent;
            border: none;
            cursor: pointer;
            font-weight: 300;
            line-height: 1;
            transition: all 0.3s;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .lightbox-close:hover {
            color: #088178;
            transform: rotate(90deg);
        }

        /* Navigation buttons */
        .lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            font-size: 30px;
            padding: 15px 20px;
            cursor: pointer;
            transition: all 0.3s;
            border-radius: 5px;
            backdrop-filter: blur(10px);
        }

        .lightbox-nav:hover {
            background-color: rgba(8, 129, 120, 0.8);
            transform: translateY(-50%) scale(1.1);
        }

        .lightbox-prev {
            left: 20px;
        }

        .lightbox-next {
            right: 20px;
        }

        /* Image counter */
        .lightbox-counter {
            position: absolute;
            bottom: -40px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            font-size: 16px;
            background-color: rgba(0, 0, 0, 0.5);
            padding: 8px 16px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes zoomIn {
            from { 
                transform: scale(0.5);
                opacity: 0;
            }
            to { 
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Make images clickable */
        #MainImg {
            cursor: pointer;
            transition: transform 0.3s;
        }

        #MainImg:hover {
            transform: scale(1.02);
        }

        .small-img {
            cursor: pointer;
            transition: all 0.3s;
        }

        .small-img:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
    </style>

    <!-- Reviews Section (Moved inside tab) -->
    <section id="reviews-section" class="section-p1" style="display:none;">
        <!-- This section is now moved to tabs -->
    </section>

    <!-- Image Lightbox -->
    <div class="image-lightbox" id="imageLightbox">
        <div class="lightbox-content">
            <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
            <button class="lightbox-nav lightbox-prev" onclick="previousImage()">&#10094;</button>
            <button class="lightbox-nav lightbox-next" onclick="nextImage()">&#10095;</button>
            <img id="lightboxImage" src="" alt="Phóng to ảnh">
            <div class="lightbox-counter" id="lightboxCounter">1 / 1</div>
        </div>
    </div>

    <!-- Related Products Section -->
    <section id="product1" class="section-p1">
        <h2>Sản phẩm liên quan</h2>
        <p>Sản phẩm cùng danh mục</p>
        <div class="pro-container">
            <?php if (!empty($related_products)): ?>
                <?php foreach ($related_products as $related): ?>
                    <?php
                        $related_discount = 0;
                        $related_final_price = $related['gia'];
                        if ($related['gia'] > 0 && $related['gia_khuyen_mai'] > 0) {
                            $related_discount = round((($related['gia'] - $related['gia_khuyen_mai']) / $related['gia']) * 100);
                            $related_final_price = $related['gia_khuyen_mai'];
                        }
                    ?>
                <div class="pro" onclick="window.location.href='sproduct.php?id=<?php echo $related['id_sanpham']; ?>';">
                    <?php if ($related_discount > 0): ?>
                        <div class="discount-badge discount-<?php echo $related_discount; ?>">
                            <div class="percent">-<?php echo $related_discount; ?>%</div>
                            <div class="text">OFF</div>
                        </div>
                    <?php endif; ?>
                    <div class="product-image-container">
                        <img src="<?php echo htmlspecialchars(!empty($related['hinh_anh']) ? $related['hinh_anh'] : 'img/products/f1.jpg'); ?>" alt="<?php echo htmlspecialchars($related['ten_sanpham']); ?>">
                        <div class="view-details-overlay">
                            <a href="sproduct.php?id=<?php echo $related['id_sanpham']; ?>" class="view-details-btn">
                                <i class="fa-solid fa-eye"></i> Xem chi tiết
                            </a>
                        </div>
                    </div>
                    <div class="des">
                        <span><?php echo htmlspecialchars(isset($product['ten_danhmuc']) ? $product['ten_danhmuc'] : 'Sản phẩm'); ?></span>
                        <h5><?php echo htmlspecialchars($related['ten_sanpham']); ?></h5>
                        <div class="star">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <?php if ($related_discount > 0): ?>
                            <h4><del style="color: #999; font-size: 0.9em;"><?php echo number_format($related['gia'], 0, ',', '.'); ?>VNĐ</del> <span style="color: #e74c3c; font-weight: bold;"><?php echo number_format($related_final_price, 0, ',', '.'); ?>VNĐ</span></h4>
                        <?php else: ?>
                            <h4><?php echo number_format($related['gia'], 0, ',', '.'); ?> VNĐ</h4>
                        <?php endif; ?>
                    </div>
                    <a href="#"><i class="fa-solid fa-cart-plus cart"></i></a>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; width: 100%;">Không có sản phẩm liên quan.</p>
            <?php endif; ?>
        </div>
    </section>

    <?php require('includes/footer.php'); ?>

    <script>
        // Lightbox Functions
        let currentImageIndex = 0;
        let allProductImages = [];

        // Initialize product images array
        document.addEventListener('DOMContentLoaded', function() {
            // Collect all images (main + sub images)
            <?php
            echo "allProductImages = [";
            $img_array = array();
            if (!empty($product['hinh_anh'])) {
                $img_array[] = "'" . addslashes($product['hinh_anh']) . "'";
            }
            if (!empty($product['anh_con'])) {
                $anh_con_array = explode('|', $product['anh_con']);
                foreach ($anh_con_array as $anh_con_item) {
                    $trimmed = trim($anh_con_item);
                    if (!empty($trimmed)) {
                        $img_array[] = "'" . addslashes($trimmed) . "'";
                    }
                }
            }
            echo implode(', ', $img_array);
            echo "];";
            ?>
        });

        function openLightbox(imageSrc) {
            const lightbox = document.getElementById('imageLightbox');
            const lightboxImage = document.getElementById('lightboxImage');
            
            // Find index of clicked image
            currentImageIndex = allProductImages.indexOf(imageSrc);
            if (currentImageIndex === -1) currentImageIndex = 0;
            
            lightboxImage.src = imageSrc;
            updateCounter();
            lightbox.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            const lightbox = document.getElementById('imageLightbox');
            lightbox.classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        function nextImage() {
            currentImageIndex = (currentImageIndex + 1) % allProductImages.length;
            const lightboxImage = document.getElementById('lightboxImage');
            lightboxImage.src = allProductImages[currentImageIndex];
            updateCounter();
        }

        function previousImage() {
            currentImageIndex = (currentImageIndex - 1 + allProductImages.length) % allProductImages.length;
            const lightboxImage = document.getElementById('lightboxImage');
            lightboxImage.src = allProductImages[currentImageIndex];
            updateCounter();
        }

        function updateCounter() {
            const counter = document.getElementById('lightboxCounter');
            counter.textContent = (currentImageIndex + 1) + ' / ' + allProductImages.length;
        }

        // Close lightbox when clicking outside the image
        document.addEventListener('DOMContentLoaded', function() {
            const lightbox = document.getElementById('imageLightbox');
            lightbox.addEventListener('click', function(e) {
                if (e.target === lightbox) {
                    closeLightbox();
                }
            });

            // Close with ESC key, navigate with arrow keys
            document.addEventListener('keydown', function(e) {
                const lightbox = document.getElementById('imageLightbox');
                if (lightbox.classList.contains('active')) {
                    if (e.key === 'Escape') {
                        closeLightbox();
                    } else if (e.key === 'ArrowRight') {
                        nextImage();
                    } else if (e.key === 'ArrowLeft') {
                        previousImage();
                    }
                }
            });
        });

        // Image gallery functionality
        var MainImg = document.getElementById("MainImg");
        var smallimg = document.getElementsByClassName("small-img");
        
        // Add click event to main image for lightbox
        if (MainImg) {
            MainImg.addEventListener('click', function() {
                openLightbox(this.src);
            });
        }
        
        for(let i = 0; i < smallimg.length; i++) {
            smallimg[i].onclick = function() {
                MainImg.src = smallimg[i].src;
                // Also open lightbox when clicking small images
                openLightbox(smallimg[i].src);
            }
        }
        
        // Color selection functionality
        var colorBoxes = document.querySelectorAll('.color-box');
        var colorSelect = document.getElementById('color-select');
        
        colorBoxes.forEach(function(box) {
            box.addEventListener('click', function() {
                // Remove selected class from all boxes
                colorBoxes.forEach(function(b) {
                    b.classList.remove('selected');
                });
                // Add selected class to clicked box
                this.classList.add('selected');
                // Update hidden input value
                colorSelect.value = this.getAttribute('data-color');
            });
        });
        
        // Quantity control functions
        var quantityInput = document.getElementById('quantity-input');
        if (quantityInput) {
            var maxStock = parseInt(quantityInput.getAttribute('data-max'));
            var quantityError = document.getElementById('quantity-error');
        }
        
        function increaseQty() {
            if (!quantityInput) return;
            var currentValue = parseInt(quantityInput.value) || 1;
            if (currentValue < maxStock) {
                quantityInput.value = currentValue + 1;
                validateQuantity();
            } else {
                showQuantityError();
            }
        }
        
        function decreaseQty() {
            if (!quantityInput) return;
            var currentValue = parseInt(quantityInput.value) || 1;
            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
                validateQuantity();
            }
        }
        
        function validateQuantity() {
            if (!quantityInput) return;
            var currentValue = parseInt(quantityInput.value) || 1;
            
            // Validate min
            if (currentValue < 1) {
                quantityInput.value = 1;
                hideQuantityError();
                return;
            }
            
            // Validate max
            if (currentValue > maxStock) {
                quantityInput.value = maxStock;
                showQuantityError();
                return;
            }
            
            hideQuantityError();
        }
        
        function showQuantityError() {
            if (quantityError) quantityError.style.display = 'flex';
        }
        
        function hideQuantityError() {
            if (quantityError) quantityError.style.display = 'none';
        }
        
        // Validate on input
        if (quantityInput) {
            quantityInput.addEventListener('input', validateQuantity);
            quantityInput.addEventListener('blur', function() {
                if (!quantityInput.value || quantityInput.value == '') {
                    quantityInput.value = 1;
                }
                validateQuantity();
            });
        }
        
        // Add to cart function
        function addToCartDetail() {
            <?php if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']): ?>
                alert('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng');
                window.location.href = 'login.php?redirect=sproduct.php?id=<?php echo $product['id_sanpham']; ?>';
                return;
            <?php endif; ?>
            
            var colorSelect = document.getElementById('color-select');
            var quantityInput = document.getElementById('quantity-input');
            
            // Lấy màu đã chọn
            var selectedColor = colorSelect ? colorSelect.value : '';
            
            // Kiểm tra bắt buộc chọn màu sắc
            if (!selectedColor || selectedColor === '') {
                alert('Bạn chưa chọn màu sắc. Vui lòng chọn màu sắc!');
                return;
            }
            
            var quantity = parseInt(quantityInput.value) || 1;
            
            // Gửi AJAX request
            var formData = new FormData();
            formData.append('user_id', <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; ?>);
            formData.append('product_id', <?php echo $product['id_sanpham']; ?>);
            formData.append('color_id', selectedColor);
            formData.append('quantity', quantity);
            
            fetch('api/add_to_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    // Reset quantity
                    quantityInput.value = 1;
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi thêm vào giỏ hàng');
            });
        }
    </script>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js?v=<?php echo time(); ?>"></script>
    <script src="https://cdn.botpress.cloud/webchat/v3.3/inject.js" defer></script>
    <script src="https://files.bpcontent.cloud/2025/11/26/16/20251126163853-AFN0KSEV.js" defer></script>
    <script src="js/mobile-responsive.js?v=1765636811"></script>
</body>
</html>