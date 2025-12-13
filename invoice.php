<?php
session_start();
require_once __DIR__ . '/config/database.php';

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
$shipping_fee = 0;
$discount_percent = 0;
$discount_amount = 0;
$promo_code = '';
$final_total = 0;

// Lấy thông tin từ session nếu có
if (isset($_SESSION['cart_summary'])) {
    $cart_summary = $_SESSION['cart_summary'];
    $tong_tien = $cart_summary['subtotal'];
    $shipping_fee = $cart_summary['shipping_fee'];
    $discount_percent = $cart_summary['discount_percent'];
    $discount_amount = $cart_summary['discount_amount'];
    $promo_code = $cart_summary['promo_code'];
    $final_total = $cart_summary['total'];
}

try {
    $sql = "SELECT 
                g.id_giohang,
                g.id_sanpham,
                g.so_luong,
                g.thanh_tien,
                s.ten_sanpham,
                s.hinh_anh,
                s.gia,
                s.gia_khuyen_mai,
                s.mau_sac
            FROM gio_hang g
            INNER JOIN san_pham s ON g.id_sanpham = s.id_sanpham
            WHERE g.ma_user = ?
            ORDER BY g.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute(array($user_id));
    $cart_items = $stmt->fetchAll();
    
    // Nếu chưa có thông tin từ session, tính toán lại
    if (!isset($_SESSION['cart_summary'])) {
        foreach ($cart_items as $item) {
            $tong_tien += $item['thanh_tien'];
        }
        
        // Lấy phí ship mặc định
        $stmt_ship = $conn->prepare("SELECT tien_ship FROM don_hang WHERE ma_user = ? ORDER BY ma_donhang DESC LIMIT 1");
        $stmt_ship->execute(array($user_id));
        $ship_info = $stmt_ship->fetch();
        $shipping_fee = $ship_info ? $ship_info['tien_ship'] : 30000;
        
        $final_total = $tong_tien + $shipping_fee;
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
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>
    <link rel="stylesheet" href="css/responsive.css?v=1765636813">">
    <style>
        body {
            font-size: 16px;
            color: #000;
        }
        
        .invoice-container {
            max-width: 1200px;
            margin: 50px auto;
            background: white;
            padding: 40px;
            border: 2px solid #000;
            font-size: 16px;
            color: #000;
        }
        
        .invoice-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .invoice-header h1 {
            font-size: 32px;
            margin: 0;
            font-weight: bold;
            color: #000;
        }
        
        .company-info, .customer-info {
            margin-bottom: 30px;
        }
        
        .company-info h3, .customer-info h3 {
            font-size: 20px;
            font-weight: bold;
            color: #000;
            margin-bottom: 15px;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .info-table td {
            padding: 10px;
            border: 1px solid #ddd;
            font-size: 16px;
            color: #000;
        }
        
        .info-table td:first-child {
            font-weight: bold;
            width: 200px;
            background: #f8f9fa;
        }
        
        .editable-input {
            border: none;
            border-bottom: 1px dashed #ccc;
            padding: 5px;
            font-size: 16px;
            width: 100%;
            background: transparent;
            color: #000;
        }
        
        .editable-input:focus {
            outline: none;
            border-bottom: 1px solid #088178;
            background: #f9f9f9;
        }
        
        .error-message {
            color: #e74c3c;
            font-size: 14px;
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
            font-size: 16px;
            color: #000;
        }
        
        .invoice-table td {
            border: 1px solid #000;
            padding: 12px;
            text-align: center;
            font-size: 16px;
            color: #000;
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
            margin-top: 30px;
            font-size: 16px;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
        }
        
        .total-row strong {
            display: inline-block;
            margin-left: 20px;
            font-weight: bold;
            color: #000;
        }
        
        .payment-buttons {
            margin-top: 30px;
            margin-bottom: 20px;
            text-align: center;
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            padding: 20px;
            background: #fff;
            border-top: 2px solid #e0e0e0;
        }
        
        .payment-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 15px 30px;
            background: #088178;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .payment-btn:hover {
            background: #066d63;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .payment-btn.sepay-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .payment-btn.sepay-btn:hover {
            background: linear-gradient(135deg, #5568d3 0%, #6a3f91 100%);
        }
        
        .payment-btn i {
            font-size: 18px;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 20px 0 30px 0;
            padding: 12px 24px;
            background: #f8f9fa;
            color: #088178;
            text-decoration: none;
            font-size: 19px;
            font-weight: bold;
            border: 2px solid #088178;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .back-link:hover {
            background: #088178;
            color: white;
            text-decoration: none;
            transform: translateX(-5px);
        }
        
        .back-link i {
            font-size: 16px;
            transition: transform 0.3s;
        }
        
        .back-link:hover i {
            transform: translateX(-3px);
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
        <a href="index.php"><img src="img/logo7.png" width="150px" class="logo" alt=""></a>
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
                        <a href="my_orders.php">Đơn hàng của tôi</a>
                        <a href="admin.php">Quản trị viên</a>
                        <a href="logout.php">Đăng xuất</a>
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
        </div>
    </section>

    <div class="invoice-container">
        <a href="cart.php" class="back-link">
            <i class="fas fa-arrow-left"></i>Quay lại giỏ hàng
        </a>
        
        <div class="invoice-header">
            <h1>Hóa đơn đặt hàng</h1>
        </div>
        
        <div class="company-info">
            <h3>Thông tin công ty</h3>
            <table class="info-table">
                <tr>
                    <td>Công ty TNHH</td>
                    <td>Aki-Store</td>
                </tr>
                <tr>
                    <td>Mã số thuế</td>
                    <td>0981523130</td>
                </tr>
                <tr>
                    <td>Địa chỉ</td>
                    <td>124 Lê Quang Định, phường Bình Thạnh, TP.HỒ CHÍ MINH, VIỆT NAM</td>
                </tr>
                <tr>
                    <td>Số điện thoại</td>
                    <td>0981523130</td>
                </tr>
            </table>
        </div>
        
        <div style="border-bottom: 1px solid #ccc; margin: 30px 0;"></div>
        
        <div class="customer-info">
            <h3>Thông tin người mua</h3>
            <table class="info-table">
                <tr>
                    <td>Họ tên người mua</td>
                    <td>
                        <input type="text" class="editable-input" id="customer-name" 
                               value="<?php echo $user_info ? htmlspecialchars($user_info['ho_ten']) : ''; ?>"
                               required>
                        <div class="error-message" id="name-error">Vui lòng nhập họ tên</div>
                    </td>
                </tr>
                <tr>
                    <td>Địa chỉ</td>
                    <td>
                        <input type="text" class="editable-input" id="customer-address" 
                               value="<?php echo $user_info ? htmlspecialchars($user_info['dia_chi']) : ''; ?>"
                               required>
                        <div class="error-message" id="address-error">Vui lòng nhập địa chỉ</div>
                    </td>
                </tr>
                <tr>
                    <td>Số điện thoại</td>
                    <td>
                        <input type="text" class="editable-input" id="customer-phone" 
                               value="<?php echo $user_info ? htmlspecialchars($user_info['phone']) : ''; ?>"
                               pattern="[0-9]{10}" maxlength="10" required>
                        <div class="error-message" id="phone-error">Số điện thoại phải đủ 10 số</div>
                    </td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>
                        <input type="email" class="editable-input" id="customer-email" 
                               value="<?php echo $user_info ? htmlspecialchars($user_info['email']) : ''; ?>"
                               required>
                        <div class="error-message" id="email-error">Vui lòng nhập email hợp lệ</div>
                    </td>
                </tr>
            </table>
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
            <h3 style="text-align: left; margin-bottom: 15px; font-size: 18px;">Tổng thanh toán</h3>
            <table style="width: 100%; margin-bottom: 10px;">
                <tr>
                    <td style="text-align: right; padding: 5px; border: none;"><strong>Tạm tính:</strong></td>
                    <td style="text-align: right; padding: 5px; border: none; width: 200px;"><?php echo number_format($tong_tien, 0, ',', '.'); ?> VNĐ</td>
                </tr>
                <?php if ($discount_percent > 0): ?>
                <tr>
                    <td style="text-align: right; padding: 5px; border: none;"><strong>Giảm giá (<?php echo $discount_percent; ?>%):</strong></td>
                    <td style="text-align: right; padding: 5px; border: none; color: red;">-<?php echo number_format($discount_amount, 0, ',', '.'); ?> VNĐ</td>
                </tr>
                <tr style="background: #f0f0f0;">
                    <td style="text-align: right; padding: 8px 5px; border: none;"><strong>Tổng tạm tính:</strong></td>
                    <td style="text-align: right; padding: 8px 5px; border: none;"><strong><?php echo number_format($tong_tien - $discount_amount, 0, ',', '.'); ?> VNĐ</strong></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td style="text-align: right; padding: 5px; border: none;"><strong>Phí vận chuyển:</strong></td>
                    <td style="text-align: right; padding: 5px; border: none;"><?php echo number_format($shipping_fee, 0, ',', '.'); ?> VNĐ</td>
                </tr>
                <tr style="border-top: 2px solid #000; background: #f8f8f8;">
                    <td style="text-align: right; padding: 12px 5px; border: none;"><strong style="font-size: 20px;">Tổng cộng:</strong></td>
                    <td style="text-align: right; padding: 12px 5px; border: none;"><strong style="font-size: 20px; color: #088178;"><?php echo number_format($final_total, 0, ',', '.'); ?> VNĐ</strong></td>
                </tr>
            </table>
        </div>
        
        <div class="payment-buttons">
            <button class="payment-btn" onclick="window.location.href='payment_vnpay.php'">
                <i class="fab fa-cc-visa"></i> Thanh toán VNPAY
            </button>
            <button class="payment-btn sepay-btn" onclick="submitSepayPayment()">
                <i class="fas fa-qrcode"></i> Thanh toán QR Code
            </button>
            <button class="payment-btn" onclick="confirmCOD()">
                <i class="fas fa-money-bill-wave"></i> Thanh toán khi nhận hàng
            </button>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="script.js"></script>
    <script src="js/mobile-responsive.js?v=1765636813"></script>
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
        
        function submitSepayPayment() {
            // Validate tất cả các trường
            const nameInput = document.getElementById('customer-name');
            const addressInput = document.getElementById('customer-address');
            const phoneInput = document.getElementById('customer-phone');
            const emailInput = document.getElementById('customer-email');
            
            let isValid = true;
            
            // Validate họ tên
            if (nameInput.value.trim() === '') {
                nameInput.classList.add('error');
                document.getElementById('name-error').classList.add('show');
                isValid = false;
            }
            
            // Validate địa chỉ
            if (addressInput.value.trim() === '') {
                addressInput.classList.add('error');
                document.getElementById('address-error').classList.add('show');
                isValid = false;
            }
            
            // Validate số điện thoại
            if (phoneInput.value.trim() === '' || phoneInput.value.length !== 10) {
                phoneInput.classList.add('error');
                document.getElementById('phone-error').classList.add('show');
                isValid = false;
            }
            
            // Validate email
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (emailInput.value.trim() === '' || !emailPattern.test(emailInput.value)) {
                emailInput.classList.add('error');
                document.getElementById('email-error').classList.add('show');
                isValid = false;
            }
            
            if (!isValid) {
                alert('Vui lòng điền đầy đủ thông tin!');
                return;
            }
            
            // Chuyển sang trang sepay/order.php với POST data
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'sepay/order.php';
            
            // Thêm các trường thông tin
            const fields = {
                'ten_nguoinhan': nameInput.value,
                'diachi_nhan': addressInput.value,
                'so_dienthoai': phoneInput.value,
                'email_nguoinhan': emailInput.value,
                'tong_tien': <?php echo $final_total; ?>,
                'ma_user': <?php echo $user_id; ?>
            };
            
            for (let key in fields) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = fields[key];
                form.appendChild(input);
            }
            
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>
