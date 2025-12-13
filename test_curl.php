<?php
/**
 * Test CURL và SePay API
 */

echo "<h1>Test CURL Configuration</h1>";
echo "<style>body{font-family:Arial;padding:20px;} pre{background:#f5f5f5;padding:15px;border-radius:5px;} .success{color:green;} .error{color:red;}</style>";

// 1. Check if CURL is enabled
echo "<h2>1. Check CURL Extension</h2>";
if (function_exists('curl_version')) {
    $version = curl_version();
    echo "<p class='success'>✅ CURL is enabled!</p>";
    echo "<pre>";
    echo "Version: " . $version['version'] . "\n";
    echo "SSL Version: " . $version['ssl_version'] . "\n";
    echo "</pre>";
} else {
    echo "<p class='error'>❌ CURL is NOT enabled!</p>";
    echo "<p>Please enable curl extension in php.ini</p>";
    exit;
}

// 2. Test simple CURL request
echo "<h2>2. Test Simple CURL (Google)</h2>";
$ch = curl_init('https://www.google.com');
curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT => 10
));
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($http_code == 200) {
    echo "<p class='success'>✅ CURL works! HTTP {$http_code}</p>";
} else {
    echo "<p class='error'>❌ CURL failed! HTTP {$http_code}</p>";
    echo "<p>Error: {$error}</p>";
}

// 3. Test SePay API
echo "<h2>3. Test SePay API</h2>";
define('SEPAY_API_KEY', '7O2MCQT0UISAX1BNW3KGZFHKESPOJOC4HRUE1MEBXDBABIELFARZWUL68FNYV2MD');
define('SEPAY_ACCOUNT_NUMBER', '0981523130');

$api_url = 'https://my.sepay.vn/userapi/transactions/list?account_number=' . SEPAY_ACCOUNT_NUMBER . '&limit=5';

echo "<p>API URL: <code>{$api_url}</code></p>";

$ch = curl_init();
curl_setopt_array($ch, array(
    CURLOPT_URL => $api_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer ' . SEPAY_API_KEY,
        'Content-Type: application/json'
    ),
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CONNECTTIMEOUT => 30,
    CURLOPT_VERBOSE => true
));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
$info = curl_getinfo($ch);
curl_close($ch);

echo "<h3>Response:</h3>";
echo "<p>HTTP Code: <strong>{$http_code}</strong></p>";

if ($error) {
    echo "<p class='error'>CURL Error: {$error}</p>";
}

if ($http_code == 200 && $response) {
    echo "<p class='success'>✅ SePay API works!</p>";
    
    $data = json_decode($response, true);
    if (isset($data['transactions'])) {
        echo "<p>Found " . count($data['transactions']) . " transactions</p>";
        
        echo "<h3>Recent Transactions:</h3>";
        echo "<table border='1' cellpadding='10' style='border-collapse:collapse;'>";
        echo "<tr style='background:#088178;color:white;'><th>Content</th><th>Amount</th><th>Date</th></tr>";
        
        foreach ($data['transactions'] as $t) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars(isset($t['transaction_content']) ? $t['transaction_content'] : 'N/A') . "</td>";
            echo "<td>" . number_format(isset($t['amount_in']) ? $t['amount_in'] : 0) . " VND</td>";
            echo "<td>" . (isset($t['transaction_date']) ? $t['transaction_date'] : 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check for DH8074
        echo "<h3>Searching for DH8074:</h3>";
        $found = false;
        foreach ($data['transactions'] as $t) {
            $content = isset($t['transaction_content']) ? $t['transaction_content'] : '';
            if (preg_match('/DH\s*8074/i', $content)) {
                echo "<p class='success'>🎉 FOUND! Transaction: " . htmlspecialchars($content) . "</p>";
                echo "<pre>" . print_r($t, true) . "</pre>";
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            echo "<p class='error'>❌ No transaction with content 'DH8074' found</p>";
            echo "<p>Please check your transfer content. It should be exactly: <strong>DH8074</strong></p>";
        }
        
    } else {
        echo "<p class='error'>No transactions found</p>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }
} else {
    echo "<p class='error'>❌ SePay API failed!</p>";
    echo "<p>HTTP Code: {$http_code}</p>";
    echo "<p>Error: {$error}</p>";
    
    echo "<h3>CURL Info:</h3>";
    echo "<pre>" . print_r($info, true) . "</pre>";
    
    if ($response) {
        echo "<h3>Response:</h3>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }
}

echo "<hr>";
echo "<h2>Next Steps:</h2>";
echo "<ul>";
echo "<li>If CURL works but SePay API fails → Check API key</li>";
echo "<li>If transaction DH8074 not found → Check transfer content in your bank app</li>";
echo "<li>If everything works → <a href='check_order_8074.php'>Run Order Check</a></li>";
echo "<li>Or use manual update → <a href='manual_update_8074.php'>Manual Update</a></li>";
echo "</ul>";

echo "<h3>PHP Info:</h3>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>OS: " . PHP_OS . "</p>";
?>
