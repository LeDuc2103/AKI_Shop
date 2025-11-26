# Hướng Dẫn Quản Lý Sản Phẩm - KLTN Admin Panel

## 📋 Tổng Quan
Đã hoàn thành tích hợp hệ thống quản lý sản phẩm vào Admin Panel với kết nối cơ sở dữ liệu bảng `san_pham` và hiển thị sản phẩm trên các trang người dùng.

---

## ✅ Những Gì Đã Hoàn Thành

### 1. **Admin Panel - Quản Lý Sản Phẩm** (`admin.php`)

#### 📌 Chức năng đã thêm:
- ✅ Menu "Quản lý Sản phẩm" trong sidebar (có active state)
- ✅ Trang "Danh sách Sản phẩm" với bảng hiển thị đầy đủ thông tin:
  - Mã sản phẩm (ID)
  - Hình ảnh (hiển thị thumbnail 50x50px)
  - Tên sản phẩm
  - Giá bán (định dạng VNĐ)
  - Số lượng tồn kho (badge màu theo mức tồn)
  - Danh mục
  - Trạng thái (Đang bán/Ngừng bán)
  - Ngày tạo
  - Thao tác (nút Sửa/Xóa)

#### 📌 Modal Thêm Sản Phẩm:
- Form nhập đầy đủ thông tin sản phẩm mới:
  - Tên sản phẩm (*)
  - Giá bán (*)
  - Số lượng (*)
  - Danh mục
  - Mô tả
  - Hình ảnh (URL)
  - Trạng thái (Đang bán/Ngừng bán)

#### 📌 Xử lý Backend:
```php
// Đã thêm function getSanPhamList()
function getSanPhamList($conn) {
    $stmt = $conn->prepare("SELECT * FROM san_pham ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Xử lý POST thêm sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    // Lấy ID tự động tăng
    // Insert vào bảng san_pham
    // Hiển thị thông báo thành công/lỗi
}
```

---

### 2. **Trang Shop - Hiển Thị Sản Phẩm** (`shop.php`)

#### 📌 Cập nhật:
- ✅ Kết nối database `config/database.php`
- ✅ Lấy danh sách sản phẩm từ bảng `san_pham`:
```php
$stmt = $conn->prepare("SELECT * FROM san_pham WHERE trang_thai = 'active' OR trang_thai IS NULL ORDER BY created_at DESC");
```

- ✅ Thay thế sản phẩm hardcode bằng vòng lặp PHP:
```php
<?php foreach ($products as $product): ?>
    <div class="pro" onclick="window.location.href='sproduct.php?id=<?php echo $product['ma_sanpham']; ?>';">
        <!-- Hiển thị badge giảm giá nếu có ma_km -->
        <!-- Hiển thị hình ảnh từ database -->
        <!-- Hiển thị tên, giá, danh mục từ database -->
    </div>
<?php endforeach; ?>
```

#### 📌 Tính năng hiển thị:
- Hình ảnh sản phẩm từ database
- Badge giảm giá tự động (nếu có `ma_km`)
- Tên sản phẩm
- Danh mục sản phẩm
- Giá (định dạng VNĐ)
- Rating 5 sao
- Nút thêm vào giỏ hàng

---

### 3. **Trang Index** (`index.php`)

#### 📌 Trạng thái:
- ✅ Đã có sẵn kết nối database
- ✅ Đã hiển thị sản phẩm từ bảng `san_pham`:
  - Featured Products: 8 sản phẩm có khuyến mãi (`ma_km`)
  - New Products: 8 sản phẩm mới nhất

---

## 🎨 Giao Diện Admin Panel

### Sidebar Navigation:
```
🏠 Dashboard
👥 Quản lý Nhân viên
👫 Quản lý Khách hàng
📦 Quản lý Sản phẩm ⭐ [MỚI]
🛒 Quản lý Đơn hàng
```

### Màu Badge Số Lượng:
- 🟢 **Xanh lá** (success): > 10 sản phẩm
- 🟡 **Vàng** (warning): 1-10 sản phẩm
- 🔴 **Đỏ** (danger): 0 sản phẩm (hết hàng)

### Màu Badge Trạng Thái:
- 🟢 **Xanh lá** (success): Đang bán
- ⚪ **Xám** (secondary): Ngừng bán

---

## 📊 Cấu Trúc Database

### Bảng `san_pham`:
```sql
- ma_sanpham (ID)
- ten_sanpham (Tên)
- gia (Giá bán)
- so_luong (Số lượng tồn)
- ma_danhmuc (Danh mục)
- mo_ta (Mô tả)
- hinh_anh (Đường dẫn hình)
- ma_km (Mã khuyến mãi %)
- trang_thai (active/inactive)
- created_at (Ngày tạo)
```

---

## 🚀 Hướng Dẫn Sử Dụng

### 1. Truy cập Admin Panel:
```
URL: http://localhost/KLTN/admin.php
```

### 2. Đăng nhập với tài khoản quản lý:
- Vai trò: `quanly` hoặc `nhanvien`
- Chỉ các tài khoản này mới vào được admin panel

### 3. Quản lý sản phẩm:
1. Click menu **"Quản lý Sản phẩm"** ở sidebar
2. Xem danh sách tất cả sản phẩm trong bảng
3. Click nút **"Thêm Sản phẩm"** để thêm mới
4. Điền form và nhấn **"Thêm"**
5. Sản phẩm mới sẽ hiển thị ngay trên bảng

### 4. Kiểm tra hiển thị trên trang người dùng:
- **Trang chủ** (`index.php`): Xem sản phẩm nổi bật và mới
- **Trang sản phẩm** (`shop.php`): Xem toàn bộ sản phẩm

---

## 🔧 Các File Đã Cập Nhật

### 1. `admin.php`:
- ✅ Thêm function `getSanPhamList()`
- ✅ Thêm xử lý POST `add_product`
- ✅ Thêm section "Quản lý Sản phẩm"
- ✅ Thêm modal "Thêm Sản phẩm"
- ✅ Update sidebar menu active state

### 2. `shop.php`:
- ✅ Thêm kết nối database ở đầu file
- ✅ Thay thế hardcode products bằng database query
- ✅ Thêm vòng lặp PHP hiển thị sản phẩm động
- ✅ Thêm điều kiện hiển thị badge giảm giá

### 3. `index.php`:
- ✅ Đã có sẵn, không cần sửa (đã kết nối database)

---

## 🎯 Tính Năng Đang Hoạt Động

✅ **Admin Panel:**
- Xem danh sách sản phẩm từ database
- Thêm sản phẩm mới vào database
- Hiển thị hình ảnh, giá, số lượng, trạng thái
- Badge màu tự động theo số lượng tồn

✅ **Trang Người Dùng:**
- `index.php`: Hiển thị 16 sản phẩm (8 featured + 8 new)
- `shop.php`: Hiển thị toàn bộ sản phẩm có `trang_thai = 'active'`
- Badge giảm giá tự động nếu có `ma_km`
- Click vào sản phẩm → chuyển đến `sproduct.php?id=X`

---

## 📝 Lưu Ý

### 1. Hình ảnh sản phẩm:
- Nhập đường dẫn tương đối, ví dụ: `img/products/product.jpg`
- Nếu để trống hoặc NULL → hiển thị ảnh mặc định `img/products/f1.jpg`

### 2. Trạng thái sản phẩm:
- `active`: Đang bán → hiển thị trên shop
- `inactive`: Ngừng bán → không hiển thị trên shop
- `NULL`: Coi như active → hiển thị trên shop

### 3. Mã khuyến mãi (`ma_km`):
- Nhập số %, ví dụ: 15, 20, 30
- Nếu có giá trị → hiển thị badge giảm giá
- Nếu NULL hoặc rỗng → không hiển thị badge

### 4. ID tự động tăng:
- Hệ thống tự động lấy `MAX(ma_sanpham) + 1`
- Không cần nhập thủ công

---

## 🔄 Luồng Dữ Liệu

```
Admin thêm sản phẩm
       ↓
admin.php (POST add_product)
       ↓
INSERT INTO san_pham
       ↓
Database kltn
       ↓
SELECT * FROM san_pham
       ↓
index.php & shop.php
       ↓
Hiển thị cho khách hàng
```

---

## 🎨 Thiết Kế

### Admin Panel:
- Bootstrap 5 cards & tables
- FontAwesome 6 icons
- Gradient sidebar: #008187 → #006064
- Responsive design
- Hover effects trên buttons

### Frontend (shop.php):
- Grid layout responsive
- Product cards với hover effect
- Discount badges động
- Click-through đến chi tiết sản phẩm

---

## 🚧 Tính Năng Có Thể Mở Rộng

### 1. Chỉnh sửa sản phẩm:
- Thêm xử lý `edit_product`
- Modal sửa thông tin
- UPDATE query

### 2. Xóa sản phẩm:
- Thêm xử lý `delete_product`
- Confirm dialog
- Soft delete hoặc hard delete

### 3. Tìm kiếm/Lọc:
- Tìm theo tên
- Lọc theo danh mục
- Lọc theo trạng thái

### 4. Upload hình ảnh:
- Thêm `enctype="multipart/form-data"`
- Xử lý upload file PHP
- Lưu vào thư mục `img/products/`

### 5. Phân trang:
- Giới hạn 20 sản phẩm/trang
- Pagination links
- AJAX load more

---

## ✨ Kết Luận

Hệ thống quản lý sản phẩm đã hoàn tất với:
- ✅ Menu "Quản lý Sản phẩm" trong admin panel
- ✅ Danh sách sản phẩm từ database `san_pham`
- ✅ Form thêm sản phẩm mới
- ✅ Hiển thị sản phẩm trên `shop.php` từ database
- ✅ Hiển thị sản phẩm trên `index.php` (đã có sẵn)
- ✅ Badge giảm giá tự động
- ✅ UI/UX chuyên nghiệp với Bootstrap 5

**Tất cả các yêu cầu đã được thực hiện thành công! 🎉**

---

## 📞 Hỗ Trợ

Nếu có lỗi hoặc cần thêm tính năng, vui lòng kiểm tra:
1. Kết nối database trong `config/database.php`
2. Bảng `san_pham` đã có dữ liệu chưa
3. Session đã đăng nhập với vai trò `quanly` hoặc `nhanvien`
4. WAMP server đang chạy (Apache + MySQL)

---

*Tài liệu được tạo ngày: 2025*
*Dự án: KLTN - Quản lý bán hàng E-commerce*
*Phát triển bởi: Le Van Tuc - Huynh Dinh Chieu*
