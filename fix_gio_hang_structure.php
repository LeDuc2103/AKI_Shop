<?php
require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<h3>Sửa cấu trúc bảng gio_hang để cho phép nhiều sản phẩm cho 1 user</h3>";
    
    // Bước 1: Hiển thị cấu trúc hiện tại
    echo "<h4>Bước 1: Kiểm tra cấu trúc hiện tại</h4>";
    $indexes = $conn->query("SHOW INDEX FROM gio_hang");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Key Name</th><th>Column</th><th>Unique</th></tr>";
    $unique_keys = array();
    while ($idx = $indexes->fetch()) {
        echo "<tr>";
        echo "<td>" . $idx['Key_name'] . "</td>";
        echo "<td>" . $idx['Column_name'] . "</td>";
        echo "<td>" . ($idx['Non_unique'] == 0 ? 'YES' : 'NO') . "</td>";
        echo "</tr>";
        
        if ($idx['Non_unique'] == 0 && $idx['Key_name'] != 'PRIMARY') {
            $unique_keys[] = $idx['Key_name'];
        }
    }
    echo "</table>";
    
    // Bước 2: Xóa tất cả UNIQUE constraints (trừ PRIMARY KEY)
    echo "<h4>Bước 2: Xóa các UNIQUE constraints</h4>";
    $unique_keys = array_unique($unique_keys);
    
    foreach ($unique_keys as $key_name) {
        try {
            $sql = "ALTER TABLE gio_hang DROP INDEX " . $key_name;
            $conn->exec($sql);
            echo "<p style='color: green;'>✅ Đã xóa UNIQUE KEY: " . $key_name . "</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Lỗi khi xóa " . $key_name . ": " . $e->getMessage() . "</p>";
        }
    }
    
    // Bước 3: Tạo lại index cho tìm kiếm nhanh (KHÔNG unique)
    echo "<h4>Bước 3: Tạo index cho tìm kiếm (không unique)</h4>";
    try {
        $conn->exec("ALTER TABLE gio_hang ADD INDEX idx_user_product (ma_user, id_sanpham)");
        echo "<p style='color: green;'>✅ Đã tạo index idx_user_product</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "Duplicate key name") !== false) {
            echo "<p style='color: orange;'>⚠️ Index idx_user_product đã tồn tại</p>";
        } else {
            echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
        }
    }
    
    // Bước 4: Hiển thị cấu trúc sau khi sửa
    echo "<h4>Bước 4: Cấu trúc sau khi sửa</h4>";
    $indexes2 = $conn->query("SHOW INDEX FROM gio_hang");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Key Name</th><th>Column</th><th>Unique</th></tr>";
    while ($idx = $indexes2->fetch()) {
        echo "<tr>";
        echo "<td>" . $idx['Key_name'] . "</td>";
        echo "<td>" . $idx['Column_name'] . "</td>";
        echo "<td>" . ($idx['Non_unique'] == 0 ? 'YES' : 'NO') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3 style='color: green;'>✅ HOÀN TẤT! Bây giờ mỗi user có thể thêm nhiều sản phẩm vào giỏ hàng.</h3>";
    echo "<p><a href='sproduct.php?id=1'>← Quay lại thử thêm sản phẩm</a></p>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>Lỗi: " . $e->getMessage() . "</p>";
}
?>
