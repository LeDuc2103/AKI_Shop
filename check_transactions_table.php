<?php
require 'config/database.php';
$db = new Database();
$conn = $db->getConnection();

try {
    $stmt = $conn->query('DESCRIBE tb_transactions');
    echo "Columns in tb_transactions:\n";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . "\n";
    }
} catch(PDOException $e) {
    echo "Table tb_transactions does not exist. Error: " . $e->getMessage() . "\n";
    
    // Try transactions table instead
    try {
        $stmt = $conn->query('DESCRIBE transactions');
        echo "\nColumns in transactions:\n";
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo $row['Field'] . "\n";
        }
    } catch(PDOException $e2) {
        echo "Table transactions does not exist either: " . $e2->getMessage();
    }
}
