<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// Hàm tự động detect base URL
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    
    // Lấy đường dẫn từ SCRIPT_NAME (ví dụ: /KLTN4/KLTN_TUC/vnpay_php/config.php)
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    
    // Đảm bảo có dấu / ở đầu
    if (substr($scriptPath, 0, 1) !== '/') {
        $scriptPath = '/' . $scriptPath;
    }
    
    // Loại bỏ dấu / ở cuối nếu có
    $scriptPath = rtrim($scriptPath, '/');
    
    return $protocol . $host . $scriptPath;
}

$vnp_TmnCode = "Y0XCP3EQ"; //Website ID in VNPAY System
$vnp_HashSecret = "5YJ5TJAMLG3DJQ61LJZSV7IIKQ1SBS55"; //Secret key
$vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";

// Tự động detect URL trả về dựa trên URL hiện tại
$baseUrl = getBaseUrl();
$vnp_Returnurl = $baseUrl . "/vnpay_return.php";

// Nếu muốn hardcode (khi đã biết chính xác URL), bỏ comment dòng dưới và comment dòng trên
// $vnp_Returnurl = "http://localhost/KLTN4/KLTN_TUC/vnpay_php/vnpay_return.php";

$vnp_apiUrl = "http://sandbox.vnpayment.vn/merchant_webapi/merchant.html";
//Config input format
//Expire - Tăng lên 30 phút để khách hàng có thời gian thanh toán
$startTime = date("YmdHis");
$expire = date('YmdHis',strtotime('+30 minutes',strtotime($startTime)));
