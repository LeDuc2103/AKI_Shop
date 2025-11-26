<?php
// Test PHP compatibility
echo "<h1>Test PHP Compatibility</h1>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";

// Test cú pháp array
echo "<h2>Test Array Syntax:</h2>";

// Cú pháp cũ (PHP 5.x tương thích)
$old_syntax = array('item1', 'item2', 'item3');
echo "<p>✓ Old array syntax: array() - Works</p>";

// Test cú pháp mới (PHP 5.4+)
if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
    $new_syntax = ['item1', 'item2', 'item3'];
    echo "<p>✓ New array syntax: [] - Supported</p>";
} else {
    echo "<p>✗ New array syntax: [] - NOT Supported (PHP " . phpversion() . ")</p>";
}

// Test database connection
echo "<h2>Test Database Connection:</h2>";
try {
    require_once 'config/database.php';
    $conn = $db->getConnection();
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Test query
    $stmt = $conn->query("SELECT 1 as test");
    $result = $stmt->fetch();
    if ($result['test'] == 1) {
        echo "<p style='color: green;'>✓ Database query successful</p>";
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
}

echo "<h2>Recommendations:</h2>";
if (version_compare(PHP_VERSION, '5.4.0') < 0) {
    echo "<p style='color: orange;'>⚠️ Your PHP version is old. Consider upgrading to PHP 5.4+ for better compatibility.</p>";
    echo "<p>Current fixes applied:</p>";
    echo "<ul>";
    echo "<li>✓ Changed [] to array() syntax</li>";
    echo "<li>✓ Used older PHP compatible functions</li>";
    echo "</ul>";
} else {
    echo "<p style='color: green;'>✓ Your PHP version supports modern syntax.</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>→ Test trang chủ</a></p>";
?>

<style>
    body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
    h1, h2 { color: #333; }
    .error { color: red; }
    .success { color: green; }
    .warning { color: orange; }
</style>