<?php
/**
 * Payment Success Page
 */
session_start();
require_once 'config.php';

$order_code = isset($_GET['order_code']) ? $_GET['order_code'] : '';

if (empty($order_code)) {
    header("Location: ../index.php");
    exit();
}

// Get order info
$conn = getDB();
$stmt = $conn->prepare("
    SELECT dh.*, t.transaction_content, t.amount_in, t.transaction_date, t.bank_brand_name
    FROM don_hang dh
    LEFT JOIN transactions t ON dh.ma_donhang = t.ma_donhang
    WHERE dh.order_code = :order_code
");
$stmt->execute(array(':order_code' => $order_code));
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán Thành Công</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .success-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
            animation: slideIn 0.5s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .success-header {
            background: linear-gradient(135deg, #55efc4 0%, #00b894 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .success-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: checkmark 0.8s ease-in-out;
        }
        
        @keyframes checkmark {
            0% {
                transform: scale(0);
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
            }
        }
        
        .success-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .success-body {
            padding: 40px;
        }
        
        .order-info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
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
            color: #636e72;
            font-size: 15px;
        }
        
        .info-value {
            color: #2d3436;
            font-weight: 600;
            font-size: 16px;
            text-align: right;
        }
        
        .amount-highlight {
            color: #00b894;
            font-size: 24px;
            font-weight: 700;
        }
        
        .btn-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }
        
        .btn-secondary:hover {
            background: #667eea;
            color: white;
        }
        
        @media (max-width: 768px) {
            .btn-group {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-header">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Thanh Toán Thành Công!</h1>
            <p>Đơn hàng của bạn đã được xác nhận</p>
        </div>
        
        <div class="success-body">
            <div class="order-info">
                <div class="info-row">
                    <span class="info-label">Mã đơn hàng:</span>
                    <span class="info-value">#<?php echo $order['order_code']; ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Số tiền đã thanh toán:</span>
                    <span class="info-value amount-highlight">
                        <?php echo number_format($order['tong_tien'], 0, ',', '.'); ?> VNĐ
                    </span>
                </div>
                
                <?php if ($order['transaction_date']): ?>
                <div class="info-row">
                    <span class="info-label">Thời gian giao dịch:</span>
                    <span class="info-value">
                        <?php echo date('d/m/Y H:i', strtotime($order['transaction_date'])); ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if ($order['bank_brand_name']): ?>
                <div class="info-row">
                    <span class="info-label">Ngân hàng:</span>
                    <span class="info-value"><?php echo $order['bank_brand_name']; ?></span>
                </div>
                <?php endif; ?>
                
                <div class="info-row">
                    <span class="info-label">Trạng thái:</span>
                    <span class="info-value" style="color: #00b894;">
                        <i class="fas fa-check"></i> Đã thanh toán
                    </span>
                </div>
            </div>
            
            <div style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <p style="color: #1976d2; margin: 0;">
                    <i class="fas fa-info-circle"></i> 
                    Chúng tôi sẽ xử lý đơn hàng của bạn trong thời gian sớm nhất. 
                    Vui lòng kiểm tra email để nhận thông tin chi tiết.
                </p>
            </div>
            
            <div class="btn-group">
                <a href="../my_orders.php" class="btn btn-primary">
                    <i class="fas fa-list"></i> Đơn hàng của tôi
                </a>
                <a href="../shop.php" class="btn btn-secondary">
                    <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
                </a>
            </div>
        </div>
    </div>
</body>
</html>
