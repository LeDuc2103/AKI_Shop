<?php
/**
 * Manual Update Payment for Order 8074
 * Cập nhật thủ công khi API không hoạt động
 */
require_once 'config/database.php';

$order_code = '8074';

echo "<html><head><meta charset='UTF-8'>";
echo "<style>body{font-family:Arial;padding:30px;background:#f5f5f5;} .box{background:white;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);max-width:800px;margin:0 auto;} h1{color:#088178;} .success{color:green;font-size:18px;font-weight:bold;} .error{color:red;} .btn{display:inline-block;padding:15px 30px;background:#088178;color:white;text-decoration:none;border-radius:5px;margin:10px 5px;font-weight:bold;} .btn:hover{background:#066d63;} .info{background:#e8f5f4;padding:15px;border-radius:5px;margin:15px 0;border-left:4px solid #088178;}</style>";
echo "</head><body><div class='box'>";

echo "<h1>🔧 Manual Payment Update</h1>";
echo "<p>Order Code: <strong>{$order_code}</strong></p>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get order info
    $stmt = $conn->prepare("SELECT * FROM don_hang WHERE order_code = ?");
    $stmt->execute(array($order_code));
    $order = $stmt->fetch();
    
    if (!$order) {
        echo "<p class='error'>❌ Order not found!</p>";
        exit;
    }
    
    echo "<div class='info'>";
    echo "<strong>Order Information:</strong><br>";
    echo "Order ID: " . $order['ma_donhang'] . "<br>";
    echo "Amount: " . number_format($order['tong_tien']) . " VND<br>";
    echo "Current Status: " . $order['trangthai_thanhtoan'] . "<br>";
    echo "</div>";
    
    if ($order['trangthai_thanhtoan'] == 'da_thanh_toan') {
        echo "<p class='success'>✅ This order is already PAID!</p>";
        echo "<a href='sepay-php-main/success_page.php?order_code={$order_code}' class='btn'>View Success Page</a>";
    } else {
        echo "<h2>Update Payment Status</h2>";
        echo "<p>Do you want to mark this order as PAID?</p>";
        
        if (isset($_POST['confirm_payment'])) {
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
                SET transaction_content = ?,
                    amount_in = ?,
                    is_processed = 1,
                    transaction_date = NOW()
                WHERE ma_donhang = ?
            ");
            $update_trans->execute(array(
                'DH' . $order_code,
                $order['tong_tien'],
                $order['ma_donhang']
            ));
            
            // Delete cart
            $delete_cart = $conn->prepare("DELETE FROM gio_hang WHERE ma_user = ?");
            $delete_cart->execute(array($order['ma_user']));
            
            echo "<div class='success'>";
            echo "<h2>✅ Payment Updated Successfully!</h2>";
            echo "<p>✓ Order status: PAID</p>";
            echo "<p>✓ Transaction processed</p>";
            echo "<p>✓ Cart cleared</p>";
            echo "</div>";
            
            echo "<a href='sepay-php-main/success_page.php?order_code={$order_code}' class='btn'>→ Go to Success Page</a>";
            echo "<a href='my_orders.php' class='btn'>View My Orders</a>";
            
            echo "<script>setTimeout(function(){ window.location.href='sepay-php-main/success_page.php?order_code={$order_code}'; }, 3000);</script>";
            echo "<p>Redirecting in 3 seconds...</p>";
            
        } else {
            echo "<form method='POST'>";
            echo "<button type='submit' name='confirm_payment' class='btn' style='background:#28a745;font-size:18px;'>✓ YES, Mark as PAID</button>";
            echo "</form>";
            echo "<br><br>";
            echo "<div style='background:#fff3cd;padding:15px;border-radius:5px;border-left:4px solid #ffc107;'>";
            echo "<strong>⚠️ Note:</strong><br>";
            echo "Chỉ nhấn nút này nếu bạn đã chuyển khoản thành công!<br>";
            echo "Số tiền: <strong>" . number_format($order['tong_tien']) . " VND</strong><br>";
            echo "Nội dung CK: <strong>DH{$order_code}</strong>";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>ERROR: " . $e->getMessage() . "</p>";
}

echo "</div></body></html>";
?>
