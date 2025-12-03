<?php
session_start();
require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();

// Lấy số lượng giỏ hàng
include_once 'includes/cart_count.php';

// Xử lý form gửi tin nhắn
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $ho_va_ten = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $so_dien_thoai = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $noi_dung = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    // Validation
    $errors = array();
    
    // Kiểm tra họ và tên
    if (empty($ho_va_ten)) {
        $errors[] = 'Vui lòng nhập họ và tên';
    } elseif (strlen($ho_va_ten) < 3) {
        $errors[] = 'Họ và tên phải có ít nhất 3 ký tự';
    }
    
    // Kiểm tra email
    if (empty($email)) {
        $errors[] = 'Vui lòng nhập email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không đúng định dạng';
    }
    
    // Kiểm tra số điện thoại
    if (empty($so_dien_thoai)) {
        $errors[] = 'Vui lòng nhập số điện thoại';
    } elseif (!preg_match('/^(0|\+84)[0-9]{9,10}$/', $so_dien_thoai)) {
        $errors[] = 'Số điện thoại không đúng định dạng (VD: 0912345678 hoặc +84912345678)';
    }
    
    // Kiểm tra nội dung
    if (empty($noi_dung)) {
        $errors[] = 'Vui lòng nhập nội dung tin nhắn';
    } elseif (strlen($noi_dung) < 10) {
        $errors[] = 'Nội dung tin nhắn phải có ít nhất 10 ký tự';
    }
    
    // Nếu không có lỗi, lưu vào database
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO hotro (ho_va_ten, email, so_dien_thoai, noi_dung, ngay_gui, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
            $ngay_gui = date('Y-m-d');
            $result = $stmt->execute(array($ho_va_ten, $email, $so_dien_thoai, $noi_dung, $ngay_gui));
            
            if ($result) {
                $success_message = 'Cảm ơn bạn đã gửi tin nhắn! Chúng tôi sẽ phản hồi trong thời gian sớm nhất.';
                // Reset form
                $ho_va_ten = $email = $so_dien_thoai = $noi_dung = '';
            } else {
                $error_message = 'Có lỗi xảy ra khi gửi tin nhắn. Vui lòng thử lại!';
            }
        } catch (PDOException $e) {
            $error_message = 'Lỗi database: ' . $e->getMessage();
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <title>AKISTORE - Liên hệ</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        /* Contact Page Styles */
        .contact-hero {
            background-image: url('img/banner/abbner.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            color: white;
            text-align: center;
            padding: 60px 20px;
            margin-bottom: 60px;
            position: relative;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .contact-hero h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0;
            position: relative;
            z-index: 2;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .contact-hero p {
            display: none;
        }
        
        .contact-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 60px;
        }
        
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 60px;
        }
        
        .contact-info-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        }
        
        .contact-info-card h2 {
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        
        .contact-info-card h3 {
            color: #667eea;
            font-size: 1.3rem;
            margin: 25px 0 15px;
        }
        
        .contact-info-card span {
            color: #667eea;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        
        .contact-list {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }
        
        .contact-list li {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            transition: transform 0.3s ease;
        }
        
        .contact-list li:hover {
            transform: translateX(5px);
            background: #e9ecef;
        }
        
        .contact-list i {
            color: #667eea;
            font-size: 1.3rem;
            margin-right: 15px;
            margin-top: 3px;
            min-width: 25px;
        }
        
        .contact-list p {
            margin: 0;
            color: #555;
            line-height: 1.6;
        }
        
        .map-container {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            height: 100%;
            min-height: 400px;
        }
        
        .map-container iframe {
            width: 100%;
            height: 100%;
            min-height: 400px;
            border: none;
        }
        
        .contact-form-section {
            background: white;
            padding: 50px;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            margin-bottom: 60px;
        }
        
        .contact-form-section h2 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .contact-form-section span {
            color: #667eea;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        
        .contact-form {
            margin-top: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            font-family: inherit;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 150px;
        }
        
        .submit-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .team-section {
            margin-bottom: 60px;
        }
        
        .team-section h2 {
            text-align: center;
            color: #333;
            font-size: 2rem;
            margin-bottom: 40px;
        }
        
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }
        
        .team-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .team-card:hover {
            transform: translateY(-10px);
        }
        
        .team-card img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
            border: 4px solid #667eea;
        }
        
        .team-card h3 {
            color: #333;
            font-size: 1.3rem;
            margin-bottom: 5px;
        }
        
        .team-card .role {
            color: #667eea;
            font-size: 0.95rem;
            margin-bottom: 15px;
            font-weight: 500;
        }
        
        .team-card .contact-detail {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.8;
            margin: 5px 0;
        }
        
        .team-card .contact-detail i {
            color: #667eea;
            margin-right: 8px;
            width: 20px;
        }
        
        @media (max-width: 968px) {
            .contact-grid {
                grid-template-columns: 1fr;
            }
            
            .contact-form-section {
                padding: 30px 20px;
            }
            
            .contact-hero h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <section id="header">
        <a href="index.php"><img src="img/logo1.png" width="150px" class="logo" alt="KLTN Logo"></a>
        <div>
            <ul id="navbar">
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="shop.php">Sản phẩm</a></li>
                <li><a href="blog.php">Tin tức</a></li>
                <li><a href="about.php">Về chúng tôi</a></li>
                <li><a class="active" href="contact.php">Liên hệ</a></li>
                <li id="search-icon"><a href="#" onclick="toggleSearch(event)"><i class="fa-solid fa-search"></i></a></li>
                <li id="user-icon" tabindex="0">
                    <a href="#" tabindex="-1"><i class="fa-solid fa-user"></i></a>
                    <div class="user-dropdown">
                        <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                            <a href="#">Xin chào, <?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
                            <a href="my_orders.php">Đơn hàng của tôi</a>
                            <?php 
                            $user_role = isset($_SESSION['user_role']) ? strtolower(trim($_SESSION['user_role'])) : '';
                            if ($user_role === 'quanly'): 
                            ?>
                                <a href="admin.php">Quản trị viên</a>
                            <?php elseif ($user_role === 'nhanvien'): ?>
                                <a href="nhanvienbanhang.php">Quản trị viên</a>
                            <?php elseif ($user_role === 'nhanvienkho'): ?>
                                <a href="nhanvienkho.php">Quản trị viên</a>
                            <?php endif; ?>
                            <a href="logout.php">Đăng xuất</a>
                        <?php else: ?>
                            <a href="login.php">Đăng Nhập</a>
                            <a href="register.php">Đăng Ký</a>
                        <?php endif; ?>
                    </div>
                </li>
                <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                <?php endif; ?>
                <li id="lg-bag">
                    <a href="cart.php" style="position: relative;">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-badge"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <a href="#" id="close"><i class="fa-solid fa-xmark"></i></a>    
            </ul> 
        <div id="mobile">
            <a href="cart.php" style="position: relative;">
                <i class="fa-solid fa-cart-shopping"></i>
                <?php if ($cart_count > 0): ?>
                    <span class="cart-badge"><?php echo $cart_count; ?></span>
                <?php endif; ?>
            </a>
            <i id="bar" class="fas fa-outdent"></i>
        </div>  
        </section>

    <!-- Hero Section -->
    <div class="contact-hero">
        
    </div>

    <!-- Main Content -->
    <div class="contact-container">
        
        <!-- Intro Text -->
        <div style="text-align: center; margin-bottom: 40px;">
            <h2><i class="fas fa-envelope"></i> Liên Hệ Với Chúng Tôi</h2>
            <p style="font-size: 1.2rem; color: #555; max-width: 800px; margin: 0 auto;">Chúng tôi luôn sẵn sàng hỗ trợ và giải đáp mọi thắc mắc của bạn</p>
        </div>
        
        <!-- Contact Info & Map Grid -->
        <div class="contact-grid">
            <!-- Contact Information -->
            <div class="contact-info-card">
                <span>LIÊN HỆ VỚI CHÚNG TÔI</span>
                <h2>Thông Tin Liên Hệ</h2>
                <h3>Trụ Sở Chính</h3>
                
                <ul class="contact-list">
                    <li>
                        <i class="fas fa-map-marker-alt"></i>
                        <p><strong>Địa chỉ:</strong><br>124 Lê Quang Định, phường Bình Thạnh, TP.HCM, Việt Nam</p>
                    </li>
                    <li>
                        <i class="fas fa-envelope"></i>
                        <p><strong>Email:</strong><br>leduc2103@gmail.com</p>
                    </li>
                    <li>
                        <i class="fas fa-phone-alt"></i>
                        <p><strong>Hotline:</strong><br>098152222</p>
                    </li>
                    <li>
                        <i class="fas fa-clock"></i>
                        <p><strong>Giờ làm việc:</strong><br>Thứ 2 - Thứ 7 làm việc từ 8:00 - 17:00<br>Chủ nhật: Nghỉ</p>
                    </li>
                </ul>
            </div>

            <!-- Google Map -->
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3918.4860834737444!2d106.68359831533332!3d10.838009260872822!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317529b6a2b2b2b3%3A0x123456789!2s12%20Nguy%E1%BB%85n%20V%C4%83n%20B%E1%BA%A3o%2C%20Ph%C6%B0%E1%BB%9Dng%2013%2C%20G%C3%B2%20V%E1%BA%A5p%2C%20Th%C3%A0nh%20ph%E1%BB%91%20H%E1%BB%93%20Ch%C3%AD%20Minh!5e0!3m2!1svi!2s!4v1609765766860!5m2!1svi!2s" 
                        allowfullscreen="" 
                        loading="lazy">
                </iframe>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="contact-form-section">
            <span><h2 style="color:brown">GỬI TIN NHẮN</h2></span>
            <h2>Chúng Tôi Rất Mong Nhận Được Ý Kiến Từ Bạn</h2>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin: 20px 0; border: 1px solid #c3e6cb;">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 20px 0; border: 1px solid #f5c6cb;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <form class="contact-form" action="" method="POST" id="contactForm">
                <div class="form-group">
                    <label for="name"><i class="fas fa-user"></i> Họ và tên <span style="color: red;">*</span></label>
                    <input type="text" id="name" name="name" placeholder="Nhập họ và tên của bạn" required 
                           value="<?php echo isset($ho_va_ten) ? htmlspecialchars($ho_va_ten) : ''; ?>"
                           minlength="3" maxlength="255">
                    <small style="color: #666; font-size: 0.85rem;">Họ và tên phải có ít nhất 3 ký tự</small>
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email <span style="color: red;">*</span></label>
                    <input type="email" id="email" name="email" placeholder="example@email.com" required 
                           value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                           pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                    <small style="color: #666; font-size: 0.85rem;">Ví dụ: example@email.com</small>
                </div>
                
                <div class="form-group">
                    <label for="phone"><i class="fas fa-phone"></i> Số điện thoại <span style="color: red;">*</span></label>
                    <input type="tel" id="phone" name="phone" placeholder="0912345678" required 
                           value="<?php echo isset($so_dien_thoai) ? htmlspecialchars($so_dien_thoai) : ''; ?>"
                           pattern="^(0|\+84)[0-9]{9,10}$">
                    <small style="color: #666; font-size: 0.85rem;">Ví dụ: 0912345678 hoặc +84912345678</small>
                </div>
                
                <div class="form-group">
                    <label for="message"><i class="fas fa-comment-dots"></i> Nội dung <span style="color: red;">*</span></label>
                    <textarea id="message" name="message" placeholder="Nhập nội dung tin nhắn của bạn..." required 
                              minlength="10" maxlength="1000"><?php echo isset($noi_dung) ? htmlspecialchars($noi_dung) : ''; ?></textarea>
                    <small style="color: #666; font-size: 0.85rem;">Nội dung phải có ít nhất 10 ký tự</small>
                </div>
                
                <button type="submit" name="send_message" class="submit-btn">
                    <i class="fas fa-paper-plane"></i> Gửi Tin Nhắn
                </button>
            </form>
        </div>

        <!-- Team Section -->
        <div class="team-section">
            <h2><i class="fas fa-users"></i> Đội Ngũ Hỗ Trợ Khách Hàng</h2>
            
            <div class="team-grid">
                <div class="team-card">
                    <img src="img/people/1.png" alt="Lê Văn Túc">
                    <h3>Lê Văn Túc</h3>
                    <p class="role">Senior Marketing Manager</p>
                    <p class="contact-detail"><i class="fas fa-phone"></i> +84 123 456 789</p>
                    <p class="contact-detail"><i class="fas fa-envelope"></i> tuc@akistore.com</p>
                </div>
                
                <div class="team-card">
                    <img src="img/people/2.png" alt="Huỳnh Đình Chiểu">
                    <h3>Huỳnh Đình Chiểu</h3>
                    <p class="role">Customer Service Manager</p>
                    <p class="contact-detail"><i class="fas fa-phone"></i> +84 987 654 321</p>
                    <p class="contact-detail"><i class="fas fa-envelope"></i> chieu@akistore.com</p>
                </div>
                
                <div class="team-card">
                    <img src="img/people/3.png" alt="Nguyễn Văn A">
                    <h3>Nguyễn Văn A</h3>
                    <p class="role">Technical Support Manager</p>
                    <p class="contact-detail"><i class="fas fa-phone"></i> +84 555 666 777</p>
                    <p class="contact-detail"><i class="fas fa-envelope"></i> a@akistore.com</p>
                </div>
            </div>
        </div>

    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Search Box -->
    <div class="search-box" id="search-box">
        <div class="search-container">
            <form action="shop.php" method="GET" id="search-form">
                <div class="search-input-wrapper">
                    <input type="text" id="search-input" name="search" placeholder="Tìm kiếm sản phẩm..." autocomplete="off">
                    <button type="submit" class="search-btn">
                        <i class="fa-solid fa-search"></i>
                    </button>
                    <div class="search-suggestions" id="search-suggestions"></div>
                </div>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        // Validation form liên hệ
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            let isValid = true;
            let errorMessages = [];
            
            // Kiểm tra họ và tên
            const name = document.getElementById('name').value.trim();
            if (name.length < 3) {
                isValid = false;
                errorMessages.push('Họ và tên phải có ít nhất 3 ký tự');
            }
            
            // Kiểm tra email
            const email = document.getElementById('email').value.trim();
            const emailPattern = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i;
            if (!emailPattern.test(email)) {
                isValid = false;
                errorMessages.push('Email không đúng định dạng');
            }
            
            // Kiểm tra số điện thoại
            const phone = document.getElementById('phone').value.trim();
            const phonePattern = /^(0|\+84)[0-9]{9,10}$/;
            if (!phonePattern.test(phone)) {
                isValid = false;
                errorMessages.push('Số điện thoại không đúng định dạng (VD: 0912345678 hoặc +84912345678)');
            }
            
            // Kiểm tra nội dung
            const message = document.getElementById('message').value.trim();
            if (message.length < 10) {
                isValid = false;
                errorMessages.push('Nội dung tin nhắn phải có ít nhất 10 ký tự');
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Vui lòng kiểm tra lại thông tin:\n\n' + errorMessages.join('\n'));
            }
        });
        
        // Real-time validation cho số điện thoại
        document.getElementById('phone').addEventListener('input', function(e) {
            const value = e.target.value;
            const phonePattern = /^(0|\+84)[0-9]{0,10}$/;
            
            if (value && !phonePattern.test(value)) {
                e.target.setCustomValidity('Số điện thoại phải bắt đầu bằng 0 hoặc +84 và có 10-11 số');
            } else {
                e.target.setCustomValidity('');
            }
        });
        
        // Real-time validation cho email
        document.getElementById('email').addEventListener('input', function(e) {
            const value = e.target.value;
            const emailPattern = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i;
            
            if (value && !emailPattern.test(value)) {
                e.target.setCustomValidity('Email không đúng định dạng');
            } else {
                e.target.setCustomValidity('');
            }
        });
    </script>
    
    <!-- Scroll to Top Button -->
    <button id="scrollToTop" title="Trở về đầu trang">
        <i class="fas fa-arrow-up"></i>
    </button>
    
    <script src="https://cdn.botpress.cloud/webchat/v3.3/inject.js" defer></script>
    <script src="https://files.bpcontent.cloud/2025/11/26/16/20251126163853-AFN0KSEV.js" defer></script>
</body>
</html>