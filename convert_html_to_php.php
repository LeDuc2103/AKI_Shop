<?php
// Script để chuyển đổi tất cả file HTML sang PHP

$html_files = [
    'shop.html',
    'cart.html', 
    'about.html',
    'contact.html',
    'blog.html',
    'sproduct.html'
];

$directory = 'd:\\wamp\\www\\KLTN\\';

foreach ($html_files as $html_file) {
    $html_path = $directory . $html_file;
    $php_file = str_replace('.html', '.php', $html_file);
    $php_path = $directory . $php_file;
    
    if (file_exists($html_path)) {
        // Đọc nội dung HTML
        $content = file_get_contents($html_path);
        
        // Thêm PHP session start ở đầu
        $php_content = "<?php\nsession_start();\n?>\n" . $content;
        
        // Thay thế các link HTML thành PHP
        $php_content = str_replace('.html', '.php', $php_content);
        
        // Thay thế language
        $php_content = str_replace('lang="en"', 'lang="vi"', $php_content);
        
        // Thay thế title
        $php_content = str_replace('<title>Akishop</title>', '<title>KLTN Shop</title>', $php_content);
        
        // Thêm logic user dropdown
        $user_dropdown_old = '<div class="user-dropdown">
                        <a href="login.php">Đăng Nhập</a>
                        <a href="register.php">Đăng Ký</a>
                    </div>';
                    
        $user_dropdown_new = '<div class="user-dropdown">
                        <?php if (isset($_SESSION[\'user_logged_in\']) && $_SESSION[\'user_logged_in\']): ?>
                            <a href="#">Xin chào, <?php echo htmlspecialchars($_SESSION[\'user_name\']); ?></a>
                            <a href="logout.php">Đăng xuất</a>
                        <?php else: ?>
                            <a href="login.php">Đăng Nhập</a>
                            <a href="register.php">Đăng Ký</a>
                        <?php endif; ?>
                    </div>';
                    
        $php_content = str_replace($user_dropdown_old, $user_dropdown_new, $php_content);
        
        // Ghi file PHP mới
        file_put_contents($php_path, $php_content);
        
        echo "✅ Converted: $html_file → $php_file\n";
    } else {
        echo "❌ File not found: $html_file\n";
    }
}

echo "\n🎉 Conversion completed!\n";
?>