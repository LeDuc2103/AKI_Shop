<?php
// Kiểm tra đăng nhập
if (!isset($_SESSION['nhanvien_logged_in']) || $_SESSION['nhanvien_logged_in'] != true) {
    header('Location: ../login.php');
    exit();
}

// Xử lý xóa yêu cầu hỗ trợ
if (isset($_POST['delete_hotro'])) {
    $id = $_POST['id'];
    try {
        $stmt = $conn->prepare("DELETE FROM hotro WHERE id = ?");
        $stmt->execute(array($id));
        $success_message = "Đã xóa yêu cầu hỗ trợ thành công!";
    } catch (PDOException $e) {
        $error_message = "Lỗi khi xóa: " . $e->getMessage();
    }
}

// Xử lý cập nhật trạng thái (nếu cần thêm cột trạng thái sau này)
if (isset($_POST['update_status'])) {
    $id = $_POST['id'];
    // Có thể thêm chức năng cập nhật trạng thái ở đây
}

// Phân trang
$items_per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = "";
$params = array();

if (!empty($search)) {
    $where_clause = "WHERE ho_va_ten LIKE ? OR email LIKE ? OR so_dien_thoai LIKE ? OR noi_dung LIKE ?";
    $search_param = "%$search%";
    $params = array($search_param, $search_param, $search_param, $search_param);
}

// Đếm tổng số bản ghi
$count_sql = "SELECT COUNT(*) as total FROM hotro $where_clause";
$stmt = $conn->prepare($count_sql);
$stmt->execute($params);
$count_result = $stmt->fetch(PDO::FETCH_ASSOC);
$total_records = $count_result['total'];
$total_pages = ceil($total_records / $items_per_page);

// Lấy danh sách hỗ trợ
$sql = "SELECT * FROM hotro $where_clause ORDER BY ngay_gui DESC, created_at DESC LIMIT $items_per_page OFFSET $offset";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$hotro_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-headset"></i> Quản Lý Hỗ Trợ Khách Hàng</h2>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Tìm kiếm và lọc -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="action" value="hotro">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" name="search" placeholder="Tìm theo tên, email, SĐT, nội dung..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="per_page" class="form-select">
                        <option value="10" <?php echo $items_per_page == 10 ? 'selected' : ''; ?>>10 / trang</option>
                        <option value="20" <?php echo $items_per_page == 20 ? 'selected' : ''; ?>>20 / trang</option>
                        <option value="50" <?php echo $items_per_page == 50 ? 'selected' : ''; ?>>50 / trang</option>
                        <option value="100" <?php echo $items_per_page == 100 ? 'selected' : ''; ?>>100 / trang</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Lọc
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Thống kê -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5><i class="fas fa-inbox"></i> Tổng Yêu Cầu</h5>
                    <h2><?php echo $total_records; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5><i class="fas fa-calendar-day"></i> Hôm Nay</h5>
                    <h2>
                        <?php
                        $today_sql = "SELECT COUNT(*) as total FROM hotro WHERE DATE(ngay_gui) = CURDATE()";
                        $stmt = $conn->prepare($today_sql);
                        $stmt->execute();
                        $today_result = $stmt->fetch(PDO::FETCH_ASSOC);
                        echo $today_result['total'];
                        ?>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5><i class="fas fa-calendar-week"></i> Tuần Này</h5>
                    <h2>
                        <?php
                        $week_sql = "SELECT COUNT(*) as total FROM hotro WHERE YEARWEEK(ngay_gui, 1) = YEARWEEK(CURDATE(), 1)";
                        $stmt = $conn->prepare($week_sql);
                        $stmt->execute();
                        $week_result = $stmt->fetch(PDO::FETCH_ASSOC);
                        echo $week_result['total'];
                        ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng danh sách -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-list"></i> Danh Sách Yêu Cầu Hỗ Trợ (<?php echo $total_records; ?>)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th style="width: 150px;">Họ và Tên</th>
                            <th style="width: 180px;">Email</th>
                            <th style="width: 120px;">Số Điện Thoại</th>
                            <th>Nội Dung</th>
                            <th style="width: 110px;">Ngày Gửi</th>
                            <th style="width: 100px;" class="text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($hotro_list) > 0): ?>
                            <?php foreach ($hotro_list as $item): ?>
                                <tr>
                                    <td><?php echo $item['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item['ho_va_ten']); ?></strong>
                                    </td>
                                    <td>
                                        <a href="mailto:<?php echo htmlspecialchars($item['email']); ?>">
                                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($item['email']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="tel:<?php echo htmlspecialchars($item['so_dien_thoai']); ?>">
                                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($item['so_dien_thoai']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" 
                                             title="<?php echo htmlspecialchars($item['noi_dung']); ?>">
                                            <?php echo htmlspecialchars($item['noi_dung']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($item['ngay_gui']) {
                                            echo date('d/m/Y', strtotime($item['ngay_gui']));
                                        }
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $item['id']; ?>" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $item['id']; ?>" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal Xem Chi Tiết -->
                                <div class="modal fade" id="viewModal<?php echo $item['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header bg-info text-white">
                                                <h5 class="modal-title"><i class="fas fa-info-circle"></i> Chi Tiết Yêu Cầu Hỗ Trợ #<?php echo $item['id']; ?></h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <strong><i class="fas fa-user"></i> Họ và Tên:</strong>
                                                        <p><?php echo htmlspecialchars($item['ho_va_ten']); ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <strong><i class="fas fa-calendar"></i> Ngày Gửi:</strong>
                                                        <p><?php echo $item['ngay_gui'] ? date('d/m/Y H:i', strtotime($item['ngay_gui'])) : 'N/A'; ?></p>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <strong><i class="fas fa-envelope"></i> Email:</strong>
                                                        <p><a href="mailto:<?php echo htmlspecialchars($item['email']); ?>"><?php echo htmlspecialchars($item['email']); ?></a></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <strong><i class="fas fa-phone"></i> Số Điện Thoại:</strong>
                                                        <p><a href="tel:<?php echo htmlspecialchars($item['so_dien_thoai']); ?>"><?php echo htmlspecialchars($item['so_dien_thoai']); ?></a></p>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <strong><i class="fas fa-comment-dots"></i> Nội Dung:</strong>
                                                        <div class="p-3 bg-light rounded mt-2">
                                                            <?php echo nl2br(htmlspecialchars($item['noi_dung'])); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-md-12">
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock"></i> Tạo lúc: <?php echo $item['created_at'] ? date('d/m/Y H:i:s', strtotime($item['created_at'])) : 'N/A'; ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <a href="mailto:<?php echo htmlspecialchars($item['email']); ?>" class="btn btn-primary">
                                                    <i class="fas fa-reply"></i> Trả Lời Email
                                                </a>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Xóa -->
                                <div class="modal fade" id="deleteModal<?php echo $item['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title"><i class="fas fa-trash"></i> Xác Nhận Xóa</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Bạn có chắc chắn muốn xóa yêu cầu hỗ trợ từ <strong><?php echo htmlspecialchars($item['ho_va_ten']); ?></strong>?</p>
                                                <p class="text-muted"><small>Hành động này không thể hoàn tác!</small></p>
                                            </div>
                                            <div class="modal-footer">
                                                <form method="POST">
                                                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" name="delete_hotro" class="btn btn-danger">
                                                        <i class="fas fa-trash"></i> Xóa
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Chưa có yêu cầu hỗ trợ nào</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Phân trang -->
        <?php if ($total_pages > 1): ?>
            <div class="card-footer">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <!-- First Page -->
                        <?php if ($current_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?action=hotro&page=1&per_page=<?php echo $items_per_page; ?>&search=<?php echo urlencode($search); ?>">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Previous Page -->
                        <?php if ($current_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?action=hotro&page=<?php echo $current_page - 1; ?>&per_page=<?php echo $items_per_page; ?>&search=<?php echo urlencode($search); ?>">
                                    <i class="fas fa-angle-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);

                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?action=hotro&page=<?php echo $i; ?>&per_page=<?php echo $items_per_page; ?>&search=<?php echo urlencode($search); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <!-- Next Page -->
                        <?php if ($current_page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?action=hotro&page=<?php echo $current_page + 1; ?>&per_page=<?php echo $items_per_page; ?>&search=<?php echo urlencode($search); ?>">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Last Page -->
                        <?php if ($current_page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?action=hotro&page=<?php echo $total_pages; ?>&per_page=<?php echo $items_per_page; ?>&search=<?php echo urlencode($search); ?>">
                                    <i class="fas fa-angle-double-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <div class="text-center mt-2">
                    <small class="text-muted">
                        Trang <?php echo $current_page; ?> / <?php echo $total_pages; ?> 
                        (Tổng <?php echo $total_records; ?> yêu cầu)
                    </small>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>
