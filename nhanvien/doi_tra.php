<?php
if (!isset($conn)) {
    echo "<div class='alert alert-danger'>Không tìm thấy kết nối cơ sở dữ liệu.</div>";
    return;
}

if (!function_exists('translate_return_status')) {
    function translate_return_status($status) {
        switch ($status) {
            case 'pending':
                return 'Chờ xử lý';
            case 'approved':
                return 'Đồng ý';
            case 'rejected':
                return 'Từ chối';
            default:
                return $status;
        }
    }
}

$valid_statuses = array('pending', 'approved', 'rejected');
$success_message = '';
$error_message = '';

// Hiển thị thông báo sau redirect
if (isset($_GET['updated']) && $_GET['updated'] == '1') {
    $success_message = 'Cập nhật trạng thái đổi trả thành công.';
}

if (isset($_GET['deleted']) && $_GET['deleted'] == '1') {
    $success_message = 'Xóa yêu cầu đổi trả thành công.';
}

// Xử lý xóa yêu cầu đổi trả
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_return_id'])) {
    $delete_id = intval($_POST['delete_return_id']);
    
    try {
        $delete = $conn->prepare("DELETE FROM don_hang_doi_tra WHERE id = ?");
        $delete->execute(array($delete_id));
        
        header("Location: nhanvienbanhang.php?action=doi_tra&deleted=1");
        exit();
    } catch (PDOException $e) {
        $error_message = 'Lỗi khi xóa: ' . $e->getMessage();
    }
}

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_id'])) {
    $return_id = intval($_POST['return_id']);
    $new_status = isset($_POST['new_status']) ? $_POST['new_status'] : '';

    if (!in_array($new_status, $valid_statuses)) {
        $error_message = 'Trạng thái không hợp lệ.';
    } else {
        try {
            // Cập nhật trạng thái vào bảng don_hang_doi_tra
            $update = $conn->prepare("UPDATE don_hang_doi_tra SET status = ?, updated_at = NOW() WHERE id = ?");
            $update->execute(array($new_status, $return_id));
            
            // Redirect để tránh resubmit và force refresh data từ database
            header("Location: nhanvienbanhang.php?action=doi_tra&updated=1");
            exit();
        } catch (PDOException $e) {
            $error_message = 'Lỗi khi cập nhật: ' . $e->getMessage();
        }
    }
}

// Phân trang
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Đếm tổng số yêu cầu đổi trả
$stmt_count = $conn->query("SELECT COUNT(*) as total FROM don_hang_doi_tra");
$total_requests = $stmt_count->fetch(PDO::FETCH_ASSOC);
$total_requests = $total_requests['total'];
$total_pages = ceil($total_requests / $per_page);

// Lấy danh sách yêu cầu đổi trả với phân trang
$stmt = $conn->query("SELECT 
        r.*,
        d.ten_nguoinhan,
        d.tong_tien,
        d.trang_thai,
        d.phuongthuc_thanhtoan,
        d.trangthai_thanhtoan,
        d.email_nguoinhan,
        d.so_dienthoai,
        d.diachi_nhan,
        u.ho_ten AS ten_khach,
        u.email AS email_khach
    FROM don_hang_doi_tra r
    LEFT JOIN don_hang d ON r.ma_donhang = d.ma_donhang
    LEFT JOIN user u ON r.ma_user = u.ma_user
    ORDER BY 
        CASE r.status 
            WHEN 'pending' THEN 1 
            WHEN 'approved' THEN 2 
            WHEN 'rejected' THEN 3 
        END,
        r.id ASC
    LIMIT " . intval($per_page) . " OFFSET " . intval($offset));
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Đếm theo trạng thái (cho thống kê)
$stmt_stats = $conn->query("SELECT status, COUNT(*) as count FROM don_hang_doi_tra GROUP BY status");
$stats = array('pending' => 0, 'approved' => 0, 'rejected' => 0);
while ($row = $stmt_stats->fetch(PDO::FETCH_ASSOC)) {
    $stats[$row['status']] = $row['count'];
}
?>

<div class="container">
    <h2 class="mb-4"><i class="fas fa-undo-alt me-2"></i> Quản lý đổi trả</h2>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Thống kê nhanh -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <h5 class="card-title text-warning">
                        <i class="fas fa-clock"></i> Chờ xử lý
                    </h5>
                    <h2 class="mb-0">
                        <?php echo $stats['pending']; ?>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h5 class="card-title text-success">
                        <i class="fas fa-check-circle"></i> Đã đồng ý
                    </h5>
                    <h2 class="mb-0">
                        <?php echo $stats['approved']; ?>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center border-danger">
                <div class="card-body">
                    <h5 class="card-title text-danger">
                        <i class="fas fa-times-circle"></i> Đã từ chối
                    </h5>
                    <h2 class="mb-0">
                        <?php echo $stats['rejected']; ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (empty($requests)): ?>
                <p class="text-muted text-center py-4">
                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                    Chưa có yêu cầu đổi trả nào.
                </p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle" style="border: 1px solid #dee2e6;">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 40px; text-align: center; vertical-align: middle; border: 1px solid #dee2e6;">#</th>
                                <th style="width: 150px; text-align: center; vertical-align: middle; border: 1px solid #dee2e6;">Đơn hàng</th>
                                <th style="width: 180px; text-align: center; vertical-align: middle; border: 1px solid #dee2e6;">Khách hàng</th>
                                <th style="width: 250px; text-align: center; vertical-align: middle; border: 1px solid #dee2e6;">Lý do đổi trả</th>
                                <th style="width: 150px; text-align: center; vertical-align: middle; border: 1px solid #dee2e6;">Bằng chứng</th>
                                <th style="width: 110px; text-align: center; vertical-align: middle; border: 1px solid #dee2e6;">Trạng thái</th>
                                <th style="width: 100px; text-align: center; vertical-align: middle; border: 1px solid #dee2e6;">Ngày gửi</th>
                                <th style="width: 180px; text-align: center; vertical-align: middle; border: 1px solid #dee2e6;">Hành động</th>
                                <th style="width: 80px; text-align: center; vertical-align: middle; border: 1px solid #dee2e6;">Xóa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $start_index = ($page - 1) * $per_page;
                            foreach ($requests as $index => $req): 
                            ?>
                                <tr>
                                    <td style="text-align: center; vertical-align: middle; border: 1px solid #dee2e6; padding: 12px;">
                                        <?php echo $start_index + $index + 1; ?>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle; border: 1px solid #dee2e6; padding: 12px;">
                                        <strong class="text-primary">#<?php echo $req['ma_donhang']; ?></strong><br>
                                        <small class="text-muted">
                                            <i class="fas fa-money-bill-wave"></i>
                                            <?php echo number_format($req['tong_tien'], 0, ',', '.'); ?>₫
                                        </small><br>
                                        <small>
                                            <?php 
                                            if ($req['phuongthuc_thanhtoan'] == 'vnpay') {
                                                echo '<span class="badge bg-info"><i class="fas fa-credit-card"></i> VNPay</span>';
                                            } else {
                                                echo '<span class="badge bg-secondary"><i class="fas fa-hand-holding-usd"></i> COD</span>';
                                            }
                                            ?>
                                        </small>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle; border: 1px solid #dee2e6; padding: 12px;">
                                        <strong><?php echo htmlspecialchars($req['ten_khach']); ?></strong><br>
                                        <small class="text-muted" style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($req['email_khach']); ?>
                                        </small><br>
                                        <small class="text-muted">
                                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($req['so_dienthoai']); ?>
                                        </small>
                                    </td>
                                    <td style="vertical-align: top;  border: 1px solid #dee2e6; padding: 12px 8px -100px;">
                                        <div style="max-height: 100px; overflow-y: auto; white-space: pre-wrap; font-size: 13px; line-height: 1.5; text-align: left; word-wrap: break-word;">
                                            <?php echo nl2br(htmlspecialchars($req['ly_do'])); ?>
                                        </div>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle; border: 1px solid #dee2e6; padding: 12px;">
                                        <?php if (!empty($req['bang_chung'])): ?>
                                            <?php 
                                            $file_extension = strtolower(pathinfo($req['bang_chung'], PATHINFO_EXTENSION));
                                            $is_video = in_array($file_extension, array('mp4', 'avi', 'mov'));
                                            ?>
                                            <?php if ($is_video): ?>
                                                <a href="<?php echo htmlspecialchars($req['bang_chung']); ?>" target="_blank" class="btn btn-sm btn-info">
                                                    <i class="fas fa-video"></i> Xem video
                                                </a>
                                            <?php else: ?>
                                                <a href="<?php echo htmlspecialchars($req['bang_chung']); ?>" target="_blank">
                                                    <img src="<?php echo htmlspecialchars($req['bang_chung']); ?>" alt="Bằng chứng" style="max-width: 100px; max-height: 100px; border-radius: 5px; cursor: pointer;" onclick="window.open(this.src, '_blank')">
                                                </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <small class="text-muted"><i class="fas fa-ban"></i> Không có</small>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle; border: 1px solid #dee2e6; padding: 12px;">`
                                        <span class="badge bg-<?php echo ($req['status'] == 'approved' ? 'success' : ($req['status'] == 'rejected' ? 'danger' : 'warning text-dark')); ?> fs-6">
                                            <?php 
                                            if ($req['status'] == 'pending') {
                                                echo '<i class="fas fa-clock"></i>';
                                            } elseif ($req['status'] == 'approved') {
                                                echo '<i class="fas fa-check-circle"></i>';
                                            } else {
                                                echo '<i class="fas fa-times-circle"></i>';
                                            }
                                            echo ' ' . translate_return_status($req['status']); 
                                            ?>
                                        </span>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle; border: 1px solid #dee2e6; padding: 12px;">
                                        <small>
                                            <?php echo date('d/m/Y', strtotime($req['created_at'])); ?><br>
                                            <?php echo date('H:i', strtotime($req['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle; border: 1px solid #dee2e6; padding: 12px;">
                                        <form method="post" class="d-flex flex-column gap-2">
                                            <input type="hidden" name="return_id" value="<?php echo $req['id']; ?>">
                                            <select name="new_status" class="form-select form-select-sm" required>
                                                <option value="pending" <?php echo ($req['status'] == 'pending') ? 'selected' : ''; ?>>
                                                    Chờ xử lý
                                                </option>
                                                <option value="approved" <?php echo ($req['status'] == 'approved') ? 'selected' : ''; ?>>
                                                    Đồng ý
                                                </option>
                                                <option value="rejected" <?php echo ($req['status'] == 'rejected') ? 'selected' : ''; ?>>
                                                    Từ chối
                                                </option>
                                            </select>
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-save"></i> Cập nhật
                                            </button>
                                        </form>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle; border: 1px solid #dee2e6; padding: 12px;">
                                        <form method="post" onsubmit="return confirm('Bạn có chắc chắn muốn xóa yêu cầu đổi trả này?');">
                                            <input type="hidden" name="delete_return_id" value="<?php echo $req['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i> Xóa
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- PHÂN TRANG -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Phân trang đổi trả" class="mt-3">
                    <ul class="pagination justify-content-center">
                        <?php
                        $query_base = 'nhanvienbanhang.php?action=doi_tra';
                        
                        $prev = $page - 1;
                        $next = $page + 1;
                        
                        if ($page > 1) {
                            echo '<li class="page-item"><a class="page-link" href="' . $query_base . '&page=1">&laquo;</a></li>';
                            echo '<li class="page-item"><a class="page-link" href="' . $query_base . '&page=' . $prev . '">&lsaquo;</a></li>';
                        } else {
                            echo '<li class="page-item disabled"><span class="page-link">&laquo;</span></li>';
                            echo '<li class="page-item disabled"><span class="page-link">&lsaquo;</span></li>';
                        }
                        
                        $start = max(1, $page - 2);
                        $end = min($total_pages, $page + 2);
                        
                        for ($i = $start; $i <= $end; $i++) {
                            if ($i == $page) {
                                echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
                            } else {
                                echo '<li class="page-item"><a class="page-link" href="' . $query_base . '&page=' . $i . '">' . $i . '</a></li>';
                            }
                        }
                        
                        if ($page < $total_pages) {
                            echo '<li class="page-item"><a class="page-link" href="' . $query_base . '&page=' . $next . '">&rsaquo;</a></li>';
                            echo '<li class="page-item"><a class="page-link" href="' . $query_base . '&page=' . $total_pages . '">&raquo;</a></li>';
                        } else {
                            echo '<li class="page-item disabled"><span class="page-link">&rsaquo;</span></li>';
                            echo '<li class="page-item disabled"><span class="page-link">&raquo;</span></li>';
                        }
                        ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
