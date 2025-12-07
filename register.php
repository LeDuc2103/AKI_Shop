<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ho_ten = trim($_POST['ho_ten']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $dia_chi = trim($_POST['dia_chi']);
    
    // Validation - Kiểm tra tất cả các trường bắt buộc
    if (empty($ho_ten) || empty($email) || empty($password) || empty($confirm_password) || empty($phone) || empty($dia_chi)) {
        $error = 'Vui lòng điền đầy đủ thông tin!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ!';
    } elseif (strlen($password) < 8) {
        $error = 'Mật khẩu phải có ít nhất 8 ký tự!';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = 'Mật khẩu phải có ít nhất một chữ hoa!';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $error = 'Mật khẩu phải có ít nhất một chữ thường!';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = 'Mật khẩu phải có ít nhất một chữ số!';
    } elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $error = 'Mật khẩu phải có ít nhất một ký tự đặc biệt!';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp!';
    } else {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Kiểm tra email đã tồn tại
            $check_stmt = $conn->prepare("SELECT ma_user FROM user WHERE email = ?");
            $check_stmt->execute(array($email));
            
            if ($check_stmt->fetch()) {
                $error = 'Email này đã được sử dụng!';
            } else {
                // Lấy ID tiếp theo
                $stmt = $conn->query("SELECT MAX(ma_user) as max_id FROM user");
                $result = $stmt->fetch();
                $next_id = ($result['max_id'] ? $result['max_id'] : 0) + 1;
                
                // Thêm user mới
                $stmt = $conn->prepare("INSERT INTO user (ma_user, ho_ten, email, password, phone, dia_chi, vai_tro, trang_thai, created_at) VALUES (?, ?, ?, ?, ?, ?, 'khachhang', 'active', NOW())");
                
                if ($stmt->execute(array($next_id, $ho_ten, $email, md5($password), $phone, $dia_chi))) {
                    $success = 'Đăng ký tài khoản thành công!';
                    
                    // Xóa dữ liệu form sau khi đăng ký thành công
                    $_POST = array();
                } else {
                    $error = 'Có lỗi xảy ra. Vui lòng thử lại!';
                }
            }
        } catch (Exception $e) {
            $error = 'Có lỗi xảy ra: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <title>Đăng Ký - KLTN Shop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <section id="header">
        <a href="#"><img src="img/logo7.png" width="150px" class="logo" alt="KLTN Logo"></a>
        <div>
            <ul id="navbar">
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="shop.php">Sản phẩm</a></li>
                <li><a href="blog.php">Tin tức</a></li>
                <li><a href="about.php">Về chúng tôi</a></li>
                <li><a href="contact.php">Liên hệ</a></li>
                <li id="search-icon"><a href="#"><i class="fa-solid fa-search"></i></a></li>
                <li id="user-icon" tabindex="0">
                    <a href="#" tabindex="-1"><i class="fa-solid fa-user"></i></a>
                    <div class="user-dropdown">
                        <a href="login.php">Đăng Nhập</a>
                        <a href="register.php" class="active">Đăng Ký</a>
                    </div>
                </li>
                <li id="lg-bag"><a href="cart.php"><i class="fa-solid fa-cart-shopping"></i></a></li>
                <a href="#" id="close"><i class="fa-solid fa-xmark"></i></a>    
            </ul> 
            <div id="mobile">
                <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i></a>
                <i id="bar" class="fas fa-outdent"></i>
            </div>  
        </div>
    </section>
    
    <!--Register page-->
    <section id="register-page">
        <div class="register-container">
            <h2>Đăng Ký Tài Khoản</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger" style="background: #fee; color: #c33; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fcc; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="ho_ten">Họ và tên *</label>
                    <input type="text" id="ho_ten" name="ho_ten" value="<?php echo isset($_POST['ho_ten']) ? htmlspecialchars($_POST['ho_ten']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Số điện thoại *</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="dia_chi">Địa chỉ *</label>
                    <input type="text" id="dia_chi" name="dia_chi" value="<?php echo isset($_POST['dia_chi']) ? htmlspecialchars($_POST['dia_chi']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Mật khẩu *</label>
                    <input type="password" id="password" name="password" required>
                    <small style="color: #666; font-size: 12px;">Mật khẩu phải có ít nhất 8 ký tự bao gồm: chữ hoa, chữ thường, chữ số và ký tự đặc biệt</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Xác nhận mật khẩu *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="agree_terms" required>
                    <label for="agree_terms">Tôi đồng ý với <a href="#">Điều khoản dịch vụ</a> và <a href="#">Chính sách bảo mật</a></label>
                </div>
                
                <button type="submit" class="register-btn">ĐĂNG KÝ</button>
                
                <div class="login-link">
                    <p>Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
                </div>
            </form>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>

    <script src="script.js"></script>
</body>
</html>
