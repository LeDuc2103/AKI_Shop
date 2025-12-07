<?php
session_start();
require_once 'config/database.php';

// Lấy order_id từ URL
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id <= 0) {
    header('Location: index.php');
    exit();
}

// Khởi tạo database
$db = new Database();
$conn = $db->getConnection();

// Lấy thông tin đơn hàng
$sql = "SELECT 
            dh.*,
            u.ho_ten,
            u.email
        FROM don_hang dh
        LEFT JOIN user u ON dh.ma_user = u.ma_user
        WHERE dh.ma_donhang = ?";

$stmt = $conn->prepare($sql);
$stmt->execute(array($order_id));
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: index.php');
    exit();
}

// Lấy chi tiết sản phẩm
$sqlDetails = "SELECT 
                    ct.*,
                    sp.ten_sanpham,
                    sp.hinh_anh
                FROM chitiet_donhang ct
                INNER JOIN san_pham sp ON ct.id_sanpham = sp.id_sanpham
                WHERE ct.ma_donhang = ?";

$stmtDetails = $conn->prepare($sqlDetails);
$stmtDetails->execute(array($order_id));
$order_details = $stmtDetails->fetchAll(PDO::FETCH_ASSOC);

// Kiểm tra trạng thái thanh toán
$is_paid = ($order['trangthai_thanhtoan'] === 'da_thanh_toan');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_paid ? 'Thanh toán thành công' : 'Xác nhận đơn hàng'; ?> - Đơn hàng #<?php echo $order_id; ?></title>
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

        .success-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
            text-align: center;
            animation: slideIn 0.5s ease-out;
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

        .success-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: bounce 1s ease-in-out;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-20px);
            }
            60% {
                transform: translateY(-10px);
            }
        }

        .success-title {
            font-size: 32px;
            font-weight: bold;
            color: #27ae60;
            margin-bottom: 10px;
        }

        .pending-title {
            font-size: 32px;
            font-weight: bold;
            color: #f39c12;
            margin-bottom: 10px;
        }

        .success-message {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
        }

        .order-info {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: left;
        }

        .order-info h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #666;
            font-weight: 500;
        }

        .info-value {
            color: #333;
            font-weight: bold;
        }

        .product-list {
            margin-top: 20px;
        }

        .product-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: white;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }

        .product-details {
            flex: 1;
            text-align: left;
        }

        .product-name {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .product-quantity {
            color: #666;
            font-size: 14px;
        }

        .product-price {
            font-weight: bold;
            color: #667eea;
        }

        .total-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 18px;
        }

        .total-row.grand-total {
            font-size: 24px;
            font-weight: bold;
            padding-top: 15px;
            border-top: 2px solid rgba(255,255,255,0.3);
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #27ae60;
            color: white;
        }

        .btn-secondary:hover {
            background: #229954;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.4);
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            margin-left: 10px;
        }

        .status-success {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        @media (max-width: 768px) {
            .success-container {
                padding: 20px;
            }

            .success-icon {
                font-size: 60px;
            }

            .success-title, .pending-title {
                font-size: 24px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .product-item {
                flex-direction: column;
                text-align: center;
            }

            .product-image {
                margin-right: 0;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <?php if ($is_paid): ?>
            <div class="success-icon">✅</div>
            <h1 class="success-title">THANH TOÁN THÀNH CÔNG!</h1>
            <p class="success-message">
                Cảm ơn bạn đã đặt hàng. Đơn hàng của bạn đã được xác nhận và đang được xử lý.
            </p>
        <?php else: ?>
            <div class="success-icon">⏳</div>
            <h1 class="pending-title">ĐƠN HÀNG ĐÃ ĐƯỢC TẠO</h1>
            <p class="success-message">
                Đơn hàng đang chờ thanh toán. Vui lòng hoàn tất thanh toán để được xử lý.
            </p>
        <?php endif; ?>

        <div class="order-info">
            <h3>
                <i class="fas fa-receipt"></i> Thông tin đơn hàng
                <span class="status-badge <?php echo $is_paid ? 'status-success' : 'status-pending'; ?>">
                    <?php echo $is_paid ? 'Đã thanh toán' : 'Chờ thanh toán'; ?>
                </span>
            </h3>
            
            <div class="info-row">
                <span class="info-label">Mã đơn hàng:</span>
                <span class="info-value">#<?php echo str_pad($order_id, 4, '0', STR_PAD_LEFT); ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Người nhận:</span>
                <span class="info-value"><?php echo htmlspecialchars($order['ten_nguoinhan']); ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Số điện thoại:</span>
                <span class="info-value"><?php echo htmlspecialchars($order['so_dienthoai']); ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Địa chỉ:</span>
                <span class="info-value"><?php echo htmlspecialchars($order['diachi_nhan']); ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Phương thức thanh toán:</span>
                <span class="info-value"><?php echo $order['phuongthuc_thanhtoan'] === 'sepay' ? 'SePay QR Code' : strtoupper($order['phuongthuc_thanhtoan']); ?></span>
            </div>

            <?php if (!empty($order_details)): ?>
            <div class="product-list">
                <h4 style="margin: 20px 0 10px 0; color: #333;">
                    <i class="fas fa-box"></i> Sản phẩm đã đặt:
                </h4>
                <?php foreach ($order_details as $item): ?>
                <div class="product-item">
                    <img src="<?php echo htmlspecialchars($item['hinh_anh']); ?>" alt="<?php echo htmlspecialchars($item['ten_sanpham']); ?>" class="product-image">
                    <div class="product-details">
                        <div class="product-name"><?php echo htmlspecialchars($item['ten_sanpham']); ?></div>
                        <div class="product-quantity">Số lượng: <?php echo $item['so_luong']; ?> x <?php echo number_format($item['don_gia'], 0, ',', '.'); ?>đ</div>
                    </div>
                    <div class="product-price">
                        <?php echo number_format($item['so_luong'] * $item['don_gia'], 0, ',', '.'); ?>đ
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="total-section">
            <div class="total-row">
                <span>Tiền hàng:</span>
                <span><?php echo number_format($order['tien_hang'], 0, ',', '.'); ?>đ</span>
            </div>
            <div class="total-row">
                <span>Phí vận chuyển:</span>
                <span><?php echo number_format($order['tien_ship'], 0, ',', '.'); ?>đ</span>
            </div>
            <div class="total-row grand-total">
                <span>Tổng cộng:</span>
                <span><?php echo number_format($order['tong_tien'], 0, ',', '.'); ?>đ</span>
            </div>
        </div>

        <div class="action-buttons">
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Về trang chủ
            </a>
            <a href="my_orders.php" class="btn btn-secondary">
                <i class="fas fa-list"></i> Xem đơn hàng của tôi
            </a>
        </div>
    </div>

    <?php if (!$is_paid): ?>
    <script>
        // Auto reload mỗi 5 giây để check trạng thái thanh toán
        setTimeout(function() {
            location.reload();
        }, 5000);
    </script>
    <?php endif; ?>
</body>
</html>
