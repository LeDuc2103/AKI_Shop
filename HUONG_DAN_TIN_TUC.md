# Hướng dẫn cài đặt Panel Quản lý Tin tức

## Bước 1: Cập nhật cấu trúc Database

Trước khi sử dụng, cần chạy file SQL để thêm AUTO_INCREMENT cho bảng tin_tuc:

1. Mở phpMyAdmin
2. Chọn database `tuc`
3. Vào tab "SQL"
4. Chạy lệnh sau:

```sql
ALTER TABLE `tin_tuc` MODIFY `ma_tintuc` bigint(20) NOT NULL AUTO_INCREMENT;
```

Hoặc import file: `fix_tin_tuc_structure.sql`

## Bước 2: Truy cập Panel

1. Đăng nhập với tài khoản nhân viên bán hàng
2. Truy cập: `nhanvienbanhang.php?action=tin_tuc`
3. Click menu "Quản lý tin tức" trên sidebar

## Chức năng đã hoàn thành

✅ **Hiển thị danh sách tin tức**
   - Bảng hiển thị: STT, ID, Hình ảnh, Tiêu đề, Người tạo, Ngày tạo
   - KHÔNG hiển thị cột Nội dung (theo yêu cầu)
   - Phân trang 10 tin/trang
   - Sắp xếp theo ID ASC

✅ **Tìm kiếm thông minh**
   - Tìm theo ID (nhập số)
   - Tìm theo tiêu đề, người tạo (nhập text)
   - Hiển thị số kết quả tìm được

✅ **Thêm tin tức mới**
   - Form nhập: Tiêu đề (bắt buộc), Nội dung (bắt buộc)
   - Tùy chọn: Người tạo, Ngày tạo, Hình ảnh
   - Upload hình: jpg, jpeg, png, gif
   - Lưu vào thư mục: img/blog/

✅ **Sửa tin tức**
   - Load dữ liệu cũ vào form
   - Hiển thị preview hình ảnh
   - Cập nhật timestamp

✅ **Xóa tin tức**
   - Xác nhận trước khi xóa
   - Tự động xóa file hình ảnh
   - Thông báo kết quả

✅ **Xem chi tiết**
   - Modal popup đẹp
   - Hiển thị đầy đủ thông tin
   - Hình ảnh full size
   - Nút Sửa trong modal

## Cấu trúc File

```
nhanvienbanhang.php (đã cập nhật)
├── nhanvien/
│   ├── donhang.php
│   ├── doi_tra.php
│   └── tin_tuc.php (MỚI)
├── img/
│   └── blog/ (thư mục lưu ảnh tin tức)
└── fix_tin_tuc_structure.sql (SQL sửa DB)
```

## Thiết kế UI

- Bootstrap 5.3.0 responsive
- Font Awesome 6.4.0 icons
- Table striped + hover effect
- Modal cho xem chi tiết
- Alert thông báo success/error
- Tương thích PHP 5.2.6

## Lưu ý

1. Bảng tin_tuc PHẢI có AUTO_INCREMENT (chạy SQL trước)
2. Thư mục img/blog/ đã tồn tại
3. Session lưu nhanvien_id để gán ma_user khi thêm tin
4. File ảnh được đặt tên: news_[timestamp].[ext]
