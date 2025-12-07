<?php
// Kiểm tra quyền truy cập
if (!isset($_SESSION['nhanvien_logged_in']) || $_SESSION['nhanvien_logged_in'] != true) {
    header('Location: login.php');
    exit();
}

// Xử lý xóa đánh giá
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    try {
        $stmt = $conn->prepare("DELETE FROM comments WHERE id_danh_gia = ?");
        $stmt->execute(array($delete_id));
        $delete_success = "Đã xóa đánh giá thành công!";
    } catch(PDOException $e) {
        $delete_error = "Lỗi khi xóa: " . $e->getMessage();
    }
}

// Xử lý ẩn/hiện đánh giá
if (isset($_GET['toggle_status'])) {
    $toggle_id = intval($_GET['toggle_status']);
    try {
        $stmt = $conn->prepare("UPDATE comments 
                               SET trang_thai = CASE WHEN trang_thai = 'hien' THEN 'an' ELSE 'hien' END
                               WHERE id_danh_gia = ?");
        $stmt->execute(array($toggle_id));
        $toggle_success = "Đã cập nhật trạng thái!";
    } catch(PDOException $e) {
        $toggle_error = "Lỗi khi cập nhật: " . $e->getMessage();
    }
}

// Xử lý phản hồi đánh giá
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_review'])) {
    $review_id = intval($_POST['review_id']);
    $reply_text = trim($_POST['reply_text']);
    
    // Lấy tên vai trò từ bảng vai_tro
    $user_email = '';
    if (isset($_SESSION['admin_email'])) {
        $user_email = $_SESSION['admin_email'];
    } elseif (isset($_SESSION['nhanvien_email'])) {
        $user_email = $_SESSION['nhanvien_email'];
    }
    
    $role_name = 'Nhân viên AKI-Store';
    
    if (!empty($user_email)) {
        try {
            $role_stmt = $conn->prepare("SELECT vt.ten_vaitro 
                                         FROM user u 
                                         LEFT JOIN vai_tro vt ON u.ma_vaitro = vt.ma_vaitro 
                                         WHERE u.email = ?");
            $role_stmt->execute(array($user_email));
            $role_data = $role_stmt->fetch(PDO::FETCH_ASSOC);
            if ($role_data && !empty($role_data['ten_vaitro'])) {
                $role_name = $role_data['ten_vaitro'];
            }
        } catch(PDOException $e) {
            // Giữ tên mặc định nếu có lỗi
        }
    }
    
    if (!empty($reply_text)) {
        try {
            $stmt = $conn->prepare("UPDATE comments 
                                   SET phan_hoi = ?, 
                                       nguoi_phan_hoi = ?,
                                       ngay_phan_hoi = NOW()
                                   WHERE id_danh_gia = ?");
            $stmt->execute(array($reply_text, $role_name, $review_id));
            $reply_success = "Đã gửi phản hồi thành công!";
        } catch(PDOException $e) {
            $reply_error = "Lỗi khi gửi phản hồi: " . $e->getMessage();
        }
    }
}

// Lấy thống kê
$stats_query = "SELECT 
                COUNT(*) as total_reviews,
                AVG(so_sao) as avg_rating,
                SUM(CASE WHEN trang_thai = 'hien' THEN 1 ELSE 0 END) as visible_reviews,
                SUM(CASE WHEN trang_thai = 'an' THEN 1 ELSE 0 END) as hidden_reviews
                FROM comments";
$stats_stmt = $conn->query($stats_query);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Phân trang và lọc
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$filter_rating = isset($_GET['rating']) ? intval($_GET['rating']) : 0;
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_category = isset($_GET['category']) ? intval($_GET['category']) : 0;
$search_keyword = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$where_conditions = array();
$params = array();

if ($filter_rating > 0) {
    $where_conditions[] = "c.so_sao = ?";
    $params[] = $filter_rating;
}

if ($filter_status !== '') {
    $where_conditions[] = "c.trang_thai = ?";
    $params[] = $filter_status;
}

if ($filter_category > 0) {
    $where_conditions[] = "sp.id_danhmuc = ?";
    $params[] = $filter_category;
}

if (!empty($search_keyword)) {
    $where_conditions[] = "(sp.ten_sanpham LIKE ? OR u.ho_ten LIKE ? OR c.noi_dung LIKE ?)";
    $search_param = "%{$search_keyword}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_clause = count($where_conditions) > 0 ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Count total
$count_query = "SELECT COUNT(*) as total 
                FROM comments c 
                LEFT JOIN san_pham sp ON c.id_sanpham = sp.id_sanpham
                LEFT JOIN user u ON c.ma_user = u.ma_user 
                {$where_clause}";
$count_stmt = $conn->prepare($count_query);
$count_stmt->execute($params);
$count_result = $count_stmt->fetch(PDO::FETCH_ASSOC);
$total_records = $count_result['total'];
$total_pages = ceil($total_records / $per_page);

// Get reviews
$query = "SELECT c.*, 
          sp.ten_sanpham, sp.hinh_anh,
          u.ho_ten as ten_nguoi_danh_gia,
          DATE_FORMAT(c.ngay_danh_gia, '%d/%m/%Y %H:%i') as ngay_formatted
          FROM comments c
          LEFT JOIN san_pham sp ON c.id_sanpham = sp.id_sanpham
          LEFT JOIN user u ON c.ma_user = u.ma_user
          {$where_clause}
          ORDER BY c.ngay_danh_gia DESC
          LIMIT {$per_page} OFFSET {$offset}";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách danh mục
$categories = array();
try {
    $cat_stmt = $conn->query("SELECT id_danhmuc, ten_danhmuc FROM danh_muc ORDER BY ten_danhmuc");
    $categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Lỗi lấy danh mục: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Đánh giá - AKI Store</title>
    <style>
        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
        }
        
        .stat-card:nth-child(2) {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .stat-card:nth-child(3) {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .stat-card:nth-child(4) {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        
        .stat-card h3 {
            font-size: 36px;
            font-weight: 700;
            margin: 0 0 10px 0;
        }
        
        .stat-card p {
            margin: 0;
            opacity: 0.9;
            font-size: 14px;
        }
        
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .filter-section .row {
            align-items: end;
        }
        
        .review-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .review-card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .product-info {
            display: flex;
            gap: 15px;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .product-info img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .product-info h6 {
            margin: 0;
            color: #333;
            font-weight: 600;
        }
        
        .rating-stars {
            color: #ffc107;
            margin-top: 5px;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-hien {
            background: #d4edda;
            color: #155724;
        }
        
        .status-an {
            background: #f8d7da;
            color: #721c24;
        }
        
        .review-content {
            margin: 15px 0;
        }
        
        .review-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn-action {
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 13px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .no-reviews {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .no-reviews i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        .staff-reply {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        
        .staff-reply strong {
            color: #2e7d32;
        }
        
        .reply-form-container {
            background: #f8f9fa;
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            border: 2px dashed #dee2e6;
        }
        
        .reply-form-container textarea {
            resize: vertical;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-star me-2"></i> Quản lý Đánh giá Sản phẩm</h2>
        </div>

        <!-- Thông báo -->
        <?php if (isset($reply_success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i> <?php echo $reply_success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($reply_error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $reply_error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($delete_success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i> <?php echo $delete_success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($toggle_success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i> <?php echo $toggle_success; ?>
            </div>
        <?php endif; ?>

        <!-- Thống kê -->
        <div class="stats-container">
            <div class="stat-card">
                <h3><?php echo number_format($stats['total_reviews']); ?></h3>
                <p><i class="fas fa-comments me-2"></i> Tổng đánh giá</p>
            </div>
            <div class="stat-card">
                <h3><?php echo number_format($stats['avg_rating'], 1); ?> <i class="fas fa-star" style="font-size: 24px;"></i></h3>
                <p><i class="fas fa-chart-line me-2"></i> Đánh giá trung bình</p>
            </div>
            <div class="stat-card">
                <h3><?php echo number_format($stats['visible_reviews']); ?></h3>
                <p><i class="fas fa-eye me-2"></i> Đang hiển thị</p>
            </div>
            <div class="stat-card">
                <h3><?php echo number_format($stats['hidden_reviews']); ?></h3>
                <p><i class="fas fa-eye-slash me-2"></i> Đã ẩn</p>
            </div>
        </div>

        <!-- Bộ lọc -->
        <div class="filter-section">
            <form method="GET" class="row g-3">
                <input type="hidden" name="action" value="danh_gia">
                
                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-search me-2"></i> Tìm kiếm</label>
                    <input type="text" name="search" class="form-control" placeholder="Tên sản phẩm, người đánh giá..." value="<?php echo htmlspecialchars($search_keyword); ?>">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label"><i class="fas fa-list me-2"></i> Danh mục</label>
                    <select name="category" class="form-select">
                        <option value="0">Tất cả</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id_danhmuc']; ?>" <?php echo $filter_category == $cat['id_danhmuc'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['ten_danhmuc']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label"><i class="fas fa-star me-2"></i> Số sao</label>
                    <select name="rating" class="form-select">
                        <option value="0">Tất cả</option>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?php echo $i; ?>" <?php echo $filter_rating == $i ? 'selected' : ''; ?>>
                                <?php echo $i; ?> sao
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label"><i class="fas fa-toggle-on me-2"></i> Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="hien" <?php echo $filter_status == 'hien' ? 'selected' : ''; ?>>Hiển thị</option>
                        <option value="an" <?php echo $filter_status == 'an' ? 'selected' : ''; ?>>Ẩn</option>
                    </select>
                </div>
                
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i> Lọc
                    </button>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <a href="?action=danh_gia" class="btn btn-secondary w-100">
                        <i class="fas fa-redo me-2"></i> Đặt lại
                    </a>
                </div>
            </form>
        </div>

        <!-- Danh sách đánh giá -->
        <div class="reviews-container">
            <?php if (count($reviews) > 0): ?>
                <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                    <!-- Thông tin sản phẩm -->
                    <div class="product-info">
                        <img src="<?php echo htmlspecialchars($review['hinh_anh']); ?>" alt="Product">
                        <div class="flex-grow-1">
                            <h6><?php echo htmlspecialchars($review['ten_sanpham']); ?></h6>
                            <div class="rating-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fa-solid fa-star <?php echo $i <= $review['so_sao'] ? '' : 'text-muted'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <span class="status-badge status-<?php echo $review['trang_thai']; ?>">
                            <?php echo $review['trang_thai'] == 'hien' ? 'Hiển thị' : 'Ẩn'; ?>
                        </span>
                    </div>

                    <!-- Nội dung đánh giá -->
                    <div class="review-content">
                        <div class="mb-2">
                            <strong><i class="fas fa-user-circle me-2"></i><?php echo htmlspecialchars($review['ten_nguoi_danh_gia']); ?></strong>
                            <span class="text-muted ms-3">
                                <i class="far fa-clock me-1"></i> <?php echo $review['ngay_formatted']; ?>
                            </span>
                        </div>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($review['noi_dung'])); ?></p>
                    </div>

                    <!-- Phản hồi của nhân viên -->
                    <?php if (!empty($review['phan_hoi'])): ?>
                    <div class="staff-reply">
                        <div class="mb-2">
                            <strong><i class="fas fa-headset me-2"></i><?php echo htmlspecialchars($review['nguoi_phan_hoi']); ?></strong>
                            <span class="text-muted ms-3">
                                <i class="far fa-clock me-1"></i> <?php echo date('d/m/Y H:i', strtotime($review['ngay_phan_hoi'])); ?>
                            </span>
                        </div>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($review['phan_hoi'])); ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- Form phản hồi -->
                    <div class="reply-form-container" id="reply-form-<?php echo $review['id_danh_gia']; ?>" style="display: none;">
                        <form method="POST" action="">
                            <input type="hidden" name="review_id" value="<?php echo $review['id_danh_gia']; ?>">
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-comment-dots me-2"></i>Nội dung phản hồi</label>
                                <textarea name="reply_text" class="form-control" rows="3" required placeholder="Nhập phản hồi của bạn..."><?php echo !empty($review['phan_hoi']) ? htmlspecialchars($review['phan_hoi']) : ''; ?></textarea>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" name="reply_review" class="btn btn-success btn-sm">
                                    <i class="fas fa-paper-plane me-1"></i> Gửi phản hồi
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="toggleReplyForm(<?php echo $review['id_danh_gia']; ?>)">
                                    <i class="fas fa-times me-1"></i> Hủy
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Nút hành động -->
                    <div class="review-actions">
                        <button type="button" class="btn btn-info btn-action" onclick="toggleReplyForm(<?php echo $review['id_danh_gia']; ?>)">
                            <i class="fas fa-reply"></i> <?php echo !empty($review['phan_hoi']) ? 'Sửa phản hồi' : 'Phản hồi'; ?>
                        </button>
                        
                        <a href="?action=danh_gia&toggle_status=<?php echo $review['id_danh_gia']; ?>&page=<?php echo $page; ?>&rating=<?php echo $filter_rating; ?>&status=<?php echo $filter_status; ?>&category=<?php echo $filter_category; ?>&search=<?php echo urlencode($search_keyword); ?>" 
                           class="btn btn-warning btn-action">
                            <i class="fas fa-eye<?php echo $review['trang_thai'] == 'hien' ? '-slash' : ''; ?>"></i>
                            <?php echo $review['trang_thai'] == 'hien' ? 'Ẩn' : 'Hiện'; ?>
                        </a>
                        
                        <a href="?action=danh_gia&delete_id=<?php echo $review['id_danh_gia']; ?>&page=<?php echo $page; ?>&rating=<?php echo $filter_rating; ?>&status=<?php echo $filter_status; ?>&category=<?php echo $filter_category; ?>&search=<?php echo urlencode($search_keyword); ?>" 
                           class="btn btn-danger btn-action" 
                           onclick="return confirm('Bạn có chắc muốn xóa đánh giá này?')">
                            <i class="fas fa-trash"></i> Xóa
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Phân trang -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?action=danh_gia&page=<?php echo $page - 1; ?>&rating=<?php echo $filter_rating; ?>&status=<?php echo $filter_status; ?>&category=<?php echo $filter_category; ?>&search=<?php echo urlencode($search_keyword); ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?action=danh_gia&page=<?php echo $i; ?>&rating=<?php echo $filter_rating; ?>&status=<?php echo $filter_status; ?>&category=<?php echo $filter_category; ?>&search=<?php echo urlencode($search_keyword); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?action=danh_gia&page=<?php echo $page + 1; ?>&rating=<?php echo $filter_rating; ?>&status=<?php echo $filter_status; ?>&category=<?php echo $filter_category; ?>&search=<?php echo urlencode($search_keyword); ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>

            <?php else: ?>
                <div class="no-reviews">
                    <i class="fas fa-inbox"></i>
                    <h5>Không có đánh giá nào</h5>
                    <p class="text-muted">Chưa có đánh giá phù hợp với bộ lọc của bạn.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    function toggleReplyForm(reviewId) {
        const form = document.getElementById('reply-form-' + reviewId);
        if (form.style.display === 'none') {
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    }
    </script>
</body>
</html>
