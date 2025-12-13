<?php
if (!isset($conn)) {
    echo "<div class='alert alert-danger'>Không tìm thấy kết nối cơ sở dữ liệu.</div>";
    return;
}

$success_message = '';
$error_message = '';

// Xử lý thêm/sửa khuyến mãi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add') {
            $ten_km = trim($_POST['ten_km']);
            $phan_tram_km = floatval($_POST['phan_tram_km']);
            $ngay_bat_dau = $_POST['ngay_bat_dau'];
            $ngay_ket_thuc = $_POST['ngay_ket_thuc'];
            
            try {
                $stmt = $conn->prepare("INSERT INTO khuyen_mai (ten_km, phan_tram_km, ngay_bat_dau, ngay_ket_thuc, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
                $stmt->execute(array($ten_km, $phan_tram_km, $ngay_bat_dau, $ngay_ket_thuc));
                $success_message = "Thêm khuyến mãi thành công!";
            } catch (PDOException $e) {
                $error_message = "Lỗi: " . $e->getMessage();
            }
        } elseif ($action === 'edit') {
            $id = intval($_POST['id']);
            $ten_km = trim($_POST['ten_km']);
            $phan_tram_km = floatval($_POST['phan_tram_km']);
            $ngay_bat_dau = $_POST['ngay_bat_dau'];
            $ngay_ket_thuc = $_POST['ngay_ket_thuc'];
            
            try {
                $stmt = $conn->prepare("UPDATE khuyen_mai SET ten_km = ?, phan_tram_km = ?, ngay_bat_dau = ?, ngay_ket_thuc = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute(array($ten_km, $phan_tram_km, $ngay_bat_dau, $ngay_ket_thuc, $id));
                $success_message = "Cập nhật khuyến mãi thành công!";
            } catch (PDOException $e) {
                $error_message = "Lỗi: " . $e->getMessage();
            }
        } elseif ($action === 'delete') {
            $id = intval($_POST['id']);
            
            try {
                $stmt = $conn->prepare("DELETE FROM khuyen_mai WHERE id = ?");
                $stmt->execute(array($id));
                $success_message = "Xóa khuyến mãi thành công!";
            } catch (PDOException $e) {
                $error_message = "Lỗi: " . $e->getMessage();
            }
        }
    }
}

// Lấy danh sách khuyến mãi
$khuyenmai_list = array();
try {
    $stmt = $conn->query("SELECT * FROM khuyen_mai ORDER BY id DESC");
    $khuyenmai_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Lỗi khi lấy danh sách: " . $e->getMessage();
}
?>

<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-gift me-2"></i> Quản lý khuyến mãi</h2>

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

    <!-- Nút thêm khuyến mãi -->
    <div class="mb-3">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fas fa-plus me-2"></i> Thêm khuyến mãi mới
        </button>
    </div>

    <!-- Bảng danh sách khuyến mãi -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($khuyenmai_list)): ?>
                <p class="text-muted text-center py-4">
                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                    Chưa có khuyến mãi nào.
                </p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 60px;">ID</th>
                                <th>Tên khuyến mãi</th>
                                <th style="width: 120px;">Phần trăm (%)</th>
                                <th style="width: 150px;">Ngày bắt đầu</th>
                                <th style="width: 150px;">Ngày kết thúc</th>
                                <th style="width: 120px;">Trạng thái</th>
                                <th style="width: 150px;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($khuyenmai_list as $km): ?>
                                <?php
                                $today = date('Y-m-d');
                                $is_active = false;
                                $status_text = 'Chưa bắt đầu';
                                $status_class = 'secondary';
                                
                                if (!empty($km['ngay_bat_dau']) && !empty($km['ngay_ket_thuc'])) {
                                    if ($today >= $km['ngay_bat_dau'] && $today <= $km['ngay_ket_thuc']) {
                                        $is_active = true;
                                        $status_text = 'Đang diễn ra';
                                        $status_class = 'success';
                                    } elseif ($today > $km['ngay_ket_thuc']) {
                                        $status_text = 'Đã kết thúc';
                                        $status_class = 'danger';
                                    }
                                }
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $km['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($km['ten_km']); ?></strong></td>
                                    <td class="text-center">
                                        <span class="badge bg-info fs-6"><?php echo $km['phan_tram_km']; ?>%</span>
                                    </td>
                                    <td class="text-center"><?php echo !empty($km['ngay_bat_dau']) ? date('d/m/Y', strtotime($km['ngay_bat_dau'])) : '-'; ?></td>
                                    <td class="text-center"><?php echo !empty($km['ngay_ket_thuc']) ? date('d/m/Y', strtotime($km['ngay_ket_thuc'])) : '-'; ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-warning" 
                                                onclick='editKhuyenMai(<?php echo json_encode($km); ?>)'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="deleteKhuyenMai(<?php echo $km['id']; ?>, '<?php echo htmlspecialchars($km['ten_km']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal thêm khuyến mãi -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i> Thêm khuyến mãi mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label class="form-label">Tên khuyến mãi <span class="text-danger">*</span></label>
                        <input type="text" name="ten_km" class="form-control" required 
                               placeholder="VD: GIAM20, KHUYENMAI50">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Phần trăm giảm giá (%) <span class="text-danger">*</span></label>
                        <input type="number" name="phan_tram_km" class="form-control" required 
                               min="0" max="100" step="0.01" placeholder="VD: 10, 20, 50">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                        <input type="date" name="ngay_bat_dau" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                        <input type="date" name="ngay_ket_thuc" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal sửa khuyến mãi -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Sửa khuyến mãi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Tên khuyến mãi <span class="text-danger">*</span></label>
                        <input type="text" name="ten_km" id="edit_ten_km" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Phần trăm giảm giá (%) <span class="text-danger">*</span></label>
                        <input type="number" name="phan_tram_km" id="edit_phan_tram_km" class="form-control" required 
                               min="0" max="100" step="0.01">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                        <input type="date" name="ngay_bat_dau" id="edit_ngay_bat_dau" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                        <input type="date" name="ngay_ket_thuc" id="edit_ngay_ket_thuc" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal xác nhận xóa -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-trash me-2"></i> Xác nhận xóa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    <p>Bạn có chắc chắn muốn xóa khuyến mãi <strong id="delete_name"></strong>?</p>
                    <p class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i> Hành động này không thể hoàn tác!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i> Xóa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editKhuyenMai(km) {
    document.getElementById('edit_id').value = km.id;
    document.getElementById('edit_ten_km').value = km.ten_km;
    document.getElementById('edit_phan_tram_km').value = km.phan_tram_km;
    document.getElementById('edit_ngay_bat_dau').value = km.ngay_bat_dau;
    document.getElementById('edit_ngay_ket_thuc').value = km.ngay_ket_thuc;
    
    var editModal = new bootstrap.Modal(document.getElementById('editModal'));
    editModal.show();
}

function deleteKhuyenMai(id, name) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_name').textContent = name;
    
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>
