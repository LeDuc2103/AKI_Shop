<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';

// Xử lý đăng nhập khi submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (!empty($email) && !empty($password)) {
        try {
            // Lấy kết nối database
            $conn = $db->getConnection();
            
            // 1. Truy vấn tài khoản bất kể trạng thái
            $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
            $stmt->execute(array($email));
            $user = $stmt->fetch();
            
            // Kiểm tra tài khoản
            if ($user) {
                // 2. Kiểm tra mật khẩu
                if ($user['password'] === md5($password)) {
                    
                    // 3. KIỂM TRA TRẠNG THÁI 'LOCKED'
                    if (isset($user['trang_thai']) && $user['trang_thai'] === 'locked') {
                        $error = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên để được hỗ trợ!';
                        // DỪNG xử lý, không cho đăng nhập
                        
                    } else {
                        // Tài khoản hoạt động và mật khẩu đúng - Bắt đầu đăng nhập
                        
                        // Đăng nhập thành công - Lưu session chung
                        $_SESSION['user_logged_in'] = true;
                        $_SESSION['user_id'] = $user['ma_user'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_name'] = $user['ho_ten'];
                        $_SESSION['user_role'] = $user['vai_tro'];
                        
                        // Lấy vai trò và chuyển về chữ thường để so sánh
                        $vai_tro = strtolower(trim($user['vai_tro']));
                        
                        // Lưu session cho từng vai trò để truy cập trang quản trị
                        if ($vai_tro === 'quanly') {
                            $_SESSION['admin_logged_in'] = true;
                            $_SESSION['admin_id'] = $user['ma_user'];
                            $_SESSION['admin_email'] = $user['email'];
                            $_SESSION['admin_name'] = $user['ho_ten'];
                        } elseif ($vai_tro === 'nhanvienkho') {
                            $_SESSION['nhanvienkho_logged_in'] = true; 
                            $_SESSION['nhanvienkho_id'] = $user['ma_user'];
                            $_SESSION['nhanvienkho_email'] = $user['email'];
                            $_SESSION['nhanvienkho_name'] = $user['ho_ten'];
                        } elseif ($vai_tro === 'nhanvien') {
                            $_SESSION['nhanvien_logged_in'] = true;
                            $_SESSION['nhanvien_id'] = $user['ma_user'];
                            $_SESSION['nhanvien_email'] = $user['email'];
                            $_SESSION['nhanvien_name'] = $user['ho_ten'];
                        }
                        
                        // TẤT CẢ VAI TRÒ đều chuyển về index.php
                        header('Location: index.php');
                        exit();
                    } 
                } else {
                    $error = 'Email hoặc mật khẩu không đúng!';
                }
            } else {
                $error = 'Email hoặc mật khẩu không đúng!';
            }
        } catch (Exception $e) {
            $error = 'Có lỗi xảy ra: ' . $e->getMessage();
        }
    } else {
        $error = 'Vui lòng điền đầy đủ thông tin!';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <title>Đăng Nhập - KLTN Shop</title>
    <!-- Giả định file style.css tồn tại và chứa các style chung -->
    <link rel="stylesheet" href="style.css"> 
    <style>
        /* CSS cho trang Đăng Nhập */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .login-container {
            max-width: 500px;
            margin: 80px auto;
            padding: 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .login-container h2 {
            text-align: center;
            color: #088178;
            margin-bottom: 30px;
            font-size: 28px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #088178;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            background: #088178;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-login:hover {
            background: #0a6e65;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        .register-link a {
            color: #088178;
            text-decoration: none;
            font-weight: 600;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        .info-box {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid #bee5eb;
        }
        .debug-link {
            text-align: center;
            margin-top: 15px;
        }
        .debug-link a {
            color: #6c757d;
            font-size: 13px;
            text-decoration: none;
            margin: 0 5px;
        }
        .debug-link a:hover {
            text-decoration: underline;
        }
        /* Style cho Header (Giữ lại cấu trúc cũ nếu đây là 1 phần của hệ thống) */
        #header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 80px;
            background: #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.06);
            z-index: 999;
            position: sticky;
            top: 0;
            left: 0;
        }
        #navbar {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #navbar li {
            list-style: none;
            padding: 0 20px;
            position: relative;
        }
        #navbar li a {
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            color: #1a1a1a;
            transition: 0.3s ease;
        }
        #navbar li a:hover,
        #navbar li a.active {
            color: #088178;
        }
        .user-dropdown {
            display: none;
            position: absolute;
            top: 35px;
            right: 0;
            background: #fff;
            min-width: 150px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            z-index: 1000;
            border-radius: 8px;
            overflow: hidden;
            padding: 10px 0;
        }
        #user-icon:focus .user-dropdown,
        #user-icon:hover .user-dropdown {
            display: block;
        }
        .user-dropdown a {
            padding: 10px 15px;
            display: block;
            color: #333;
            font-weight: 500;
            text-align: left;
        }
        .user-dropdown a:hover {
            background: #f4f4f4;
            color: #088177;
        }
        #mobile {
            display: none;
            align-items: center;
        }
        #close {
            display: none;
        }
        /* Responsive adjustments for mobile */
        @media (max-width: 799px) {
            #header {
                padding: 10px 30px;
            }
            #navbar {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                justify-content: flex-start;
                position: fixed;
                top: 0;
                right: -300px;
                height: 100vh;
                width: 300px;
                background-color: #E3E6F3;
                box-shadow: 0 40px 60px rgba(0, 0, 0, 0.1);
                padding: 80px 0 0 10px;
                transition: 0.3s;
            }
            #navbar.active {
                right: 0px;
            }
            #navbar li {
                margin-bottom: 25px;
            }
            #mobile {
                display: flex;
            }
            #mobile i {
                color: #1a1a1a;
                font-size: 24px;
                padding-left: 20px;
            }
            #close {
                display: initial;
                position: absolute;
                top: 30px;
                left: 30px;
                color: #222;
                font-size: 24px;
            }
            #lg-bag {
                display: none;
            }
            .login-container {
                margin: 40px 20px;
                padding: 25px;
            }
        }
    </style>
</head>
<body>
    <section id="header">
        <a href="index.php"><img src="img/logo1.png" width="150px" class="logo" alt="KLTN Shop Logo"></a>
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
                        <a href="login.php" class="active">Đăng Nhập</a>
                        <a href="register.php">Đăng Ký</a>
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

    <div class="login-container">
        <h2><i class="fas fa-sign-in-alt"></i> Đăng Nhập</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="email" name="email" placeholder="Nhập email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Mật khẩu</label>
                <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
            </div>
            
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Đăng nhập
            </button>
        </form>
        
        <div class="register-link">
            Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
        </div>
        
        <div class="debug-link">
            <a href="view_nhanvien.php" target="_blank"><i class="fas fa-database"></i> Xem dữ liệu bảng user</a> |
            <a href="fix_admin_role.php" target="_blank"><i class="fas fa-wrench"></i> Sửa vai trò admin</a>
        </div>
    </div>

    <!-- Giả định file includes/footer.php tồn tại -->
    <?php // include 'includes/footer.php'; ?>

    <!-- Giả định file script.js tồn tại -->
    <script>
        // Logic JS đơn giản cho header mobile
        const bar = document.getElementById('bar');
        const nav = document.getElementById('navbar');
        const close = document.getElementById('close');

        if (bar) {
            bar.addEventListener('click', () => {
                nav.classList.add('active');
            })
        }

        if (close) {
            close.addEventListener('click', () => {
                nav.classList.remove('active');
            })
        }
    </script>
</body>
</html>