<?php
session_start();
require_once 'config/database.php';

// Khởi tạo database
$db = new Database();
$conn = $db->getConnection();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header('Location: login.php');
    exit();
}

// Lấy thông tin người dùng từ database
$user_info = null;
$user_id = $_SESSION['user_id'];

if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT * FROM user WHERE ma_user = ?");
    $stmt->execute(array($user_id));
    $user_info = $stmt->fetch();
}

// Lấy dữ liệu giỏ hàng từ database
$cart_items = array();
$tong_tien = 0;

try {
    $sql = "SELECT 
                g.id_giohang,
                g.id_sanpham,
                g.so_luong,
                g.thanh_tien,
                s.ten_sanpham,
                s.hinh_anh,
                s.gia,
                s.gia_khuyen_mai
            FROM gio_hang g
            INNER JOIN san_pham s ON g.id_sanpham = s.id_sanpham
            WHERE g.ma_user = ?
            ORDER BY g.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute(array($user_id));
    $cart_items = $stmt->fetchAll();
    
    // Tính tổng tiền
    foreach ($cart_items as $item) {
        $tong_tien += $item['thanh_tien'];
    }
} catch (PDOException $e) {
    error_log("Lỗi: " . $e->getMessage());
}

// Lấy số lượng giỏ hàng
include_once 'includes/cart_count.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn đặt hàng - KLTN Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        .invoice-container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 40px;
            border: 2px solid #000;
        }
        
        .invoice-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .invoice-header h1 {
            font-size: 28px;
            margin: 0;
            font-weight: bold;
        }
        
        .company-info, .customer-info {
            margin-bottom: 30px;
        }
        
        .info-row {
            display: flex;
            margin: 8px 0;
            font-size: 14px;
        }
        
        .info-label {
            font-weight: bold;
            min-width: 150px;
            flex-shrink: 0;
        }
        
        .info-value {
            flex: 1;
        }
        
        .editable-input {
            border: none;
            border-bottom: 1px dashed #ccc;
            padding: 2px 5px;
            font-size: 14px;
            width: 100%;
            background: transparent;
        }
        
        .editable-input:focus {
            outline: none;
            border-bottom: 1px solid #088178;
            background: #f9f9f9;
        }
        
        .error-message {
            color: #e74c3c;
            font-size: 12px;
            margin-top: 3px;
            display: none;
        }
        
        .error-message.show {
            display: block;
        }
        
        .editable-input.error {
            border-bottom-color: #e74c3c;
        }
        
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        
        .invoice-table th {
            background: #f0f0f0;
            border: 1px solid #000;
            padding: 12px;
            text-align: center;
            font-weight: bold;
        }
        
        .invoice-table td {
            border: 1px solid #000;
            padding: 12px;
            text-align: center;
        }
        
        .invoice-table td:first-child {
            text-align: center;
            width: 50px;
        }
        
        .invoice-table td:nth-child(2) {
            text-align: left;
        }
        
        .total-row {
            text-align: right;
            margin-top: 20px;
            font-size: 16px;
        }
        
        .total-row strong {
            display: inline-block;
            margin-left: 20px;
        }
        
        .payment-buttons {
            margin-top: 40px;
            text-align: center;
        }
        
        .payment-btn {
            display: inline-block;
            padding: 15px 40px;
            margin: 10px;
            background: #e0e0e0;
            border: 1px solid #999;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .payment-btn:hover {
            background: #d0d0d0;
        }
        
        .back-link {
            display: inline-block;
            margin: 30px 0;
            color: #088178;
            text-decoration: none;
            font-size: 16px;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        @media print {
            .payment-buttons, .back-link, #header, footer {
                display: none;
            }
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
        </div>
    </section>

    <div class="invoice-container">
        <a href="cart.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Quay lại giỏ hàng
        </a>
        
        <div class="invoice-header">
            <h1>Hóa đơn đặt hàng</h1>
        </div>
        
        <div class="company-info">
            <div class="info-row">
                <span class="info-label">Công ty TNHH</span>
                <span class="info-value">Aki Shop</span>
            </div>
            <div class="info-row">
                <span class="info-label">Mã số thuế:</span>
                <span class="info-value">0981523130</span>
            </div>
            <div class="info-row">
                <span class="info-label">Địa chỉ:</span>
                <span class="info-value">124 Lê Quang Định, phường Bình Thạnh, TP.HỒ CHÍ MINH, VIỆT NAM</span>
            </div>
            <div class="info-row">
                <span class="info-label">Số điện thoại:</span>
                <span class="info-value">0981523130</span>
            </div>
        </div>
        
        <div style="border-bottom: 1px solid #ccc; margin: 30px 0;"></div>
        
        <div class="customer-info">
            <div class="info-row">
                <span class="info-label">Họ tên người mua:</span>
                <span class="info-value">
                    <input type="text" class="editable-input" id="customer-name" 
                           value="<?php echo $user_info ? htmlspecialchars($user_info['ho_ten']) : ''; ?>"
                           required>
                    <div class="error-message" id="name-error">Vui lòng nhập họ tên</div>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Địa chỉ:</span>
                <span class="info-value">
                    <input type="text" class="editable-input" id="customer-address" 
                           value="<?php echo $user_info ? htmlspecialchars($user_info['dia_chi']) : ''; ?>"
                           required>
                    <div class="error-message" id="address-error">Vui lòng nhập địa chỉ</div>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Số điện thoại:</span>
                <span class="info-value">
                    <input type="text" class="editable-input" id="customer-phone" 
                           value="<?php echo $user_info ? htmlspecialchars($user_info['phone']) : ''; ?>"
                           pattern="[0-9]{10}" maxlength="10" required>
                    <div class="error-message" id="phone-error">Số điện thoại phải đủ 10 số</div>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value">
                    <input type="email" class="editable-input" id="customer-email" 
                           value="<?php echo $user_info ? htmlspecialchars($user_info['email']) : ''; ?>"
                           required>
                    <div class="error-message" id="email-error">Sai định dạng mail</div>
                </span>
            </div>
        </div>
        
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Tên sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Đơn giá</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (count($cart_items) > 0):
                    foreach ($cart_items as $index => $item): 
                        $don_gia = $item['gia_khuyen_mai'] > 0 ? $item['gia_khuyen_mai'] : $item['gia'];
                ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($item['ten_sanpham']); ?></td>
                    <td><?php echo $item['so_luong']; ?></td>
                    <td><?php echo number_format($don_gia, 0, ',', '.'); ?> VNĐ</td>
                    <td><?php echo number_format($item['thanh_tien'], 0, ',', '.'); ?> VNĐ</td>
                </tr>
                <?php 
                    endforeach;
                else:
                ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">Giỏ hàng trống</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="total-row">
            <strong>Tổng tiền: <?php echo number_format($tong_tien, 0, ',', '.'); ?> VNĐ</strong>
        </div>
        
        <div class="payment-buttons">
            <button class="payment-btn" onclick="window.location.href='payment_vnpay.php'">
                Thanh toán VNPAY
            </button>
            <button class="payment-btn" onclick="confirmCOD()">
                Thanh toán khi nhận hàng
            </button>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="script.js"></script>
    <script>
        // Validation cho các trường input
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('customer-name');
            const addressInput = document.getElementById('customer-address');
            const phoneInput = document.getElementById('customer-phone');
            const emailInput = document.getElementById('customer-email');
            
            const nameError = document.getElementById('name-error');
            const addressError = document.getElementById('address-error');
            const phoneError = document.getElementById('phone-error');
            const emailError = document.getElementById('email-error');
            
            // Validate họ tên
            nameInput.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.classList.add('error');
                    nameError.textContent = 'Vui lòng nhập họ tên';
                    nameError.classList.add('show');
                } else {
                    this.classList.remove('error');
                    nameError.classList.remove('show');
                }
            });
            
            // Validate địa chỉ
            addressInput.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.classList.add('error');
                    addressError.textContent = 'Vui lòng nhập địa chỉ';
                    addressError.classList.add('show');
                } else {
                    this.classList.remove('error');
                    addressError.classList.remove('show');
                }
            });
            
            // Validate số điện thoại
            phoneInput.addEventListener('input', function() {
                // Chỉ cho phép nhập số
                this.value = this.value.replace(/[^0-9]/g, '');
            });
            
            phoneInput.addEventListener('blur', function() {
                const phoneValue = this.value.trim();
                if (phoneValue === '') {
                    this.classList.add('error');
                    phoneError.textContent = 'Vui lòng nhập số điện thoại';
                    phoneError.classList.add('show');
                } else if (phoneValue.length !== 10) {
                    this.classList.add('error');
                    phoneError.textContent = 'Số điện thoại phải đủ 10 số';
                    phoneError.classList.add('show');
                } else {
                    this.classList.remove('error');
                    phoneError.classList.remove('show');
                }
            });
            
            // Validate email
            emailInput.addEventListener('blur', function() {
                const emailValue = this.value.trim();
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (emailValue === '') {
                    this.classList.add('error');
                    emailError.textContent = 'Vui lòng nhập email';
                    emailError.classList.add('show');
                } else if (!emailPattern.test(emailValue)) {
                    this.classList.add('error');
                    emailError.textContent = 'Sai định dạng mail';
                    emailError.classList.add('show');
                } else {
                    this.classList.remove('error');
                    emailError.classList.remove('show');
                }
            });
            
            // Xóa error khi user bắt đầu nhập
            [nameInput, addressInput, phoneInput, emailInput].forEach(input => {
                input.addEventListener('focus', function() {
                    this.classList.remove('error');
                    const errorId = this.id + '-error';
                    document.getElementById(errorId).classList.remove('show');
                });
            });
        });
        
        function confirmCOD() {
            if (confirm('Xác nhận đặt hàng và thanh toán khi nhận hàng?')) {
                window.location.href = 'payment_cod.php';
            }
        }
    </script>
</body>
</html>
