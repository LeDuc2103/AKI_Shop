<?php
// File: nhanvien/banner.php
// Chức năng: Quản lý banner cho nhân viên bán hàng

// Tăng giới hạn upload file lên 20MB
@ini_set('upload_max_filesize', '20M');
@ini_set('post_max_size', '20M');
@ini_set('memory_limit', '256M');
@ini_set('max_execution_time', '300');

if (!isset($conn)) {
    echo "<div class='alert alert-danger'>Không tìm thấy kết nối cơ sở dữ liệu.</div>";
    return;
}

$success_message = '';
$error_message = '';
$action = isset($_GET['act']) ? $_GET['act'] : 'list';
$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;

// Lấy thông tin nhân viên từ session
$ma_user_session = isset($_SESSION['nhanvien_id']) ? $_SESSION['nhanvien_id'] : 0;

// Xử lý xóa banner
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    try {
        // Lấy thông tin hình ảnh trước khi xóa
        $stmt_img = $conn->prepare("SELECT hinh_anh FROM banners WHERE ma_banner = ?");
        $stmt_img->execute(array($delete_id));
        $img_data = $stmt_img->fetch(PDO::FETCH_ASSOC);
        
        // Xóa banner
        $stmt_delete = $conn->prepare("DELETE FROM banners WHERE ma_banner = ?");
        $stmt_delete->execute(array($delete_id));
        
        // Xóa file hình ảnh nếu có
        if ($img_data && !empty($img_data['hinh_anh'])) {
            $img_full_path = dirname(__FILE__) . '/../' . $img_data['hinh_anh'];
            if (file_exists($img_full_path)) {
                @unlink($img_full_path);
            }
        }
        
        header("Location: nhanvienbanhang.php?action=banner");
        exit;
    } catch (PDOException $e) {
        $error_message = 'Lỗi khi xóa banner: ' . $e->getMessage();
    }
}

// Xử lý thêm/sửa banner
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_banner'])) {
    $loai_banner = isset($_POST['loai_banner']) ? trim($_POST['loai_banner']) : '';
    $ngay_dang = isset($_POST['ngay_dang']) ? trim($_POST['ngay_dang']) : date('Y-m-d');
    $ma_banner_edit = isset($_POST['ma_banner']) ? intval($_POST['ma_banner']) : 0;
    
    if (empty($loai_banner)) {
        $error_message = 'Vui lòng chọn loại banner!';
    } else {
        try {
            // --- LOGIC UPLOAD ẢNH (GIỐNG SANPHAM.PHP) ---
            $upload_dir = dirname(__FILE__) . '/../img/banner/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $hinh_anh = '';
            $upload_ok = true;
            
            // Kiểm tra xem file CÓ được gửi lên không
            if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] != UPLOAD_ERR_NO_FILE) {
                
                // File ĐÃ được gửi, kiểm tra xem có lỗi không
                if ($_FILES['hinh_anh']['error'] == UPLOAD_ERR_OK) {
                    $f = $_FILES['hinh_anh'];
                    
                    // Kiểm tra kích thước file (tối đa 15MB)
                    $max_file_size = 15 * 1024 * 1024; // 15MB
                    if ($f['size'] > $max_file_size) {
                        $error_message = 'Lỗi: File ảnh quá lớn. Kích thước tối đa cho phép là 15MB. File của bạn: ' . round($f['size'] / (1024 * 1024), 2) . 'MB';
                        $upload_ok = false;
                    } else {
                    
                    if (is_dir($upload_dir)) {
                        $orig = basename($f['name']);
                        $safe_name = preg_replace('/[\\\\\\/]+/', '', $orig);
                        $filename = $safe_name;
                        $dest = $upload_dir . $filename;
                        $idx = 1;
                        
                        while (file_exists($dest)) {
                            $ext = pathinfo($safe_name, PATHINFO_EXTENSION);
                            $name_only = pathinfo($safe_name, PATHINFO_FILENAME);
                            $filename = $name_only . '_' . $idx . '.' . $ext;
                            $dest = $upload_dir . $filename;
                            $idx++;
                        }
                        
                        if (move_uploaded_file($f['tmp_name'], $dest)) {
                            $hinh_anh = 'img/banner/' . $filename;
                        } else {
                            $error_message = 'Lỗi 1: Không thể di chuyển file ảnh. Kiểm tra cấp quyền thư mục img/banner.';
                            $upload_ok = false;
                        }
                    } else {
                        $error_message = 'Lỗi 2: Thư mục lưu ảnh không tồn tại.';
                        $upload_ok = false;
                    }
                    }
                    
                } else {
                    $upload_ok = false;
                    switch ($_FILES['hinh_anh']['error']) {
                        case UPLOAD_ERR_INI_SIZE:
                            $error_message = 'Lỗi: File ảnh quá lớn (vượt quá upload_max_filesize ' . ini_get('upload_max_filesize') . ' trong php.ini). Vui lòng chọn file nhỏ hơn 15MB.';
                            break;
                        case UPLOAD_ERR_FORM_SIZE:
                            $error_message = 'Lỗi: File ảnh vượt quá giới hạn cho phép trong form.';
                            break;
                        case UPLOAD_ERR_PARTIAL:
                            $error_message = 'Lỗi: File chỉ được upload một phần.';
                            break;
                        default:
                            $error_message = 'Lỗi 3: Lỗi upload không xác định. Mã: ' . $_FILES['hinh_anh']['error'];
                    }
                }
            }
            
            // --- LOGIC UPDATE (SỬA) ---
            if ($ma_banner_edit > 0) {
                // Lấy thông tin banner cũ
                $stmt_old = $conn->prepare("SELECT hinh_anh FROM banners WHERE ma_banner = ?");
                $stmt_old->execute(array($ma_banner_edit));
                $old_data = $stmt_old->fetch(PDO::FETCH_ASSOC);
                $hinh_anh_cu = $old_data ? $old_data['hinh_anh'] : '';
                
                // Xác định đường dẫn ảnh để lưu vào DB
                if (!empty($hinh_anh)) {
                    // Có file mới - xóa file cũ
                    if (!empty($hinh_anh_cu)) {
                        $old_path = dirname(__FILE__) . '/../' . $hinh_anh_cu;
                        if (file_exists($old_path)) @unlink($old_path);
                    }
                    $hinh_path_db = $hinh_anh;
                } else {
                    // Không có file mới - giữ ảnh cũ
                    $hinh_path_db = $hinh_anh_cu;
                }
                
                $stmt = $conn->prepare("UPDATE banners SET loai_banner = ?, hinh_anh = ?, ngay_dang = ?, update_at = NOW() WHERE ma_banner = ?");
                $result = $stmt->execute(array($loai_banner, $hinh_path_db, $ngay_dang, $ma_banner_edit));
                
                if ($result) {
                    header("Location: nhanvienbanhang.php?action=banner");
                    exit();
                } else {
                    $errorInfo = $stmt->errorInfo();
                    $error_message = 'Lỗi khi cập nhật banner. Lỗi SQL: ' . htmlspecialchars($errorInfo[2]);
                }
            }
            // --- LOGIC INSERT (THÊM MỚI) ---
            else {
                if ($upload_ok) {
                    $stmt = $conn->prepare("INSERT INTO banners (loai_banner, hinh_anh, ngay_dang, created_at, update_at) VALUES (?, ?, ?, NOW(), NOW())");
                    $result = $stmt->execute(array($loai_banner, $hinh_anh, $ngay_dang));
                    
                    if ($result) {
                        header("Location: nhanvienbanhang.php?action=banner");
                        exit();
                    } else {
                        $errorInfo = $stmt->errorInfo();
                        $error_message = 'Lỗi khi thêm banner vào Database. Lỗi SQL: ' . htmlspecialchars($errorInfo[2]);
                    }
                }
            }
        } catch (PDOException $e) {
            $error_message = 'Lỗi database: ' . $e->getMessage();
        }
    }
}

// Lấy dữ liệu cho form sửa
$edit_data = null;
if ($action == 'edit' && $edit_id > 0) {
    $stmt_edit = $conn->prepare("SELECT * FROM banners WHERE ma_banner = ?");
    $stmt_edit->execute(array($edit_id));
    $edit_data = $stmt_edit->fetch(PDO::FETCH_ASSOC);
}

// Định nghĩa các loại banner
$loai_banners = array(
    'Trang_chu' => 'Banner trang chủ',
    'tin_tuc' => 'Banner tin tức'
);

// Hiển thị form thêm/sửa
if ($action == 'add' || $action == 'edit') {
    ?>
    <div class="container-fluid">
        <h2 class="mb-4">
            <i class="fas fa-images me-2"></i>
            <?php echo ($action == 'edit') ? 'Sửa banner' : 'Thêm banner mới'; ?>
        </h2>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>Lỗi:</strong> <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Thành công:</strong> <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="card shadow">
            <div class="card-body">
                <form method="post" enctype="multipart/form-data">
                    <?php if ($action == 'edit' && $edit_data): ?>
                        <input type="hidden" name="ma_banner" value="<?php echo $edit_data['ma_banner']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><strong>Loại banner <span class="text-danger">*</span></strong></label>
                                <select name="loai_banner" class="form-select" required>
                                    <option value="">-- Chọn loại banner --</option>
                                    <?php foreach ($loai_banners as $key => $label): ?>
                                        <option value="<?php echo $key; ?>" 
                                            <?php echo ($edit_data && $edit_data['loai_banner'] == $key) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><strong>Ngày đăng</strong></label>
                                <input type="date" name="ngay_dang" class="form-control" 
                                       value="<?php echo $edit_data ? htmlspecialchars($edit_data['ngay_dang']) : date('Y-m-d'); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label"><strong>Hình ảnh banner</strong></label>
                                <?php if ($edit_data && !empty($edit_data['hinh_anh'])): ?>
                                    <div class="mb-2">
                                        <img src="<?php echo htmlspecialchars($edit_data['hinh_anh']); ?>" 
                                             alt="Banner hiện tại" 
                                             style="max-width: 400px; max-height: 200px; border: 1px solid #ddd; padding: 5px;">
                                        <p class="text-muted small mt-1">Ảnh hiện tại (để trống nếu không muốn thay đổi)</p>
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="hinh_anh" class="form-control" accept="image/*">
                                <small class="text-muted">Định dạng: JPG, PNG, GIF. Kích thước đề xuất: 2839x1016px (Tối đa 15MB)</small>
                            </div>
                        </div>
                        
                        <div class="col-md-12 text-end">
                            <a href="nhanvienbanhang.php?action=banner" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Hủy
                            </a>
                            <button type="submit" name="save_banner" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> <?php echo ($action == 'edit') ? 'Cập nhật banner' : 'Thêm banner'; ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
    return;
}

// --- HIỂN THỊ DANH SÁCH BANNER ---

// Phân trang
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
if (!in_array($per_page, array(5, 10, 20, 50))) {
    $per_page = 10;
}
$offset = ($page - 1) * $per_page;

// Đếm tổng số banner
$stmt_count = $conn->query("SELECT COUNT(*) as total FROM banners");
$total_banners = $stmt_count->fetch(PDO::FETCH_ASSOC);
$total_banners = $total_banners['total'];
$total_pages = ceil($total_banners / $per_page);

// Lấy danh sách banner
$sql = "SELECT * FROM banners
        ORDER BY ma_banner DESC
        LIMIT " . intval($per_page) . " OFFSET " . intval($offset);

$stmt = $conn->query($sql);
$banner_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

$start_index = ($page - 1) * $per_page;
?>

<div class="container-fluid">
    <h2 class="mb-4">
        <i class="fas fa-images me-2"></i> Quản lý Banner
    </h2>
    
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Nút thêm banner -->
    <div class="mb-3">
        <a href="nhanvienbanhang.php?action=banner&act=add" class="btn btn-success">
            <i class="fas fa-plus-circle me-1"></i> Thêm banner mới
        </a>
    </div>
    
    <!-- Bảng danh sách -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>STT</th>
                            <th>Hình ảnh</th>
                            <th>Loại banner</th>
                            <th>Ngày đăng</th>
                            <th>Ngày tạo</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($banner_list)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    <i class="fas fa-images fa-2x mb-2"></i>
                                    <p>Chưa có banner nào.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($banner_list as $index => $banner): ?>
                                <tr>
                                    <td><?php echo $start_index + $index + 1; ?></td>
                                    <td>
                                        <?php if (!empty($banner['hinh_anh'])): ?>
                                            <img src="<?php echo htmlspecialchars($banner['hinh_anh']); ?>" 
                                                 alt="Banner" class="img-thumbnail" 
                                                 style="max-width: 120px; max-height: 60px; object-fit: cover;">
                                        <?php else: ?>
                                            <span class="text-muted"><i class="fas fa-image"></i> Không có ảnh</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $loai = isset($banner['loai_banner']) ? $banner['loai_banner'] : '';
                                        if ($loai == 'Trang_chu') {
                                            echo '<span class="badge bg-primary">Banner trang chủ</span>';
                                        } elseif ($loai == 'tin_tuc') {
                                            echo '<span class="badge bg-info">Banner tin tức</span>';
                                        } else {
                                            echo '<span class="text-muted">Không xác định</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if (!empty($banner['ngay_dang']) && $banner['ngay_dang'] != '0000-00-00') {
                                            echo date('d/m/Y', strtotime($banner['ngay_dang']));
                                        } else {
                                            echo '<span class="text-muted">--</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if (!empty($banner['created_at']) && $banner['created_at'] != '0000-00-00 00:00:00') {
                                            echo '<small>' . date('d/m/Y H:i', strtotime($banner['created_at'])) . '</small>';
                                        } else {
                                            echo '<span class="text-muted">--</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <a href="nhanvienbanhang.php?action=banner&act=edit&edit_id=<?php echo $banner['ma_banner']; ?>" 
                                           class="btn btn-sm btn-warning" title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="nhanvienbanhang.php?action=banner&delete_id=<?php echo $banner['ma_banner']; ?>" 
                                           class="btn btn-sm btn-danger" title="Xóa"
                                           onclick="return confirm('Bạn có chắc chắn muốn xóa banner này?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Phân trang và chọn số dòng -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <!-- Dropdown chọn số dòng hiển thị -->
                <div class="d-flex align-items-center">
                    <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='nhanvienbanhang.php?action=banner&per_page=' + this.value + '&page=1'">
                        <option value="5" <?php echo ($per_page == 5) ? 'selected' : ''; ?>>5 / page</option>
                        <option value="10" <?php echo ($per_page == 10) ? 'selected' : ''; ?>>10 / page</option>
                        <option value="20" <?php echo ($per_page == 20) ? 'selected' : ''; ?>>20 / page</option>
                        <option value="50" <?php echo ($per_page == 50) ? 'selected' : ''; ?>>50 / page</option>
                    </select>
                </div>
                
                <!-- Nút phân trang -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Phân trang">
                        <ul class="pagination mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="nhanvienbanhang.php?action=banner&page=<?php echo ($page - 1); ?>&per_page=<?php echo $per_page; ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link"><i class="fas fa-chevron-left"></i></span>
                                </li>
                            <?php endif; ?>
                            
                            <?php
                            // Hiển thị tối đa 5 số trang
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            
                            if ($start_page > 1) {
                                echo '<li class="page-item"><a class="page-link" href="nhanvienbanhang.php?action=banner&page=1&per_page=' . $per_page . '">1</a></li>';
                                if ($start_page > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                            }
                            
                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="nhanvienbanhang.php?action=banner&page=<?php echo $i; ?>&per_page=<?php echo $per_page; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php 
                            endfor;
                            
                            if ($end_page < $total_pages) {
                                if ($end_page < $total_pages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="nhanvienbanhang.php?action=banner&page=' . $total_pages . '&per_page=' . $per_page . '">' . $total_pages . '</a></li>';
                            }
                            ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="nhanvienbanhang.php?action=banner&page=<?php echo ($page + 1); ?>&per_page=<?php echo $per_page; ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link"><i class="fas fa-chevron-right"></i></span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
