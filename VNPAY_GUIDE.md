# 📋 HƯỚNG DẪN CÁC FILE CẦN CHÚ Ý KHI DÙNG VNPAY

## 🔴 **FILE QUAN TRỌNG NHẤT - BẮT BUỘC PHẢI KIỂM TRA**

### 1. **`vnpay_php/config.php`** ⭐⭐⭐
**Vị trí**: `KLTN_TUC/vnpay_php/config.php`

**Nội dung cần kiểm tra:**
```php
$vnp_TmnCode = "EFRFPHWG";              // Mã website VNPAY (BẮT BUỘC ĐÚNG)
$vnp_HashSecret = "FDIDNBY3NSUGCBSQL6J5BCNKSUCLM365";  // Secret key (BẮT BUỘC ĐÚNG)
$vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";  // URL sandbox
$vnp_Returnurl = "http://localhost/KLTN4/KLTN/vnpay_php/vnpay_return.php";  // URL trả về
```

**⚠️ KHI UP HOST:**
- **SANDBOX**: Giữ nguyên `$vnp_Url` là `sandbox.vnpayment.vn`
- **PRODUCTION**: Đổi thành `https://www.vnpayment.vn/paymentv2/vpcpay.html`
- **$vnp_Returnurl**: Đổi thành domain thật của bạn
  - Ví dụ: `https://yourdomain.com/vnpay_php/vnpay_return.php`
  - Hoặc: `https://yourdomain.com/KLTN_TUC/vnpay_php/vnpay_return.php` (nếu đặt trong thư mục con)

---

### 2. **`payment_vnpay.php`** ⭐⭐⭐
**Vị trí**: `KLTN_TUC/payment_vnpay.php`

**Chức năng:**
- Tạo đơn hàng trong bảng `don_hang` với `trangthai_thanhtoan = 'chua_thanh_toan'`
- Lưu chi tiết vào `chitiet_donhang`
- Tự động submit form sang VNPay

**Cần kiểm tra:**
- ✅ Đường dẫn form action: `vnpay_php/vnpay_create_payment.php` (đúng cấu trúc thư mục)
- ✅ Tính toán `$tong_tien` = `$tong_tien_hang + $tien_ship` (hiện tại ship = 15000 VNĐ)

---

### 3. **`vnpay_php/vnpay_return.php`** ⭐⭐⭐
**Vị trí**: `KLTN_TUC/vnpay_php/vnpay_return.php`

**Chức năng:**
- Nhận kết quả thanh toán từ VNPay
- Kiểm tra checksum (bảo mật)
- Cập nhật `don_hang`: `trangthai_thanhtoan = 'da_thanh_toan'` nếu thành công
- Xóa giỏ hàng sau khi thanh toán thành công
- Ghi log vào bảng `vnpay_transactions` (nếu có)

**⚠️ KHI UP HOST:**
- Đảm bảo URL này được cấu hình đúng trong `config.php` → `$vnp_Returnurl`
- File này phải **public accessible** (không được chặn bởi .htaccess)

---

### 4. **`vnpay_php/vnpay_create_payment.php`** ⭐⭐
**Vị trí**: `KLTN_TUC/vnpay_php/vnpay_create_payment.php`

**Chức năng:**
- Tạo URL thanh toán VNPay với đầy đủ tham số
- Tính toán checksum (hash)
- Redirect user sang cổng VNPay

**Lưu ý:**
- File này **KHÔNG CẦN SỬA** nếu đã cấu hình đúng `config.php`
- Chỉ cần đảm bảo `require_once("./config.php")` đúng đường dẫn

---

## 🟡 **FILE HỖ TRỢ - NÊN KIỂM TRA**

### 5. **`invoice.php`** ⭐
**Vị trí**: `KLTN_TUC/invoice.php`

**Chức năng:**
- Hiển thị form đặt hàng
- Có nút "Thanh toán VNPAY" → gọi `payment_vnpay.php`

**Cần kiểm tra:**
- Dòng 367: `onclick="window.location.href='payment_vnpay.php'"` phải đúng đường dẫn

---

### 6. **`vnpay_php/vnpay_ipn.php`** (Tùy chọn)
**Vị trí**: `KLTN_TUC/vnpay_php/vnpay_ipn.php`

**Chức năng:**
- IPN (Instant Payment Notification) - VNPay gọi ngầm để báo kết quả
- Hiện tại chưa tích hợp đầy đủ, có thể bỏ qua nếu chỉ dùng `vnpay_return.php`

---

## 🟢 **DATABASE - CẦN CÓ CÁC BẢNG SAU**

### Bảng bắt buộc:
1. **`don_hang`** - Lưu thông tin đơn hàng
   - `ma_donhang` (PK)
   - `trangthai_thanhtoan` (enum: 'chua_thanh_toan', 'da_thanh_toan')
   - `phuongthuc_thanhtoan` (varchar)
   - `tong_tien` (double)

2. **`chitiet_donhang`** - Chi tiết sản phẩm trong đơn
   - `ma_donhang` (FK)
   - `id_sanpham`, `so_luong`, `don_gia`

3. **`gio_hang`** - Giỏ hàng (sẽ bị xóa sau khi thanh toán thành công)
   - `ma_user`, `id_sanpham`, `so_luong`, `thanh_tien`

### Bảng tùy chọn (để log):
4. **`vnpay_transactions`** - Lưu lịch sử giao dịch VNPay
   ```sql
   CREATE TABLE `vnpay_transactions` (
     `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
     `ma_donhang` bigint(20) NOT NULL,
     `vnp_TransactionNo` varchar(50) DEFAULT NULL,
     `vnp_ResponseCode` varchar(10) DEFAULT NULL,
     `vnp_Amount` double DEFAULT NULL,
     `vnp_BankCode` varchar(50) DEFAULT NULL,
     `vnp_PayDate` varchar(20) DEFAULT NULL,
     `raw_data` text,
     `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
     PRIMARY KEY (`id`),
     KEY `fk_vnpay_transactions_don_hang` (`ma_donhang`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
   ```

---

## 📝 **CHECKLIST KHI UP HOST**

### Trước khi up:
- [ ] Kiểm tra `vnpay_php/config.php`:
  - [ ] `$vnp_TmnCode` đúng (sandbox hoặc production)
  - [ ] `$vnp_HashSecret` đúng
  - [ ] `$vnp_Url` đúng môi trường (sandbox vs production)
  - [ ] `$vnp_Returnurl` = domain thật của bạn

### Sau khi up:
- [ ] Test thanh toán với số tiền nhỏ (ví dụ: 10,000 VNĐ)
- [ ] Kiểm tra `vnpay_return.php` có nhận được callback không
- [ ] Kiểm tra database: `don_hang` có cập nhật `trangthai_thanhtoan = 'da_thanh_toan'` không
- [ ] Kiểm tra giỏ hàng có bị xóa sau khi thanh toán thành công không

---

## ⚠️ **LỖI THƯỜNG GẶP**

### 1. "Website này chưa được phê duyệt"
- **Nguyên nhân**: TmnCode chưa được VNPAY phê duyệt hoặc dùng sai môi trường
- **Giải pháp**: Liên hệ VNPAY để họ phê duyệt website / cấp đúng thông tin sandbox

### 2. "Chữ ký không hợp lệ"
- **Nguyên nhân**: `$vnp_HashSecret` sai hoặc URL trả về không khớp
- **Giải pháp**: Kiểm tra lại `config.php` và `$vnp_Returnurl`

### 3. Không nhận được callback
- **Nguyên nhân**: `vnpay_return.php` không accessible hoặc URL sai
- **Giải pháp**: Kiểm tra `.htaccess`, firewall, và URL trong `config.php`

---

## 📞 **LIÊN HỆ HỖ TRỢ**

Nếu gặp vấn đề với VNPAY:
- **Email**: support@vnpayment.vn
- **Hotline**: 1900 636 999
- **Website**: https://sandbox.vnpayment.vn/

---

**Cập nhật lần cuối**: 2025-01-XX

