<?php
session_start();
require_once("./config.php");
require_once("../config/database.php");

$vnp_SecureHash = isset($_GET['vnp_SecureHash']) ? $_GET['vnp_SecureHash'] : '';
$inputData = array();
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

unset($inputData['vnp_SecureHash']);
ksort($inputData);
$i = 0;
$hashData = "";
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
}

$secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

// Thông tin từ VNPay
$vnp_TxnRef = isset($inputData['vnp_TxnRef']) ? $inputData['vnp_TxnRef'] : null;
$ma_donhang = $vnp_TxnRef ? (int)$vnp_TxnRef : null;
$vnpAmount = isset($inputData['vnp_Amount']) ? ($inputData['vnp_Amount'] / 100) : 0;
$vnpResponseCode = isset($inputData['vnp_ResponseCode']) ? $inputData['vnp_ResponseCode'] : null;
$vnp_TransactionNo = isset($inputData['vnp_TransactionNo']) ? $inputData['vnp_TransactionNo'] : '';
$vnp_BankCode = isset($inputData['vnp_BankCode']) ? $inputData['vnp_BankCode'] : '';
$vnp_PayDate = isset($inputData['vnp_PayDate']) ? $inputData['vnp_PayDate'] : '';
$vnp_OrderInfo = isset($inputData['vnp_OrderInfo']) ? $inputData['vnp_OrderInfo'] : '';

// Xác định trạng thái thanh toán
$isValidHash = ($secureHash == $vnp_SecureHash);
$isSuccess = ($isValidHash && $vnpResponseCode == '00');
$paymentStatus = '';
$paymentMessage = '';
$paymentIcon = '';

if ($isValidHash) {
    if ($vnpResponseCode == '00') {
        $paymentStatus = 'success';
        $paymentMessage = 'Thanh toán thành công!';
        $paymentIcon = 'fa-check-circle';
    } else {
        $paymentStatus = 'failed';
        $paymentMessage = 'Thanh toán thất bại';
        $paymentIcon = 'fa-times-circle';
    }
} else {
    $paymentStatus = 'error';
    $paymentMessage = 'Chữ ký không hợp lệ';
    $paymentIcon = 'fa-exclamation-triangle';
}

// Cập nhật đơn hàng trong hệ thống nếu checksum hợp lệ và thanh toán thành công
if ($isSuccess && $ma_donhang) {
    try {
        $db = new Database();
        $conn = $db->getConnection();

        // Lấy đơn hàng từ bảng don_hang theo ma_donhang
        $stmt = $conn->prepare("SELECT * FROM don_hang WHERE ma_donhang = ?");
        $stmt->execute(array($ma_donhang));
        $order = $stmt->fetch();

        // Ghi log giao dịch VNPay vào bảng vnpay_transactions (nếu có)
        try {
            $logStmt = $conn->prepare("INSERT INTO vnpay_transactions 
                (ma_donhang, vnp_TransactionNo, vnp_ResponseCode, vnp_Amount, vnp_BankCode, vnp_PayDate, raw_data)
                VALUES (:ma_donhang, :vnp_TransactionNo, :vnp_ResponseCode, :vnp_Amount, :vnp_BankCode, :vnp_PayDate, :raw_data)");

            $logStmt->execute(array(
                ':ma_donhang' => $ma_donhang,
                ':vnp_TransactionNo' => $vnp_TransactionNo,
                ':vnp_ResponseCode' => $vnpResponseCode,
                ':vnp_Amount' => $vnpAmount,
                ':vnp_BankCode' => $vnp_BankCode,
                ':vnp_PayDate' => $vnp_PayDate,
                ':raw_data' => json_encode($inputData)
            ));
        } catch (Exception $e) {
            error_log('VNPay log insert error: ' . $e->getMessage());
        }

        if ($order) {
            if ($order['trangthai_thanhtoan'] == 'chua_thanh_toan') {
                // Cập nhật trạng thái thanh toán
                $update = $conn->prepare("UPDATE don_hang 
                                          SET trangthai_thanhtoan = 'da_thanh_toan',
                                              thanh_toan = 'đã thanh toán',
                                              phuongthuc_thanhtoan = 'vnpay',
                                              trang_thai = 'xac_nhan'
                                          WHERE ma_donhang = ?");
                $update->execute(array($ma_donhang));

                // Xóa giỏ hàng của user sau khi thanh toán thành công
                $deleteCart = $conn->prepare("DELETE FROM gio_hang WHERE ma_user = ?");
                $deleteCart->execute(array($order['ma_user']));
            }
        }
    } catch (Exception $e) {
        error_log('VNPay return update error: ' . $e->getMessage());
    }
}

// Lấy số lượng giỏ hàng
include_once '../includes/cart_count.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <title>Kết quả thanh toán - KLTN Shop</title>
    <link rel="stylesheet" href="../style.css?v=<?php echo time(); ?>">
    <style>
        .payment-result-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .payment-icon {
            font-size: 80px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .payment-icon.success {
            color: #28a745;
        }
        
        .payment-icon.failed {
            color: #dc3545;
        }
        
        .payment-icon.error {
            color: #ffc107;
        }
        
        .payment-title {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 30px;
        }
        
        .payment-title.success {
            color: #28a745;
        }
        
        .payment-title.failed {
            color: #dc3545;
        }
        
        .payment-title.error {
            color: #ffc107;
        }
        
        .payment-info {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin: 30px 0;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: bold;
            color: #666;
        }
        
        .info-value {
            color: #333;
            text-align: right;
        }
        
        .btn-group {
            text-align: center;
            margin-top: 40px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 0 10px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #088178;
            color: white;
        }
        
        .btn-primary:hover {
            background: #066d63;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
    </style>
</head>
<body>
    <section id="header">
        <a href="../index.php"><img src="../img/logo1.png" width="150px" class="logo" alt="KLTN Logo"></a>
        <div>
            <ul id="navbar">
                <li><a href="../index.php">Trang chủ</a></li>
                <li><a href="../shop.php">Sản phẩm</a></li>
                <li><a href="../blog.php">Tin tức</a></li>
                <li><a href="../about.php">Về chúng tôi</a></li>
                <li><a href="../contact.php">Liên hệ</a></li>
                <li id="search-icon"><a href="#"><i class="fa-solid fa-search"></i></a></li>
                <li id="user-icon" tabindex="0">
                    <a href="#" tabindex="-1"><i class="fa-solid fa-user"></i></a>
                    <div class="user-dropdown">
                        <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                            <a href="#">Xin chào, <?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
                            <?php if (in_array($_SESSION['user_role'], array('admin', 'quanly', 'nhanvien', 'nhanvienkho'))): ?>
                                <a href="../admin.php">Quản trị viên</a>
                            <?php endif; ?>
                            <a href="../logout.php">Đăng xuất</a>
                        <?php else: ?>
                            <a href="../login.php">Đăng Nhập</a>
                            <a href="../register.php">Đăng Ký</a>
                        <?php endif; ?>
                    </div>
                </li>
                <li id="lg-bag">
                    <a href="../cart.php" style="position: relative;">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-badge"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <a href="#" id="close"><i class="fa-solid fa-xmark"></i></a>    
            </ul> 
            <div id="mobile">
                <a href="../cart.php" style="position: relative;">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-badge"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
                <i id="bar" class="fas fa-outdent"></i>
            </div>  
        </div>
    </section>

    <section id="page-header" class="about-header">
        <h2>Kết quả thanh toán</h2>
        <p>Thông tin giao dịch của bạn</p>
    </section>

    <div class="payment-result-container section-p1">
        <div class="payment-icon <?php echo $paymentStatus; ?>">
            <i class="fas <?php echo $paymentIcon; ?>"></i>
        </div>
        
        <h1 class="payment-title <?php echo $paymentStatus; ?>">
            <?php echo $paymentMessage; ?>
        </h1>

        <?php if ($isSuccess): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> 
                Đơn hàng của bạn đã được thanh toán thành công. Chúng tôi sẽ xử lý và giao hàng trong thời gian sớm nhất.
            </div>
        <?php elseif ($paymentStatus == 'failed'): ?>
            <div class="alert alert-danger">
                <i class="fas fa-times-circle"></i> 
                Thanh toán không thành công. Vui lòng thử lại hoặc liên hệ hỗ trợ nếu bạn đã bị trừ tiền.
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> 
                Có lỗi xảy ra trong quá trình xác thực. Vui lòng liên hệ hỗ trợ để được giải quyết.
            </div>
        <?php endif; ?>

        <div class="payment-info">
            <h3 style="margin-bottom: 20px; color: #088178;">
                <i class="fas fa-receipt"></i> Thông tin giao dịch
            </h3>
            
            <div class="info-row">
                <span class="info-label"><i class="fas fa-hashtag"></i> Mã đơn hàng:</span>
                <span class="info-value"><strong>#<?php echo htmlspecialchars($ma_donhang ? $ma_donhang : 'N/A'); ?></strong></span>
            </div>
            
            <div class="info-row">
                <span class="info-label"><i class="fas fa-money-bill-wave"></i> Số tiền:</span>
                <span class="info-value"><strong><?php echo number_format($vnpAmount, 0, ',', '.'); ?> VNĐ</strong></span>
            </div>
            
            <?php if ($vnp_OrderInfo): ?>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-file-alt"></i> Nội dung:</span>
                <span class="info-value"><?php echo htmlspecialchars($vnp_OrderInfo); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($vnp_TransactionNo): ?>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-exchange-alt"></i> Mã giao dịch VNPay:</span>
                <span class="info-value"><?php echo htmlspecialchars($vnp_TransactionNo); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($vnp_BankCode): ?>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-university"></i> Ngân hàng:</span>
                <span class="info-value"><?php echo htmlspecialchars($vnp_BankCode); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($vnp_PayDate): ?>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-clock"></i> Thời gian:</span>
                <span class="info-value">
                    <?php 
                    // Format ngày từ VNPay (YYYYMMDDHHmmss)
                    if (strlen($vnp_PayDate) == 14) {
                        $date = substr($vnp_PayDate, 0, 4) . '-' . substr($vnp_PayDate, 4, 2) . '-' . substr($vnp_PayDate, 6, 2);
                        $time = substr($vnp_PayDate, 8, 2) . ':' . substr($vnp_PayDate, 10, 2) . ':' . substr($vnp_PayDate, 12, 2);
                        echo $date . ' ' . $time;
                    } else {
                        echo htmlspecialchars($vnp_PayDate);
                    }
                    ?>
                </span>
            </div>
            <?php endif; ?>
            
            <div class="info-row">
                <span class="info-label"><i class="fas fa-shield-alt"></i> Trạng thái:</span>
                <span class="info-value">
                    <?php if ($isValidHash): ?>
                        <span style="color: #28a745;"><i class="fas fa-check"></i> Chữ ký hợp lệ</span>
                    <?php else: ?>
                        <span style="color: #dc3545;"><i class="fas fa-times"></i> Chữ ký không hợp lệ</span>
                    <?php endif; ?>
                </span>
            </div>
        </div>

        <div class="btn-group">
            <a href="../index.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Về trang chủ
            </a>
            <?php if ($isSuccess): ?>
                <a href="../shop.php" class="btn btn-secondary">
                    <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
                </a>
            <?php else: ?>
                <a href="../cart.php" class="btn btn-secondary">
                    <i class="fas fa-shopping-cart"></i> Quay lại giỏ hàng
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="../script.js"></script>
    <script src="https://cdn.botpress.cloud/webchat/v3.3/inject.js" defer></script>
    <script src="https://files.bpcontent.cloud/2025/11/26/16/20251126163853-AFN0KSEV.js" defer></script>
</body>
</html>
