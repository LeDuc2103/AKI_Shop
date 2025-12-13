<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';

// Xử lý form đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($email) || empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Vui lòng điền đầy đủ thông tin!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ!';
    } elseif (strlen($new_password) < 6) {
        $error = 'Mật khẩu mới phải có ít nhất 6 ký tự!';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp!';
    } else {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Kiểm tra email và mật khẩu cũ
            $stmt = $conn->prepare("SELECT ma_user, ho_ten, password FROM user WHERE email = ?");
            $stmt->execute(array($email));
            $user = $stmt->fetch();
            
            if (!$user) {
                $error = 'Email không tồn tại trong hệ thống!';
            } elseif (md5($old_password) !== $user['password']) {
                $error = 'Mật khẩu cũ không đúng!';
            } else {
                // Cập nhật mật khẩu mới
                $hashed_password = md5($new_password);
                
                $stmt = $conn->prepare("UPDATE user SET password = ? WHERE email = ?");
                $stmt->execute(array($hashed_password, $email));
                
                // Chuyển về login với thông báo
                $_SESSION['reset_success'] = 'Đổi mật khẩu thành công! Vui lòng đăng nhập bằng mật khẩu mới.';
                header('Location: login.php');
                exit();
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
    <title>Đổi Mật Khẩu - KLTN Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
            animation: slideIn 0.5s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header i {
            font-size: 60px;
            color: #088178;
            margin-bottom: 15px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .header h2 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .header p {
            color: #666;
            font-size: 14px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            animation: fadeIn 0.3s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .info-text {
            background: #e7f3ff;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #004085;
            border-left: 4px solid #088178;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        .password-wrapper {
            position: relative;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        
        .password-wrapper input {
            padding-right: 45px;
        }
        
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            font-size: 18px;
            transition: color 0.3s;
        }
        
        .toggle-password:hover {
            color: #088178;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #088178;
            box-shadow: 0 0 10px rgba(8, 129, 120, 0.2);
        }
        
        .password-strength {
            font-size: 12px;
            margin-top: 5px;
            height: 20px;
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            background: #088178;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: #066d63;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(8, 129, 120, 0.4);
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #088178;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }
        
        .back-link a:hover {
            color: #066d63;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <i class="fas fa-key"></i>
            <h2>Đổi Mật Khẩu</h2>
            <p>Nhập email và mật khẩu mới để đổi mật khẩu</p>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="info-text">
            <i class="fas fa-info-circle"></i> Nhập email của bạn và mật khẩu cũ để xác nhận, sau đó nhập mật khẩu mới để đổi mật khẩu.
        </div>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="email" name="email" placeholder="Nhập email của bạn" required autofocus value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="old_password"><i class="fas fa-lock"></i> Mật khẩu cũ</label>
                <div class="password-wrapper">
                    <input type="password" id="old_password" name="old_password" placeholder="Nhập mật khẩu cũ" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePassword('old_password', this)"></i>
                </div>
            </div>
            
            <div class="form-group">
                <label for="new_password"><i class="fas fa-lock"></i> Mật khẩu mới</label>
                <div class="password-wrapper">
                    <input type="password" id="new_password" name="new_password" placeholder="Nhập mật khẩu mới (tối thiểu 6 ký tự)" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePassword('new_password', this)"></i>
                </div>
                <div class="password-strength" id="password-strength"></div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password"><i class="fas fa-lock"></i> Xác nhận mật khẩu</label>
                <div class="password-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu mới" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePassword('confirm_password', this)"></i>
                </div>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-check-circle"></i> Đổi Mật Khẩu
            </button>
        </form>
        
        <div class="back-link">
            <a href="login.php"><i class="fas fa-arrow-left"></i> Quay lại đăng nhập</a>
        </div>
    </div>
    
    <script>
        // Toggle password visibility
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Password strength indicator
        var passwordInput = document.getElementById('new_password');
        var confirmInput = document.getElementById('confirm_password');
        var strengthDiv = document.getElementById('password-strength');
        
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                var password = this.value;
                var strength = '';
                var color = '';
                
                if (password.length === 0) {
                    strength = '';
                } else if (password.length < 6) {
                    strength = '⚠️ Yếu - Cần ít nhất 6 ký tự';
                    color = '#dc3545';
                } else if (password.length < 8) {
                    strength = '✓ Trung bình';
                    color = '#ffc107';
                } else if (password.length >= 8 && /[A-Z]/.test(password) && /[0-9]/.test(password)) {
                    strength = '✓✓ Mạnh';
                    color = '#28a745';
                } else {
                    strength = '✓ Khá';
                    color = '#17a2b8';
                }
                
                strengthDiv.textContent = strength;
                strengthDiv.style.color = color;
            });
        }
        
        // Check password match
        if (confirmInput) {
            confirmInput.addEventListener('input', function() {
                var password = passwordInput.value;
                var confirm = this.value;
                
                if (confirm.length > 0) {
                    if (password === confirm) {
                        this.style.borderColor = '#28a745';
                    } else {
                        this.style.borderColor = '#dc3545';
                    }
                } else {
                    this.style.borderColor = '#ddd';
                }
            });
        }
    </script>
</body>
</html>
