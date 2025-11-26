<?php
require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<h3>Kiểm tra cấu trúc bảng gio_hang</h3>";
    
    // Hiển thị cấu trúc bảng
    $result = $conn->query("SHOW CREATE TABLE gio_hang");
    $row = $result->fetch();
    
    echo "<pre>";
    echo htmlspecialchars($row['Create Table']);
    echo "</pre>";
    
    echo "<h3>Kiểm tra các INDEX</h3>";
    $indexes = $conn->query("SHOW INDEX FROM gio_hang");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Key Name</th><th>Column</th><th>Non Unique</th><th>Seq</th></tr>";
    while ($idx = $indexes->fetch()) {
        echo "<tr>";
        echo "<td>" . $idx['Key_name'] . "</td>";
        echo "<td>" . $idx['Column_name'] . "</td>";
        echo "<td>" . $idx['Non_unique'] . "</td>";
        echo "<td>" . $idx['Seq_in_index'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Xóa UNIQUE KEY trên ma_user</h3>";
    
    // Xóa UNIQUE constraint trên ma_user nếu có
    try {
        $conn->exec("ALTER TABLE gio_hang DROP INDEX ma_user");
        echo "<p style='color: green;'>✅ Đã xóa UNIQUE KEY trên cột ma_user</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "check that column/key exists") !== false) {
            echo "<p style='color: orange;'>⚠️ Không tìm thấy UNIQUE KEY 'ma_user'</p>";
        } else {
            echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
        }
    }
    
    // Kiểm tra lại sau khi xóa
    echo "<h3>Cấu trúc sau khi sửa</h3>";
    $indexes2 = $conn->query("SHOW INDEX FROM gio_hang");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Key Name</th><th>Column</th><th>Non Unique</th></tr>";
    while ($idx = $indexes2->fetch()) {
        echo "<tr>";
        echo "<td>" . $idx['Key_name'] . "</td>";
        echo "<td>" . $idx['Column_name'] . "</td>";
        echo "<td>" . $idx['Non_unique'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>✅ Hoàn tất! Bây giờ có thể thêm nhiều sản phẩm cho cùng 1 user.</h3>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>Lỗi: " . $e->getMessage() . "</p>";
}
?>
