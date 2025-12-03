<?php
/**
 * File test để kiểm tra URL trả về VNPay
 * Truy cập: http://localhost/KLTN4/KLTN_TUC/vnpay_php/test_return_url.php
 */

require_once("./config.php");

echo "<h2>Thông tin URL VNPay Return</h2>";
echo "<p><strong>Base URL:</strong> " . getBaseUrl() . "</p>";
echo "<p><strong>Return URL:</strong> " . $vnp_Returnurl . "</p>";
echo "<p><strong>File path:</strong> " . __FILE__ . "</p>";
echo "<p><strong>Script name:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p><strong>HTTP Host:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";
echo "<p><strong>Request URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";

echo "<hr>";
echo "<h3>Test link:</h3>";
echo "<a href='" . $vnp_Returnurl . "?vnp_TxnRef=123&vnp_ResponseCode=00&vnp_SecureHash=test'>Test vnpay_return.php</a>";
?>

