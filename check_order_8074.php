<?php
/**
 * Quick Check Order 8074
 */
require_once 'config/database.php';

$order_code = '8074';

echo "<h1>Checking Order: {$order_code}</h1>";
echo "<style>body{font-family:monospace;padding:20px;background:#1e1e1e;color:#00ff00;} h1{color:#ffa500;} .success{color:#00ff00;} .error{color:#ff0000;} pre{background:#2d2d2d;padding:15px;border-radius:5px;}</style>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Check order
    echo "<h2>1. Database - don_hang</h2>";
    $stmt = $conn->prepare("SELECT * FROM don_hang WHERE order_code = ?");
    $stmt->execute(array($order_code));
    $order = $stmt->fetch();
    
    if ($order) {
        echo "<p class='success'>✅ Order found!</p>";
        echo "<pre>";
        echo "Order ID: " . $order['ma_donhang'] . "\n";
        echo "Amount: " . number_format($order['tong_tien']) . " VND\n";
        echo "Payment Status: " . $order['trangthai_thanhtoan'] . "\n";
        echo "Order Status: " . $order['trang_thai'] . "\n";
        echo "Payment Method: " . $order['phuongthuc_thanhtoan'] . "\n";
        echo "</pre>";
        
        if ($order['trangthai_thanhtoan'] == 'da_thanh_toan') {
            echo "<p class='success'>✅ ALREADY PAID! Should redirect to success page.</p>";
            echo "<p><a href='sepay-php-main/success_page.php?order_code={$order_code}' style='color:#00ff00;font-size:20px;'>→ GO TO SUCCESS PAGE</a></p>";
        } else {
            echo "<p class='error'>❌ NOT PAID YET</p>";
        }
        
        // Check transaction
        echo "<h2>2. Database - transactions</h2>";
        $stmt_trans = $conn->prepare("SELECT * FROM transactions WHERE ma_donhang = ?");
        $stmt_trans->execute(array($order['ma_donhang']));
        $trans = $stmt_trans->fetch();
        
        if ($trans) {
            echo "<p class='success'>✅ Transaction record found!</p>";
            echo "<pre>";
            echo "Transaction ID: " . ($trans['id'] ?? 'N/A') . "\n";
            echo "Content: " . ($trans['transaction_content'] ?? 'N/A') . "\n";
            echo "Amount In: " . number_format($trans['amount_in'] ?? 0) . " VND\n";
            echo "Processed: " . ($trans['is_processed'] ? 'YES' : 'NO') . "\n";
            echo "Bank: " . ($trans['bank_brand_name'] ?? 'N/A') . "\n";
            echo "Date: " . ($trans['transaction_date'] ?? 'N/A') . "\n";
            echo "</pre>";
        } else {
            echo "<p class='error'>❌ No transaction record</p>";
        }
        
        // Call SePay API
        echo "<h2>3. SePay API Check</h2>";
        
        define('SEPAY_API_KEY', '7O2MCQT0UISAX1BNW3KGZFHKESPOJOC4HRUE1MEBXDBABIELFARZWUL68FNYV2MD');
        define('SEPAY_ACCOUNT_NUMBER', '0981523130');
        
        $api_url = 'https://my.sepay.vn/userapi/transactions/list?account_number=' . SEPAY_ACCOUNT_NUMBER . '&limit=20';
        
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . SEPAY_API_KEY,
                'Content-Type: application/json'
            ),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 15
        ));
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code == 200 && $response) {
            echo "<p class='success'>✅ API Connected: HTTP {$http_code}</p>";
            $data = json_decode($response, true);
            
            if (isset($data['transactions'])) {
                $transactions = $data['transactions'];
                echo "<p>Found " . count($transactions) . " recent transactions</p>";
                
                // Search for DH8074
                $pattern = '/DH\s*' . $order_code . '/i';
                $matched = null;
                
                echo "<h3>Searching for: DH{$order_code}</h3>";
                echo "<table border='1' cellpadding='10' style='color:#00ff00;border-color:#444;'>";
                echo "<tr style='color:#ffa500;'><th>Content</th><th>Amount</th><th>Date</th><th>Match?</th></tr>";
                
                foreach ($transactions as $t) {
                    $content = $t['transaction_content'] ?? '';
                    $amount = $t['amount_in'] ?? 0;
                    $date = $t['transaction_date'] ?? '';
                    $is_match = preg_match($pattern, $content);
                    
                    $row_color = '';
                    if ($is_match && $amount >= floatval($order['tong_tien'])) {
                        $matched = $t;
                        $row_color = 'background:#1a4d1a;';
                    }
                    
                    echo "<tr style='{$row_color}'>";
                    echo "<td>" . htmlspecialchars($content) . "</td>";
                    echo "<td>" . number_format($amount) . " VND</td>";
                    echo "<td>{$date}</td>";
                    echo "<td>" . ($is_match ? '✅ YES' : '❌ NO') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
                
                if ($matched) {
                    echo "<h3 class='success'>🎉 FOUND MATCHING TRANSACTION!</h3>";
                    echo "<p>Now updating database...</p>";
                    
                    // Update database
                    $update_order = $conn->prepare("
                        UPDATE don_hang 
                        SET trangthai_thanhtoan = 'da_thanh_toan',
                            trang_thai = 'xac_nhan',
                            thanh_toan = 'đã thanh toán',
                            update_at = NOW()
                        WHERE ma_donhang = ?
                    ");
                    $update_order->execute(array($order['ma_donhang']));
                    
                    $update_trans = $conn->prepare("
                        UPDATE transactions 
                        SET id = ?,
                            account_number = ?,
                            amount_in = ?,
                            transaction_content = ?,
                            bank_brand_name = ?,
                            transaction_date = ?,
                            is_processed = 1
                        WHERE ma_donhang = ?
                    ");
                    $update_trans->execute(array(
                        $matched['id'] ?? '',
                        $matched['account_number'] ?? '',
                        $matched['amount_in'] ?? 0,
                        $matched['transaction_content'] ?? '',
                        $matched['bank_brand_name'] ?? '',
                        $matched['transaction_date'] ?? date('Y-m-d H:i:s'),
                        $order['ma_donhang']
                    ));
                    
                    // Delete cart
                    $delete_cart = $conn->prepare("DELETE FROM gio_hang WHERE ma_user = ?");
                    $delete_cart->execute(array($order['ma_user']));
                    
                    echo "<p class='success'>✅ DATABASE UPDATED!</p>";
                    echo "<p class='success'>✅ CART CLEARED!</p>";
                    echo "<h2><a href='sepay-php-main/success_page.php?order_code={$order_code}' style='color:#00ff00;'>→→→ CLICK HERE TO GO TO SUCCESS PAGE →→→</a></h2>";
                    echo "<script>setTimeout(function(){ window.location.href='sepay-php-main/success_page.php?order_code={$order_code}'; }, 3000);</script>";
                    echo "<p>Redirecting in 3 seconds...</p>";
                } else {
                    echo "<p class='error'>❌ No matching transaction found!</p>";
                    echo "<p>Please check:</p>";
                    echo "<ul>";
                    echo "<li>Transfer content must be: <strong>DH{$order_code}</strong></li>";
                    echo "<li>Amount must be at least: <strong>" . number_format($order['tong_tien']) . " VND</strong></li>";
                    echo "</ul>";
                }
            } else {
                echo "<p class='error'>❌ No transactions in API response</p>";
            }
        } else {
            echo "<p class='error'>❌ API Error: HTTP {$http_code}</p>";
        }
        
    } else {
        echo "<p class='error'>❌ Order not found!</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>ERROR: " . $e->getMessage() . "</p>";
}
?>
