# 🚀 HƯỚNG DẪN CÀI ĐẶT VÀ CẤU HÌNH EMAIL

## ✅ CÁC BƯỚC THỰC HIỆN

### Bước 1: Cài Đặt Composer (Nếu Chưa Có)

1. **Tải Composer:**
   - Truy cập: https://getcomposer.org/download/
   - Tải file: **Composer-Setup.exe**
   - Chạy file và làm theo hướng dẫn (chọn PHP từ WAMP: `C:\wamp64\bin\php\php8.x.x\php.exe`)

2. **Kiểm tra cài đặt:**
   ```powershell
   composer --version
   ```

### Bước 2: Cài Đặt PHPMailer

Mở PowerShell tại thư mục dự án và chạy:

```powershell
cd D:\wamp\www\KLTN_AKISTORE
composer require phpmailer/phpmailer
```

Lệnh này sẽ tạo thư mục `vendor/` và cài đặt PHPMailer.

### Bước 3: Tạo App Password Từ Gmail

1. **Đăng nhập Gmail** của bạn

2. **Bật xác minh 2 bước:**
   - Truy cập: https://myaccount.google.com/security
   - Tìm "2-Step Verification" → Bật

3. **Tạo App Password:**
   - Sau khi bật 2-Step, tìm "App passwords"
   - Chọn:
     * App: **Mail**
     * Device: **Windows Computer**
   - Click **Generate**
   - **Lưu lại mật khẩu 16 ký tự** (dạng: abcd efgh ijkl mnop)

### Bước 4: Cấu Hình Email

Mở file: `config/email_config.php` và sửa:

```php
define('SMTP_USERNAME', 'your-email@gmail.com'); // ← Email Gmail của bạn
define('SMTP_PASSWORD', 'abcdefghijklmnop');     // ← App Password (bỏ dấu cách)
```

**Ví dụ:**
```php
define('SMTP_USERNAME', '95.levantuc.toky@gmail.com');
define('SMTP_PASSWORD', 'abcd efgh ijkl mnop'); // Gmail tạo ra
```

### Bước 5: Test Gửi Email

1. Khởi động WAMP
2. Truy cập: http://localhost/KLTN_AKISTORE/forgot_password.php
3. Nhập email có trong database
4. Kiểm tra hộp thư Gmail

---

## 🔧 TROUBLESHOOTING

### Lỗi: "composer: command not found"

**Giải pháp:**
- Khởi động lại PowerShell sau khi cài Composer
- Hoặc sử dụng đường dẫn đầy đủ: `C:\ProgramData\ComposerSetup\bin\composer.bat`

### Lỗi: "SMTP connect() failed"

**Nguyên nhân:**
- Sai App Password
- Chưa bật 2-Step Verification
- Firewall chặn port 587

**Giải pháp:**
1. Kiểm tra lại App Password (copy chính xác, bỏ dấu cách)
2. Đảm bảo 2-Step đã bật
3. Tắt tạm firewall để test

### Lỗi: "Could not authenticate"

**Giải pháp:**
- Tạo lại App Password mới
- Kiểm tra email đúng định dạng
- Xóa khoảng trắng trong password

### Email vào Spam

**Giải pháp:**
- Thêm địa chỉ email gửi vào danh sách an toàn
- Gửi từ email thật, không dùng alias
- Cho production nên dùng SMTP riêng

---

## 📁 CẤU TRÚC FILE SAU KHI CÀI ĐẶT

```
KLTN_AKISTORE/
├── config/
│   ├── email.php           ← Code gửi email
│   └── email_config.php    ← Cấu hình SMTP (KHÔNG commit)
├── vendor/                 ← PHPMailer (tự động tạo)
│   └── phpmailer/
├── composer.json           ← Dependencies
├── composer.lock           ← Version lock
└── .gitignore              ← Bảo vệ thông tin nhạy cảm
```

---

## 🎯 KIỂM TRA NHANH

Chạy lệnh này để test:

```powershell
cd D:\wamp\www\KLTN_AKISTORE
php -r "echo (file_exists('vendor/autoload.php') ? '✅ PHPMailer OK' : '❌ Chưa cài PHPMailer');"
```

---

## ⚠️ LƯU Ý BẢO MẬT

1. **KHÔNG** commit file `email_config.php` lên GitHub
2. File `.gitignore` đã được tạo để bảo vệ
3. Với production, dùng biến môi trường thay vì hardcode

---

**Sau khi hoàn tất các bước trên, hệ thống sẽ tự động gửi email thật qua Gmail SMTP!**
