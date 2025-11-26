<?php

/**
 * Hàm chuyển đổi vai trò (enum) sang tiếng Việt dễ đọc.
 */
function convert_vai_tro($role) {
    switch (strtolower($role)) {
        case 'quanly':
            return 'Quản lý';
        case 'nhanvien':
            return 'Nhân viên bán hàng';
        case 'nhanvienkho':
            return 'Nhân viên kho';
        case 'khachhang':
            return 'Khách hàng'; 
        default:
            return $role;
    }
}

/**
 * Hàm ánh xạ vai trò enum sang mã vai trò (ma_vaitro) theo DB.
 */
function get_ma_vaitro($vai_tro_enum) {
    // Tương thích với PHP < 5.4
    $mapping = array( 
        'quanly' => 1,
        'nhanvien' => 2,
        'nhanvienkho' => 3,
        'khachhang' => 0,
    );
    
    $lower_role = strtolower($vai_tro_enum);
    // Tương thích với PHP < 7.0
    return isset($mapping[$lower_role]) ? $mapping[$lower_role] : null; 
}

/**
 * Hàm chuẩn hóa Họ tên (viết hoa chữ cái đầu mỗi từ).
 */
function format_ho_ten($name) {
    // Chuyển về chữ thường, sau đó viết hoa chữ cái đầu mỗi từ
    return ucwords(strtolower($name));
}

/**
 * Hàm kiểm tra ràng buộc (Validation)
 * @param array $data Dữ liệu cần kiểm tra (ho_ten, email, phone, password, vai_tro)
 * @param bool $is_edit True nếu đang sửa, False nếu đang thêm mới
 * @return array Kết quả kiểm tra (status, message, formatted_data)
 */
function validate_input($data, $is_edit = false) {
    $errors = array();
    $formatted_data = array();

    // --- 1. Họ Tên (chuẩn hóa và kiểm tra rỗng) ---
    $formatted_data['ho_ten'] = format_ho_ten(trim($data['ho_ten']));
    if (empty($formatted_data['ho_ten'])) {
        $errors[] = "Họ tên không được để trống.";
    }

    // --- 2. Email (kiểm tra định dạng và rỗng) ---
    $formatted_data['email'] = trim($data['email']);
    if (empty($formatted_data['email'])) {
        $errors[] = "Email không được để trống.";
    } elseif (!filter_var($formatted_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không đúng định dạng.";
    }

    // --- 3. Số điện thoại (10 số, không rỗng) ---
    $formatted_data['phone'] = trim($data['phone']);
    if (empty($formatted_data['phone'])) {
         $errors[] = "Số điện thoại không được để trống.";
    } elseif (!preg_match('/^[0-9]{10}$/', $formatted_data['phone'])) {
        $errors[] = "Số điện thoại phải có đúng 10 chữ số.";
    }
    
    // Thêm cột địa chỉ cho đầy đủ
    $formatted_data['dia_chi'] = trim($data['dia_chi']);

    // --- 4. Mật khẩu (Chỉ bắt buộc khi thêm mới hoặc khi sửa và có nhập mật khẩu mới) ---
    if (!$is_edit || (!empty($data['password_new']) && $is_edit)) {
        $password = $is_edit ? $data['password_new'] : $data['password'];
        
        if (strlen($password) < 8) {
            $errors[] = "Mật khẩu phải có tối thiểu 8 ký tự.";
        }
        // Kiểm tra phải có chữ cái (hoa hoặc thường) và số
        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors[] = "Mật khẩu phải chứa ít nhất một chữ cái và một chữ số.";
        }
        $formatted_data['password'] = $password;
    }
    
    // --- 5. Vai trò ---
    $formatted_data['vai_tro'] = trim($data['vai_tro']);
    $formatted_data['ma_vaitro'] = get_ma_vaitro($formatted_data['vai_tro']);
    if ($formatted_data['ma_vaitro'] === null) {
        $errors[] = "Vai trò không hợp lệ.";
    }

    if (count($errors) > 0) {
        return array(
            'status' => 'error',
            'message' => implode('<br>', $errors),
            'formatted_data' => $formatted_data
        );
    }
    
    return array(
        'status' => 'success',
        'message' => 'Validation thành công',
        'formatted_data' => $formatted_data
    );
}


// === XỬ LÝ CẬP NHẬT NHÂN VIÊN (SỬA) ===
if (isset($_POST['edit_user'])) {
    $validation_data = $_POST;
    $validation_data['password'] = $_POST['password_new']; 
    $result = validate_input($validation_data, true); 
    $ma_user = intval($_POST['ma_user']);
    $current_time = date('Y-m-d H:i:s');
    
    if ($result['status'] === 'success' && $ma_user > 0) {
        $data = $result['formatted_data'];
        $password_new = trim($_POST['password_new']); 
        
        $params = array($data['ho_ten'], $data['email'], $data['phone'], $data['dia_chi'], $data['vai_tro'], $data['ma_vaitro'], $current_time);
        $sql = "UPDATE user SET ho_ten = ?, email = ?, phone = ?, dia_chi = ?, vai_tro = ?, ma_vaitro = ?, update_at = ?";

        if (!empty($password_new)) {
            $password_hashed = md5($password_new);
            $sql .= ", password = ?";
            $params[] = $password_hashed;
        }

        $sql .= " WHERE ma_user = ?";
        $params[] = $ma_user;

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        echo "<div class='alert alert-success'>Cập nhật nhân viên thành công!</div>";

    } else {
        echo "<div class='alert alert-danger'>Lỗi cập nhật:<br>" . $result['message'] . "</div>";
    }
}


// === XỬ LÝ THÊM NHÂN VIÊN MỚI ===
if (isset($_POST['add_user'])) {
    $result = validate_input($_POST, false); 

    if ($result['status'] === 'success') {
        $data = $result['formatted_data'];
        
        $password = md5($data['password']); 
        $current_time = date('Y-m-d H:i:s');
        $trang_thai_default = 'active'; 

        $stmt = $conn->prepare("
            INSERT INTO user (
                ho_ten, email, password, phone, dia_chi, vai_tro, ma_vaitro, trang_thai, created_at, update_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute(array(
            $data['ho_ten'], $data['email'], $password, $data['phone'], $data['dia_chi'], $data['vai_tro'], $data['ma_vaitro'], $trang_thai_default, $current_time, $current_time
        ));
        echo "<div class='alert alert-success'>Thêm nhân viên thành công!</div>";
    } else {
        echo "<div class='alert alert-danger'>Lỗi thêm mới:<br>" . $result['message'] . "</div>";
    }
}

// === XỬ LÝ XÓA NHÂN VIÊN (LOGIC BẢO MẬT) ===
if (isset($_GET['delete_user'])) {
    $ma_user = intval($_GET['delete_user']);
    
    $stmt_check = $conn->prepare("SELECT vai_tro FROM user WHERE ma_user = ?");
    $stmt_check->execute(array($ma_user));
    $user_to_delete = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if ($user_to_delete && strtolower($user_to_delete['vai_tro']) === 'quanly') {
        echo "<div class='alert alert-danger'>KHÔNG THỂ XÓA TÀI KHOẢN QUẢN LÝ vì lý do bảo mật hệ thống!</div>";
    } else {
        $stmt = $conn->prepare("DELETE FROM user WHERE ma_user = ?");
        $stmt->execute(array($ma_user));
        echo "<div class='alert alert-success'>Đã xóa nhân viên.</div>";
    }
}

// === KHÓA / MỞ NHÂN VIÊN (Cập nhật cột update_at) ===
$current_time = date('Y-m-d H:i:s');

if (isset($_GET['lock_user'])) {
    $ma_user = intval($_GET['lock_user']);
    $conn->prepare("UPDATE user SET trang_thai = 'locked', update_at = ? WHERE ma_user = ?")->execute(array($current_time, $ma_user));
    echo "<div class='alert alert-warning'>Đã khóa tài khoản.</div>";
}

if (isset($_GET['unlock_user'])) {
    $ma_user = intval($_GET['unlock_user']);
    $conn->prepare("UPDATE user SET trang_thai = 'active', update_at = ? WHERE ma_user = ?")->execute(array($current_time, $ma_user));
    echo "<div class='alert alert-success'>Đã mở khóa tài khoản.</div>";
}

// === LẤY DANH SÁCH NHÂN VIÊN VÀ QUẢN LÝ (ĐÃ THÊM LOGIC TÌM KIẾM) ===
$search_term = '';
$search_query = '';
$params = array();

// Chỉ giới hạn tìm kiếm trong các vai trò nhân viên và quản lý
$where_role = " LOWER(vai_tro) IN ('quanly', 'nhanvien', 'nhanvienkho') ";

if (isset($_GET['search_term']) && trim($_GET['search_term']) !== '') {
    $search_term = trim($_GET['search_term']);
    
    // Thêm điều kiện tìm kiếm theo họ tên HOẶC email
    $search_query = " AND (ho_ten LIKE ? OR email LIKE ?) ";
    $params[] = '%' . $search_term . '%';
    $params[] = '%' . $search_term . '%';
}

$sql = "
    SELECT * FROM user 
    WHERE " . $where_role . $search_query . "
    ORDER BY created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h2><i class="fas fa-user-tie me-2"></i> Quản lý Nhân viên</h2>
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-plus-circle me-1"></i> Thêm nhân viên
        </button>

        <form method="GET" action="admin.php" class="d-flex">
            <input type="hidden" name="action" value="nhanvien"> 
            
            <input type="text" name="search_term" class="form-control me-2" placeholder="Nhập thông tin nhân viên" value="<?php echo htmlspecialchars($search_term); ?>">
            <button class="btn btn-outline-success" type="submit"><i class="fas fa-search"></i></button>
            <?php if (!empty($search_term)): ?>
                <a href="admin.php?action=nhanvien" class="btn btn-outline-danger ms-2"><i class="fas fa-times"></i></a>
            <?php endif; ?>
        </form>
    </div>

    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Họ tên</th>
                <th>Email</th>
                <th>SĐT</th>
                <th>Địa chỉ</th>
                <th>Vai trò</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td data-id="<?php echo $user['ma_user']; ?>"><?php echo $user['ma_user']; ?></td> 
                <td data-ho_ten="<?php echo htmlspecialchars($user['ho_ten']); ?>"><?php echo htmlspecialchars($user['ho_ten']); ?></td>
                <td data-email="<?php echo htmlspecialchars($user['email']); ?>"><?php echo htmlspecialchars($user['email']); ?></td>
                <td data-phone="<?php echo htmlspecialchars($user['phone']); ?>"><?php echo htmlspecialchars($user['phone']); ?></td>
                <td data-dia_chi="<?php echo htmlspecialchars($user['dia_chi']); ?>"><?php echo htmlspecialchars($user['dia_chi']); ?></td>
                
                <td data-vai_tro="<?php echo htmlspecialchars($user['vai_tro']); ?>">
                    <?php echo convert_vai_tro($user['vai_tro']); ?>
                </td>
                
                <td>
                    <?php if (strtolower($user['trang_thai']) === 'active'): ?>
                        <span class="badge bg-success">Hoạt động</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Đã khóa</span>
                    <?php endif; ?>
                </td>
                
                <td>
                    <button class="btn btn-sm btn-info btn-edit-user" 
                        data-bs-toggle="modal" 
                        data-bs-target="#editUserModal"
                        data-id="<?php echo $user['ma_user']; ?>"
                        data-ho_ten="<?php echo htmlspecialchars($user['ho_ten']); ?>"
                        data-email="<?php echo htmlspecialchars($user['email']); ?>"
                        data-phone="<?php echo htmlspecialchars($user['phone']); ?>"
                        data-dia_chi="<?php echo htmlspecialchars($user['dia_chi']); ?>"
                        data-vai_tro="<?php echo htmlspecialchars($user['vai_tro']); ?>"
                        title="Chỉnh sửa thông tin">
                        <i class="fas fa-edit"></i>
                    </button>
                    
                    <?php if (strtolower($user['trang_thai']) === 'active'): ?>
                        <a href="?action=nhanvien&lock_user=<?php echo $user['ma_user']; ?>" class="btn btn-sm btn-warning" title="Khóa tài khoản">
                            <i class="fas fa-lock"></i>
                        </a>
                    <?php else: ?>
                        <a href="?action=nhanvien&unlock_user=<?php echo $user['ma_user']; ?>" class="btn btn-sm btn-success" title="Mở khóa tài khoản">
                            <i class="fas fa-lock-open"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (strtolower($user['vai_tro']) !== 'quanly'): ?>
                        <a href="?action=nhanvien&delete_user=<?php echo $user['ma_user']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xóa nhân viên <?php echo htmlspecialchars($user['ho_ten']); ?>?');" title="Xóa">
                            <i class="fas fa-trash"></i>
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="admin.php?action=nhanvien">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Thêm Nhân viên</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label>Họ tên</label>
            <input type="text" name="ho_ten" class="form-control" required>
          </div>
          <div class="mb-2">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-2">
            <label>Mật khẩu</label>
            <input type="password" name="password" class="form-control" required>
            <small class="form-text text-muted">Tối thiểu 8 ký tự, phải có số và chữ cái.</small>
          </div>
          <div class="mb-2">
            <label>Số điện thoại (10 số)</label>
            <input type="text" name="phone" class="form-control">
          </div>
          <div class="mb-2">
            <label>Địa chỉ</label>
            <input type="text" name="dia_chi" class="form-control">
          </div>
          <div class="mb-2">
            <label>Vai trò</label>
            <select name="vai_tro" class="form-select">
              <option value="nhanvien">Nhân viên bán hàng</option>
              <option value="nhanvienkho">Nhân viên kho</option>
              <option value="quanly">Quản lý</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="add_user" class="btn btn-success">Lưu</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="admin.php?action=nhanvien">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Sửa thông tin Nhân viên</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="ma_user" id="edit_ma_user">
          <div class="mb-2">
            <label>Họ tên</label>
            <input type="text" name="ho_ten" id="edit_ho_ten" class="form-control" required>
          </div>
          <div class="mb-2">
            <label>Email</label>
            <input type="email" name="email" id="edit_email" class="form-control" required>
          </div>
          <div class="mb-2">
            <label>Mật khẩu (Để trống nếu không thay đổi)</label>
            <input type="password" name="password_new" class="form-control">
            <small class="form-text text-muted">Tối thiểu 8 ký tự, phải có số và chữ cái.</small>
          </div>
          <div class="mb-2">
            <label>Số điện thoại (10 số)</label>
            <input type="text" name="phone" id="edit_phone" class="form-control">
          </div>
          <div class="mb-2">
            <label>Địa chỉ</label>
            <input type="text" name="dia_chi" id="edit_dia_chi" class="form-control">
          </div>
          <div class="mb-2">
            <label>Vai trò</label>
            <select name="vai_tro" id="edit_vai_tro" class="form-select">
              <option value="nhanvien">Nhân viên bán hàng</option>
              <option value="nhanvienkho">Nhân viên kho</option>
              <option value="quanly">Quản lý</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="edit_user" class="btn btn-success">Lưu thay đổi</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('.btn-edit-user').on('click', function() {
            var ma_user = $(this).data('id');
            var ho_ten = $(this).data('ho_ten');
            var email = $(this).data('email');
            var phone = $(this).data('phone');
            var dia_chi = $(this).data('dia_chi');
            var vai_tro = $(this).data('vai_tro');
            
            // Đổ dữ liệu vào các trường trong Modal Sửa
            $('#edit_ma_user').val(ma_user);
            $('#edit_ho_ten').val(ho_ten);
            $('#edit_email').val(email);
            $('#edit_phone').val(phone);
            $('#edit_dia_chi').val(dia_chi);
            
            // Chọn đúng vai trò trong dropdown
            $('#edit_vai_tro').val(vai_tro);
            
            // Xóa trường mật khẩu để tránh gửi mật khẩu cũ
            $('input[name="password_new"]').val('');
        });
    });
</script>