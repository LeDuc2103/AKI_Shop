<?php
require 'config/database.php';
$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->query('DESCRIBE don_hang');
echo "Columns in don_hang table:\n";
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . "\n";
}
