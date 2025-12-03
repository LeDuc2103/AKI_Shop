<?php
// File test để debug lỗi hủy đơn hàng
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config/database.php';

// Giả lập session (thay bằng session thật của bạn)
if (!isset($_SESSION['user_logged_in'])) {
    echo "<h2>Vui lòng đăng nhập trước</h2>";
    echo "<p>Hoặc sửa code để set session thủ công:</p>";
    echo "<pre>";
    echo "\$_SESSION['user_logged_in'] = true;\n";
    echo "\$_SESSION['user_id'] = YOUR_USER_ID;\n";
    echo "</pre>";
    exit;
}

$user_id = $_SESSION['user_id'];
$db = new Database();
$conn = $db->getConnection();

// Lấy mã đơn hàng từ GET (để test)
$ma_donhang = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($ma_donhang <= 0) {
    echo "<h2>Lỗi: Mã đơn hàng không hợp lệ</h2>";
    echo "<p>Vui lòng truyền ?id=MA_DON_HANG vào URL</p>";
    exit;
}

echo "<h2>Test Hủy Đơn Hàng #$ma_donhang</h2>";

try {
    // Kiểm tra đơn hàng
    $stmt = $conn->prepare("SELECT * FROM don_hang WHERE ma_donhang = ? AND ma_user = ?");
    $stmt->execute(array($ma_donhang, $user_id));
    $order = $stmt->fetch();

    if (!$order) {
        echo "<p style='color: red;'>❌ Không tìm thấy đơn hàng hoặc không có quyền</p>";
        exit;
    }

    echo "<h3>Thông tin đơn hàng:</h3>";
    echo "<pre>";
    print_r($order);
    echo "</pre>";

    // Kiểm tra điều kiện
    echo "<h3>Kiểm tra điều kiện:</h3>";
    
    if ($order['phuongthuc_thanhtoan'] != 'cod') {
        echo "<p style='color: red;'>❌ Phương thức thanh toán: " . $order['phuongthuc_thanhtoan'] . " (không phải COD)</p>";
    } else {
        echo "<p style='color: green;'>✓ Phương thức thanh toán: COD</p>";
    }

    if ($order['trangthai_thanhtoan'] == 'da_thanh_toan') {
        echo "<p style='color: red;'>❌ Đã thanh toán</p>";
    } else {
        echo "<p style='color: green;'>✓ Chưa thanh toán</p>";
    }

    if ($order['trang_thai'] == 'huy') {
        echo "<p style='color: red;'>❌ Đã bị hủy</p>";
    } else {
        echo "<p style='color: green;'>✓ Trạng thái: " . $order['trang_thai'] . "</p>";
    }

    // Test transaction
    echo "<h3>Test Transaction:</h3>";
    $use_transaction = method_exists($conn, 'beginTransaction');
    echo "<p>Transaction hỗ trợ: " . ($use_transaction ? "✓ Có" : "✗ Không") . "</p>";

    if ($use_transaction) {
        try {
            $conn->beginTransaction();
            echo "<p style='color: green;'>✓ beginTransaction() thành công</p>";
            $conn->rollBack();
            echo "<p style='color: green;'>✓ rollBack() thành công</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Transaction error: " . $e->getMessage() . "</p>";
        }
    }

    // Test update
    echo "<h3>Test Update:</h3>";
    try {
        $testUpdate = $conn->prepare("UPDATE don_hang SET trang_thai = ? WHERE ma_donhang = ?");
        $testUpdate->execute(array($order['trang_thai'], $ma_donhang)); // Giữ nguyên giá trị
        echo "<p style='color: green;'>✓ Update query thành công</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Update error: " . $e->getMessage() . "</p>";
    }

    // Test lấy chi tiết đơn hàng
    echo "<h3>Test Lấy Chi Tiết Đơn Hàng:</h3>";
    try {
        $stmtDetails = $conn->prepare("SELECT id_sanpham, so_luong FROM chitiet_donhang WHERE ma_donhang = ?");
        $stmtDetails->execute(array($ma_donhang));
        $order_details = $stmtDetails->fetchAll();
        echo "<p style='color: green;'>✓ Tìm thấy " . count($order_details) . " sản phẩm</p>";
        if (!empty($order_details)) {
            echo "<pre>";
            print_r($order_details);
            echo "</pre>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Lỗi lấy chi tiết: " . $e->getMessage() . "</p>";
    }

    // Test update tồn kho
    if (!empty($order_details)) {
        echo "<h3>Test Update Tồn Kho:</h3>";
        foreach ($order_details as $detail) {
            try {
                // Kiểm tra sản phẩm có tồn tại không
                $checkProduct = $conn->prepare("SELECT id_sanpham, so_luong FROM san_pham WHERE id_sanpham = ?");
                $checkProduct->execute(array($detail['id_sanpham']));
                $product = $checkProduct->fetch();
                
                if ($product) {
                    echo "<p>✓ Sản phẩm #{$detail['id_sanpham']}: Tồn kho hiện tại = {$product['so_luong']}</p>";
                    
                    // Test update (không thực sự update, chỉ test query)
                    $testStock = $conn->prepare("UPDATE san_pham SET so_luong = so_luong + ? WHERE id_sanpham = ?");
                    // Không execute để không thay đổi dữ liệu
                    echo "<p style='color: green;'>✓ Query update tồn kho hợp lệ</p>";
                } else {
                    echo "<p style='color: red;'>❌ Không tìm thấy sản phẩm #{$detail['id_sanpham']}</p>";
                }
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Lỗi kiểm tra sản phẩm #{$detail['id_sanpham']}: " . $e->getMessage() . "</p>";
            }
        }
    }

    echo "<hr>";
    echo "<h3>Kết luận:</h3>";
    echo "<p>Nếu tất cả các test trên đều ✓, thì có thể thử hủy đơn hàng thật:</p>";
    echo "<form method='POST' action='cancel_order.php'>";
    echo "<input type='hidden' name='ma_donhang' value='$ma_donhang'>";
    echo "<button type='submit' style='padding: 10px 20px; background: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer;'>Hủy Đơn Hàng Thật</button>";
    echo "</form>";

} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Lỗi tổng quát:</h3>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . " | Line: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 40px;
        line-height: 1.6;
    }
    pre {
        background: #f5f5f5;
        padding: 10px;
        border-radius: 5px;
        overflow-x: auto;
    }
</style>

