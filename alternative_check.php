<?php
/**
 * Alternative API Check using file_get_contents
 */

define('SEPAY_API_KEY', '7O2MCQT0UISAX1BNW3KGZFHKESPOJOC4HRUE1MEBXDBABIELFARZWUL68FNYV2MD');
define('SEPAY_ACCOUNT_NUMBER', '0981523130');

echo "<h1>Alternative API Test (file_get_contents)</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} pre{background:#f5f5f5;padding:15px;border-radius:5px;}</style>";

$api_url = 'https://my.sepay.vn/userapi/transactions/list?account_number=' . SEPAY_ACCOUNT_NUMBER . '&limit=10';

echo "<p>Calling: <code>{$api_url}</code></p>";

$context = stream_context_create(array(
    'http' => array(
        'method' => 'GET',
        'header' => "Authorization: Bearer " . SEPAY_API_KEY . "\r\n" .
                    "Content-Type: application/json\r\n"
    ),
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false
    )
));

$response = @file_get_contents($api_url, false, $context);

if ($response === false) {
    echo "<p class='error'>❌ Request failed!</p>";
    $error = error_get_last();
    echo "<pre>" . print_r($error, true) . "</pre>";
} else {
    echo "<p class='success'>✅ Request successful!</p>";
    
    $data = json_decode($response, true);
    
    if (isset($data['transactions']) && is_array($data['transactions'])) {
        echo "<p>Found " . count($data['transactions']) . " transactions</p>";
        
        echo "<table border='1' cellpadding='10' style='border-collapse:collapse;width:100%;'>";
        echo "<tr style='background:#088178;color:white;'><th>Content</th><th>Amount</th><th>Date</th><th>Match DH8074?</th></tr>";
        
        $found = false;
        foreach ($data['transactions'] as $t) {
            $content = isset($t['transaction_content']) ? $t['transaction_content'] : '';
            $amount = isset($t['amount_in']) ? $t['amount_in'] : 0;
            $date = isset($t['transaction_date']) ? $t['transaction_date'] : '';
            $is_match = preg_match('/DH\s*8074/i', $content);
            
            $bg = $is_match ? 'background:#d4edda;' : '';
            
            echo "<tr style='{$bg}'>";
            echo "<td>" . htmlspecialchars($content) . "</td>";
            echo "<td>" . number_format($amount) . " VND</td>";
            echo "<td>{$date}</td>";
            echo "<td>" . ($is_match ? "<strong style='color:green;'>✅ YES</strong>" : "❌") . "</td>";
            echo "</tr>";
            
            if ($is_match) {
                $found = $t;
            }
        }
        echo "</table>";
        
        if ($found) {
            echo "<h2 class='success'>🎉 FOUND Transaction DH8074!</h2>";
            echo "<pre>" . print_r($found, true) . "</pre>";
            
            echo "<p><strong>Now updating database...</strong></p>";
            
            // Update database
            require_once 'config/database.php';
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("SELECT * FROM don_hang WHERE order_code = '8074'");
            $stmt->execute();
            $order = $stmt->fetch();
            
            if ($order) {
                // Update don_hang
                $update_order = $conn->prepare("
                    UPDATE don_hang 
                    SET trangthai_thanhtoan = 'da_thanh_toan',
                        trang_thai = 'xac_nhan',
                        thanh_toan = 'đã thanh toán',
                        update_at = NOW()
                    WHERE ma_donhang = ?
                ");
                $update_order->execute(array($order['ma_donhang']));
                
                // Update transactions
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
                    isset($found['id']) ? $found['id'] : '',
                    isset($found['account_number']) ? $found['account_number'] : '',
                    isset($found['amount_in']) ? $found['amount_in'] : 0,
                    isset($found['transaction_content']) ? $found['transaction_content'] : '',
                    isset($found['bank_brand_name']) ? $found['bank_brand_name'] : '',
                    isset($found['transaction_date']) ? $found['transaction_date'] : date('Y-m-d H:i:s'),
                    $order['ma_donhang']
                ));
                
                // Delete cart
                $delete_cart = $conn->prepare("DELETE FROM gio_hang WHERE ma_user = ?");
                $delete_cart->execute(array($order['ma_user']));
                
                echo "<h2 class='success'>✅ DATABASE UPDATED!</h2>";
                echo "<p>✓ Payment status: PAID</p>";
                echo "<p>✓ Transaction recorded</p>";
                echo "<p>✓ Cart cleared</p>";
                
                echo "<h2><a href='sepay-php-main/success_page.php?order_code=8074' style='background:#088178;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;display:inline-block;font-size:18px;'>→ GO TO SUCCESS PAGE</a></h2>";
                
                echo "<script>setTimeout(function(){ window.location.href='sepay-php-main/success_page.php?order_code=8074'; }, 3000);</script>";
                echo "<p>Redirecting in 3 seconds...</p>";
            } else {
                echo "<p class='error'>Order not found in database!</p>";
            }
            
        } else {
            echo "<p class='error'>❌ No transaction with content 'DH8074' found</p>";
            echo "<p>Please check your bank transfer. Content should be: <strong>DH8074</strong></p>";
        }
        
    } else {
        echo "<p class='error'>Invalid response format</p>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }
}
?>
