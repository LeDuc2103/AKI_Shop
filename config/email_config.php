<?php
// Cấu hình SMTP Email
// Lưu ý: KHÔNG commit file này lên GitHub nếu chứa thông tin nhạy cảm

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'leduc2103@gmail.com'); // Thay bằng email của bạn
define('SMTP_PASSWORD', 'zzcf mebw gqlk upar'); // App Password từ Gmail (16 ký tự)
define('SMTP_FROM_EMAIL', 'noreply@akishop.com');
define('SMTP_FROM_NAME', 'KLTN AKI Shop');
define('SMTP_ENCRYPTION', 'tls'); // tls hoặc ssl

// Bật/tắt chế độ debug
define('SMTP_DEBUG', false); // true để xem chi tiết lỗi, false cho production
?>
