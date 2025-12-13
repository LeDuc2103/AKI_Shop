<?php
/**
 * Webhook Controller cho KLTN_AKISTORE - Nhận thông báo từ SePay
 * URL: https://nodose-jamika-astylar.ngrok-free.dev/KLTN_AKISTORE/index.php?controller=webhook_sepay&action=handle
 * API Key: 7O2MCQT0UISAX1BNW3KGZFHKESPOJOC4HRUE1MEBXDBABIELFARZWUL68FNYV2MD
 */

require_once __DIR__ . '/../config/database.php';

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
     * Xử lý webhook từ SePay khi khách hàng thanh toán
     */
    public function handle()
    {
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
                    trang_thai = 'Da_thanh_toan',
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
                        account_name,
                        amount_in, 
                        transaction_content, 
                        bank_brand_name,
                        transaction_date, 
                        is_processed,
                        created_at
                    ) VALUES (
                        :ma_donhang, 
                        :account_number, 
                        :account_name,
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
}
