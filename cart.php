<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

// Import database connection
require_once 'config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    // Chưa đăng nhập -> chuyển đến trang đăng nhập
    header('Location: login.php?redirect=cart.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$cart_items = array();
$total_amount = 0;
$shipping_fee = 0;
$discount_percent = 0;
$discount_amount = 0;
$promo_code = '';

try {
    // Kết nối database
    $db = new Database();
    $conn = $db->getConnection();
    
    // Truy vấn giỏ hàng với JOIN để lấy thông tin sản phẩm
    $sql = "SELECT 
                g.id_giohang,
                g.id_sanpham,
                g.so_luong,
                g.thanh_tien,
                s.ten_sanpham,
                s.hinh_anh,
                s.gia,
                s.gia_khuyen_mai,
                s.mau_sac,
                s.so_luong as ton_kho
            FROM gio_hang g
            INNER JOIN san_pham s ON g.id_sanpham = s.id_sanpham
            WHERE g.ma_user = ?
            ORDER BY g.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute(array($user_id));
    $cart_items = $stmt->fetchAll();
    
    // Tính tổng tiền sản phẩm
    foreach ($cart_items as $item) {
        $total_amount += $item['thanh_tien'];
    }
    
    // Phí vận chuyển chuẩn cho mỗi đơn hàng (lấy từ cấu hình)
    $shipping_fee = 15000; // 15,000 VND cho mỗi giỏ hàng
    
    // Tự động áp dụng mã giảm giá còn hiệu lực (không cần nhập)
    $stmt_promo = $conn->prepare("SELECT id, ten_km, phan_tram_km FROM khuyen_mai 
                                   WHERE (ngay_ket_thuc IS NULL OR ngay_ket_thuc >= CURDATE())
                                   AND (ngay_bat_dau IS NULL OR ngay_bat_dau <= CURDATE())
                                   ORDER BY phan_tram_km DESC 
                                   LIMIT 1");
    $stmt_promo->execute();
    $promo_info = $stmt_promo->fetch();
    
    if ($promo_info) {
        $discount_percent = $promo_info['phan_tram_km'];
        $promo_code = $promo_info['ten_km'];
        // Tính số tiền giảm (chỉ áp dụng cho tổng sản phẩm)
        $discount_amount = ($total_amount * $discount_percent) / 100;
        
        // Lưu vào session để tracking
        $_SESSION['promo_code'] = $promo_code;
        $_SESSION['promo_id'] = $promo_info['id'];
    }
    
    // Lưu thông tin vào session để sử dụng ở invoice.php
    $_SESSION['cart_summary'] = array(
        'subtotal' => $total_amount,
        'shipping_fee' => $shipping_fee,
        'discount_percent' => $discount_percent,
        'discount_amount' => $discount_amount,
        'promo_code' => $promo_code,
        'total' => $total_amount + $shipping_fee - $discount_amount
    );
    
    // Debug: Uncomment để xem giá trị
    // echo "<!-- DEBUG: discount_percent = $discount_percent, discount_amount = $discount_amount, promo_code = $promo_code -->";
    
} catch (PDOException $e) {
    $error_message = 'Lỗi database: ' . $e->getMessage();
}

// Lấy số lượng giỏ hàng (sử dụng $cart_items đã có)
$cart_count = 0;
foreach ($cart_items as $item) {
    $cart_count += $item['so_luong'];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <title>KLTN Shop - Giỏ hàng</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>
    <link rel="stylesheet" href="css/responsive.css?v=1765636813">">
    <style>
        /* Cart Page Enhanced Styles */
        #cart table {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        #cart table tbody tr {
            transition: background 0.3s;
        }
        
        #cart table tbody tr:hover {
            background: #f8f8f8;
        }
        
        #cart table img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .quantity-input {
            width: 60px;
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .delete-icon {
            color: #e74c3c;
            font-size: 20px;
            cursor: pointer;
            transition: color 0.3s;
        }
        
        .delete-icon:hover {
            color: #c0392b;
        }
        
        .empty-cart-message {
            text-align: center;
            padding: 80px 20px;
            font-size: 18px;
            color: #999;
        }
        
        .empty-cart-message i {
            font-size: 80px;
            color: #ddd;
            display: block;
            margin-bottom: 20px;
        }
        
        .continue-shopping-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: #088178;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .continue-shopping-btn:hover {
            background: #066d63;
        }
        
        .error-alert {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .stock-warning {
            animation: fadeIn 0.3s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>
    <section id="header">
        <a href="index.php"><img src="img/logo7.png" width="150px" class="logo" alt="KLTN Logo"></a>
        <div>
            <ul id="navbar">
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="shop.php">Sản phẩm</a></li>
                <li><a href="blog.php">Tin tức</a></li>
                <li><a href="about.php">Về chúng tôi</a></li>
                <li><a href="contact.php">Liên hệ</a></li>
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
                <li id="lg-bag">
                    <a href="cart.php" class="active" style="position: relative;">
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
    <!--Cart page-->
    <section id="page-header" class="about-header">
         <h1 style="color:#f8f8f8">Giỏ hàng của bạn</h1> 
    </section>

    <?php if (isset($error_message)): ?>
        <div class="error-alert section-p1"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['promo_message'])): ?>
        <div class="section-p1" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['promo_message']; unset($_SESSION['promo_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['promo_error'])): ?>
        <div class="section-p1" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['promo_error']; unset($_SESSION['promo_error']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($cart_items)): ?>
    <section id="cart" class="section-p1">
        <table width="100%">
            <thead>
                <tr>
                    <td>Hình ảnh</td>
                    <td>Sản phẩm</td>
                    <td>Giá</td>
                    <td>Số lượng</td>
                    <td>Tổng cộng</td>
                    <td>Xóa</td>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                <tr data-cart-id="<?php echo $item['id_giohang']; ?>">
                    <td><img src="<?php echo htmlspecialchars($item['hinh_anh'] ? $item['hinh_anh'] : 'img/products/f1.jpg'); ?>" alt="<?php echo htmlspecialchars($item['ten_sanpham']); ?>"></td>
                    <td>
                        <div><?php echo htmlspecialchars($item['ten_sanpham']); ?></div>
                        <?php if (!empty($item['mau_sac'])): ?>
                            <small style="color: #666; font-size: 0.9em;">Màu sắc: <?php echo htmlspecialchars($item['mau_sac']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="unit-price">
                        <?php 
                            // Tính đơn giá từ thành tiền / số lượng
                            $unit_price = $item['thanh_tien'] / $item['so_luong'];
                            echo number_format($unit_price, 0, ',', '.'); 
                        ?>VNĐ
                    </td>
                    <td>
                        <input type="number" 
                               class="quantity-input" 
                               value="<?php echo $item['so_luong']; ?>" 
                               min="1" 
                               max="<?php echo $item['ton_kho']; ?>"
                               data-cart-id="<?php echo $item['id_giohang']; ?>"
                               data-unit-price="<?php echo $unit_price; ?>"
                               data-max-stock="<?php echo $item['ton_kho']; ?>"
                               onchange="updateQuantity(this)"
                               oninput="checkStock(this)">
                        <div class="stock-warning" id="warning-<?php echo $item['id_giohang']; ?>" style="display: none; color: red; font-size: 12px; margin-top: 5px;">
                            Tối đa: <?php echo $item['ton_kho']; ?> sản phẩm
                        </div>
                    </td>
                    <td class="item-total"><?php echo number_format($item['thanh_tien'], 0, ',', '.'); ?>VNĐ</td>
                    <td>
                        <a href="javascript:void(0);" onclick="deleteItem(<?php echo $item['id_giohang']; ?>)">
                            <i class="far fa-times-circle delete-icon"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section id="cart-add" class="section-p1">
        <div id="coupon">
            <?php if (!empty($promo_code)): ?>
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                    <h4 style="margin: 0 0 10px 0; font-size: 16px;">
                        <i class="fas fa-gift"></i> Khuyến mãi đang áp dụng
                    </h4>
                    <p style="margin: 0; font-size: 14px;">
                        <strong>Mã: <?php echo htmlspecialchars($promo_code); ?></strong> 
                        - Giảm <?php echo $discount_percent; ?>%
                    </p>
                </div>
            <?php else: ?>
                <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                    <p style="margin: 0; font-size: 14px;">
                        <i class="fas fa-info-circle"></i> Hiện tại chưa có chương trình khuyến mãi nào
                    </p>
                </div>
            <?php endif; ?>
        </div>
        <div id="subtotal">
            <h3>Tổng thanh toán</h3>
            <table>
                <tr>
                    <td>Tạm tính</td>
                    <td id="subtotal-amount"><?php echo number_format($total_amount, 0, ',', '.'); ?>VNĐ</td>
                </tr>
                <?php if ($discount_percent > 0): ?>
                <tr>
                    <td>Giảm giá (<?php echo $discount_percent; ?>%)</td>
                    <td style="color: red;" id="discount-amount">-<?php echo number_format($discount_amount, 0, ',', '.'); ?>VNĐ</td>
                </tr>
                <tr>
                    <td><strong>Tổng tạm tính</strong></td>
                    <td><strong id="after-discount-amount"><?php echo number_format($total_amount - $discount_amount, 0, ',', '.'); ?>VNĐ</strong></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td>Phí vận chuyển</td>
                    <td id="shipping-amount"><?php echo number_format($shipping_fee, 0, ',', '.'); ?>VNĐ</td>
                </tr>
                <tr style="border-top: 2px solid #ddd;">
                    <td><strong>Tổng cộng</strong></td>
                    <td><strong id="total-amount" style="color: #088178; font-size: 18px;"><?php echo number_format($total_amount + $shipping_fee - $discount_amount, 0, ',', '.'); ?>VNĐ</strong></td>
                </tr>
            </table>
            <a href="invoice.php"><button class="normal">Tiến hành thanh toán</button></a>
        </div>
    </section>
    <?php else: ?>
    <section class="section-p1">
        <div class="empty-cart-message">
            <i class="fa-solid fa-cart-shopping"></i>
            <p>Giỏ hàng của bạn đang trống</p>
            <a href="shop.php" class="continue-shopping-btn">
                <i class="fa-solid fa-arrow-left"></i> Tiếp tục mua sắm
            </a>
        </div>
    </section>
    <?php endif; ?>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Kiểm tra số lượng tồn kho
        function checkStock(input) {
            const cartId = input.getAttribute('data-cart-id');
            const maxStock = parseInt(input.getAttribute('data-max-stock'));
            const currentValue = parseInt(input.value) || 1;
            const warningDiv = document.getElementById('warning-' + cartId);
            
            if (currentValue > maxStock) {
                input.value = maxStock;
                warningDiv.style.display = 'block';
                setTimeout(function() {
                    warningDiv.style.display = 'none';
                }, 3000);
            } else {
                warningDiv.style.display = 'none';
            }
        }
        
        // Cập nhật số lượng sản phẩm
        function updateQuantity(input) {
            const cartId = input.getAttribute('data-cart-id');
            const quantity = parseInt(input.value);
            const unitPrice = parseFloat(input.getAttribute('data-unit-price'));
            
            if (quantity < 1) {
                alert('Số lượng phải lớn hơn 0');
                input.value = 1;
                return;
            }
            
            // Gửi AJAX request để cập nhật
            fetch('api/update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'cart_id=' + cartId + '&quantity=' + quantity
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Cập nhật thành tiền của sản phẩm
                    const row = input.closest('tr');
                    const itemTotal = row.querySelector('.item-total');
                    const newTotal = unitPrice * quantity;
                    itemTotal.textContent = new Intl.NumberFormat('vi-VN').format(newTotal) + 'VNĐ';
                    
                    // Cập nhật tổng tiền
                    updateTotalAmount();
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi cập nhật giỏ hàng');
            });
        }
        
        // Xóa sản phẩm khỏi giỏ hàng
        function deleteItem(cartId) {
            if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
                return;
            }
            
            fetch('api/delete_cart_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'cart_id=' + cartId
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Xóa dòng sản phẩm
                    const row = document.querySelector('tr[data-cart-id="' + cartId + '"]');
                    row.remove();
                    
                    // Cập nhật tổng tiền
                    updateTotalAmount();
                    
                    // Kiểm tra nếu giỏ hàng trống
                    const tbody = document.querySelector('#cart table tbody');
                    if (tbody.children.length === 0) {
                        location.reload();
                    }
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi xóa sản phẩm');
            });
        }
        
        // Cập nhật tổng tiền
        function updateTotalAmount() {
            let subtotal = 0;
            document.querySelectorAll('.item-total').forEach(item => {
                const amount = parseFloat(item.textContent.replace(/[^\d]/g, ''));
                subtotal += amount;
            });
            
            // Lấy phí vận chuyển
            const shippingText = document.getElementById('shipping-amount').textContent;
            const shippingFee = parseFloat(shippingText.replace(/[^\d]/g, ''));
            
            // Lấy giảm giá nếu có
            let discount = 0;
            const discountElement = document.getElementById('discount-amount');
            if (discountElement) {
                const discountText = discountElement.textContent;
                discount = parseFloat(discountText.replace(/[^\d]/g, ''));
            }
            
            // Tính tổng tạm tính (sau giảm giá)
            const afterDiscount = subtotal - discount;
            
            // Tính tổng cộng
            const finalTotal = afterDiscount + shippingFee;
            
            // Cập nhật các giá trị
            document.getElementById('subtotal-amount').textContent = new Intl.NumberFormat('vi-VN').format(subtotal) + 'VNĐ';
            
            // Cập nhật tổng tạm tính nếu có giảm giá
            const afterDiscountElement = document.getElementById('after-discount-amount');
            if (afterDiscountElement) {
                afterDiscountElement.textContent = new Intl.NumberFormat('vi-VN').format(afterDiscount) + 'VNĐ';
            }
            
            document.getElementById('total-amount').textContent = new Intl.NumberFormat('vi-VN').format(finalTotal) + 'VNĐ';
        }
    </script>

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

    <!-- Scroll to Top Button -->
    <button id="scrollToTop" title="Trở về đầu trang">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script src="script.js?v=<?php echo time(); ?>"></script>
    <script src="https://cdn.botpress.cloud/webchat/v3.3/inject.js" defer></script>
    <script src="https://files.bpcontent.cloud/2025/11/26/16/20251126163853-AFN0KSEV.js" defer></script>
    <script src="js/mobile-responsive.js?v=1765636813"></script>
</body>
</html>