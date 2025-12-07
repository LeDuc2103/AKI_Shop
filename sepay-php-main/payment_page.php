<?php
/**
 * SePay Payment Page - Hiển thị QR và tracking realtime
 */
session_start();
require_once 'config.php';

// Check login
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

$order_code = isset($_GET['order_code']) ? $_GET['order_code'] : '';
$amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0;

if (empty($order_code) || $amount <= 0) {
    die("Thông tin đơn hàng không hợp lệ!");
}

// Get order info
$conn = getDB();
$stmt = $conn->prepare("SELECT * FROM don_hang WHERE order_code = :order_code");
$stmt->execute(array(':order_code' => $order_code));
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Không tìm thấy đơn hàng!");
}

// Transaction content
$transaction_content = "Thanh Toan Don Hang " . $order_code;

// VietQR URL
$qr_url = "https://img.vietqr.io/image/" . SEPAY_BANK_CODE . "-" . SEPAY_ACCOUNT_NUMBER . "-compact2.png?" . 
          "amount=" . $amount . 
          "&addInfo=" . urlencode($transaction_content) . 
          "&accountName=" . urlencode(SEPAY_ACCOUNT_NAME);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán QR - SePay</title>
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
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .status-bar {
            padding: 20px;
            margin: 20px;
            border-radius: 12px;
            text-align: center;
            font-size: 18px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .status-pending {
            background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%);
            color: #2d3436;
            border: 3px solid #fdcb6e;
        }
        
        .status-success {
            background: linear-gradient(135deg, #55efc4 0%, #00b894 100%);
            color: white;
            border: 3px solid #00b894;
        }
        
        .payment-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            padding: 40px;
        }
        
        .info-section {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .info-card {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            border-radius: 8px;
        }
        
        .info-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
        }
        
        .info-value {
            font-size: 20px;
            color: #2d3436;
            font-weight: 700;
        }
        
        .amount-value {
            font-size: 28px;
            color: #e74c3c;
        }
        
        .content-box {
            background: #fff3cd;
            border: 2px dashed #ffc107;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            font-size: 18px;
            font-weight: 700;
            color: #856404;
            word-break: break-word;
        }
        
        .timer-box {
            background: linear-gradient(135deg, #a29bfe 0%, #6c5ce7 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }
        
        .timer-value {
            font-size: 36px;
            font-weight: 700;
            margin-top: 10px;
        }
        
        .qr-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }
        
        .qr-wrapper {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }
        
        .qr-image {
            width: 100%;
            max-width: 400px;
            border-radius: 10px;
        }
        
        .transaction-list {
            margin: 20px 40px;
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
        }
        
        .transaction-list h3 {
            color: #2d3436;
            margin-bottom: 15px;
            font-size: 20px;
        }
        
        .transaction-item {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border-left: 4px solid #dfe6e9;
            transition: all 0.3s;
        }
        
        .transaction-item.matched {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border-left-color: #28a745;
        }
        
        .transaction-content {
            font-weight: 600;
            color: #2d3436;
            margin-bottom: 8px;
        }
        
        .transaction-amount {
            font-size: 18px;
            color: #e74c3c;
            font-weight: 700;
        }
        
        .transaction-date {
            font-size: 13px;
            color: #636e72;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        
        .pulse {
            animation: pulse 2s ease-in-out infinite;
        }
        
        @media (max-width: 768px) {
            .payment-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-qrcode"></i> Thanh Toán SePay QR</h1>
            <p>Đơn hàng #<?php echo $order_code; ?></p>
        </div>
        
        <div class="status-bar status-pending pulse" id="statusBar">
            <i class="fas fa-clock"></i> Đang chờ thanh toán...
        </div>
        
        <div class="payment-grid">
            <!-- Left: Info -->
            <div class="info-section">
                <div class="info-card">
                    <div class="info-label">Ngân hàng:</div>
                    <div class="info-value">
                        <i class="fas fa-university"></i> MBBank
                    </div>
                </div>
                
                <div class="info-card">
                    <div class="info-label">Số tài khoản:</div>
                    <div class="info-value">
                        <i class="fas fa-credit-card"></i> <?php echo SEPAY_ACCOUNT_NUMBER; ?>
                    </div>
                </div>
                
                <div class="info-card">
                    <div class="info-label">Chủ tài khoản:</div>
                    <div class="info-value">
                        <i class="fas fa-user"></i> <?php echo SEPAY_ACCOUNT_NAME; ?>
                    </div>
                </div>
                
                <div class="info-card">
                    <div class="info-label">Số tiền cần thanh toán:</div>
                    <div class="info-value amount-value">
                        <?php echo number_format($amount, 0, ',', '.'); ?> VNĐ
                    </div>
                </div>
                
                <div class="info-card">
                    <div class="info-label">Nội dung chuyển khoản:</div>
                    <div class="content-box">
                        <?php echo $transaction_content; ?>
                    </div>
                    <small style="color: #e74c3c; margin-top: 10px; display: block;">
                        ⚠️ Vui lòng nhập CHÍNH XÁC nội dung trên
                    </small>
                </div>
                
                <div class="timer-box">
                    <div><i class="fas fa-hourglass-half"></i> Thời gian còn lại</div>
                    <div class="timer-value" id="timer">5:00</div>
                </div>
            </div>
            
            <!-- Right: QR -->
            <div class="qr-section">
                <div class="qr-wrapper">
                    <img src="<?php echo $qr_url; ?>" alt="QR Code" class="qr-image" id="qrImage">
                </div>
                <div style="text-align: center; color: #636e72;">
                    <i class="fas fa-mobile-alt"></i> Quét mã QR bằng app ngân hàng
                    <br>
                    <small>Hệ thống kiểm tra tự động mỗi <?php echo CHECK_INTERVAL; ?> giây</small>
                </div>
            </div>
        </div>
        
        <!-- Transaction List -->
        <div class="transaction-list" id="transactionList" style="display: none;">
            <h3><i class="fas fa-list"></i> Giao dịch gần đây</h3>
            <div id="transactions"></div>
        </div>
    </div>
    
    <script>
        const ORDER_CODE = '<?php echo $order_code; ?>';
        const AMOUNT = <?php echo $amount; ?>;
        const CHECK_INTERVAL = <?php echo CHECK_INTERVAL * 1000; ?>; // Convert to ms
        let timeLeft = <?php echo PAYMENT_TIMEOUT; ?>;
        let checkTimer;
        let countdownTimer;
        
        // Countdown timer
        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            document.getElementById('timer').textContent = 
                minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
            
            if (timeLeft <= 0) {
                clearInterval(countdownTimer);
                clearInterval(checkTimer);
                document.getElementById('statusBar').className = 'status-bar';
                document.getElementById('statusBar').style.background = 'linear-gradient(135deg, #ff7675 0%, #d63031 100%)';
                document.getElementById('statusBar').style.color = 'white';
                document.getElementById('statusBar').innerHTML = '<i class="fas fa-times-circle"></i> Đơn hàng đã hết hạn';
                
                // Redirect after timeout
                setTimeout(() => {
                    window.location.href = '../cart.php';
                }, 3000);
            }
            timeLeft--;
        }
        
        // Check payment status
        function checkPayment() {
            fetch('check_payment.php?order_code=' + ORDER_CODE)
                .then(response => response.json())
                .then(data => {
                    console.log('Payment check:', data);
                    
                    // Display transactions
                    if (data.transactions && data.transactions.length > 0) {
                        displayTransactions(data.transactions);
                    }
                    
                    // Check if paid
                    if (data.status === 'success' && data.paid) {
                        clearInterval(countdownTimer);
                        clearInterval(checkTimer);
                        
                        document.getElementById('statusBar').className = 'status-bar status-success';
                        document.getElementById('statusBar').innerHTML = 
                            '<i class="fas fa-check-circle"></i> Thanh toán thành công! Đang chuyển hướng...';
                        
                        setTimeout(() => {
                            window.location.href = 'success_page.php?order_code=' + ORDER_CODE;
                        }, 2000);
                    }
                })
                .catch(error => {
                    console.error('Check payment error:', error);
                });
        }
        
        // Display transactions
        function displayTransactions(transactions) {
            const listDiv = document.getElementById('transactionList');
            const transDiv = document.getElementById('transactions');
            listDiv.style.display = 'block';
            
            const pattern = new RegExp('(?:Thanh\\s*Toan\\s*)?Don\\s*Hang\\s*' + ORDER_CODE, 'i');
            
            transDiv.innerHTML = transactions.map(trans => {
                const isMatched = pattern.test(trans.transaction_content) && 
                                 trans.amount_in >= AMOUNT;
                
                return `
                    <div class="transaction-item ${isMatched ? 'matched' : ''}">
                        <div class="transaction-content">
                            ${isMatched ? '<i class="fas fa-check-circle" style="color: #28a745;"></i> ' : ''}
                            ${trans.transaction_content}
                        </div>
                        <div class="transaction-amount">
                            ${new Intl.NumberFormat('vi-VN').format(trans.amount_in)} VNĐ
                        </div>
                        <div class="transaction-date">
                            <i class="far fa-clock"></i> ${trans.transaction_date}
                            ${trans.bank_brand_name ? ' - ' + trans.bank_brand_name : ''}
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        // Start timers
        countdownTimer = setInterval(updateTimer, 1000);
        checkTimer = setInterval(checkPayment, CHECK_INTERVAL);
        
        // Check immediately
        setTimeout(checkPayment, 2000);
    </script>
</body>
</html>
