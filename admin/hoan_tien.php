<?php
if (!isset($conn)) {
    echo "<div class='alert alert-danger'>Không tìm thấy kết nối cơ sở dữ liệu.</div>";
    return;
}

// Tạo bảng hoan_tien nếu chưa tồn tại
$conn->exec("CREATE TABLE IF NOT EXISTS hoan_tien (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    ma_donhang BIGINT(20) NOT NULL,
    ma_user BIGINT(20) NOT NULL,
    so_tai_khoan VARCHAR(50) NOT NULL,
    ten_ngan_hang VARCHAR(100) NOT NULL,
    ly_do TEXT,
    so_tien DECIMAL(15,2) NOT NULL DEFAULT 0,
    trang_thai ENUM('chua_hoan_tien','da_hoan_tien') DEFAULT 'chua_hoan_tien',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_ma_donhang (ma_donhang),
    KEY idx_ma_user (ma_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

// Kiểm tra và thêm cột ma_user nếu chưa có (cho trường hợp bảng đã tồn tại từ trước)
try {
    $check_column = $conn->query("SHOW COLUMNS FROM hoan_tien LIKE 'ma_user'");
    if ($check_column->rowCount() == 0) {
        $conn->exec("ALTER TABLE hoan_tien ADD COLUMN ma_user BIGINT(20) NOT NULL AFTER ma_donhang");
        $conn->exec("ALTER TABLE hoan_tien ADD KEY idx_ma_user (ma_user)");
    }
} catch (PDOException $e) {
    // Bỏ qua lỗi nếu cột đã tồn tại
}

// Kiểm tra và thêm cột trang_thai nếu chưa có
try {
    $check_status = $conn->query("SHOW COLUMNS FROM hoan_tien LIKE 'trang_thai'");
    if ($check_status->rowCount() == 0) {
        $conn->exec("ALTER TABLE hoan_tien ADD COLUMN trang_thai ENUM('chua_hoan_tien','da_hoan_tien') DEFAULT 'chua_hoan_tien' AFTER so_tien");
    }
} catch (PDOException $e) {
    // Bỏ qua lỗi nếu cột đã tồn tại
}

// Kiểm tra và thêm các cột còn thiếu
try {
    // Kiểm tra so_tai_khoan
    $check_stk = $conn->query("SHOW COLUMNS FROM hoan_tien LIKE 'so_tai_khoan'");
    if ($check_stk->rowCount() == 0) {
        $conn->exec("ALTER TABLE hoan_tien ADD COLUMN so_tai_khoan VARCHAR(50) NOT NULL AFTER ma_user");
    }
    
    // Kiểm tra ten_ngan_hang
    $check_ngan_hang = $conn->query("SHOW COLUMNS FROM hoan_tien LIKE 'ten_ngan_hang'");
    if ($check_ngan_hang->rowCount() == 0) {
        $conn->exec("ALTER TABLE hoan_tien ADD COLUMN ten_ngan_hang VARCHAR(100) NOT NULL AFTER so_tai_khoan");
    }
    
    // Kiểm tra ly_do
    $check_ly_do = $conn->query("SHOW COLUMNS FROM hoan_tien LIKE 'ly_do'");
    if ($check_ly_do->rowCount() == 0) {
        $conn->exec("ALTER TABLE hoan_tien ADD COLUMN ly_do TEXT AFTER ten_ngan_hang");
    }
    
    // Kiểm tra so_tien
    $check_so_tien = $conn->query("SHOW COLUMNS FROM hoan_tien LIKE 'so_tien'");
    if ($check_so_tien->rowCount() == 0) {
        $conn->exec("ALTER TABLE hoan_tien ADD COLUMN so_tien DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER ly_do");
    }
    
    // Kiểm tra updated_at
    $check_updated = $conn->query("SHOW COLUMNS FROM hoan_tien LIKE 'updated_at'");
    if ($check_updated->rowCount() == 0) {
        $conn->exec("ALTER TABLE hoan_tien ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL AFTER created_at");
    }
} catch (PDOException $e) {
    // Bỏ qua lỗi nếu cột đã tồn tại
}

$success_message = '';
$error_message = '';

// Xử lý xóa yêu cầu hoàn tiền
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_refund'])) {
    $refund_id = intval($_POST['refund_id']);
    
    try {
        $deleteStmt = $conn->prepare("DELETE FROM hoan_tien WHERE id = ?");
        $deleteStmt->execute(array($refund_id));
        $success_message = 'Đã xóa yêu cầu hoàn tiền thành công!';
    } catch (PDOException $e) {
        $error_message = 'Lỗi khi xóa: ' . $e->getMessage();
    }
}

// Xử lý cập nhật trạng thái hoàn tiền
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_refund'])) {
    $refund_id = intval($_POST['refund_id']);
    $new_status = $_POST['new_status'];
    
    if (in_array($new_status, array('chua_hoan_tien', 'da_hoan_tien'))) {
        try {
            // Lấy thông tin yêu cầu hoàn tiền
            $stmt = $conn->prepare("SELECT * FROM hoan_tien WHERE id = ?");
            $stmt->execute(array($refund_id));
            $refund = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($refund) {
                // Cập nhật trạng thái hoàn tiền
                $update = $conn->prepare("UPDATE hoan_tien SET trang_thai = ?, updated_at = NOW() WHERE id = ?");
                $update->execute(array($new_status, $refund_id));
                
                $success_message = 'Cập nhật trạng thái hoàn tiền thành công!';
            }
        } catch (PDOException $e) {
            $error_message = 'Lỗi: ' . $e->getMessage();
        }
    }
}

// Bộ lọc tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : '';
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';

// Phân trang
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Xây dựng điều kiện WHERE
$where_conditions = array();
$params = array();

if (!empty($search)) {
    $where_conditions[] = "(ht.ma_donhang LIKE ? OR u.ho_ten LIKE ? OR ht.so_tai_khoan LIKE ?)";
    $search_param = '%' . $search . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($status_filter)) {
    $where_conditions[] = "ht.trang_thai = ?";
    $params[] = $status_filter;
}

if (!empty($date_filter)) {
    if ($date_filter == 'today') {
        $where_conditions[] = "DATE(ht.created_at) = CURDATE()";
    } elseif ($date_filter == 'this_month') {
        $where_conditions[] = "MONTH(ht.created_at) = MONTH(CURDATE()) AND YEAR(ht.created_at) = YEAR(CURDATE())";
    } elseif ($date_filter == 'this_year') {
        $where_conditions[] = "YEAR(ht.created_at) = YEAR(CURDATE())";
    } elseif ($date_filter == 'custom' && !empty($from_date) && !empty($to_date)) {
        $where_conditions[] = "DATE(ht.created_at) BETWEEN ? AND ?";
        $params[] = $from_date;
        $params[] = $to_date;
    }
}

$where_sql = '';
if (!empty($where_conditions)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Đếm tổng số yêu cầu với bộ lọc
$count_sql = "SELECT COUNT(*) as total FROM hoan_tien ht 
              LEFT JOIN user u ON ht.ma_user = u.ma_user 
              $where_sql";
$stmt_count = $conn->prepare($count_sql);
$stmt_count->execute($params);
$total_result = $stmt_count->fetch(PDO::FETCH_ASSOC);
$total = $total_result['total'];
$total_pages = ceil($total / $per_page);

// Lấy danh sách yêu cầu hoàn tiền
$sql = "SELECT 
    ht.*,
    dh.ten_nguoinhan,
    dh.email_nguoinhan,
    dh.so_dienthoai,
    dh.tong_tien,
    dh.trang_thai as trang_thai_donhang,
    dh.phuongthuc_thanhtoan,
    u.ho_ten,
    u.email
FROM hoan_tien ht
LEFT JOIN don_hang dh ON ht.ma_donhang = dh.ma_donhang
LEFT JOIN user u ON ht.ma_user = u.ma_user
$where_sql
ORDER BY 
    CASE ht.trang_thai 
        WHEN 'chua_hoan_tien' THEN 1 
        WHEN 'da_hoan_tien' THEN 2 
    END,
    ht.id DESC
LIMIT " . intval($per_page) . " OFFSET " . intval($offset);
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$refunds = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Thống kê với bộ lọc ngày
$stats_where = '';
$stats_params = array();
if (!empty($date_filter)) {
    if ($date_filter == 'today') {
        $stats_where = "WHERE DATE(created_at) = CURDATE()";
    } elseif ($date_filter == 'this_month') {
        $stats_where = "WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
    } elseif ($date_filter == 'this_year') {
        $stats_where = "WHERE YEAR(created_at) = YEAR(CURDATE())";
    } elseif ($date_filter == 'custom' && !empty($from_date) && !empty($to_date)) {
        $stats_where = "WHERE DATE(created_at) BETWEEN ? AND ?";
        $stats_params[] = $from_date;
        $stats_params[] = $to_date;
    }
}

$stmt_stats = $conn->prepare("SELECT trang_thai, COUNT(*) as count FROM hoan_tien $stats_where GROUP BY trang_thai");
$stmt_stats->execute($stats_params);
$stats = array('chua_hoan_tien' => 0, 'da_hoan_tien' => 0);
while ($row = $stmt_stats->fetch(PDO::FETCH_ASSOC)) {
    $stats[$row['trang_thai']] = $row['count'];
}
?>

<style>
.filter-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}
.filter-row {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: end;
}
.filter-group {
    flex: 1;
    min-width: 200px;
}
.filter-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}
.filter-group input,
.filter-group select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 4px;
}
.btn-filter {
    padding: 8px 20px;
    background: #088178;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
.btn-filter:hover {
    background: #066861;
}
.btn-reset {
    padding: 8px 20px;
    background: #6c757d;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
.btn-reset:hover {
    background: #5a6268;
}
</style>

<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-money-bill-wave me-2"></i> Quản lý hoàn tiền</h2>

    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Bộ lọc -->
    <div class="filter-section">
        <form method="GET" action="admin.php" id="filterForm">
            <input type="hidden" name="action" value="hoan_tien">
            <div class="filter-row">
                <div class="filter-group">
                    <label><i class="fas fa-search"></i> Tìm kiếm</label>
                    <input type="text" name="search" placeholder="Mã đơn, tên KH, số TK..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="filter-group">
                    <label><i class="fas fa-filter"></i> Trạng thái</label>
                    <select name="status">
                        <option value="">Tất cả</option>
                        <option value="chua_hoan_tien" <?php echo $status_filter == 'chua_hoan_tien' ? 'selected' : ''; ?>>Chưa hoàn tiền</option>
                        <option value="da_hoan_tien" <?php echo $status_filter == 'da_hoan_tien' ? 'selected' : ''; ?>>Đã hoàn tiền</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label><i class="fas fa-calendar"></i> Thời gian</label>
                    <select name="date_filter" id="dateFilter" onchange="toggleCustomDate()">
                        <option value="">Tất cả</option>
                        <option value="today" <?php echo $date_filter == 'today' ? 'selected' : ''; ?>>Hôm nay</option>
                        <option value="this_month" <?php echo $date_filter == 'this_month' ? 'selected' : ''; ?>>Tháng này</option>
                        <option value="this_year" <?php echo $date_filter == 'this_year' ? 'selected' : ''; ?>>Năm nay</option>
                        <option value="custom" <?php echo $date_filter == 'custom' ? 'selected' : ''; ?>>Tùy chỉnh</option>
                    </select>
                </div>
                
                <div class="filter-group" id="customDateGroup" style="display: <?php echo $date_filter == 'custom' ? 'block' : 'none'; ?>;">
                    <label>Từ ngày</label>
                    <input type="date" name="from_date" value="<?php echo htmlspecialchars($from_date); ?>">
                </div>
                
                <div class="filter-group" id="customDateGroup2" style="display: <?php echo $date_filter == 'custom' ? 'block' : 'none'; ?>;">
                    <label>Đến ngày</label>
                    <input type="date" name="to_date" value="<?php echo htmlspecialchars($to_date); ?>">
                </div>
                
                <div class="filter-group" style="min-width: auto;">
                    <button type="submit" class="btn-filter">
                        <i class="fas fa-search"></i> Lọc
                    </button>
                </div>
                
                <div class="filter-group" style="min-width: auto;">
                    <a href="admin.php?action=hoan_tien" class="btn-reset">
                        <i class="fas fa-redo"></i> Đặt lại
                    </a>
                </div>
            </div>
        </form>
    </div>

    <script>
    function toggleCustomDate() {
        var dateFilter = document.getElementById('dateFilter').value;
        var customGroup1 = document.getElementById('customDateGroup');
        var customGroup2 = document.getElementById('customDateGroup2');
        if (dateFilter === 'custom') {
            customGroup1.style.display = 'block';
            customGroup2.style.display = 'block';
        } else {
            customGroup1.style.display = 'none';
            customGroup2.style.display = 'none';
        }
    }
    </script>

    <!-- Thống kê -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <h5 class="card-title text-warning"><i class="fas fa-clock"></i> Chưa hoàn tiền</h5>
                    <h2><?php echo $stats['chua_hoan_tien']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h5 class="card-title text-success"><i class="fas fa-check-circle"></i> Đã hoàn tiền</h5>
                    <h2><?php echo $stats['da_hoan_tien']; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Danh sách yêu cầu -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($refunds)): ?>
                <p class="text-muted text-center py-4">
                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                    Chưa có yêu cầu hoàn tiền nào.
                </p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Đơn hàng</th>
                                <th>Khách hàng</th>
                                <th>Số TK</th>
                                <th>Ngân hàng</th>
                                <th>Lý do</th>
                                <th>Số tiền</th>
                                <th>Trạng thái</th>
                                <th>Ngày yêu cầu</th>
                                <th style="width: 180px;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $start = ($page - 1) * $per_page;
                            foreach ($refunds as $idx => $rf): 
                            ?>
                            <tr>
                                <td class="text-center"><?php echo $start + $idx + 1; ?></td>
                                <td>
                                    <strong class="text-primary">#<?php echo $rf['ma_donhang']; ?></strong><br>
                                    <small><?php echo number_format($rf['tong_tien'], 0, ',', '.'); ?>₫</small>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($rf['ho_ten']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($rf['email']); ?></small><br>
                                    <small><?php echo htmlspecialchars($rf['so_dienthoai']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($rf['so_tai_khoan']); ?></td>
                                <td><?php echo htmlspecialchars($rf['ten_ngan_hang']); ?></td>
                                <td>
                                    <div style="max-height: 80px; overflow-y: auto;">
                                        <?php echo nl2br(htmlspecialchars($rf['ly_do'])); ?>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <strong><?php echo number_format($rf['so_tien'], 0, ',', '.'); ?>₫</strong>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $badge_class = 'warning';
                                    $icon = 'clock';
                                    $text = 'Chưa hoàn tiền';
                                    
                                    if ($rf['trang_thai'] == 'da_hoan_tien') {
                                        $badge_class = 'success';
                                        $icon = 'check-circle';
                                        $text = 'Đã hoàn tiền';
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo $badge_class; ?>">
                                        <i class="fas fa-<?php echo $icon; ?>"></i> <?php echo $text; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <small><?php echo date('d/m/Y H:i', strtotime($rf['created_at'])); ?></small>
                                </td>
                                <td>
                                    <form method="post" class="d-flex flex-column gap-2">
                                        <input type="hidden" name="refund_id" value="<?php echo $rf['id']; ?>">
                                        <select name="new_status" class="form-select form-select-sm">
                                            <option value="chua_hoan_tien" <?php echo $rf['trang_thai'] == 'chua_hoan_tien' ? 'selected' : ''; ?>>Chưa hoàn tiền</option>
                                            <option value="da_hoan_tien" <?php echo $rf['trang_thai'] == 'da_hoan_tien' ? 'selected' : ''; ?>>Đã hoàn tiền</option>
                                        </select>
                                        <button type="submit" name="update_refund" class="btn btn-primary btn-sm">
                                            <i class="fas fa-save"></i> Cập nhật
                                        </button>
                                        <button type="submit" name="delete_refund" class="btn btn-danger btn-sm" 
                                                onclick="return confirm('Bạn có chắc chắn muốn xóa yêu cầu hoàn tiền #<?php echo $rf['id']; ?> của đơn hàng #<?php echo $rf['ma_donhang']; ?>?');">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Phân trang -->
                <?php if ($total_pages > 1): ?>
                <nav class="mt-3">
                    <ul class="pagination justify-content-center">
                        <!-- Nút Previous -->
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="admin.php?action=hoan_tien&page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&date_filter=<?php echo urlencode($date_filter); ?>&from_date=<?php echo urlencode($from_date); ?>&to_date=<?php echo urlencode($to_date); ?>">
                                    <i class="fas fa-chevron-left"></i> Trước
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Số trang -->
                        <?php 
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        if ($start_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="admin.php?action=hoan_tien&page=1&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&date_filter=<?php echo urlencode($date_filter); ?>&from_date=<?php echo urlencode($from_date); ?>&to_date=<?php echo urlencode($to_date); ?>">1</a>
                            </li>
                            <?php if ($start_page > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="admin.php?action=hoan_tien&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&date_filter=<?php echo urlencode($date_filter); ?>&from_date=<?php echo urlencode($from_date); ?>&to_date=<?php echo urlencode($to_date); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($end_page < $total_pages): ?>
                            <?php if ($end_page < $total_pages - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="admin.php?action=hoan_tien&page=<?php echo $total_pages; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&date_filter=<?php echo urlencode($date_filter); ?>&from_date=<?php echo urlencode($from_date); ?>&to_date=<?php echo urlencode($to_date); ?>"><?php echo $total_pages; ?></a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Nút Next -->
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="admin.php?action=hoan_tien&page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&date_filter=<?php echo urlencode($date_filter); ?>&from_date=<?php echo urlencode($from_date); ?>&to_date=<?php echo urlencode($to_date); ?>">
                                    Sau <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    
                    <p class="text-center text-muted mt-2">
                        Hiển thị trang <?php echo $page; ?> / <?php echo $total_pages; ?> 
                        (Tổng <?php echo $total; ?> yêu cầu)
                    </p>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
