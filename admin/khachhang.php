<?php

/**
 * Hàm kiểm tra ràng buộc
 */
function validate_input_khachhang($data, $is_edit = false) {
    $errors = array();
    $formatted_data = array();

    // --- 1. Họ Tên (CHỈ TRIM, KHÔNG FORMAT) ---
    $formatted_data['ho_ten'] = trim($data['ho_ten']); // Giữ nguyên tên người dùng nhập
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

    // --- 3. Số điện thoại (10 số, không rỗng khi thêm mới) ---
    $formatted_data['phone'] = trim(isset($data['phone']) ? $data['phone'] : '');

    if (!$is_edit && empty($formatted_data['phone'])) {
        $errors[] = "Số điện thoại không được để trống khi thêm mới.";
    } elseif (!empty($formatted_data['phone']) && !preg_match('/^[0-9]{10}$/', $formatted_data['phone'])) {
        $errors[] = "Số điện thoại phải có đúng 10 chữ số (nếu có nhập).";
    }

    $formatted_data['dia_chi'] = isset($data['dia_chi']) ? trim($data['dia_chi']) : ''; 

    // --- 4. Mật khẩu (Chỉ bắt buộc khi thêm mới hoặc khi sửa và có nhập mật khẩu mới) ---
    $password_field = $is_edit ? 'password_new' : 'password';
    $password = isset($data[$password_field]) ? $data[$password_field] : ''; 

    if (!$is_edit || (!empty($password) && $is_edit)) {
        if (empty($password) && !$is_edit) {
             $errors[] = "Mật khẩu không được để trống.";
        } else if (strlen($password) < 8) {
            $errors[] = "Mật khẩu phải có tối thiểu 8 ký tự.";
        }
        else if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors[] = "Mật khẩu phải chứa ít nhất một chữ cái và một chữ số.";
        }
        $formatted_data['password'] = $password;
    }
    
    // Gán vai trò và mã vai trò
    $formatted_data['vai_tro'] = 'khachhang';
    $formatted_data['ma_vaitro'] = 0; 

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

// === XỬ LÝ CẬP NHẬT KHÁCH HÀNG (SỬA) ===
if (isset($_POST['edit_customer'])) {
    $validation_data = $_POST;
    $validation_data['password'] = isset($_POST['password_new']) ? $_POST['password_new'] : ''; 
    
    $result = validate_input_khachhang($validation_data, true); 
    $ma_user = intval($_POST['ma_user']); 
    $current_time = date('Y-m-d H:i:s');
    
    if ($result['status'] === 'success' && $ma_user > 0) {
        $data = $result['formatted_data'];
        $password_new = isset($_POST['password_new']) ? trim($_POST['password_new']) : ''; 
        
        $params = array($data['ho_ten'], $data['email'], $data['phone'], $data['dia_chi'], $current_time);
        $sql = "UPDATE user SET ho_ten = ?, email = ?, phone = ?, dia_chi = ?, update_at = ?";

        if (!empty($password_new)) {
            $password_hashed = md5($password_new); 
            $sql .= ", password = ?";
            $params[] = $password_hashed;
        }

        $sql .= " WHERE ma_user = ?"; 
        $params[] = $ma_user;

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        echo "<div class='alert alert-success'>Cập nhật khách hàng thành công!</div>";

    } else {
        echo "<div class='alert alert-danger'>Lỗi cập nhật:<br>" . $result['message'] . "</div>";
    }
}


// === XỬ LÝ THÊM KHÁCH HÀNG ===
if (isset($_POST['add_customer'])) {
    $result = validate_input_khachhang($_POST, false); 

    if ($result['status'] === 'success') {
        $data = $result['formatted_data'];
        
        $password = md5($data['password']); 
        $current_time = date('Y-m-d H:i:s');
        $trang_thai_default = 1; // Giả sử 1 là trạng thái mặc định (active)

        $stmt = $conn->prepare("
            INSERT INTO user (
                ho_ten, email, password, phone, dia_chi, vai_tro, ma_vaitro, trang_thai, created_at, update_at
            ) VALUES (?, ?, ?, ?, ?, 'khachhang', 0, ?, ?, ?)
        ");
        $stmt->execute(array(
            $data['ho_ten'], $data['email'], $password, $data['phone'], $data['dia_chi'], $trang_thai_default, $current_time, $current_time
        ));
        echo "<div class='alert alert-success'>Thêm khách hàng thành công!</div>";
    } else {
        echo "<div class='alert alert-danger'>Lỗi thêm mới:<br>" . $result['message'] . "</div>";
    }
}

// === XỬ LÝ XÓA KHÁCH HÀNG ===
if (isset($_GET['delete_user'])) {
    $ma_user = intval($_GET['delete_user']); 
    $stmt = $conn->prepare("DELETE FROM user WHERE ma_user = ?");
    $stmt->execute(array($ma_user));
    echo "<div class='alert alert-success'>Đã xóa khách hàng.</div>";
}

// *** ĐÃ XÓA LOGIC KHÓA/MỞ KHÓA THEO YÊU CẦU ***

// === LẤY DANH SÁCH KHÁCH HÀNG (Tìm kiếm) ===
$search_term = '';
$search_query = '';
$params = array();

if (isset($_GET['search_term']) && trim($_GET['search_term']) !== '') {
    $search_term = trim($_GET['search_term']);
    
    $search_query = " AND (ho_ten LIKE ? OR email LIKE ? OR phone LIKE ?) ";
    $params[] = '%' . $search_term . '%';
    $params[] = '%' . $search_term . '%';
    $params[] = '%' . $search_term . '%';
}

$sql = "
    SELECT * FROM user 
    WHERE LOWER(vai_tro) = 'khachhang' " . $search_query . "
    ORDER BY created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container mt-4">
    <h2><i class="fas fa-user-friends me-2"></i> Quản lý Khách hàng</h2>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <button class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
            <i class="fas fa-plus-circle me-1"></i> Thêm khách hàng
        </button>
        
        <form method="GET" action="admin.php" class="d-flex">
            <input type="hidden" name="action" value="khachhang"> 
            
            <input type="text" name="search_term" class="form-control me-2" placeholder="Tìm kiếm Tên, Email, SĐT" value="<?php echo htmlspecialchars($search_term); ?>">
            <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
            <?php if (!empty($search_term)): ?>
                <a href="admin.php?action=khachhang" class="btn btn-outline-danger ms-2" title="Xóa tìm kiếm"><i class="fas fa-times"></i></a>
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
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($customers as $row): ?>
            <tr>
                <td data-ma_user="<?php echo $row['ma_user']; ?>"><?php echo $row['ma_user']; ?></td>
                <td data-ho_ten="<?php echo htmlspecialchars($row['ho_ten']); ?>"><?php echo htmlspecialchars($row['ho_ten']); ?></td>
                <td data-email="<?php echo htmlspecialchars($row['email']); ?>"><?php echo htmlspecialchars($row['email']); ?></td>
                <td data-phone="<?php echo htmlspecialchars($row['phone']); ?>"><?php echo htmlspecialchars($row['phone']); ?></td>
                <td data-dia_chi="<?php echo htmlspecialchars($row['dia_chi']); ?>"><?php echo htmlspecialchars($row['dia_chi']); ?></td>
                
                <td>
                    <button class="btn btn-sm btn-info btn-edit-customer" 
                        data-bs-toggle="modal" 
                        data-bs-target="#editCustomerModal"
                        data-id="<?php echo $row['ma_user']; ?>"
                        data-ho_ten="<?php echo htmlspecialchars($row['ho_ten']); ?>"
                        data-email="<?php echo htmlspecialchars($row['email']); ?>"
                        data-phone="<?php echo htmlspecialchars($row['phone']); ?>"
                        data-dia_chi="<?php echo htmlspecialchars($row['dia_chi']); ?>"
                        title="Chỉnh sửa thông tin">
                        <i class="fas fa-edit"></i>
                    </button>
                    
                    <a href="?action=khachhang&delete_user=<?php echo $row['ma_user']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xóa khách hàng <?php echo htmlspecialchars($row['ho_ten']); ?>?');" title="Xóa">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="admin.php?action=khachhang">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm Khách hàng</h5>
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
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Địa chỉ</label>
                        <input type="text" name="dia_chi" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_customer" class="btn btn-success">Lưu</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="admin.php?action=khachhang">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa thông tin Khách hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="ma_user" id="edit_ma_user_customer">
                    <div class="mb-2">
                        <label>Họ tên</label>
                        <input type="text" name="ho_ten" id="edit_ho_ten_customer" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Email</label>
                        <input type="email" name="email" id="edit_email_customer" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Mật khẩu (Để trống nếu không thay đổi)</label>
                        <input type="password" name="password_new" class="form-control">
                        <small class="form-text text-muted">Tối thiểu 8 ký tự, phải có số và chữ cái.</small>
                    </div>
                    <div class="mb-2">
                        <label>Số điện thoại (10 số)</label>
                        <input type="text" name="phone" id="edit_phone_customer" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label>Địa chỉ</label>
                        <input type="text" name="dia_chi" id="edit_dia_chi_customer" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="edit_customer" class="btn btn-success">Lưu thay đổi</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('.btn-edit-customer').on('click', function() {
            var ma_user = $(this).data('id');
            var ho_ten = $(this).data('ho_ten');
            var email = $(this).data('email');
            var phone = $(this).data('phone');
            var dia_chi = $(this).data('dia_chi');
            
            // Đổ dữ liệu vào các trường trong Modal Sửa
            $('#edit_ma_user_customer').val(ma_user);
            $('#edit_ho_ten_customer').val(ho_ten);
            $('#edit_email_customer').val(email);
            $('#edit_phone_customer').val(phone);
            $('#edit_dia_chi_customer').val(dia_chi);
            
            // Xóa trường mật khẩu để tránh gửi mật khẩu cũ
            $('input[name="password_new"]').val('');
        });
    });
</script>