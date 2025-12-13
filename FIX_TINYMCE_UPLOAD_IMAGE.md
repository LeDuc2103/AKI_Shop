# Sửa lỗi Upload Ảnh trong TinyMCE Editor

## Ngày sửa: 9/12/2025

## Các lỗi đã sửa:

### 1. Lỗi đường dẫn upload ảnh trong TinyMCE

**File: `admin/sanpham.php`**
- **Vấn đề**: Đường dẫn upload ảnh trong TinyMCE sai `'../nhanvien/upload_image.php'`
- **Sửa thành**: `'nhanvien/upload_image.php'` (đường dẫn tương đối từ file admin.php)
- **Vị trí**: Dòng ~639 trong cấu hình `images_upload_handler`

**File: `nhanvien/tin_tuc.php`**
- **Vấn đề**: Đã đúng đường dẫn `'../nhanvien/upload_image.php'` vì file này được include từ `nhanvienbanhang.php`
- **Giữ nguyên**: Không cần sửa

**File: `nhanvien/upload_image.php`**
- **Vấn đề**: Đường dẫn thư mục upload dùng relative path `'../img/blog/'` không ổn định
- **Sửa thành**: Dùng đường dẫn tuyệt đối `dirname(__FILE__) . '/../img/blog/'`
- **Thêm**: Log chi tiết khi upload lỗi để dễ debug

---

### 2. Lỗi xử lý nội dung HTML từ TinyMCE

**File: `admin/sanpham.php`**

#### A. Xử lý dữ liệu POST (dòng ~71 và ~214)
- **Vấn đề**: Dùng `trim()` cho `mo_ta` và `ct_sp` làm mất định dạng HTML
- **Sửa thành**: 
  ```php
  $mo_ta = isset($_POST['mo_ta']) ? $_POST['mo_ta'] : ''; // Giữ nguyên HTML từ TinyMCE
  $ct_sp = isset($_POST['ct_sp']) ? $_POST['ct_sp'] : ''; // Giữ nguyên HTML từ TinyMCE
  ```

#### B. Hiển thị trong textarea (dòng ~573-577)
- **Vấn đề**: Dùng `htmlspecialchars()` làm TinyMCE không đọc được HTML đúng
- **Sửa thành**: 
  ```php
  <textarea name="mo_ta" id="tinymce_editor_product"><?php echo $is_edit ? $edit_data['mo_ta'] : (isset($mo_ta) ? $mo_ta : ''); ?></textarea>
  ```
  
**File: `nhanvien/tin_tuc.php`**
- **Đã đúng**: Không dùng `htmlspecialchars()` và không `trim()` nội dung HTML

---

## Tóm tắt các thay đổi:

### admin/sanpham.php:
1. ✅ Sửa đường dẫn upload: `'../nhanvien/upload_image.php'` → `'nhanvien/upload_image.php'`
2. ✅ Bỏ `trim()` cho `mo_ta` và `ct_sp` ở 2 chỗ (thêm & sửa sản phẩm)
3. ✅ Bỏ `htmlspecialchars()` trong textarea TinyMCE

### nhanvien/upload_image.php:
1. ✅ Đổi đường dẫn upload từ relative sang absolute
2. ✅ Thêm log debug khi upload lỗi

### nhanvien/tin_tuc.php:
1. ✅ Giữ nguyên (đã đúng)

---

## Hướng dẫn kiểm tra:

### 🔧 BƯỚC 1: Test đường dẫn upload
1. Mở trình duyệt và truy cập: `http://localhost/KLTN_AKISTORE/test_upload_path.php`
2. Kiểm tra tất cả các test phải hiển thị "YES"
3. Thử upload một ảnh test bằng form
4. ✅ Nếu upload thành công → Đường dẫn đúng, chuyển sang bước 2
5. ❌ Nếu upload lỗi → Xem console log và fix lỗi

### 📝 BƯỚC 2: Kiểm tra upload ảnh trong Mô tả sản phẩm:
1. Đăng nhập admin
2. Vào **Quản lý sản phẩm** → **Thêm sản phẩm**
3. Mở **Console** trong trình duyệt (F12 → Console tab)
4. Trong editor **Mô tả**, click biểu tượng **Image**
5. Chọn ảnh từ máy tính và upload
6. Xem log trong Console:
   - Phải thấy: "Bắt đầu upload ảnh: xxx.jpg"
   - Phải thấy: "Upload hoàn tất. Status: 200"
   - Phải thấy: "Upload thành công: img/blog/xxx.jpg"
7. ✅ Ảnh phải hiển thị ngay trong editor

### 2. Kiểm tra upload ảnh trong Thông số sản phẩm:
- Làm tương tự ở phần **Thông số sản phẩm**

### 3. Kiểm tra upload ảnh trong Nội dung tin tức:
1. Đăng nhập nhân viên bán hàng
2. Vào **Quản lý tin tức** → **Thêm tin tức**
3. Trong editor **Nội dung**, upload ảnh
4. ✅ Ảnh phải hiển thị ngay trong editor

### 4. Kiểm tra lưu và hiển thị:
1. Thêm sản phẩm/tin tức với ảnh trong editor
2. Lưu lại
3. Vào xem chi tiết hoặc sửa lại
4. ✅ Ảnh và HTML phải hiển thị đúng

---

## Lưu ý quan trọng:

### Quyền thư mục:
Đảm bảo thư mục `img/blog/` có quyền ghi:
```bash
chmod 755 img/blog/
```

### Kích thước file upload:
Kiểm tra trong `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 10M
```

### Debug:
Nếu vẫn lỗi, kiểm tra log PHP hoặc xem Console trong trình duyệt (F12)

---

## Nguyên nhân lỗi ban đầu:

**Lỗi "Cannot read properties of undefined (reading 'then')"** xảy ra vì:
1. TinyMCE gọi API upload ảnh với đường dẫn SAI
2. Server trả về lỗi 404 hoặc không phải JSON hợp lệ
3. TinyMCE không xử lý được response → lỗi JavaScript

**Sau khi sửa:**
- Đường dẫn đúng → Server nhận được request
- File được upload thành công → trả về JSON `{location: "img/blog/xxx.jpg"}`
- TinyMCE hiển thị ảnh trong editor ✅
