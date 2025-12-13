<?php

 /*
 File check_payment_status.php
 File phục vụ cho Ajax POST lấy kết quả trạng thái đơn hàng
 URL ajax post sẽ là https://yourwebsite.tld/check_payment_status.php
 */
 
 // Include file db_connect.php, file chứa toàn bộ kết nối CSDL
require("../config/database.php");

// Khởi tạo database
$db = new Database();
$conn = $db->getConnection();
 
 // Chỉ cho phép POST và POST có ID đơn hàng
 if(!$_POST || !isset($_POST['ma_donhang']) || !is_numeric($_POST['ma_donhang']))
    die('access denied');
 
 $order_id = $_POST['ma_donhang'];

 // Kiểm tra đơn hàng có tồn tại không
 try {
     $stmt = $conn->prepare("SELECT trangthai_thanhtoan FROM don_hang WHERE ma_donhang = ?");
     $stmt->execute([$order_id]);
     $order_details = $stmt->fetch(PDO::FETCH_OBJ);
     
     if($order_details) {
         // Trả về kết quả trạng thái đơn hàng dạng JSON
         $is_paid = ($order_details->trangthai_thanhtoan === 'da_thanh_toan');
         echo json_encode([
             'payment_status' => $order_details->trangthai_thanhtoan,
             'paid' => $is_paid
         ]);
     } else {
         // Trả về kết quả không tìm thấy đơn hàng
        echo json_encode(['payment_status' => 'order_not_found', 'paid' => false]);
     }
 } catch(PDOException $e) {
     echo json_encode(['payment_status' => 'error', 'paid' => false, 'message' => $e->getMessage()]);
 }
 

?>