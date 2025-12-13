<?php
// File: nhanvien/tin_tuc.php
// Chức năng: Quản lý tin tức cho nhân viên bán hàng

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

// Debug: Kiểm tra session (xóa sau khi hoàn thành)
if ($ma_user_session == 0) {
    error_log('Warning: ma_user_session = 0 in tin_tuc.php');
}

// Xử lý xóa tin tức
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    try {
        // Lấy thông tin hình ảnh trước khi xóa
        $stmt_img = $conn->prepare("SELECT hinh_anh FROM tin_tuc WHERE ma_tintuc = ?");
        $stmt_img->execute(array($delete_id));
        $img_data = $stmt_img->fetch(PDO::FETCH_ASSOC);
        
        // Xóa tin tức
        $stmt_delete = $conn->prepare("DELETE FROM tin_tuc WHERE ma_tintuc = ?");
        $stmt_delete->execute(array($delete_id));
        
        // Xóa file hình ảnh nếu có
        if ($img_data && !empty($img_data['hinh_anh'])) {
            $img_path = $img_data['hinh_anh'];
            if (file_exists($img_path)) {
                @unlink($img_path);
            }
        }
        
        echo "<script>
            alert('Đã xóa tin tức #" . $delete_id . " thành công!');
            window.location.href = 'nhanvienbanhang.php?action=tin_tuc';
        </script>";
        exit;
    } catch (PDOException $e) {
        $error_message = 'Lỗi khi xóa: ' . $e->getMessage();
    }
}

// Xử lý thêm/sửa tin tức
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_news'])) {
    // Debug: Log POST data
    error_log("POST save_news triggered");
    error_log("ma_user_session: " . $ma_user_session);
    
    $tieu_de = isset($_POST['tieu_de']) ? trim($_POST['tieu_de']) : '';
    // Nội dung HTML từ TinyMCE - giữ nguyên HTML tags
    $noi_dung = isset($_POST['noi_dung']) ? $_POST['noi_dung'] : '';
    $nguoi_tao = isset($_POST['nguoi_tao']) ? trim($_POST['nguoi_tao']) : '';
    $ngay_tao = isset($_POST['ngay_tao']) ? trim($_POST['ngay_tao']) : date('Y-m-d');
    $ma_tintuc_edit = isset($_POST['ma_tintuc']) ? intval($_POST['ma_tintuc']) : 0;
    
    error_log("Tieu de: " . $tieu_de);
    error_log("Noi dung length: " . strlen($noi_dung));
    
    // Validate dữ liệu
    if (empty($tieu_de)) {
        $error_message = 'Vui lòng nhập tiêu đề tin tức!';
    } elseif (empty($noi_dung)) {
        $error_message = 'Vui lòng nhập nội dung tin tức!';
    }
    
    // --- XỬ LÝ UPLOAD HÌNH ẢNH (LOGIC GIỐNG SANPHAM.PHP) ---
    $hinh_anh = '';
    $upload_dir = dirname(__FILE__) . '/../img/blog/';
    
    if (!is_dir($upload_dir)) {
        @mkdir($upload_dir, 0755, true);
    }
    
    $upload_ok = true; // Cờ theo dõi chung
    
    // Kiểm tra xem file CÓ được gửi lên không
    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] != UPLOAD_ERR_NO_FILE) {
        
        // File ĐÃ được gửi, kiểm tra xem có lỗi không
        if ($_FILES['hinh_anh']['error'] == UPLOAD_ERR_OK) {
            $f = $_FILES['hinh_anh'];
            
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
                    // THÀNH CÔNG: Gán đường dẫn
                    $hinh_anh = 'img/blog/' . $filename;
                } else {
                    $error_message = 'Lỗi: Không thể di chuyển file ảnh. Kiểm tra CẤP QUYỀN (CHMOD) thư mục img/blog/';
                    $upload_ok = false;
                }
            } else {
                $error_message = 'Lỗi: Thư mục lưu ảnh không tồn tại. Path: ' . htmlspecialchars($upload_dir);
                $upload_ok = false;
            }
            
        } else {
            // File CÓ GỬI nhưng BỊ LỖI (vd: quá dung lượng)
            $upload_ok = false;
            switch ($_FILES['hinh_anh']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $error_message = 'Lỗi: File ảnh quá lớn (vượt quá upload_max_filesize trong php.ini).';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $error_message = 'Lỗi: File chỉ được upload một phần.';
                    break;
                default:
                    $error_message = 'Lỗi: Lỗi upload không xác định. Mã: ' . $_FILES['hinh_anh']['error'];
            }
        }
    }
    // else: (UPLOAD_ERR_NO_FILE) -> Không có file. $hinh_anh = '' và $upload_ok = true.
    
    // --- LOGIC LƯU VÀO DATABASE ---
    // Chỉ chạy INSERT/UPDATE nếu $upload_ok (không có lỗi file) và không có lỗi validation
    if (empty($error_message) && $upload_ok) {
        try {
            if ($ma_tintuc_edit > 0) {
                // --- XỬ LÝ ẢNH KHI CẬP NHẬT (GIỐNG SANPHAM.PHP) ---
                $hinh_path_db = ''; // Đường dẫn ảnh mới (nếu có)
                
                // Lấy ảnh cũ từ database
                $stmt_old = $conn->prepare("SELECT hinh_anh FROM tin_tuc WHERE ma_tintuc = ?");
                $stmt_old->execute(array($ma_tintuc_edit));
                $old_data = $stmt_old->fetch(PDO::FETCH_ASSOC);
                $hinh_anh_cu = $old_data ? $old_data['hinh_anh'] : '';
                
                // Kiểm tra xem có file MỚI được gửi lên không
                if (!empty($hinh_anh)) {
                    // CÓ FILE MỚI - Xóa ảnh cũ và dùng ảnh mới
                    if (!empty($hinh_anh_cu)) {
                        $old_path = dirname(__FILE__) . '/../' . $hinh_anh_cu;
                        if (file_exists($old_path)) {
                            @unlink($old_path);
                        }
                    }
                    $hinh_path_db = $hinh_anh; // Dùng ảnh mới
                } else {
                    // KHÔNG CÓ FILE MỚI - Giữ ảnh cũ
                    $hinh_path_db = $hinh_anh_cu;
                }
                
                // Cập nhật database
                $stmt = $conn->prepare("UPDATE tin_tuc SET tieu_de = ?, noi_dung = ?, nguoi_tao = ?, ngay_tao = ?, hinh_anh = ?, update_at = NOW() WHERE ma_tintuc = ?");
                $stmt->execute(array($tieu_de, $noi_dung, $nguoi_tao, $ngay_tao, $hinh_path_db, $ma_tintuc_edit));
                
                // Redirect sau khi cập nhật thành công
                header("Location: nhanvienbanhang.php?action=tin_tuc");
                exit();
                
            } else {
                // --- THÊM MỚI TIN TỨC (GIỐNG SANPHAM.PHP) ---
                $stmt = $conn->prepare("INSERT INTO tin_tuc (tieu_de, noi_dung, nguoi_tao, ngay_tao, hinh_anh, ma_user, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $result = $stmt->execute(array($tieu_de, $noi_dung, $nguoi_tao, $ngay_tao, $hinh_anh, $ma_user_session));
                
                if ($result) {
                    // *** REDIRECT SAU KHI THÀNH CÔNG (GIỐNG SANPHAM.PHP) ***
                    header("Location: nhanvienbanhang.php?action=tin_tuc");
                    exit();
                } else {
                    $errorInfo = $stmt->errorInfo();
                    $error_message = 'Lỗi khi thêm tin tức vào Database. Lỗi SQL: ' . htmlspecialchars($errorInfo[2]);
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
    $stmt_edit = $conn->prepare("SELECT * FROM tin_tuc WHERE ma_tintuc = ?");
    $stmt_edit->execute(array($edit_id));
    $edit_data = $stmt_edit->fetch(PDO::FETCH_ASSOC);
}

// Hiển thị form thêm/sửa
if ($action == 'add' || $action == 'edit') {
    ?>
    <div class="container-fluid">
        <h2 class="mb-4">
            <i class="fas fa-newspaper me-2"></i>
            <?php echo ($action == 'edit') ? 'Sửa tin tức' : 'Thêm tin tức mới'; ?>
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
                <form method="post" enctype="multipart/form-data" onsubmit="return validateForm();">
                    <?php if ($action == 'edit' && $edit_data): ?>
                        <input type="hidden" name="ma_tintuc" value="<?php echo $edit_data['ma_tintuc']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label"><strong>Tiêu đề <span class="text-danger">*</span></strong></label>
                                <input type="text" name="tieu_de" class="form-control" 
                                       value="<?php echo $edit_data ? htmlspecialchars($edit_data['tieu_de']) : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label"><strong>Nội dung <span class="text-danger">*</span></strong></label>
                                <textarea name="noi_dung" id="tinymce_editor" class="form-control" rows="15"><?php echo $edit_data ? $edit_data['noi_dung'] : ''; ?></textarea>
                                <small class="text-muted">Bạn có thể thêm chữ, hình ảnh, định dạng văn bản...</small>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label"><strong>Người tạo</strong></label>
                                <input type="text" name="nguoi_tao" class="form-control" 
                                       value="<?php echo $edit_data ? htmlspecialchars($edit_data['nguoi_tao']) : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label"><strong>Ngày tạo</strong></label>
                                <input type="date" name="ngay_tao" class="form-control" 
                                       value="<?php echo $edit_data ? $edit_data['ngay_tao'] : date('Y-m-d'); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label"><strong>Hình ảnh</strong></label>
                                <input type="file" name="hinh_anh" class="form-control" accept="image/*">
                                <?php if ($edit_data && !empty($edit_data['hinh_anh'])): ?>
                                    <div class="mt-2">
                                        <img src="../<?php echo htmlspecialchars($edit_data['hinh_anh']); ?>" 
                                             alt="Preview" class="img-thumbnail" style="max-width: 200px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" name="save_news" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i><?php echo ($action == 'edit') ? 'Cập nhật' : 'Thêm mới'; ?>
                        </button>
                        <a href="nhanvienbanhang.php?action=tin_tuc" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- TinyMCE Editor -->
    <script src="https://cdn.tiny.cloud/1/f0qu5j0bm9mm6kncwoq8rgcdhhgt0gf6gddmchbp6vle0221/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        function validateForm() {
            console.log('Form validation triggered');
            
            // Trigger TinyMCE to save content
            if (tinymce.get('tinymce_editor')) {
                tinymce.get('tinymce_editor').save();
                console.log('TinyMCE content saved');
            }
            
            var tieu_de = document.querySelector('input[name="tieu_de"]').value.trim();
            var noi_dung = document.querySelector('textarea[name="noi_dung"]').value.trim();
            
            console.log('Tiêu đề:', tieu_de);
            console.log('Nội dung length:', noi_dung.length);
            
            if (tieu_de === '') {
                alert('Vui lòng nhập tiêu đề tin tức!');
                return false;
            }
            
            if (noi_dung === '') {
                alert('Vui lòng nhập nội dung tin tức!');
                return false;
            }
            
            console.log('Form validation passed');
            return true;
        }
        
        tinymce.init({
            selector: '#tinymce_editor',
            height: 400,
            menubar: true,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | image media link | help',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
            language: 'vi',
            image_title: true,
            automatic_uploads: true,
            file_picker_types: 'image',
            images_upload_handler: function (blobInfo, progress) {
                return new Promise(function(resolve, reject) {
                    var xhr, formData;
                    
                    console.log('Bắt đầu upload ảnh:', blobInfo.filename());
                    
                    xhr = new XMLHttpRequest();
                    xhr.withCredentials = false;
                    xhr.open('POST', '../nhanvien/upload_image.php');
                    
                    xhr.upload.onprogress = function (e) {
                        progress(e.loaded / e.total * 100);
                    };
                    
                    xhr.onload = function() {
                        console.log('Upload hoàn tất. Status:', xhr.status);
                        console.log('Response:', xhr.responseText);
                        
                        if (xhr.status != 200) {
                            console.error('HTTP Error:', xhr.status);
                            reject('Lỗi HTTP: ' + xhr.status);
                            return;
                        }
                        
                        try {
                            var json = JSON.parse(xhr.responseText);
                            if (!json || typeof json.location != 'string') {
                                console.error('Invalid JSON response:', xhr.responseText);
                                reject('Phản hồi không hợp lệ: ' + xhr.responseText);
                                return;
                            }
                            console.log('Upload thành công:', json.location);
                            resolve(json.location);
                        } catch (e) {
                            console.error('Lỗi parse JSON:', e);
                            reject('Lỗi xử lý phản hồi: ' + e.message);
                        }
                    };
                    
                    xhr.onerror = function() {
                        console.error('Lỗi kết nối');
                        reject('Lỗi kết nối đến server');
                    };
                    
                    formData = new FormData();
                    formData.append('file', blobInfo.blob(), blobInfo.filename());
                    xhr.send(formData);
                });
            }
        });
    </script>
    <?php
    return;
}

// Phần danh sách tin tức
$search_term = '';
$search_query = '';
$params = array();

// Xử lý tìm kiếm
if (isset($_GET['search_term']) && trim($_GET['search_term']) !== '') {
    $search_term = trim($_GET['search_term']);
    
    if (is_numeric($search_term)) {
        $search_query = " WHERE ma_tintuc = ?";
        $params[] = intval($search_term);
    } else {
        $search_query = " WHERE (tieu_de LIKE ? OR nguoi_tao LIKE ?)";
        $params[] = '%' . $search_term . '%';
        $params[] = '%' . $search_term . '%';
    }
}

// Phân trang
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Đếm tổng số tin tức
$sql_count = "SELECT COUNT(*) as total FROM tin_tuc" . $search_query;
$stmt_count = $conn->prepare($sql_count);
$stmt_count->execute($params);
$total_news = $stmt_count->fetch(PDO::FETCH_ASSOC);
$total_news = $total_news['total'];
$total_pages = ceil($total_news / $per_page);

// Lấy danh sách tin tức
$sql = "SELECT t.*, u.ho_ten AS ten_user 
        FROM tin_tuc t
        LEFT JOIN user u ON t.ma_user = u.ma_user" 
        . $search_query . "
        ORDER BY t.ma_tintuc ASC
        LIMIT " . intval($per_page) . " OFFSET " . intval($offset);

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$news_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

$start_index = ($page - 1) * $per_page;
?>

<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-newspaper me-2"></i> Quản lý tin tức</h2>
    
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Toolbar -->
    <div class="row mb-3">
        <div class="col-md-6">
            <a href="nhanvienbanhang.php?action=tin_tuc&act=add" class="btn btn-success">
                <i class="fas fa-plus me-2"></i>Thêm tin tức mới
            </a>
        </div>
        <div class="col-md-6">
            <form method="GET" action="nhanvienbanhang.php" class="d-flex">
                <input type="hidden" name="action" value="tin_tuc">
                <input type="text" name="search_term" class="form-control me-2" 
                       placeholder="Tìm theo ID, tiêu đề, người tạo..." 
                       value="<?php echo htmlspecialchars($search_term); ?>">
                <button class="btn btn-outline-primary" type="submit">
                    <i class="fas fa-search"></i>
                </button>
                <?php if (!empty($search_term)): ?>
                    <a href="nhanvienbanhang.php?action=tin_tuc" class="btn btn-outline-danger ms-2">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <?php if (!empty($search_term)): ?>
        <p class="text-info">Kết quả tìm kiếm cho: <strong><?php echo htmlspecialchars($search_term); ?></strong> (<?php echo $total_news; ?> kết quả)</p>
    <?php endif; ?>
    
    <!-- Bảng danh sách -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>STT</th>
                            <th>Hình ảnh</th>
                            <th>Tiêu đề</th>
                            <th>Người tạo</th>
                            <th>Ngày tạo</th>
                            <th>Ngày cập nhật</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($news_list)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p>Không có tin tức nào.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($news_list as $index => $news): ?>
                                <tr>
                                    <td><?php echo $start_index + $index + 1; ?></td>
                                    <td>
                                        <?php if (!empty($news['hinh_anh'])): ?>
                                            <img src="<?php echo htmlspecialchars($news['hinh_anh']); ?>" 
                                                 alt="Thumbnail" class="img-thumbnail" 
                                                 style="max-width: 80px; max-height: 60px; object-fit: cover;">
                                        <?php else: ?>
                                            <span class="text-muted"><i class="fas fa-image"></i> Không có ảnh</span>
                                            <!-- DEBUG: <?php echo 'hinh_anh = [' . (isset($news['hinh_anh']) ? $news['hinh_anh'] : 'NULL') . ']'; ?> -->
                                        <?php endif; ?>
                                    </td>
                                    <td style="max-width: 200px;">
                                        <div style="font-size: 0.9rem; line-height: 1.4; white-space: normal;">
                                            <?php echo htmlspecialchars($news['tieu_de']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($news['nguoi_tao']); ?></td>
                                    <td>
                                        <?php 
                                        if (!empty($news['ngay_tao']) && $news['ngay_tao'] != '0000-00-00') {
                                            echo date('d/m/Y', strtotime($news['ngay_tao']));
                                        } else {
                                            echo '<span class="text-muted">--</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if (!empty($news['update_at']) && $news['update_at'] != '0000-00-00 00:00:00') {
                                            echo '<small>' . date('d/m/Y H:i', strtotime($news['update_at'])) . '</small>';
                                        } else {
                                            echo '<span class="text-muted">--</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <a href="nhanvienbanhang.php?action=tin_tuc&act=view&id=<?php echo $news['ma_tintuc']; ?>" 
                                           class="btn btn-sm btn-info" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="nhanvienbanhang.php?action=tin_tuc&act=edit&edit_id=<?php echo $news['ma_tintuc']; ?>" 
                                           class="btn btn-sm btn-warning" title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="nhanvienbanhang.php?action=tin_tuc&delete_id=<?php echo $news['ma_tintuc']; ?>" 
                                           class="btn btn-sm btn-danger" title="Xóa"
                                           onclick="return confirm('Bạn có chắc chắn muốn xóa tin tức này?\n\nTiêu đề: <?php echo addslashes($news['tieu_de']); ?>');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Phân trang -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Phân trang tin tức" class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php
                    $query_base = 'nhanvienbanhang.php?action=tin_tuc';
                    if ($search_term != '') $query_base .= '&search_term=' . urlencode($search_term);
                    
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
                <p class="text-center text-muted">Trang <?php echo $page; ?> / <?php echo $total_pages; ?> (Tổng: <?php echo $total_news; ?> tin tức)</p>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal xem chi tiết -->
<?php
if ($action == 'view' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $view_id = intval($_GET['id']);
    $stmt_view = $conn->prepare("SELECT t.*, u.ho_ten AS ten_user FROM tin_tuc t LEFT JOIN user u ON t.ma_user = u.ma_user WHERE ma_tintuc = ?");
    $stmt_view->execute(array($view_id));
    $view_data = $stmt_view->fetch(PDO::FETCH_ASSOC);
    
    if ($view_data):
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
            viewModal.show();
        });
    </script>
    
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-newspaper me-2"></i>Chi tiết tin tức #<?php echo $view_data['ma_tintuc']; ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="window.location.href='nhanvienbanhang.php?action=tin_tuc'"></button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($view_data['hinh_anh'])): ?>
                        <div class="text-center mb-4">
                            <img src="../<?php echo htmlspecialchars($view_data['hinh_anh']); ?>" 
                                 alt="Hình ảnh tin tức" class="img-fluid rounded" style="max-height: 400px;">
                        </div>
                    <?php endif; ?>
                    
                    <h3 class="mb-3"><?php echo htmlspecialchars($view_data['tieu_de']); ?></h3>
                    <hr>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong><i class="fas fa-user me-2"></i>Người tạo:</strong> <?php echo htmlspecialchars($view_data['nguoi_tao']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong><i class="fas fa-calendar me-2"></i>Ngày tạo:</strong> 
                            <?php 
                            if (!empty($view_data['ngay_tao']) && $view_data['ngay_tao'] != '0000-00-00') {
                                echo date('d/m/Y', strtotime($view_data['ngay_tao']));
                            } else {
                                echo '--';
                            }
                            ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h5><i class="fas fa-align-left me-2"></i>Nội dung:</h5>
                        <div class="border rounded p-3 bg-light" style="overflow-x: auto;">
                            <?php echo $view_data['noi_dung']; ?>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="text-muted small">
                        <p class="mb-1"><strong>Tạo lúc:</strong> 
                        <?php 
                        if (!empty($view_data['created_at']) && $view_data['created_at'] != '0000-00-00 00:00:00') {
                            echo date('d/m/Y H:i:s', strtotime($view_data['created_at']));
                        } else {
                            echo '--';
                        }
                        ?>
                        </p>
                        <?php if (!empty($view_data['update_at']) && $view_data['update_at'] != '0000-00-00 00:00:00'): ?>
                            <p class="mb-0"><strong>Cập nhật lúc:</strong> <?php echo date('d/m/Y H:i:s', strtotime($view_data['update_at'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="nhanvienbanhang.php?action=tin_tuc&act=edit&edit_id=<?php echo $view_data['ma_tintuc']; ?>" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>Sửa
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="window.location.href='nhanvienbanhang.php?action=tin_tuc'">
                        <i class="fas fa-times me-2"></i>Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php
    endif;
}
?>
