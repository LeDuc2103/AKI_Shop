# HƯỚNG DẪN SỬA LỖI BẢNG TIN_TỨC

## Vấn đề:
Bảng `tin_tuc` trong database không có AUTO_INCREMENT cho cột `ma_tintuc`, 
dẫn đến không thể thêm tin tức mới.

## Giải pháp:

### Cách 1: Sử dụng phpMyAdmin
1. Mở phpMyAdmin: http://localhost/phpmyadmin
2. Chọn database `tuc`
3. Click vào bảng `tin_tuc`
4. Click tab "Structure" (Cấu trúc)
5. Click "Change" (Sửa) ở cột `ma_tintuc`
6. Tích chọn checkbox "A_I" (AUTO_INCREMENT)
7. Click "Save" (Lưu)

### Cách 2: Chạy SQL Command
1. Mở phpMyAdmin
2. Chọn database `tuc`
3. Click tab "SQL"
4. Dán câu lệnh sau và click "Go":

```sql
ALTER TABLE `tin_tuc` 
MODIFY COLUMN `ma_tintuc` bigint(20) NOT NULL AUTO_INCREMENT;
```

### Cách 3: Chạy file SQL
1. Mở file: `fix_tin_tuc_table.sql`
2. Import vào phpMyAdmin

## Kiểm tra:
Sau khi chạy SQL, kiểm tra lại:
```sql
DESCRIBE `tin_tuc`;
```

Cột `ma_tintuc` phải có:
- Type: bigint(20)
- Null: NO
- Key: PRI
- **Extra: auto_increment** ← Quan trọng!

## Lưu ý:
- Hệ thống đã được cấu hình đúng để lấy tên người tạo từ bảng `user`
- Query JOIN: `LEFT JOIN user u ON t.ma_user = u.ma_user`
- Hiển thị: `u.ho_ten AS ten_user`
- Chỉ cần sửa cấu trúc bảng là hoạt động bình thường

## Sau khi sửa:
1. Đăng nhập trang nhân viên
2. Vào "Quản lý tin tức"
3. Click "Thêm tin tức mới"
4. Điền thông tin và lưu
5. Tên người tạo sẽ tự động hiển thị từ tài khoản đăng nhập
