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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_id'])) {
    $return_id = intval($_POST['return_id']);
    $new_status = isset($_POST['new_status']) ? $_POST['new_status'] : '';
    $admin_note = isset($_POST['admin_note']) ? trim($_POST['admin_note']) : '';

    if (!in_array($new_status, $valid_statuses)) {
        $error_message = 'Trạng thái không hợp lệ.';
    } else {
        try {
            // Nếu chấp nhận đổi trả (approved), hoàn lại số lượng sản phẩm
            if ($new_status == 'approved') {
                // Lấy thông tin đơn hàng để hoàn lại số lượng
                $returnInfo = $conn->prepare("SELECT ma_donhang FROM don_hang_doi_tra WHERE id = ?");
                $returnInfo->execute(array($return_id));
                $returnData = $returnInfo->fetch(PDO::FETCH_ASSOC);
                
                if ($returnData) {
                    $ma_donhang = $returnData['ma_donhang'];
                    
                    // Lấy chi tiết sản phẩm trong đơn hàng
                    $detailStmt = $conn->prepare("SELECT id_sanpham, so_luong FROM chitiet_donhang WHERE ma_donhang = ?");
                    $detailStmt->execute(array($ma_donhang));
                    $details = $detailStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Hoàn lại số lượng cho từng sản phẩm
                    $restoreStock = $conn->prepare("UPDATE san_pham SET so_luong = so_luong + ? WHERE id_sanpham = ?");
                    foreach ($details as $detail) {
                        $restoreStock->execute(array($detail['so_luong'], $detail['id_sanpham']));
                    }
                    
                    // Cập nhật trạng thái đơn hàng thành 'huy'
                    $updateOrder = $conn->prepare("UPDATE don_hang SET trang_thai = 'huy' WHERE ma_donhang = ?");
                    $updateOrder->execute(array($ma_donhang));
                }
            }
            
            $update = $conn->prepare("UPDATE don_hang_doi_tra SET status = ?, ly_do = ly_do, updated_at = NOW() WHERE id = ?");
            $update->execute(array($new_status, $return_id));
            $success_message = 'Cập nhật trạng thái đổi trả thành công.';
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
$count_stmt = $conn->query("SELECT COUNT(*) as total FROM don_hang_doi_tra");
$count_result = $count_stmt->fetch(PDO::FETCH_ASSOC);
$total = $count_result['total'];
$total_pages = ceil($total / $per_page);

// Lấy danh sách yêu cầu đổi trả với phân trang
$stmt = $conn->query("SELECT 
        r.*,
        d.ten_nguoinhan,
        d.tong_tien,
        d.trang_thai,
        d.phuongthuc_thanhtoan,
        u.ho_ten AS ten_khach,
        u.email AS email_khach
    FROM don_hang_doi_tra r
    LEFT JOIN don_hang d ON r.ma_donhang = d.ma_donhang
    LEFT JOIN user u ON r.ma_user = u.ma_user
    ORDER BY r.id DESC
    LIMIT " . intval($per_page) . " OFFSET " . intval($offset));
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2 class="mb-4"><i class="fas fa-undo-alt me-2"></i> Quản lý đổi trả</h2>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <?php if (empty($requests)): ?>
                <p class="text-muted">Chưa có yêu cầu đổi trả nào.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Đơn hàng</th>
                                <th>Khách hàng</th>
                                <th>Lý do</th>
                                <th>Trạng thái</th>
                                <th>Ngày gửi</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $start_index = ($page - 1) * $per_page;
                            foreach ($requests as $index => $req): 
                            ?>
                                <tr>
                                    <td><?php echo $start_index + $index + 1; ?></td>
                                    <td>
                                        <strong>#<?php echo $req['ma_donhang']; ?></strong><br>
                                        <small><?php echo number_format($req['tong_tien'], 0, ',', '.'); ?>₫</small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($req['ten_khach']); ?><br>
                                        <small><?php echo htmlspecialchars($req['email_khach']); ?></small>
                                    </td>
                                    <td style="max-width: 250px;">
                                        <div style="white-space: pre-wrap;"><?php echo nl2br(htmlspecialchars($req['ly_do'])); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo ($req['status'] == 'approved' ? 'success' : ($req['status'] == 'rejected' ? 'danger' : 'warning text-dark')); ?>">
                                            <?php echo translate_return_status($req['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($req['created_at'])); ?></td>
                                    <td>
                                        <form method="post" class="row g-2">
                                            <input type="hidden" name="return_id" value="<?php echo $req['id']; ?>">
                                            <div class="col-12">
                                                <select name="new_status" class="form-select form-select-sm">
                                                    <?php foreach ($valid_statuses as $status_option): ?>
                                                        <option value="<?php echo $status_option; ?>" <?php echo ($req['status'] == $status_option) ? 'selected' : ''; ?>>
                                                            <?php echo translate_return_status($status_option); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-primary btn-sm w-100">Cập nhật</button>
                                            </div>
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
                                <a class="page-link" href="admin.php?action=doi_tra&page=<?php echo $page - 1; ?>">
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
                                <a class="page-link" href="admin.php?action=doi_tra&page=1">1</a>
                            </li>
                            <?php if ($start_page > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="admin.php?action=doi_tra&page=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($end_page < $total_pages): ?>
                            <?php if ($end_page < $total_pages - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="admin.php?action=doi_tra&page=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Nút Next -->
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="admin.php?action=doi_tra&page=<?php echo $page + 1; ?>">
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

