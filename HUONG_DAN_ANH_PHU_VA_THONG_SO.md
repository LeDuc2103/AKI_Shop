# HƯỚNG DẪN SỬ DỤNG TÍNH NĂNG ẢNH PHỤ VÀ THÔNG SỐ SẢN PHẨM

## 📋 Tổng quan
Đã thêm 2 tính năng mới vào hệ thống quản lý sản phẩm:
1. **Ảnh phụ sản phẩm**: Cho phép upload nhiều ảnh để hiển thị dưới ảnh chính
2. **Thông số sản phẩm**: Thông tin chi tiết với TinyMCE editor (hỗ trợ hình, video)

---

## 🗄️ BƯỚC 1: CẬP NHẬT DATABASE

Chạy file SQL migration để thêm 2 cột mới vào bảng `san_pham`:

```sql
-- File: update_san_pham_columns.sql
```

**Cách chạy:**
1. Mở phpMyAdmin
2. Chọn database `tuc`
3. Vào tab SQL
4. Copy nội dung file `update_san_pham_columns.sql` và chạy
5. Hoặc import file SQL trực tiếp

**Kết quả:** Bảng `san_pham` sẽ có thêm 2 cột:
- `anh_con` (TEXT): Lưu đường dẫn các ảnh phụ
- `ct_sp` (LONGTEXT): Lưu thông số sản phẩm (HTML)

---

## 📝 BƯỚC 2: QUẢN LÝ SẢN PHẨM (Admin)

### Vào trang Admin
1. Đăng nhập với tài khoản admin
2. Vào **Admin Panel** → **Quản lý Sản phẩm**
3. Click **"Thêm sản phẩm"** hoặc **"Sửa"** sản phẩm có sẵn

### Các trường mới trong form:

#### 1️⃣ Mô tả (đã có - đã nâng cấp với TinyMCE)
- Editor hỗ trợ: văn bản, hình ảnh, video, bảng biểu
- Upload ảnh trực tiếp vào editor

#### 2️⃣ Thông số sản phẩm (MỚI)
- Nhập chi tiết thông số kỹ thuật
- **TinyMCE Editor** với đầy đủ tính năng:
  - Định dạng văn bản (bold, italic, màu sắc...)
  - Chèn hình ảnh (upload hoặc link)
  - Chèn video (YouTube, Vimeo...)
  - Tạo bảng biểu thông số
  - Chèn danh sách

**Ví dụ thông số:**
```
Kích thước: 15.6 inch
Độ phân giải: 1920x1080
CPU: Intel Core i5
RAM: 8GB
Ổ cứng: SSD 256GB
```

#### 3️⃣ Ảnh phụ sản phẩm (MỚI)
- Click **"Choose Files"** để chọn nhiều ảnh
- Có thể chọn 2-10 ảnh cùng lúc
- **Khi sửa sản phẩm:**
  - Ảnh cũ được hiển thị dưới dạng thumbnail
  - Chọn ảnh mới = thay thế hoàn toàn ảnh cũ
  - Không chọn ảnh = giữ nguyên ảnh cũ

---

## 🖼️ BƯỚC 3: HIỂN THỊ TRÊN TRANG SẢN PHẨM

### Trang chi tiết sản phẩm (sproduct.php)

#### Hiển thị ảnh phụ:
- **Ảnh chính** hiển thị lớn ở trên
- **Ảnh phụ** hiển thị ở dưới (4 ảnh nhỏ)
- Click vào ảnh nhỏ → hiển thị lớn ở trên
- Nếu có nhiều hơn 4 ảnh, 4 ảnh đầu sẽ được hiển thị

#### Tabs Thông tin:
Có 2 tab mới:

**📌 Tab 1: Thông số sản phẩm**
- Hiển thị nội dung từ trường `ct_sp`
- Hỗ trợ HTML, hình ảnh, video, bảng biểu
- Tab này được mở mặc định

**⭐ Tab 2: Đánh giá**
- Hiển thị form đánh giá + danh sách đánh giá
- Chuyển từ section cũ sang tab mới

---

## 🎨 TÍNH NĂNG CHI TIẾT

### Upload ảnh phụ:
✅ Hỗ trợ nhiều định dạng: JPG, PNG, GIF, WebP
✅ Tự động đổi tên nếu trùng (thêm _sub1, _sub2...)
✅ Lưu trong thư mục danh mục tương ứng
✅ Xóa ảnh cũ khi thay thế

### TinyMCE Editor:
✅ Upload ảnh trực tiếp vào editor
✅ Hỗ trợ video embed (YouTube, Vimeo)
✅ Tạo bảng thông số đẹp
✅ Format văn bản đa dạng
✅ Preview trước khi lưu

### Hiển thị ảnh:
✅ Responsive trên mobile
✅ Click để phóng to
✅ Smooth transition
✅ Tự động lấy ảnh chính + ảnh phụ

### Tabs:
✅ Bootstrap 5 tabs
✅ Responsive design
✅ Smooth switching
✅ Icon đẹp mắt

---

## 📂 CẤU TRÚC DỮ LIỆU

### Cột `anh_con` trong database:
```
img/products/ao/ao1_sub1.jpg|img/products/ao/ao1_sub2.jpg|img/products/ao/ao1_sub3.jpg
```
- Phân cách bởi dấu `|`
- Mỗi item là 1 đường dẫn ảnh

### Cột `ct_sp` trong database:
```html
<h3>Thông số kỹ thuật</h3>
<table>
  <tr><td>CPU</td><td>Intel Core i5</td></tr>
  <tr><td>RAM</td><td>8GB DDR4</td></tr>
</table>
<img src="..." />
```
- Lưu HTML thuần
- Hỗ trợ tất cả thẻ HTML

---

## 🔧 FILES ĐÃ THAY ĐỔI

1. ✅ **update_san_pham_columns.sql** - Migration SQL (MỚI)
2. ✅ **admin/sanpham.php** - Form quản lý sản phẩm
   - Thêm input upload nhiều ảnh
   - Thêm TinyMCE editor cho thông số
   - Xử lý logic upload & lưu
3. ✅ **sproduct.php** - Trang chi tiết sản phẩm
   - Hiển thị ảnh phụ
   - Tạo tabs Thông số & Đánh giá
   - Thêm Bootstrap 5

---

## 🚀 HƯỚNG DẪN SỬ DỤNG NHANH

### Thêm sản phẩm mới:
1. Vào Admin → Sản phẩm → Thêm sản phẩm
2. Điền thông tin cơ bản
3. Chọn ảnh chính
4. **Chọn 3-5 ảnh phụ** (Ctrl + Click)
5. Nhập mô tả vào editor đầu tiên
6. **Nhập thông số sản phẩm** vào editor thứ 2
7. Click **Lưu**

### Sửa sản phẩm:
1. Click **Sửa** ở sản phẩm cần chỉnh
2. Form mở với dữ liệu cũ
3. Ảnh phụ cũ hiển thị dưới input
4. Để giữ ảnh cũ = không chọn file mới
5. Để thay ảnh = chọn file mới
6. Click **Cập nhật**

---

## ⚠️ LỖI THƯỜNG GẶP & CÁCH XỬ LÝ

### Lỗi: "Không thể upload ảnh"
**Nguyên nhân:** Folder không có quyền ghi
**Giải pháp:** 
```bash
chmod 755 img/products
chmod 755 img/products/*
```

### Lỗi: "Tabs không hoạt động"
**Nguyên nhân:** Thiếu Bootstrap
**Giải pháp:** Đã thêm Bootstrap 5 CDN vào sproduct.php

### Lỗi: "TinyMCE không load"
**Nguyên nhân:** API key sai hoặc mạng chậm
**Giải pháp:** Kiểm tra kết nối internet

### Lỗi database:
**Nguyên nhân:** Chưa chạy migration SQL
**Giải pháp:** Chạy file `update_san_pham_columns.sql`

---

## 📞 HỖ TRỢ

Nếu gặp vấn đề, kiểm tra:
1. ✅ Đã chạy migration SQL chưa?
2. ✅ Quyền ghi folder img/products?
3. ✅ File upload_image.php có tồn tại?
4. ✅ Bootstrap đã load chưa?

---

## 🎉 HOÀN THÀNH!

Giờ đây bạn có thể:
- ✅ Upload nhiều ảnh cho 1 sản phẩm
- ✅ Tạo thông số sản phẩm đẹp với editor
- ✅ Hiển thị ảnh phụ trên trang sản phẩm
- ✅ Xem thông số qua tab chuyên biệt

**Chúc bạn sử dụng tốt! 🚀**
