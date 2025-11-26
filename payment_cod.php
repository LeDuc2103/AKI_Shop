<?php
session_start();
require_once 'config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header('Location: login.php');
    exit();
}

// Xử lý đặt hàng (sau này sẽ lưu vào database)
// Hiện tại chỉ hiển thị thông báo thành công
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt hàng thành công - KLTN Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .success-container {
            max-width: 600px;
            margin: 100px auto;
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
        }
        
        .success-container h1 {
            color: #088178;
            margin-bottom: 15px;
        }
        
        .success-container p {
            font-size: 18px;
            line-height: 1.8;
            color: #666;
            margin-bottom: 30px;
        }
        
        .order-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: left;
        }
        
        .order-info p {
            margin: 10px 0;
            font-size: 16px;
        }
        
        .btn-group {
            margin-top: 30px;
        }
        
        .btn-group a {
            display: inline-block;
            padding: 12px 30px;
            margin: 10px;
            background: #088178;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .btn-group a:hover {
            background: #066d63;
        }
        
        .btn-secondary {
            background: #6c757d !important;
        }
        
        .btn-secondary:hover {
            background: #5a6268 !important;
        }
    </style>
</head>
<body>
    <section id="header">
        <a href="index.php"><img src="img/logo1.png" width="150px" class="logo" alt=""></a>
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
                        <a href="#">Xin chào, <?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
                        <a href="logout.php">Đăng xuất</a>
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

    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1>Đặt hàng thành công!</h1>
        
        <p>Cảm ơn bạn đã đặt hàng tại KLTN Shop.<br>
        Đơn hàng của bạn đã được tiếp nhận và đang được xử lý.</p>
        
        <div class="order-info">
            <p><strong>Phương thức thanh toán:</strong> Thanh toán khi nhận hàng (COD)</p>
            <p><strong>Trạng thái:</strong> Đang xử lý</p>
            <p><strong>Thời gian giao hàng dự kiến:</strong> 3-5 ngày làm việc</p>
        </div>
        
        <p>Chúng tôi sẽ liên hệ với bạn qua email hoặc số điện thoại<br>
        để xác nhận đơn hàng trong thời gian sớm nhất.</p>
        
        <div class="btn-group">
            <a href="index.php">Về trang chủ</a>
            <a href="shop.php" class="btn-secondary">Tiếp tục mua sắm</a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="script.js"></script>
</body>
</html>
