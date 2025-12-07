<?php
/**
 * SePay Webhook Handler & Auto Sync Worker
 * File này sẽ chạy tự động để kiểm tra giao dịch từ SePay API
 * Có thể chạy bằng cron job hoặc gọi qua AJAX
 */

require_once 'config/database.php';

// Cấu hình SePay
define('SEPAY_API_URL', 'https://my.sepay.vn/userapi/transactions/list');
define('SEPAY_API_TOKEN', '7O2MCQT0UISAX1BNW3KGZFHKESPOJOC4HRUE1MEBXDBABIELFARZWUL68FNYV2MD');
define('SEPAY_ACCOUNT_NUMBER', '0981523130'); // Số tài khoản của bạn

// Khởi tạo database
$db = new Database();
$conn = $db->getConnection();

// Log function
function logMessage($message) {
    $logFile = __DIR__ . '/logs/sepay_sync.log';
    $dir = dirname($logFile);
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

try {
    logMessage("=== Starting SePay sync ===");

    // Gọi API SePay để lấy danh sách giao dịch
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, SEPAY_API_URL . '?account_number=' . SEPAY_ACCOUNT_NUMBER . '&limit=50');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . SEPAY_API_TOKEN,
        'Content-Type: application/json'
    ));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        logMessage("Error: API returned HTTP code $httpCode");
        die(json_encode(['status' => 'error', 'message' => 'API connection failed']));
    }

    $data = json_decode($response, true);
    
    if (!isset($data['transactions']) || !is_array($data['transactions'])) {
        logMessage("Error: No transactions in response");
        die(json_encode(['status' => 'error', 'message' => 'No transactions found']));
    }

    $transactions = $data['transactions'];
    $processedCount = 0;
    $matchedCount = 0;

    logMessage("Found " . count($transactions) . " transactions from SePay API");

    foreach ($transactions as $trans) {
        $transId = isset($trans['id']) ? $trans['id'] : null;
        $accountNumber = isset($trans['account_number']) ? $trans['account_number'] : '';
        $amountIn = isset($trans['amount_in']) ? (float)$trans['amount_in'] : 0;
        $transContent = isset($trans['transaction_content']) ? $trans['transaction_content'] : '';
        $bankBrand = isset($trans['bank_brand_name']) ? $trans['bank_brand_name'] : '';
        $transDate = isset($trans['transaction_date']) ? $trans['transaction_date'] : date('Y-m-d H:i:s');

        if (!$transId || $amountIn <= 0) {
            continue;
        }

        // Kiểm tra xem transaction đã tồn tại chưa
        $checkStmt = $conn->prepare("SELECT id FROM transactions WHERE id = ?");
        $checkStmt->execute(array($transId));
        
        if ($checkStmt->rowCount() == 0) {
            // Lưu transaction mới vào database
            $insertTransStmt = $conn->prepare(
                "INSERT INTO transactions 
                (id, ma_donhang, account_number, amount_in, transaction_content, bank_brand_name, transaction_date, is_processed, created_at)
                VALUES (?, 0, ?, ?, ?, ?, ?, 0, NOW())"
            );
            $insertTransStmt->execute(array(
                $transId,
                $accountNumber,
                $amountIn,
                $transContent,
                $bankBrand,
                $transDate
            ));
            logMessage("Saved new transaction: $transId | Amount: $amountIn VND");
        }

        // Tìm mã đơn hàng trong nội dung chuyển khoản
        // Pattern: "Thanh Toan Don Hang XXXX" hoặc "Don Hang XXXX"
        if (preg_match('/Don Hang (\d{4})/i', $transContent, $matches)) {
            $orderCode = (int)$matches[1];
            
            logMessage("Found order code: $orderCode in transaction $transId");

            // Tìm đơn hàng với mã đơn hàng và trạng thái chưa thanh toán
            $orderStmt = $conn->prepare(
                "SELECT ma_donhang, tong_tien, trangthai_thanhtoan 
                FROM don_hang 
                WHERE ma_donhang = ? AND trangthai_thanhtoan = 'chua_thanh_toan'"
            );
            $orderStmt->execute(array($orderCode));
            $order = $orderStmt->fetch(PDO::FETCH_ASSOC);

            if ($order) {
                $orderAmount = (float)$order['tong_tien'];
                
                // Kiểm tra số tiền khớp (cho phép sai số 1000 VND)
                if (abs($amountIn - $orderAmount) <= 1000) {
                    logMessage("Amount matched! Order: $orderAmount, Transaction: $amountIn");
                    
                    // Bắt đầu transaction DB
                    $conn->beginTransaction();
                    
                    try {
                        // Cập nhật trạng thái đơn hàng
                        $updateOrderStmt = $conn->prepare(
                            "UPDATE don_hang 
                            SET trangthai_thanhtoan = 'da_thanh_toan',
                                thanh_toan = 'đã thanh toán',
                                trang_thai = 'xac_nhan',
                                ngay_thanhtoan = NOW()
                            WHERE ma_donhang = ?"
                        );
                        $updateOrderStmt->execute(array($orderCode));

                        // Cập nhật transaction
                        $updateTransStmt = $conn->prepare(
                            "UPDATE transactions 
                            SET ma_donhang = ?, is_processed = 1
                            WHERE id = ?"
                        );
                        $updateTransStmt->execute(array($orderCode, $transId));

                        // Xóa giỏ hàng của user
                        $getUserStmt = $conn->prepare("SELECT ma_user FROM don_hang WHERE ma_donhang = ?");
                        $getUserStmt->execute(array($orderCode));
                        $userRow = $getUserStmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($userRow) {
                            $deleteCartStmt = $conn->prepare("DELETE FROM gio_hang WHERE ma_user = ?");
                            $deleteCartStmt->execute(array($userRow['ma_user']));
                        }

                        $conn->commit();
                        
                        $matchedCount++;
                        logMessage("SUCCESS: Order #$orderCode marked as PAID and cart cleared");
                        
                    } catch (Exception $e) {
                        $conn->rollBack();
                        logMessage("ERROR in transaction: " . $e->getMessage());
                    }
                    
                } else {
                    logMessage("Amount mismatch! Order: $orderAmount, Transaction: $amountIn");
                }
            } else {
                logMessage("Order #$orderCode not found or already paid");
            }
        }

        $processedCount++;
    }

    logMessage("=== Sync completed: Processed $processedCount, Matched $matchedCount ===");

    echo json_encode([
        'status' => 'success',
        'processed' => $processedCount,
        'matched' => $matchedCount,
        'message' => 'Sync completed successfully'
    ]);

} catch (Exception $e) {
    logMessage("FATAL ERROR: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
