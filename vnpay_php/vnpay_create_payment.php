<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
date_default_timezone_set('Asia/Ho_Chi_Minh');

/**
 * Description of vnpay_ajax
 *
 * @author xonv
 */
require_once("./config.php");

// Lấy và validate dữ liệu từ POST
$vnp_TxnRef = isset($_POST['order_id']) ? trim($_POST['order_id']) : ''; // Mã đơn hàng (ma_donhang)
if (empty($vnp_TxnRef)) {
    die("Lỗi: Mã đơn hàng không được để trống");
}

$vnp_OrderInfo = isset($_POST['order_desc']) ? trim($_POST['order_desc']) : 'Thanh toan don hang';
$vnp_OrderType = isset($_POST['order_type']) ? trim($_POST['order_type']) : 'other';

// Lấy số tiền và validate
$amount_input = isset($_POST['amount']) ? trim($_POST['amount']) : '0';
// Làm sạch số tiền (loại bỏ dấu phẩy, khoảng trắng nếu có)
$amount_input = str_replace(array(',', ' '), '', $amount_input);
// Chuyển sang số nguyên
$amount_value = (float)$amount_input;
// VNPay yêu cầu số tiền tính bằng đơn vị nhỏ nhất (VNĐ * 100)
$vnp_Amount = (int)($amount_value * 100);

if ($vnp_Amount <= 0) {
    die("Lỗi: Số tiền không hợp lệ (Amount: " . $amount_input . ", Calculated: " . $vnp_Amount . ")");
}

$vnp_Locale = isset($_POST['language']) ? trim($_POST['language']) : 'vn';
$vnp_BankCode = isset($_POST['bank_code']) ? trim($_POST['bank_code']) : '';
$vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

// Add Params of 2.0.1 Version
$vnp_ExpireDate = isset($_POST['txtexpire']) ? trim($_POST['txtexpire']) : date('YmdHis', strtotime('+15 minutes'));

// Billing - Lấy và validate thông tin billing
$vnp_Bill_Mobile = isset($_POST['txt_billing_mobile']) ? trim($_POST['txt_billing_mobile']) : '';
$vnp_Bill_Email = isset($_POST['txt_billing_email']) ? trim($_POST['txt_billing_email']) : '';
$fullName = isset($_POST['txt_billing_fullname']) ? trim($_POST['txt_billing_fullname']) : '';
$vnp_Bill_FirstName = '';
$vnp_Bill_LastName = '';
if (!empty($fullName)) {
    $name = explode(' ', $fullName);
    $vnp_Bill_FirstName = array_shift($name);
    $vnp_Bill_LastName = !empty($name) ? implode(' ', $name) : '';
    if (empty($vnp_Bill_LastName)) {
        $vnp_Bill_LastName = $vnp_Bill_FirstName;
    }
}

$vnp_Bill_Address = isset($_POST['txt_inv_addr1']) ? trim($_POST['txt_inv_addr1']) : '';
$vnp_Bill_City = isset($_POST['txt_bill_city']) ? trim($_POST['txt_bill_city']) : 'Ho Chi Minh';
$vnp_Bill_Country = isset($_POST['txt_bill_country']) ? trim($_POST['txt_bill_country']) : 'VN';
$vnp_Bill_State = isset($_POST['txt_bill_state']) ? trim($_POST['txt_bill_state']) : '';

// Invoice
$vnp_Inv_Phone = isset($_POST['txt_inv_mobile']) ? trim($_POST['txt_inv_mobile']) : $vnp_Bill_Mobile;
$vnp_Inv_Email = isset($_POST['txt_inv_email']) ? trim($_POST['txt_inv_email']) : $vnp_Bill_Email;
$vnp_Inv_Customer = isset($_POST['txt_inv_customer']) ? trim($_POST['txt_inv_customer']) : $fullName;
$vnp_Inv_Address = isset($_POST['txt_inv_addr1']) ? trim($_POST['txt_inv_addr1']) : $vnp_Bill_Address;
$vnp_Inv_Company = isset($_POST['txt_inv_company']) ? trim($_POST['txt_inv_company']) : '';
$vnp_Inv_Taxcode = isset($_POST['txt_inv_taxcode']) ? trim($_POST['txt_inv_taxcode']) : '';
$vnp_Inv_Type = isset($_POST['cbo_inv_type']) ? trim($_POST['cbo_inv_type']) : 'I';
// Tạo mảng dữ liệu gửi sang VNPay (chỉ thêm các trường có giá trị)
$inputData = array(
    "vnp_Version" => "2.1.0",
    "vnp_TmnCode" => $vnp_TmnCode,
    "vnp_Amount" => $vnp_Amount,
    "vnp_Command" => "pay",
    "vnp_CreateDate" => date('YmdHis'),
    "vnp_CurrCode" => "VND",
    "vnp_IpAddr" => $vnp_IpAddr,
    "vnp_Locale" => $vnp_Locale,
    "vnp_OrderInfo" => $vnp_OrderInfo,
    "vnp_OrderType" => $vnp_OrderType,
    "vnp_ReturnUrl" => $vnp_Returnurl,
    "vnp_TxnRef" => $vnp_TxnRef,
    "vnp_ExpireDate" => $vnp_ExpireDate
);

// Chỉ thêm các trường có giá trị (không rỗng)
if (!empty($vnp_BankCode)) {
    $inputData['vnp_BankCode'] = $vnp_BankCode;
}
if (!empty($vnp_Bill_Mobile)) {
    $inputData['vnp_Bill_Mobile'] = $vnp_Bill_Mobile;
}
if (!empty($vnp_Bill_Email)) {
    $inputData['vnp_Bill_Email'] = $vnp_Bill_Email;
}
if (!empty($vnp_Bill_FirstName)) {
    $inputData['vnp_Bill_FirstName'] = $vnp_Bill_FirstName;
}
if (!empty($vnp_Bill_LastName)) {
    $inputData['vnp_Bill_LastName'] = $vnp_Bill_LastName;
}
if (!empty($vnp_Bill_Address)) {
    $inputData['vnp_Bill_Address'] = $vnp_Bill_Address;
}
if (!empty($vnp_Bill_City)) {
    $inputData['vnp_Bill_City'] = $vnp_Bill_City;
}
if (!empty($vnp_Bill_Country)) {
    $inputData['vnp_Bill_Country'] = $vnp_Bill_Country;
}
if (!empty($vnp_Bill_State)) {
    $inputData['vnp_Bill_State'] = $vnp_Bill_State;
}
if (!empty($vnp_Inv_Phone)) {
    $inputData['vnp_Inv_Phone'] = $vnp_Inv_Phone;
}
if (!empty($vnp_Inv_Email)) {
    $inputData['vnp_Inv_Email'] = $vnp_Inv_Email;
}
if (!empty($vnp_Inv_Customer)) {
    $inputData['vnp_Inv_Customer'] = $vnp_Inv_Customer;
}
if (!empty($vnp_Inv_Address)) {
    $inputData['vnp_Inv_Address'] = $vnp_Inv_Address;
}
if (!empty($vnp_Inv_Company)) {
    $inputData['vnp_Inv_Company'] = $vnp_Inv_Company;
}
if (!empty($vnp_Inv_Taxcode)) {
    $inputData['vnp_Inv_Taxcode'] = $vnp_Inv_Taxcode;
}
if (!empty($vnp_Inv_Type)) {
    $inputData['vnp_Inv_Type'] = $vnp_Inv_Type;
}

//var_dump($inputData);
ksort($inputData);
$query = "";
$i = 0;
$hashdata = "";
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashdata .= urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
    $query .= urlencode($key) . "=" . urlencode($value) . '&';
}

$vnp_Url = $vnp_Url . "?" . $query;
if (isset($vnp_HashSecret)) {
    $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret);//  
    $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
}
$returnData = array('code' => '00'
    , 'message' => 'success'
    , 'data' => $vnp_Url);
    if (isset($_POST['redirect'])) {
        header('Location: ' . $vnp_Url);
        die();
    } else {
        echo json_encode($returnData);
    }
