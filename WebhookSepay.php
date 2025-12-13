<?php
/**
 * Webhook Controller cho KLTN_AKISTORE - Nhận thông báo từ SePay
 * URL: https://nodose-jamika-astylar.ngrok-free.dev/KLTN_AKISTORE/index.php?controller=webhook_sepay&action=handle
 * API Key: 7O2MCQT0UISAX1BNW3KGZFHKESPOJOC4HRUE1MEBXDBABIELFARZWUL68FNYV2MD
 */

require_once __DIR__ . '/config/database.php';

class WebhookSepayController
{
    private $conn;
    private const API_KEY = '7O2MCQT0UISAX1BNW3KGZFHKESPOJOC4HRUE1MEBXDBABIELFARZWUL68FNYV2MD';
    
    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Test endpoint - Hiển thị thông tin kết nối khi truy cập qua browser
     */
    public function test()
    {
        $this->displayTestPage();
    }

    /**
     * Xử lý webhook từ SePay khi khách hàng thanh toán
     */
    public function handle()
    {
        // Nếu là GET request, hiển thị trang test
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->displayTestPage();
            return;
        }

        try {
            $this->logWebhook('=== WEBHOOK RECEIVED ===');
            $this->logWebhook('IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            $this->logWebhook('Time: ' . date('Y-m-d H:i:s'));

            // Verify API Key từ header
            $authHeader = $this->getAuthHeader();
            
            if (!$this->verifyApiKey($authHeader)) {
                $this->logWebhook('❌ UNAUTHORIZED - Invalid API Key: ' . $authHeader);
                $this->respondError('Unauthorized', 401);
                return;
            }

            $this->logWebhook('✅ API Key verified');

            // Lấy dữ liệu JSON từ SePay
            $json = file_get_contents('php://input');
            $this->logWebhook('Raw data: ' . $json);
            
            $data = json_decode($json, true);

            if (!$data) {
                $this->logWebhook('❌ Invalid JSON data');
                $this->respondError('Invalid JSON', 400);
                return;
            }

            // Parse dữ liệu từ SePay
            $sepayId = (int)($data['id'] ?? 0);
            $content = $data['content'] ?? '';
            $code = $data['code'] ?? null;
            $transferType = $data['transferType'] ?? 'in';
            $transferAmount = (float)($data['transferAmount'] ?? 0);
            $accountNumber = $data['accountNumber'] ?? '';
            $subAccName = $data['subAccName'] ?? '';
            $bankBrandName = $data['bankBrandName'] ?? '';
            $transactionDate = $data['transactionDate'] ?? date('Y-m-d H:i:s');

            $this->logWebhook("Transaction ID: {$sepayId}");
            $this->logWebhook("Content: {$content}");
            $this->logWebhook("Amount: {$transferAmount} VNĐ");
            $this->logWebhook("Type: {$transferType}");

            // Validate dữ liệu cơ bản
            if ($sepayId <= 0 || $transferAmount <= 0) {
                $this->logWebhook('❌ Invalid transaction data');
                $this->respondError('Invalid transaction data', 400);
                return;
            }

            // Chỉ xử lý giao dịch TIỀN VÀO (khách hàng thanh toán)
            if ($transferType !== 'in') {
                $this->logWebhook("⚠️ Ignored - Transfer type: {$transferType}");
                $this->respondSuccess('Ignored (not incoming transfer)');
                return;
            }

            // Xử lý thanh toán đơn hàng
            $this->processOrderPayment($sepayId, $content, $code, $transferAmount, $data);

        } catch (PDOException $e) {
            $this->logWebhook('❌ Database error: ' . $e->getMessage());
            $this->respondError('Database error', 500);
        } catch (Exception $e) {
            $this->logWebhook('❌ Error: ' . $e->getMessage());
            $this->respondError('Internal error', 500);
        }
    }

    /**
     * Xử lý thanh toán đơn hàng
     */
    private function processOrderPayment($sepayId, $content, $code, $transferAmount, $webhookData)
    {
        // Extract mã đơn hàng từ nội dung chuyển khoản
        $orderCode = $this->extractOrderCode($content, $code);

        if (!$orderCode) {
            $this->logWebhook('⚠️ No order code found in: ' . $content);
            $this->respondSuccess('No order code found');
            return;
        }

        $this->logWebhook("📦 Order code extracted: DH{$orderCode} => ID: {$orderCode}");

        // Kiểm tra giao dịch đã được xử lý chưa
        $stmt = $this->conn->prepare("
            SELECT id FROM transactions 
            WHERE transaction_content LIKE :sepay_id
            LIMIT 1
        ");
        $stmt->execute(['sepay_id' => "%TX: {$sepayId}%"]);
        
        if ($stmt->fetch()) {
            $this->logWebhook("⚠️ Transaction {$sepayId} already processed");
            $this->respondSuccess('Transaction already processed');
            return;
        }

        // Tìm đơn hàng trong database
        $stmt = $this->conn->prepare("
            SELECT ma_donhang, tong_tien, trangthai_thanhtoan, ma_user, trang_thai
            FROM don_hang
            WHERE ma_donhang = :ma_donhang
            LIMIT 1
        ");
        $stmt->execute(['ma_donhang' => $orderCode]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            $this->logWebhook("❌ Order {$orderCode} not found in database");
            $this->respondSuccess('Order not found');
            return;
        }

        $this->logWebhook("✅ Order found - Total: {$order['tong_tien']} VNĐ, Status: {$order['trangthai_thanhtoan']}");

        // Kiểm tra đơn hàng đã thanh toán chưa
        if ($order['trangthai_thanhtoan'] === 'da_thanh_toan') {
            $this->logWebhook("⚠️ Order {$orderCode} already paid");
            $this->respondSuccess('Order already paid');
            return;
        }

        // Kiểm tra số tiền khớp (tolerance 1%)
        $expectedAmount = (float)$order['tong_tien'];
        $amountDiff = abs($transferAmount - $expectedAmount);
        $tolerance = $expectedAmount * 0.01; // 1%

        if ($amountDiff > $tolerance) {
            $this->logWebhook(sprintf(
                "❌ Amount mismatch - Expected: %s VNĐ, Received: %s VNĐ, Diff: %s VNĐ",
                number_format($expectedAmount, 0, ',', '.'),
                number_format($transferAmount, 0, ',', '.'),
                number_format($amountDiff, 0, ',', '.')
            ));
            $this->respondSuccess('Amount mismatch - Manual review required');
            return;
        }

        $this->logWebhook("✅ Amount verified - Match!");

        // Cập nhật trạng thái đơn hàng
        try {
            $this->conn->beginTransaction();

            // Cập nhật đơn hàng
            $updateStmt = $this->conn->prepare("
                UPDATE don_hang
                SET 
                    
                    trangthai_thanhtoan = 'da_thanh_toan',
                    thanh_toan = 'Đã thanh toán',
                    updated_at = NOW()
                WHERE ma_donhang = :ma_donhang
            ");
            
            $updateStmt->execute(['ma_donhang' => $orderCode]);
            
            $this->logWebhook("✅ Order status updated to 'da_thanh_toan'");

            // Lưu thông tin giao dịch vào bảng transactions
            $this->saveTransaction($orderCode, $sepayId, $transferAmount, $content, $webhookData);

            $this->conn->commit();

            $this->logWebhook("🎉 SUCCESS - Order {$orderCode} payment completed!");
            $this->logWebhook("Amount: " . number_format($transferAmount, 0, ',', '.') . " VNĐ");
            $this->logWebhook("Transaction ID: {$sepayId}");
            
            $this->respondSuccess('Payment processed successfully', 200);

        } catch (Exception $e) {
            $this->conn->rollBack();
            $this->logWebhook('❌ Error in transaction: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lưu thông tin giao dịch vào bảng transactions
     */
    private function saveTransaction($orderCode, $sepayId, $amount, $content, $webhookData)
    {
        try {
            $accountNumber = $webhookData['accountNumber'] ?? '';
            $accountName = $webhookData['subAccName'] ?? '';
            $bankBrandName = $webhookData['bankBrandName'] ?? '';
            $bankCode = $webhookData['gateway'] ?? '';
            $transactionDate = $webhookData['transactionDate'] ?? date('Y-m-d H:i:s');
            
            // Kiểm tra xem bảng transactions có cột account_name không
            $checkStmt = $this->conn->prepare("SHOW COLUMNS FROM transactions LIKE 'account_name'");
            $checkStmt->execute();
            $hasAccountName = $checkStmt->fetch() !== false;
            
            if ($hasAccountName) {
                // Bảng có cột account_name
                $stmt = $this->conn->prepare("
                    INSERT INTO transactions (
                        ma_donhang, 
                        account_number, 
                       
                        amount_in, 
                        transaction_content, 
                        bank_brand_name,
                        transaction_date, 
                        is_processed,
                        created_at,
                         account_name
                    ) VALUES (
                        :ma_donhang, 
                        :account_number, 
                        
                        :amount_in, 
                        :transaction_content, 
                        :bank_brand_name,
                        :transaction_date, 
                        1,
                        NOW(),
                        :account_name
                    )
                ");
                
                $stmt->execute([
                    'ma_donhang' => $orderCode,
                    'account_number' => $accountNumber,
                    'account_name' => $accountName,
                    'amount_in' => $amount,
                    'transaction_content' => $content . " (TX: {$sepayId})",
                    'bank_brand_name' => $bankBrandName,
                    'transaction_date' => $transactionDate
                ]);
            } else {
                // Bảng không có cột account_name (dùng cấu trúc cũ)
                $stmt = $this->conn->prepare("
                    INSERT INTO transactions (
                        ma_donhang, 
                        account_number, 
                        amount_in, 
                        transaction_content, 
                        bank_brand_name,
                        transaction_date, 
                        is_processed,
                        created_at
                    ) VALUES (
                        :ma_donhang, 
                        :account_number, 
                        :amount_in, 
                        :transaction_content, 
                        :bank_brand_name,
                        :transaction_date, 
                        1,
                        NOW()
                    )
                ");
                
                $stmt->execute([
                    'ma_donhang' => $orderCode,
                    'account_number' => $accountNumber,
                    'amount_in' => $amount,
                    'transaction_content' => $content . " (TX: {$sepayId})",
                    'bank_brand_name' => $bankBrandName,
                    'transaction_date' => $transactionDate
                ]);
            }
            
            $this->logWebhook("✅ Transaction saved to database");
            
        } catch (PDOException $e) {
            $this->logWebhook("⚠️ Error saving transaction: " . $e->getMessage());
            // Không throw exception để không làm gián đoạn quá trình cập nhật đơn hàng
        }
    }

    /**
     * Extract mã đơn hàng từ nội dung chuyển khoản
     * Format: DH0001, DH0123, DH1111, etc.
     */
    private function extractOrderCode($content, $sepayCode)
    {
        // Nếu SePay đã nhận diện được code
        if (!empty($sepayCode)) {
            if (preg_match('/^DH(\d+)$/i', $sepayCode, $matches)) {
                return (int)$matches[1]; // DH0123 -> 123, DH1111 -> 1111
            }
            if (is_numeric($sepayCode)) {
                return (int)$sepayCode;
            }
        }

        // Tìm pattern trong content: DH + số
        if (preg_match('/\bDH(\d+)\b/i', $content, $matches)) {
            return (int)$matches[1]; // Loại bỏ số 0 đầu: DH0001 -> 1
        }

        return null;
    }

    /**
     * Verify API Key từ header
     */
    private function verifyApiKey($authHeader)
    {
        // Format: "Apikey YOUR_API_KEY"
        if (preg_match('/^Apikey\s+(.+)$/i', $authHeader, $matches)) {
            $receivedKey = trim($matches[1]);
            return $receivedKey === self::API_KEY;
        }
        return false;
    }

    /**
     * Lấy Authorization header
     */
    private function getAuthHeader()
    {
        // Thử nhiều cách lấy header
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return $_SERVER['HTTP_AUTHORIZATION'];
        }
        
        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }
        
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            return $headers['Authorization'] ?? '';
        }
        
        return '';
    }

    /**
     * Ghi log webhook
     */
    private function logWebhook($message)
    {
        $logFile = __DIR__ . '/../logs/sepay_webhook.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}\n";
        @file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    /**
     * Trả về response success cho SePay
     */
    private function respondSuccess($message = 'OK', $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Trả về response error cho SePay
     */
    private function respondError($message, $statusCode = 400)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Hiển thị trang test thông tin kết nối SePay
     */
    private function displayTestPage()
    {
        // Lấy thông tin ngân hàng từ database
        $bankInfo = $this->getBankInfo();
        
        // Lấy thống kê giao dịch
        $stats = $this->getTransactionStats();
        
        // Test kết nối SePay API
        $apiStatus = $this->testSepayConnection();
        
        header('Content-Type: text/html; charset=utf-8');
        ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SePay Webhook - KLTN_AKISTORE</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .card h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 1.5em;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: 600;
            color: #666;
        }
        .value {
            color: #333;
            font-family: 'Courier New', monospace;
        }
        .status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
        }
        .status.warning {
            background: #fff3cd;
            color: #856404;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .stat-box {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            color: white;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 0.9em;
            opacity: 0.9;
        }
        .code-block {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            margin: 10px 0;
            overflow-x: auto;
        }
        .code-block code {
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            color: #333;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #764ba2;
        }
        .timestamp {
            text-align: center;
            color: white;
            margin-top: 20px;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 SePay Webhook - KLTN_AKISTORE</h1>
            <p>Webhook nhận thông báo thanh toán tự động từ SePay</p>
        </div>

        <div class="grid">
            <!-- Thông tin kết nối -->
            <div class="card">
                <h2>📡 Thông Tin Kết Nối</h2>
                <div class="info-row">
                    <span class="label">Trạng thái API:</span>
                    <span class="status <?= $apiStatus['status'] ?>"><?= $apiStatus['message'] ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Webhook URL:</span>
                    <span class="value" style="font-size: 0.8em; word-break: break-all;">
                        <?= htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="label">API Key:</span>
                    <span class="value"><?= substr(self::API_KEY, 0, 20) ?>...</span>
                </div>
                <div class="info-row">
                    <span class="label">Method:</span>
                    <span class="value">POST</span>
                </div>
                <div class="info-row">
                    <span class="label">Server Time:</span>
                    <span class="value"><?= date('Y-m-d H:i:s') ?></span>
                </div>
            </div>

            <!-- Thông tin ngân hàng -->
            <div class="card">
                <h2>🏦 Thông Tin Ngân Hàng</h2>
                <?php if ($bankInfo): ?>
                    <div class="info-row">
                        <span class="label">Ngân hàng:</span>
                        <span class="value"><?= htmlspecialchars($bankInfo['bank_name'] ?? 'N/A') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Số tài khoản:</span>
                        <span class="value"><?= htmlspecialchars($bankInfo['account_number'] ?? 'N/A') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Chủ tài khoản:</span>
                        <span class="value"><?= htmlspecialchars($bankInfo['account_name'] ?? 'N/A') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Chi nhánh:</span>
                        <span class="value"><?= htmlspecialchars($bankInfo['branch'] ?? 'N/A') ?></span>
                    </div>
                <?php else: ?>
                    <div class="info-row">
                        <span class="status warning">⚠️ Chưa cấu hình thông tin ngân hàng</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Thống kê giao dịch -->
        <div class="card">
            <h2>📊 Thống Kê Giao Dịch</h2>
            <div class="stats">
                <div class="stat-box">
                    <div class="stat-number"><?= number_format($stats['total_orders']) ?></div>
                    <div class="stat-label">Tổng đơn hàng</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?= number_format($stats['paid_orders']) ?></div>
                    <div class="stat-label">Đã thanh toán</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?= number_format($stats['pending_orders']) ?></div>
                    <div class="stat-label">Chờ thanh toán</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?= number_format($stats['total_amount']) ?> ₫</div>
                    <div class="stat-label">Tổng doanh thu</div>
                </div>
            </div>
        </div>

        <!-- Hướng dẫn cấu hình -->
        <div class="card">
            <h2>⚙️ Hướng Dẫn Cấu Hình SePay</h2>
            <p style="margin-bottom: 15px;">Để kết nối với SePay, làm theo các bước sau:</p>
            
            <h3 style="color: #667eea; margin: 15px 0 10px 0;">1. Cấu hình Webhook trên SePay Dashboard:</h3>
            <div class="code-block">
                <strong>Webhook URL:</strong><br>
                <code><?= htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/KLTN_AKISTORE/index.php?controller=webhook_sepay&action=handle') ?></code>
            </div>
            
            <div class="code-block">
                <strong>HTTP Header:</strong><br>
                <code>Authorization: Apikey <?= self::API_KEY ?></code>
            </div>

            <h3 style="color: #667eea; margin: 15px 0 10px 0;">2. Test Webhook với CURL:</h3>
            <div class="code-block">
                <code style="display: block; white-space: pre-wrap;">curl -X POST "<?= htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" \
-H "Authorization: Apikey <?= self::API_KEY ?>" \
-H "Content-Type: application/json" \
-d '{
  "id": 123456,
  "content": "Thanh toan don hang DH0001",
  "transferAmount": 100000,
  "transferType": "in",
  "accountNumber": "1234567890",
  "subAccName": "NGUYEN VAN A",
  "bankBrandName": "Vietcombank"
}'</code>
            </div>

            <h3 style="color: #667eea; margin: 15px 0 10px 0;">3. Kiểm tra log:</h3>
            <p>Xem file log tại: <code>logs/sepay_webhook.log</code></p>
        </div>

        <div class="timestamp">
            Last updated: <?= date('d/m/Y H:i:s') ?>
        </div>
    </div>
</body>
</html>
        <?php
        exit;
    }

    /**
     * Lấy thông tin ngân hàng từ database
     */
    private function getBankInfo()
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM bank_config WHERE is_active = 1 LIMIT 1");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Lấy thống kê giao dịch
     */
    private function getTransactionStats()
    {
        try {
            $stats = [
                'total_orders' => 0,
                'paid_orders' => 0,
                'pending_orders' => 0,
                'total_amount' => 0
            ];

            // Tổng đơn hàng
            $stmt = $this->conn->query("SELECT COUNT(*) as total FROM don_hang");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_orders'] = $result['total'] ?? 0;

            // Đơn đã thanh toán
            $stmt = $this->conn->query("SELECT COUNT(*) as paid FROM don_hang WHERE trangthai_thanhtoan = 'da_thanh_toan'");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['paid_orders'] = $result['paid'] ?? 0;

            // Đơn chờ thanh toán
            $stmt = $this->conn->query("SELECT COUNT(*) as pending FROM don_hang WHERE trangthai_thanhtoan = 'chua_thanh_toan'");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['pending_orders'] = $result['pending'] ?? 0;

            // Tổng doanh thu
            $stmt = $this->conn->query("SELECT SUM(tong_tien) as total_amount FROM don_hang WHERE trangthai_thanhtoan = 'da_thanh_toan'");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_amount'] = $result['total_amount'] ?? 0;

            return $stats;
        } catch (Exception $e) {
            return [
                'total_orders' => 0,
                'paid_orders' => 0,
                'pending_orders' => 0,
                'total_amount' => 0
            ];
        }
    }

    /**
     * Test kết nối với SePay API
     */
    private function testSepayConnection()
    {
        try {
            $apiKey = self::API_KEY;
            
            // Test endpoint để lấy danh sách giao dịch
            $url = 'https://my.sepay.vn/userapi/transactions/list';
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json'
                ],
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                return [
                    'status' => 'success',
                    'message' => '✅ Kết nối thành công'
                ];
            } else {
                return [
                    'status' => 'warning',
                    'message' => '⚠️ API Key chưa được cấu hình đúng'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => '❌ Lỗi kết nối: ' . $e->getMessage()
            ];
        }
    }
}
