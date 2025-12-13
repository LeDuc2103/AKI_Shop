<?php
// Tên file: sanpham.php (Đã sửa logic upload)
if (!isset($conn)) {
    require_once '../config/database.php';
}

function vn_to_str($str) {
    $str = trim($str);
    $unicode = array(
        'a'=>'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
        'A'=>'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ằ|Ẳ|Ẵ|Ặ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
        'd'=>'đ','D'=>'Đ',
        'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
        'E'=>'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
        'i'=>'í|ì|ỉ|ĩ|ị','I'=>'Í|Ì|Ỉ|Ĩ|Ị',
        'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
        'O'=>'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
        'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
        'U'=>'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
        'y'=>'ý|ỳ|ỷ|ỹ|ỵ','Y'=>'Ý|Ỳ|Ỷ|Ỹ|Ỵ'
    );
    foreach ($unicode as $khongdau => $codau) {
        $arr = explode("|", $codau);
        $str = str_replace($arr, $khongdau, $str);
    }
    $str = preg_replace('/[^A-Za-z0-9 ]/', '', $str); 
    $str = strtolower($str);
    $str = str_replace(' ', '', $str); 
    return $str;
}

function find_category_folder($ten_danhmuc, $base_dir) {
    $candidates = array();
    $candidates[] = vn_to_str($ten_danhmuc);
    $parts = preg_split('/\s+/', trim($ten_danhmuc));
    if (count($parts) > 0) {
        $last = $parts[count($parts)-1];
        $candidates[] = vn_to_str($last);
        $candidates[] = strtolower($last);
    }
    $no_space = preg_replace('/\s+/', '', $ten_danhmuc);
    $candidates[] = $no_space;
    $candidates[] = strtolower($no_space);
    $candidates[] = $ten_danhmuc;
    $candidates[] = strtolower($ten_danhmuc);
    $candidates = array_values(array_unique($candidates));
    
    foreach ($candidates as $c) {
        $path = $base_dir . DIRECTORY_SEPARATOR . $c;
        if (is_dir($path)) {
            return $c;
        }
    }
    return null;
}

$upload_base = dirname(__FILE__) . '/../img/products';
if (!is_dir($upload_base)) {
    @mkdir($upload_base, 0755, true);
}

$action = isset($_GET['act']) ? $_GET['act'] : (isset($_GET['action']) ? $_GET['action'] : 'danhsach');
$message = '';

// --- LOGIC THÊM SẢN PHẨM (ĐÃ SỬA LỖI LOGIC UPLOAD) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_product']) && ($action == 'them')) {
    $ten = isset($_POST['ten_sanpham']) ? trim($_POST['ten_sanpham']) : '';
    $gia = isset($_POST['gia']) ? floatval($_POST['gia']) : 0;
    $gia_km = isset($_POST['gia_khuyen_mai']) ? floatval($_POST['gia_khuyen_mai']) : 0;
    $so_luong = isset($_POST['so_luong']) ? intval($_POST['so_luong']) : 0;
    $mo_ta = isset($_POST['mo_ta']) ? $_POST['mo_ta'] : ''; // Giữ nguyên HTML từ TinyMCE
    $ct_sp = isset($_POST['ct_sp']) ? $_POST['ct_sp'] : ''; // Giữ nguyên HTML từ TinyMCE
    $mau_sac = isset($_POST['mau_sac']) ? trim($_POST['mau_sac']) : '';
    $id_danhmuc = isset($_POST['id_danhmuc']) ? intval($_POST['id_danhmuc']) : 0;
    
    // --- Lấy thông tin Danh mục và chuẩn bị thư mục ---
    $stmt = $conn->prepare("SELECT ten_danhmuc FROM danh_muc WHERE id_danhmuc = ?");
    $stmt->execute(array($id_danhmuc));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $ten_danhmuc = $row ? $row['ten_danhmuc'] : '';
    
    $folder = find_category_folder($ten_danhmuc, $upload_base);
    if ($folder === null) {
        $folder = vn_to_str($ten_danhmuc);
        if ($folder == '') $folder = 'others';
        $full_folder = $upload_base . DIRECTORY_SEPARATOR . $folder;
        if (!is_dir($full_folder)) {
            if (!@mkdir($full_folder, 0755, true)) {
                 $message = '<div class="alert alert-danger">Lỗi: Không thể tạo thư mục ảnh. Kiểm tra lại cấp quyền thư mục `img/products`.</div>';
            }
        }
    }
    $full_folder = $upload_base . DIRECTORY_SEPARATOR . $folder;
    
    $hinh_path_db = '';
    $upload_ok = true; // Cờ theo dõi chung
    
    // --- LOGIC XỬ LÝ UPLOAD ẢNH (ĐÃ VIẾT LẠI) ---
    // Kiểm tra xem file CÓ được gửi lên không
    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] != UPLOAD_ERR_NO_FILE) {
        
        // File ĐÃ được gửi, kiểm tra xem có lỗi không
        if ($_FILES['hinh_anh']['error'] == UPLOAD_ERR_OK) {
            $f = $_FILES['hinh_anh'];
            
            if (is_dir($full_folder)) {
                $orig = basename($f['name']);
                $safe_name = preg_replace('/[\\\\\\/]+/', '', $orig);
                $filename = $safe_name;
                $dest = $full_folder . DIRECTORY_SEPARATOR . $filename;
                $idx = 1;
                
                while (file_exists($dest)) {
                    $ext = pathinfo($safe_name, PATHINFO_EXTENSION);
                    $name_only = pathinfo($safe_name, PATHINFO_FILENAME);
                    $filename = $name_only . '_' . $idx . '.' . $ext;
                    $dest = $full_folder . DIRECTORY_SEPARATOR . $filename;
                    $idx++;
                }
                
                if (move_uploaded_file($f['tmp_name'], $dest)) {
                    // THÀNH CÔNG: Gán đường dẫn
                    $hinh_path_db = 'img/products/' . $folder . '/' . $filename; 
                } else {
                    $message = '<div class="alert alert-danger">Lỗi 1: Không thể di chuyển file ảnh. Kiểm tra CẤP QUYỀN (CHMOD) thư mục `img/products`.</div>';
                    $upload_ok = false;
                }
            } else {
                $message = '<div class="alert alert-danger">Lỗi 2: Thư mục lưu ảnh không tồn tại. Path: ' . htmlspecialchars($full_folder) . '</div>';
                $upload_ok = false;
            }
            
        } else {
            // File CÓ GỬI nhưng BỊ LỖI (vd: quá dung lượng)
            $upload_ok = false;
            switch ($_FILES['hinh_anh']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $message = '<div class="alert alert-danger">Lỗi: File ảnh quá lớn (vượt quá `upload_max_filesize` trong php.ini).</div>';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $message = '<div class="alert alert-danger">Lỗi: File chỉ được upload một phần.</div>';
                    break;
                default:
                    $message = '<div class="alert alert-danger">Lỗi 3: Lỗi upload không xác định (ví dụ: file quá lớn). Mã: ' . $_FILES['hinh_anh']['error'] . '</div>';
            }
        }
    }
    // else: (UPLOAD_ERR_NO_FILE) -> Không có file. $hinh_path_db = '' và $upload_ok = true. -> Đúng.

    // --- XỬ LÝ UPLOAD NHIỀU ẢNH PHỤ ---
    $anh_con_paths = array();
    
    if (isset($_FILES['anh_con']) && is_array($_FILES['anh_con']['name'])) {
        $total_files = count($_FILES['anh_con']['name']);
        
        for ($i = 0; $i < $total_files; $i++) {
            // Kiểm tra từng file có được upload không
            if ($_FILES['anh_con']['error'][$i] == UPLOAD_ERR_OK) {
                $f_name = basename($_FILES['anh_con']['name'][$i]);
                $f_tmp = $_FILES['anh_con']['tmp_name'][$i];
                
                if (is_dir($full_folder)) {
                    $safe_name = preg_replace('/[\\\\\\/]+/', '', $f_name);
                    $filename = $safe_name;
                    $dest = $full_folder . DIRECTORY_SEPARATOR . $filename;
                    $idx = 1;
                    
                    while (file_exists($dest)) {
                        $ext = pathinfo($safe_name, PATHINFO_EXTENSION);
                        $name_only = pathinfo($safe_name, PATHINFO_FILENAME);
                        $filename = $name_only . '_sub' . $idx . '.' . $ext;
                        $dest = $full_folder . DIRECTORY_SEPARATOR . $filename;
                        $idx++;
                    }
                    
                    if (move_uploaded_file($f_tmp, $dest)) {
                        $anh_con_paths[] = 'img/products/' . $folder . '/' . $filename;
                    }
                }
            }
        }
    }
    
    // Chuyển mảng ảnh phụ thành chuỗi phân cách bởi |
    $anh_con_db = !empty($anh_con_paths) ? implode('|', $anh_con_paths) : '';

    // --- LOGIC LƯU VÀO DATABASE ---
    // Chỉ chạy INSERT nếu $upload_ok (không có lỗi file)
    if ($upload_ok) { 
        $stmt = $conn->prepare("INSERT INTO san_pham (ten_sanpham, gia, gia_khuyen_mai, so_luong, mo_ta, ct_sp, mau_sac, hinh_anh, anh_con, id_danhmuc, created_at, update_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $params = array($ten, $gia, $gia_km, $so_luong, $mo_ta, $ct_sp, $mau_sac, $hinh_path_db, $anh_con_db, $id_danhmuc);
        
        $ok = $stmt->execute($params);
        
        if ($ok) {
            // *** DÒNG ĐÃ THÊM: REDIRECT SAU KHI THÀNH CÔNG ***
            header("Location: admin.php?action=sanpham");
            exit(); 
        } else {
            $errorInfo = $stmt->errorInfo();
            $message = '<div class="alert alert-danger">Lỗi khi thêm sản phẩm vào Database. Lỗi SQL: ' . htmlspecialchars($errorInfo[2]) . '</div>';
        }
    }
}

// --- LOGIC SỬA SẢN PHẨM (ĐÃ SỬA LỖI LOGIC UPLOAD) ---
// --- LOGIC SỬA SẢN PHẨM (ĐÃ SỬA LỖI LOGIC UPLOAD + THÊM LOGIC DI CHUYỂN THƯ MỤC) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_product']) && ($action == 'sua') && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $ten = isset($_POST['ten_sanpham']) ? trim($_POST['ten_sanpham']) : '';
    $gia = isset($_POST['gia']) ? floatval($_POST['gia']) : 0;
    $gia_km = isset($_POST['gia_khuyen_mai']) ? floatval($_POST['gia_khuyen_mai']) : 0;
    $so_luong = isset($_POST['so_luong']) ? intval($_POST['so_luong']) : 0;
    $mo_ta = isset($_POST['mo_ta']) ? $_POST['mo_ta'] : ''; // Giữ nguyên HTML từ TinyMCE
    $ct_sp = isset($_POST['ct_sp']) ? $_POST['ct_sp'] : ''; // Giữ nguyên HTML từ TinyMCE
    $mau_sac = isset($_POST['mau_sac']) ? trim($_POST['mau_sac']) : '';
    $id_danhmuc = isset($_POST['id_danhmuc']) ? intval($_POST['id_danhmuc']) : 0;
    
    $stmt = $conn->prepare("SELECT * FROM san_pham WHERE id_sanpham = ?");
    $stmt->execute(array($id));
    $prod = $stmt->fetch(PDO::FETCH_ASSOC); // Dữ liệu cũ của sản phẩm
    
    if (!$prod) {
        $message = '<div class="alert alert-danger">Sản phẩm không tồn tại.</div>';
    } else {
        // Lấy thông tin Danh mục MỚI và chuẩn bị thư mục MỚI
        $stmt2 = $conn->prepare("SELECT ten_danhmuc FROM danh_muc WHERE id_danhmuc = ?");
        $stmt2->execute(array($id_danhmuc));
        $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
        $ten_danhmuc = $row2 ? $row2['ten_danhmuc'] : '';
        
        $folder = find_category_folder($ten_danhmuc, $upload_base);
        if ($folder === null) {
            $folder = vn_to_str($ten_danhmuc);
            if ($folder == '') $folder = 'others';
            $full_folder = $upload_base . DIRECTORY_SEPARATOR . $folder;
            if (!is_dir($full_folder)) {
                @mkdir($full_folder, 0755, true);
            }
        }
        $full_folder = $upload_base . DIRECTORY_SEPARATOR . $folder;
        
        // --- XỬ LÝ ẢNH ---
        $hinh_path_db = $prod['hinh_anh']; // Lấy đường dẫn CŨ làm mặc định
        
        // Kiểm tra xem file MỚI CÓ được gửi lên không
        if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] != UPLOAD_ERR_NO_FILE) {
            
            // File MỚI ĐÃ được gửi, kiểm tra xem có lỗi không
            if ($_FILES['hinh_anh']['error'] == UPLOAD_ERR_OK) {
                $f = $_FILES['hinh_anh'];

                if (is_dir($full_folder)) {
                    $orig = basename($f['name']);
                    $safe_name = preg_replace('/[\\\\\\/]+/', '', $orig);
                    $filename = $safe_name;
                    $dest = $full_folder . DIRECTORY_SEPARATOR . $filename;
                    $idx = 1;
                    
                    while (file_exists($dest)) {
                        $ext = pathinfo($safe_name, PATHINFO_EXTENSION);
                        $name_only = pathinfo($safe_name, PATHINFO_FILENAME);
                        $filename = $name_only . '_' . $idx . '.' . $ext;
                        $dest = $full_folder . DIRECTORY_SEPARATOR . $filename;
                        $idx++;
                    }
                    
                    if (move_uploaded_file($f['tmp_name'], $dest)) {
                        // THÀNH CÔNG: Xóa ảnh cũ (nếu có)
                        if (!empty($prod['hinh_anh'])) {
                            $oldpath = dirname(__FILE__) . '/../' . $prod['hinh_anh'];
                            if (file_exists($oldpath)) @unlink($oldpath);
                        }
                        // Gán đường dẫn MỚI
                        $hinh_path_db = 'img/products/' . $folder . '/' . $filename;
                    } else {
                        $message = '<div class="alert alert-danger">Lỗi 1 (Sửa): Không thể lưu file ảnh mới (kiểm tra quyền). Ảnh cũ ĐƯỢC GIỮ NGUYÊN.</div>';
                    }
                } else {
                     $message = '<div class="alert alert-danger">Lỗi 2 (Sửa): Thư mục lưu ảnh không tồn tại. Ảnh cũ ĐƯỢC GIỮ NGUYÊN.</div>';
                }
            } else {
                // File MỚI CÓ GỬI nhưng BỊ LỖI
                $message = '<div class="alert alert-danger">Lỗi 3 (Sửa): File ảnh mới bị lỗi (quá dung lượng?). Ảnh cũ ĐƯỢC GIỮ NGUYÊN. Mã: ' . $_FILES['hinh_anh']['error'] . '</div>';
            }
        } else {
            // Trường hợp: Không có file mới được chọn (UPLOAD_ERR_NO_FILE)
            
            $old_id_danhmuc = $prod['id_danhmuc'];
            $new_id_danhmuc = $id_danhmuc;
            
            // BẮT ĐẦU LOGIC DI CHUYỂN ẢNH KHI CHỈ THAY ĐỔI DANH MỤC
            if ($old_id_danhmuc != $new_id_danhmuc && !empty($prod['hinh_anh'])) {
                
                $current_image_path_db = $prod['hinh_anh'];
                
                // 1. Lấy tên thư mục CŨ
                $stmt_old_cat = $conn->prepare("SELECT ten_danhmuc FROM danh_muc WHERE id_danhmuc = ?");
                $stmt_old_cat->execute(array($old_id_danhmuc));
                $row_old_cat = $stmt_old_cat->fetch(PDO::FETCH_ASSOC);
                $old_ten_danhmuc = $row_old_cat ? $row_old_cat['ten_danhmuc'] : '';
                
                $old_folder = find_category_folder($old_ten_danhmuc, $upload_base);
                if ($old_folder === null) $old_folder = vn_to_str($old_ten_danhmuc);
                if ($old_folder == '') $old_folder = 'others';

                // Tên thư mục MỚI đã có là $folder
                $new_folder = $folder;

                // 2. Kiểm tra xem thư mục có khác nhau không
                if ($old_folder != $new_folder) {
                    
                    $image_file_name = basename($current_image_path_db);
                    
                    // Xây dựng đường dẫn vật lý CŨ và MỚI
                    $old_full_path = dirname(__FILE__) . '/../' . $current_image_path_db;
                    $new_full_path = $full_folder . DIRECTORY_SEPARATOR . $image_file_name;
                    
                    // Đảm bảo thư mục đích tồn tại (đã được tạo ở trên)
                    if (!is_dir($full_folder)) {
                        @mkdir($full_folder, 0755, true);
                    }
                    
                    // 3. Di chuyển file bằng rename()
                    if (file_exists($old_full_path) && @rename($old_full_path, $new_full_path)) {
                        // Cập nhật đường dẫn DB mới sau khi di chuyển thành công
                        $hinh_path_db = 'img/products/' . $new_folder . '/' . $image_file_name;
                        $message = (empty($message) ? '' : $message . ' <br>') . '<div class="alert alert-info">LƯU Ý: Đã di chuyển ảnh sang thư mục danh mục mới.</div>';
                    } else {
                        // Di chuyển thất bại, giữ lại đường dẫn DB cũ và báo lỗi nhẹ
                        $message = (empty($message) ? '' : $message . ' <br>') . '<div class="alert alert-warning">LƯU Ý: Cập nhật danh mục thành công nhưng KHÔNG thể di chuyển file ảnh. Kiểm tra CẤP QUYỀN (CHMOD). Ảnh cũ ĐƯỢC GIỮ NGUYÊN.</div>';
                    }
                }
            }
        }
        // End of image handling

        // --- XỬ LÝ UPLOAD NHIỀU ẢNH PHỤ (CHO SỬA SẢN PHẨM) ---
        $anh_con_db = $prod['anh_con']; // Giữ ảnh cũ làm mặc định
        
        if (isset($_FILES['anh_con']) && is_array($_FILES['anh_con']['name'])) {
            $anh_con_paths = array();
            $total_files = count($_FILES['anh_con']['name']);
            $has_new_files = false;
            
            // Kiểm tra xem có file mới được upload không
            for ($i = 0; $i < $total_files; $i++) {
                if ($_FILES['anh_con']['error'][$i] == UPLOAD_ERR_OK) {
                    $has_new_files = true;
                    break;
                }
            }
            
            // Nếu có file mới, xóa ảnh cũ và upload ảnh mới
            if ($has_new_files) {
                // Xóa các ảnh phụ cũ
                if (!empty($prod['anh_con'])) {
                    $old_anh_con = explode('|', $prod['anh_con']);
                    foreach ($old_anh_con as $old_img) {
                        $trimmed_old = trim($old_img);
                        if (!empty($trimmed_old)) {
                            $old_path = dirname(__FILE__) . '/../' . $trimmed_old;
                            if (file_exists($old_path)) @unlink($old_path);
                        }
                    }
                }
                
                // Upload ảnh mới
                for ($i = 0; $i < $total_files; $i++) {
                    if ($_FILES['anh_con']['error'][$i] == UPLOAD_ERR_OK) {
                        $f_name = basename($_FILES['anh_con']['name'][$i]);
                        $f_tmp = $_FILES['anh_con']['tmp_name'][$i];
                        
                        if (is_dir($full_folder)) {
                            $safe_name = preg_replace('/[\\\\\\/]+/', '', $f_name);
                            $filename = $safe_name;
                            $dest = $full_folder . DIRECTORY_SEPARATOR . $filename;
                            $idx = 1;
                            
                            while (file_exists($dest)) {
                                $ext = pathinfo($safe_name, PATHINFO_EXTENSION);
                                $name_only = pathinfo($safe_name, PATHINFO_FILENAME);
                                $filename = $name_only . '_sub' . $idx . '.' . $ext;
                                $dest = $full_folder . DIRECTORY_SEPARATOR . $filename;
                                $idx++;
                            }
                            
                            if (move_uploaded_file($f_tmp, $dest)) {
                                $anh_con_paths[] = 'img/products/' . $folder . '/' . $filename;
                            }
                        }
                    }
                }
                
                // Cập nhật đường dẫn ảnh phụ mới
                $anh_con_db = !empty($anh_con_paths) ? implode('|', $anh_con_paths) : '';
            }
        }

        // --- LOGIC UPDATE DATABASE ---
        $stmtu = $conn->prepare("UPDATE san_pham SET ten_sanpham = ?, gia = ?, gia_khuyen_mai = ?, so_luong = ?, mo_ta = ?, ct_sp = ?, mau_sac = ?, hinh_anh = ?, anh_con = ?, id_danhmuc = ?, update_at = NOW() WHERE id_sanpham = ?");
        $ok = $stmtu->execute(array($ten, $gia, $gia_km, $so_luong, $mo_ta, $ct_sp, $mau_sac, $hinh_path_db, $anh_con_db, $id_danhmuc, $id));
        
        if ($ok) {
            // Áp dụng POST-Redirect-GET
            header("Location: admin.php?action=sanpham");
            exit(); 
        } else {
            $errorInfo = $stmtu->errorInfo();
            $message = '<div class="alert alert-danger">Lỗi khi cập nhật sản phẩm. Lỗi SQL: ' . htmlspecialchars($errorInfo[2]) . '</div>';
        }
    }

}

// --- LOGIC XÓA SẢN PHẨM ---
if ($action == 'xoa' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT hinh_anh FROM san_pham WHERE id_sanpham = ?");
    $stmt->execute(array($id));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        // Xóa file ảnh vật lý trên server (nếu có)
        if (!empty($row['hinh_anh'])) {
            $oldpath = dirname(__FILE__) . '/../' . $row['hinh_anh'];
            if (file_exists($oldpath)) @unlink($oldpath);
        }
    }
    
    // Xóa sản phẩm trong Database
    $stmtd = $conn->prepare("DELETE FROM san_pham WHERE id_sanpham = ?");
    $ok = $stmtd->execute(array($id));
    
    if ($ok) {
        header("Location: admin.php?action=sanpham");
        exit(); 
    } else {
        // Nếu xóa không thành công, hiển thị lỗi trên trang hiện tại
        $message = '<div class="alert alert-danger">Lỗi khi xóa sản phẩm.</div>';
    }
}

// --- LOGIC LỌC & PHÂN TRANG ---
$stmt = $conn->prepare("SELECT id_danhmuc, ten_danhmuc FROM danh_muc ORDER BY ten_danhmuc ASC");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$danhmuc = isset($_GET['danhmuc']) ? trim($_GET['danhmuc']) : '';
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$sql = "SELECT sp.*, dm.ten_danhmuc 
        FROM san_pham sp 
        JOIN danh_muc dm ON sp.id_danhmuc = dm.id_danhmuc 
        WHERE 1";
$params = array();

if ($danhmuc != '') {
    $sql .= " AND sp.id_danhmuc = ?";
    $params[] = $danhmuc;
}
if ($keyword != '') {
    $sql .= " AND sp.ten_sanpham LIKE ?";
    $params[] = "%".$keyword."%";
}

// Lấy tổng số lượng để phân trang
$sql_count = "SELECT COUNT(*) FROM san_pham sp JOIN danh_muc dm ON sp.id_danhmuc = dm.id_danhmuc WHERE 1";
if ($danhmuc != '') {
    $sql_count .= " AND sp.id_danhmuc = ?";
}
if ($keyword != '') {
    $sql_count .= " AND sp.ten_sanpham LIKE ?";
}
$stmt_count = $conn->prepare($sql_count);
$stmt_count->execute($params);
$total_products = $stmt_count->fetchColumn();
$total_pages = ceil($total_products / $per_page);

// Lấy data cho trang hiện tại
$sql_limit = $sql . " ORDER BY sp.id_sanpham ASC LIMIT " . intval($per_page) . " OFFSET " . intval($offset);
$stmt = $conn->prepare($sql_limit);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h2><i class="fas fa-box-open me-2"></i> Quản lý Sản phẩm</h2>
    <?php echo $message; ?>

    <?php
    $is_edit = false;
    $edit_data = array();
    
    if ($action == 'sua' && isset($_GET['id'])) {
        $is_edit = true;
        $eid = intval($_GET['id']);
        $stmt = $conn->prepare("SELECT * FROM san_pham WHERE id_sanpham = ?");
        $stmt->execute(array($eid));
        $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$edit_data) {
            echo '<div class="alert alert-danger">Sản phẩm không tìm thấy.</div>';
            $is_edit = false;
        }
    }

    // Quyết định trạng thái mở/đóng form
    // Form mở nếu: 1. Đang ở chế độ Sửa. 2. Vừa POST data (để người dùng thấy lỗi nếu có).
    $form_is_open = $is_edit || ($action == 'them' && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_product']));
    $collapse_class = $form_is_open ? 'collapse show' : 'collapse';
    $button_expanded = $form_is_open ? 'true' : 'false';
    
    $form_action = $is_edit ? 'admin.php?action=sanpham&act=sua&id=' . ($is_edit ? intval($_GET['id']) : '') : 'admin.php?action=sanpham&act=them';
    ?>

    <button class="btn btn-primary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#productFormArea" aria-expanded="<?php echo $button_expanded; ?>" aria-controls="productFormArea">
        <i class="fas fa-plus-circle me-1"></i> Thêm sản phẩm
    </button>
    
    <div id="productFormArea" class="<?php echo $collapse_class; ?>">
        <form method="post" enctype="multipart/form-data" action="<?php echo $form_action; ?>">
            <div class="row">
                <div class="col-md-6 mb-2">
                    <label>Tên sản phẩm</label>
                    <input type="text" name="ten_sanpham" class="form-control" required value="<?php echo $is_edit ? htmlspecialchars($edit_data['ten_sanpham']) : (isset($ten) ? htmlspecialchars($ten) : ''); ?>">
                </div>
                <div class="col-md-3 mb-2">
                    <label>Giá</label>
                    <input type="number" name="gia" step="0.01" class="form-control" required value="<?php echo $is_edit ? htmlspecialchars($edit_data['gia']) : (isset($gia) ? htmlspecialchars($gia) : ''); ?>">
                </div>
                <div class="col-md-3 mb-2">
                    <label>Giá khuyến mãi</label>
                    <input type="number" name="gia_khuyen_mai" step="0.01" class="form-control" value="<?php echo $is_edit ? htmlspecialchars($edit_data['gia_khuyen_mai']) : (isset($gia_km) ? htmlspecialchars($gia_km) : ''); ?>">
                </div>
                <div class="col-md-3 mb-2">
                    <label>Số lượng</label>
                    <input type="number" name="so_luong" class="form-control" value="<?php echo $is_edit ? htmlspecialchars($edit_data['so_luong']) : (isset($so_luong) ? htmlspecialchars($so_luong) : '0'); ?>">
                </div>
                <div class="col-md-3 mb-2">
                    <label>Màu sắc</label>
                    <input type="text" name="mau_sac" class="form-control" value="<?php echo $is_edit ? htmlspecialchars($edit_data['mau_sac']) : (isset($mau_sac) ? htmlspecialchars($mau_sac) : ''); ?>">
                </div>
                <div class="col-md-6 mb-2">
                    <label>Danh mục</label>
                    <select name="id_danhmuc" class="form-select" required>
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach ($categories as $c): ?>
                            <?php 
                                $selected_id = $is_edit ? $edit_data['id_danhmuc'] : (isset($id_danhmuc) ? $id_danhmuc : 0);
                                $sel = ($selected_id == $c['id_danhmuc']) ? 'selected' : ''; 
                            ?>
                            <option value="<?php echo $c['id_danhmuc']; ?>" <?php echo $sel; ?>><?php echo htmlspecialchars($c['ten_danhmuc']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-2">
                    <label>Ảnh sản phẩm (nếu để trống khi sửa thì giữ ảnh cũ)</label>
                    <input type="file" name="hinh_anh" class="form-control">
                    <?php if ($is_edit && !empty($edit_data['hinh_anh'])): ?>
                        <div class="mt-2">
                            <img src="<?php echo htmlspecialchars($edit_data['hinh_anh']); ?>" style="max-width:150px;" alt="Ảnh hiện tại">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-12 mb-2">
                    <label>Mô tả</label>
                    <small class="text-muted d-block mb-1">Bạn có thể thêm chữ, hình ảnh, video, định dạng văn bản...</small>
                    <textarea name="mo_ta" id="tinymce_editor_product" class="form-control" rows="15"><?php echo $is_edit ? $edit_data['mo_ta'] : (isset($mo_ta) ? $mo_ta : ''); ?></textarea>
                </div>
                <div class="col-md-12 mb-2">
                    <label>Thông số sản phẩm</label>
                    <small class="text-muted d-block mb-1">Nhập chi tiết thông số kỹ thuật, có thể thêm hình ảnh, video, bảng biểu...</small>
                    <textarea name="ct_sp" id="tinymce_editor_specs" class="form-control" rows="15"><?php echo $is_edit ? $edit_data['ct_sp'] : (isset($ct_sp) ? $ct_sp : ''); ?></textarea>
                </div>
                <div class="col-md-12 mb-2">
                    <label>Ảnh phụ sản phẩm (nhiều ảnh)</label>
                    <small class="text-muted d-block mb-1">Chọn nhiều ảnh để hiển thị phía dưới ảnh chính</small>
                    <input type="file" name="anh_con[]" class="form-control" multiple accept="image/*">
                    <?php if ($is_edit && !empty($edit_data['anh_con'])): ?>
                        <div class="mt-2">
                            <label class="d-block mb-2">Ảnh phụ hiện tại:</label>
                            <div class="row g-2">
                                <?php 
                                $anh_con_array = explode('|', $edit_data['anh_con']);
                                foreach ($anh_con_array as $anh_con_item): 
                                    $trimmed_item = trim($anh_con_item);
                                    if (!empty($trimmed_item)):
                                ?>
                                <div class="col-md-2">
                                    <img src="<?php echo htmlspecialchars($trimmed_item); ?>" class="img-thumbnail" style="width:100%; height:100px; object-fit:cover;" alt="Ảnh phụ">
                                </div>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                            <small class="text-info d-block mt-2"><i class="fas fa-info-circle"></i> Chọn ảnh mới để thay thế hoàn toàn các ảnh phụ hiện tại</small>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-12 text-end">
                    <button type="submit" name="save_product" class="btn btn-success"><?php echo $is_edit ? 'Cập nhật' : 'Lưu'; ?></button>
                    <a href="admin.php?action=sanpham" class="btn btn-secondary">Hủy</a>
                </div>
            </div>
        </form>
        <hr>
    </div>
    
    <!-- TinyMCE Editor Script -->
    <script src="https://cdn.tiny.cloud/1/f0qu5j0bm9mm6kncwoq8rgcdhhgt0gf6gddmchbp6vle0221/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        // Hàm validate form trước khi submit
        function validateProductForm() {
            // Lưu nội dung TinyMCE về textarea trước khi submit
            if (tinymce.get('tinymce_editor_product')) {
                tinymce.get('tinymce_editor_product').save();
            }
            if (tinymce.get('tinymce_editor_specs')) {
                tinymce.get('tinymce_editor_specs').save();
            }
            return true;
        }

        // Cấu hình chung cho TinyMCE
        var tinyMCEConfig = {
            height: 400,
            menubar: false,
            language: 'vi',
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | image media | link | code | help',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
            
            // Cấu hình upload ảnh - TinyMCE 6 yêu cầu return Promise
            automatic_uploads: true,
            images_upload_handler: function (blobInfo, progress) {
                return new Promise(function(resolve, reject) {
                    var xhr, formData;
                    
                    console.log('Bắt đầu upload ảnh:', blobInfo.filename());
                    
                    xhr = new XMLHttpRequest();
                    xhr.withCredentials = false;
                    xhr.open('POST', 'nhanvien/upload_image.php');
                    
                    xhr.upload.onprogress = function (e) {
                        progress(e.loaded / e.total * 100);
                    };
                    
                    xhr.onload = function() {
                        console.log('Upload hoàn tất. Status:', xhr.status);
                        console.log('Response:', xhr.responseText);
                        console.log('Response type:', typeof xhr.responseText);
                        
                        if (xhr.status === 500) {
                            console.error('Server error 500');
                            reject('Lỗi server (500). Kiểm tra PHP error log.');
                            return;
                        }
                        
                        if (xhr.status != 200) {
                            console.error('HTTP Error:', xhr.status);
                            reject('Lỗi HTTP: ' + xhr.status);
                            return;
                        }
                        
                        try {
                            var json = JSON.parse(xhr.responseText);
                            if (!json || typeof json.location != 'string') {
                                console.error('Invalid JSON response:', xhr.responseText);
                                reject('Phản hồi không hợp lệ từ server');
                                return;
                            }
                            console.log('Upload thành công:', json.location);
                            resolve(json.location);
                        } catch (e) {
                            console.error('Lỗi parse JSON:', e);
                            console.error('Response text:', xhr.responseText);
                            reject('Lỗi xử lý phản hồi: ' + e.message + '. Response: ' + xhr.responseText.substring(0, 100));
                        }
                    };
                    
                    xhr.onerror = function () {
                        console.error('XHR Error');
                        reject('Lỗi kết nối đến server');
                    };
                    
                    formData = new FormData();
                    formData.append('file', blobInfo.blob(), blobInfo.filename());
                    xhr.send(formData);
                });
            }
        };

        // Khởi tạo TinyMCE Editor cho Mô tả sản phẩm
        tinymce.init(Object.assign({}, tinyMCEConfig, {
            selector: '#tinymce_editor_product'
        }));

        // Khởi tạo TinyMCE Editor cho Thông số sản phẩm
        tinymce.init(Object.assign({}, tinyMCEConfig, {
            selector: '#tinymce_editor_specs'
        }));

        // Gắn validation vào form
        document.addEventListener('DOMContentLoaded', function() {
            var productForm = document.querySelector('#productFormArea form');
            if (productForm) {
                productForm.addEventListener('submit', function(e) {
                    return validateProductForm();
                });
            }
        });
    </script>
    
    <div class="filter-bar mb-4 p-3 bg-light rounded shadow-sm">
      <form method="GET" action="admin.php" class="row g-3 align-items-center">
        <input type="hidden" name="action" value="sanpham">
        <div class="col-md-4">
          <label class="form-label fw-bold">Danh mục:</label>
          <select name="danhmuc" class="form-select">
            <option value="">-- Tất cả danh mục --</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?php echo $c['id_danhmuc']; ?>" <?php if($danhmuc == $c['id_danhmuc']) echo 'selected'; ?>><?php echo htmlspecialchars($c['ten_danhmuc']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-bold">Tìm theo tên sản phẩm:</label>
          <input type="text" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>" class="form-control" placeholder="Nhập tên sản phẩm...">
        </div>
        <div class="col-md-4 text-end mt-4">
          <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i> Lọc</button>
          <a href="admin.php?action=sanpham" class="btn btn-secondary"><i class="fas fa-undo me-1"></i> Reset</a>
        </div>
      </form>
    </div>

    <h3><i class="fas fa-list-ul me-2"></i> Danh sách Sản phẩm (<?php echo $total_products; ?>)</h3>
    <?php if (count($products) == 0): ?>
        <div class="alert alert-warning text-center mt-4" role="alert">
            <i class="fas fa-info-circle me-2"></i> Không có sản phẩm phù hợp với bộ lọc.
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Ảnh</th>
                    <th>Tên</th>
                    <th>Giá</th>
                    <th>Giá KM</th>
                    <th>Số lượng</th>
                    <th>Danh mục</th>
                    <th>Ngày tạo</th>
                    <th>Ngày cập nhật</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td><?php echo $p['id_sanpham']; ?></td>
                    <td>
                        <?php if (!empty($p['hinh_anh'])): ?>
                            <img src="<?php echo htmlspecialchars($p['hinh_anh']); ?>" width="80" height="80" class="rounded" alt="Ảnh sản phẩm">
                        <?php else: ?>
                            <span class="text-muted">Không có</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($p['ten_sanpham']); ?></td>
                    <td><?php echo number_format($p['gia'], 0, ',', '.'); ?>₫</td>
                    <td><?php echo number_format($p['gia_khuyen_mai'], 0, ',', '.'); ?>₫</td>
                    <td><?php echo intval($p['so_luong']); ?></td>
                    <td><?php echo htmlspecialchars($p['ten_danhmuc']); ?></td>
                    <td><?php echo $p['created_at']; ?></td>
                    <td><?php echo $p['update_at']; ?></td>
                    <td>
                        <a class="btn btn-sm btn-primary" href="admin.php?action=sanpham&act=sua&id=<?php echo $p['id_sanpham']; ?>">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a class="btn btn-sm btn-danger" href="admin.php?action=sanpham&act=xoa&id=<?php echo $p['id_sanpham']; ?>" onclick="return confirm('Xóa sản phẩm này?');">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <nav aria-label="Page navigation">
      <ul class="pagination justify-content-center">
        <?php
        $query_base = 'admin.php?action=sanpham';
        if ($danhmuc != '') $query_base .= '&danhmuc=' . $danhmuc;
        if ($keyword != '') $query_base .= '&keyword=' . urlencode($keyword);
        
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
</div>