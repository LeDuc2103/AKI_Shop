<?php
// Tên file: danhmuc.php (Quản lý Danh mục - Đã thêm created_at/update_at)
if (!isset($conn)) {
    require_once '../config/database.php';
}

$action = isset($_GET['act']) ? $_GET['act'] : 'danhsach';
$message = '';

// --- LOGIC THÊM DANH MỤC ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_category']) && ($action == 'them')) {
    $ten = isset($_POST['ten_danhmuc']) ? trim($_POST['ten_danhmuc']) : '';
    
    if (empty($ten)) {
        $message = '<div class="alert alert-warning">Tên danh mục không được để trống.</div>';
    } else {
        // ### SỬA: THÊM created_at VÀ update_at KHI INSERT ###
        $stmt = $conn->prepare("INSERT INTO danh_muc (ten_danhmuc, created_at, update_at) VALUES (?, NOW(), NOW())");
        $ok = $stmt->execute(array($ten));
        
        if ($ok) {
            // POST-Redirect-GET
            header("Location: admin.php?action=danhmuc");
            exit();
        } else {
            $errorInfo = $stmt->errorInfo();
            $message = '<div class="alert alert-danger">Lỗi khi thêm danh mục vào Database. Lỗi SQL: ' . htmlspecialchars($errorInfo[2]) . '</div>';
        }
    }
}

// --- LOGIC SỬA DANH MỤC ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_category']) && ($action == 'sua') && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $ten = isset($_POST['ten_danhmuc']) ? trim($_POST['ten_danhmuc']) : '';
    
    if (empty($ten)) {
        $message = '<div class="alert alert-warning">Tên danh mục không được để trống.</div>';
    } else {
        // ### SỬA: CHỈ CẬP NHẬT update_at KHI UPDATE ###
        $stmt = $conn->prepare("UPDATE danh_muc SET ten_danhmuc = ?, update_at = NOW() WHERE id_danhmuc = ?");
        $ok = $stmt->execute(array($ten, $id));
        
        if ($ok) {
            // POST-Redirect-GET
            header("Location: admin.php?action=danhmuc");
            exit();
        } else {
            $errorInfo = $stmt->errorInfo();
            $message = '<div class="alert alert-danger">Lỗi khi cập nhật danh mục. Lỗi SQL: ' . htmlspecialchars($errorInfo[2]) . '</div>';
        }
    }
}

// --- LOGIC XÓA DANH MỤC ---
if ($action == 'xoa' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $stmtd = $conn->prepare("DELETE FROM danh_muc WHERE id_danhmuc = ?");
    $ok = $stmtd->execute(array($id));
    
    if ($ok) {
        // POST-Redirect-GET
        header("Location: admin.php?action=danhmuc");
        exit(); 
    } else {
        $message = '<div class="alert alert-danger">Lỗi khi xóa danh mục. Hãy đảm bảo không còn sản phẩm nào thuộc danh mục này.</div>';
    }
}

// --- LOGIC HIỂN THỊ DANH SÁCH ---
$stmt = $conn->prepare("SELECT * FROM danh_muc ORDER BY id_danhmuc DESC");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h2><i class="fas fa-list me-2"></i> Quản lý Danh mục</h2>
    <?php echo $message; ?>

    <?php
    $is_edit = false;
    $edit_data = array();
    
    if ($action == 'sua' && isset($_GET['id'])) {
        $is_edit = true;
        $eid = intval($_GET['id']);
        $stmt = $conn->prepare("SELECT * FROM danh_muc WHERE id_danhmuc = ?");
        $stmt->execute(array($eid));
        $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$edit_data) {
            echo '<div class="alert alert-danger">Danh mục không tìm thấy.</div>';
            $is_edit = false;
        }
    }

    // Quyết định trạng thái mở/đóng form
    $form_is_open = $is_edit || ($action == 'them' && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_category']));
    $collapse_class = $form_is_open ? 'collapse show' : 'collapse';
    $button_expanded = $form_is_open ? 'true' : 'false';
    
    $form_action = $is_edit ? 'admin.php?action=danhmuc&act=sua&id=' . ($is_edit ? intval($_GET['id']) : '') : 'admin.php?action=danhmuc&act=them';
    ?>

    <button class="btn btn-primary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#categoryFormArea" aria-expanded="<?php echo $button_expanded; ?>" aria-controls="categoryFormArea">
        <i class="fas fa-plus-circle me-1"></i> <?php echo $is_edit ? 'Sửa Danh mục' : 'Thêm Danh mục mới'; ?>
    </button>
    
    <div id="categoryFormArea" class="<?php echo $collapse_class; ?>">
        <form method="post" action="<?php echo $form_action; ?>">
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                <input type="text" name="ten_danhmuc" class="form-control" placeholder="Nhập tên danh mục..." required 
                       value="<?php echo $is_edit ? htmlspecialchars($edit_data['ten_danhmuc']) : (isset($ten) ? htmlspecialchars($ten) : ''); ?>">
                <button type="submit" name="save_category" class="btn btn-success">
                    <i class="fas fa-save me-1"></i> <?php echo $is_edit ? 'Cập nhật' : 'Lưu'; ?>
                </button>
                <a href="admin.php?action=danhmuc" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i> Hủy
                </a>
            </div>
        </form>
        <hr>
    </div>
    
    <h3><i class="fas fa-clipboard-list me-2"></i> Danh sách Danh mục (<?php echo count($categories); ?>)</h3>
    <?php if (count($categories) == 0): ?>
        <div class="alert alert-info text-center mt-4" role="alert">
            <i class="fas fa-info-circle me-2"></i> Hiện chưa có danh mục nào.
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Tên Danh mục</th>
                    <th>Ngày tạo</th> 
                    <th>Ngày cập nhật</th> 
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($categories as $c): ?>
                <tr>
                    <td><?php echo $c['id_danhmuc']; ?></td>
                    <td><?php echo htmlspecialchars($c['ten_danhmuc']); ?></td>
                    <td><?php echo $c['created_at']; ?></td>
                    <td><?php echo $c['update_at']; ?></td>
                    <td>
                        <a class="btn btn-sm btn-primary" href="admin.php?action=danhmuc&act=sua&id=<?php echo $c['id_danhmuc']; ?>">
                            <i class="fas fa-edit"></i> Sửa
                        </a>
                        <a class="btn btn-sm btn-danger" href="admin.php?action=danhmuc&act=xoa&id=<?php echo $c['id_danhmuc']; ?>" onclick="return confirm('CẢNH BÁO: Việc xóa danh mục này có thể xóa cả sản phẩm liên quan. Bạn có chắc chắn muốn xóa?');">
                            <i class="fas fa-trash"></i> Xóa
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>