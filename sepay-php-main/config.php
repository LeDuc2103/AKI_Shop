<?php
/**
 * SePay Configuration
 * Kết nối với database tuc
 */

// Load database config
require_once dirname(__FILE__) . '/../config/database.php';

// SePay API Configuration
define('SEPAY_API_KEY', '7O2MCQT0UISAX1BNW3KGZFHKESPOJOC4HRUE1MEBXDBABIELFARZWUL68FNYV2MD');
define('SEPAY_ACCOUNT_NUMBER', '0981523130');
define('SEPAY_BANK_CODE', 'MB'); // MBBank
define('SEPAY_ACCOUNT_NAME', 'LE VAN TUC');

// Payment timeout (seconds)
define('PAYMENT_TIMEOUT', 300); // 5 minutes

// Check interval (seconds)
define('CHECK_INTERVAL', 5); // Check every 5 seconds

/**
 * Get database connection
 */
function getDB() {
    $db = new Database();
    return $db->getConnection();
}

/**
 * Log to file
 */
function logSePay($message) {
    $timestamp = date('Y-m-d H:i:s');
    $log_file = dirname(__FILE__) . '/../sepay_worker.log';
    $log_entry = "[{$timestamp}] {$message}\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}
?>
