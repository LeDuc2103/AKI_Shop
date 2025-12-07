# Hướng dẫn sử dụng hệ thống đánh giá sản phẩm - AKI Store

## 📋 Tổng quan

Hệ thống đánh giá sản phẩm cho phép:
- **Khách hàng đã đăng nhập**: Đánh giá sản phẩm với số sao (1-5) và nội dung
- **Nhân viên bán hàng**: Xem, quản lý, ẩn/hiện và xóa đánh giá

---

## 🗄️ Cấu trúc database

### Bảng `comments` (đã có sẵn)

```sql
CREATE TABLE `comments` (
  `id_danh_gia` bigint(20) unsigned NOT NULL auto_increment,
  `id_sanpham` bigint(20) unsigned NOT NULL,
  `ma_user` bigint(20) unsigned NOT NULL,
  `xep_hang` tinyint(1) NOT NULL default '5',
  `noi_dung` text,
  `ngay_danh_gia` datetime default NULL,
  `trang_thai` enum('hien','an') default 'hien',
  `created_at` timestamp NULL default NULL,
  `updated_at` timestamp NULL default NULL,
  PRIMARY KEY (`id_danh_gia`),
  KEY `id_sanpham` (`id_sanpham`),
  KEY `ma_user` (`ma_user`)
);
```

### JOIN với bảng `user`

Hệ thống sử dụng **LEFT JOIN** để lấy thông tin người dùng:

```sql
SELECT c.*, u.ho_ten as ten_nguoi_danh_gia
FROM comments c
LEFT JOIN user u ON c.ma_user = u.ma_user
WHERE c.id_sanpham = ? AND c.trang_thai = 'hien'
```

---

## 👥 Sử dụng cho Khách hàng

### 1. Đăng nhập trước khi đánh giá

Khách hàng **PHẢI đăng nhập** mới có thể đánh giá sản phẩm.

### 2. Đánh giá sản phẩm

1. Truy cập trang chi tiết sản phẩm: `sproduct.php?id=X`
2. Cuộn xuống phần **"Đánh giá sản phẩm"**
3. Điền thông tin:
   - **Đánh giá**: Chọn số sao từ 1-5 ⭐
   - **Nội dung đánh giá**: Viết nhận xét (tối thiểu 10 ký tự)
4. Nhấn **"Gửi đánh giá"**

### 3. Giao diện form đánh giá

```
┌─────────────────────────────────────┐
│ 📝 Viết đánh giá của bạn            │
│                                     │
│ Đánh giá: ☆☆☆☆☆ *                  │
│                                     │
│ Nội dung đánh giá: *                │
│ [___________________________]       │
│ [___________________________]       │
│                                     │
│ [✉ Gửi đánh giá]                   │
└─────────────────────────────────────┘
```

**Nếu chưa đăng nhập:**
```
┌─────────────────────────────────────┐
│ ⚠️ Vui lòng đăng nhập để đánh giá   │
│    sản phẩm.                        │
└─────────────────────────────────────┘
```

### 4. Xem đánh giá

Sau khi gửi, đánh giá sẽ hiển thị ngay phía dưới form:

```
┌─────────────────────────────────────┐
│ Đánh giá từ khách hàng (10)         │
│                                     │
│ ⭐ 4.5/5 (10 đánh giá)              │
│                                     │
│ ┌─────────────────────────────────┐ │
│ │ 👤 Lê Văn Túc    ⭐⭐⭐⭐⭐       │ │
│ │ Sản phẩm rất tốt, giao hàng     │ │
│ │ nhanh chóng!                    │ │
│ │ 🕒 03/12/2024 15:30             │ │
│ └─────────────────────────────────┘ │
└─────────────────────────────────────┘
```

---

## 👨‍💼 Sử dụng cho Nhân viên bán hàng

### 1. Đăng nhập

Đăng nhập với tài khoản nhân viên bán hàng tại `login.php`

### 2. Truy cập quản lý đánh giá

1. Vào trang `nhanvienbanhang.php`
2. Click menu **"⭐ Quản lý Đánh giá"**

### 3. Giao diện quản lý

```
┌──────────────────────────────────────────────────────┐
│ ⭐ Quản lý Đánh giá Sản phẩm                         │
├──────────────────────────────────────────────────────┤
│                                                      │
│ 📊 THỐNG KÊ                                          │
│ ┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐        │
│ │  150   │ │  4.3⭐ │ │  145   │ │   5    │        │
│ │ Tổng   │ │ TB    │ │ Hiện   │ │ Ẩn    │        │
│ └────────┘ └────────┘ └────────┘ └────────┘        │
│                                                      │
│ 🔍 BỘ LỌC                                            │
│ Tìm kiếm: [____________] Sao: [Tất cả ▼]            │
│ Trạng thái: [Tất cả ▼]  [Lọc] [Đặt lại]             │
│                                                      │
│ ─────────────────────────────────────────────────    │
│                                                      │
│ 📦 Máy đọc sách Kindle            [Hiển thị]        │
│ ⭐⭐⭐⭐⭐                                             │
│ 👤 Lê Văn Túc  🕒 03/12/2024 15:30                  │
│ Sản phẩm rất tốt!                                   │
│                                                      │
│ [👁 Ẩn] [🗑 Xóa]                                     │
│                                                      │
└──────────────────────────────────────────────────────┘
```

### 4. Chức năng

#### A. Xem thống kê

4 card thống kê hiển thị:
- **Tổng đánh giá**: Số lượng đánh giá tổng cộng
- **Đánh giá TB**: Trung bình số sao
- **Đang hiển thị**: Số đánh giá có trạng thái 'hien'
- **Đã ẩn**: Số đánh giá có trạng thái 'an'

#### B. Bộ lọc

- **Tìm kiếm**: Theo tên sản phẩm, tên người đánh giá, nội dung
- **Số sao**: Lọc theo 1-5 sao
- **Trạng thái**: Lọc hiển thị/ẩn

#### C. Ẩn/Hiện đánh giá

- Click nút **"👁 Ẩn"**: Đánh giá sẽ không hiển thị trên trang sản phẩm
- Click nút **"👁 Hiện"**: Đánh giá sẽ hiển thị lại
- Cập nhật trường `trang_thai` trong database

#### D. Xóa đánh giá

- Click nút **"🗑 Xóa"**
- Xác nhận: "Bạn có chắc muốn xóa đánh giá này?"
- Xóa vĩnh viễn khỏi database

#### E. Phân trang

- Hiển thị 10 đánh giá/trang
- Điều hướng: `< 1 2 3 ... >`

---

## 🔧 Cấu trúc code

### File `sproduct.php`

#### **1. Lấy danh sách đánh giá**

```php
// Đếm tổng và tính trung bình
$stmt = $conn->prepare("SELECT COUNT(*) as total, AVG(xep_hang) as avg_rating 
                       FROM comments 
                       WHERE id_sanpham = ? AND trang_thai = 'hien'");

// Lấy danh sách với JOIN user
$stmt = $conn->prepare("SELECT c.*, u.ho_ten as ten_nguoi_danh_gia,
                       DATE_FORMAT(c.ngay_danh_gia, '%d/%m/%Y %H:%i') as ngay_formatted
                       FROM comments c
                       LEFT JOIN user u ON c.ma_user = u.ma_user
                       WHERE c.id_sanpham = ? AND c.trang_thai = 'hien'
                       ORDER BY c.ngay_danh_gia DESC");
```

#### **2. Xử lý submit đánh giá**

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $ma_user = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    // Kiểm tra đăng nhập
    if (empty($ma_user)) {
        $review_error = 'Vui lòng đăng nhập để đánh giá sản phẩm!';
    } else {
        // INSERT vào bảng comments
        $stmt = $conn->prepare("INSERT INTO comments 
                (id_sanpham, ma_user, xep_hang, noi_dung, ngay_danh_gia, created_at, trang_thai) 
                VALUES (?, ?, ?, ?, NOW(), NOW(), 'hien')");
        $stmt->execute(array($product_id, $ma_user, $rating, $review_text));
    }
}
```

#### **3. HTML Form**

```php
<?php if (isset($_SESSION['user_id'])): ?>
    <form method="POST">
        <!-- Form đánh giá -->
    </form>
<?php else: ?>
    <div class="alert alert-warning">
        Vui lòng <a href="login.php">đăng nhập</a> để đánh giá sản phẩm.
    </div>
<?php endif; ?>
```

---

### File `nhanvien/danh_gia.php`

#### **1. Xử lý xóa**

```php
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM comments WHERE id_danh_gia = ?");
    $stmt->execute(array($delete_id));
}
```

#### **2. Xử lý ẩn/hiện**

```php
if (isset($_GET['toggle_status'])) {
    $stmt = $conn->prepare("UPDATE comments 
                           SET trang_thai = CASE WHEN trang_thai = 'hien' THEN 'an' ELSE 'hien' END
                           WHERE id_danh_gia = ?");
}
```

#### **3. Lấy danh sách với bộ lọc**

```php
$query = "SELECT c.*, 
          sp.ten_sanpham, sp.hinh_anh,
          u.ho_ten as ten_nguoi_danh_gia,
          DATE_FORMAT(c.ngay_danh_gia, '%d/%m/%Y %H:%i') as ngay_formatted
          FROM comments c
          LEFT JOIN san_pham sp ON c.id_sanpham = sp.id_sanpham
          LEFT JOIN user u ON c.ma_user = u.ma_user
          WHERE ...
          ORDER BY c.ngay_danh_gia DESC
          LIMIT {$per_page} OFFSET {$offset}";
```

---

## 🎨 CSS Styling

### Gradient Cards (Thống kê)

```css
.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    padding: 25px;
}
```

### Review Items

```css
.review-item {
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    border-left: 4px solid #088178;
}
```

### Star Rating (Form)

```css
.star-rating {
    display: flex;
    flex-direction: row-reverse;
}

.star-rating input:checked ~ label,
.star-rating label:hover,
.star-rating label:hover ~ label {
    color: #ffc107;
}
```

---

## 📊 Flow hoạt động

### Flow khách hàng đánh giá

```
1. User đăng nhập
   ↓
2. Vào trang sproduct.php?id=X
   ↓
3. Cuộn xuống phần "Đánh giá sản phẩm"
   ↓
4. Chọn số sao (1-5)
   ↓
5. Nhập nội dung (min 10 ký tự)
   ↓
6. Click "Gửi đánh giá"
   ↓
7. INSERT vào bảng comments với:
   - id_sanpham
   - ma_user (từ session)
   - xep_hang (số sao)
   - noi_dung
   - ngay_danh_gia = NOW()
   - trang_thai = 'hien'
   ↓
8. Hiển thị thông báo thành công
   ↓
9. Đánh giá xuất hiện trong danh sách
```

### Flow nhân viên quản lý

```
1. Nhân viên đăng nhập
   ↓
2. Vào nhanvienbanhang.php
   ↓
3. Click "Quản lý Đánh giá"
   ↓
4. Xem danh sách đánh giá (JOIN với user, san_pham)
   ↓
5. Chọn hành động:
   
   A. Ẩn đánh giá:
      - UPDATE trang_thai = 'an'
      - Không hiển thị trên sproduct.php
      
   B. Hiện đánh giá:
      - UPDATE trang_thai = 'hien'
      - Hiển thị lại trên sproduct.php
      
   C. Xóa đánh giá:
      - DELETE FROM comments
      - Xóa vĩnh viễn
```

---

## ✅ Checklist triển khai

- [x] Bảng `comments` đã có sẵn trong database
- [x] File `sproduct.php` đã cập nhật
- [x] File `nhanvienbanhang.php` đã thêm menu
- [x] File `nhanvien/danh_gia.php` đã tạo
- [x] CSS đã thêm vào `style.css`
- [ ] Test đăng nhập và đánh giá sản phẩm
- [ ] Test ẩn/hiện đánh giá từ trang nhân viên
- [ ] Test xóa đánh giá
- [ ] Test bộ lọc (tìm kiếm, số sao, trạng thái)

---

## 🐛 Xử lý lỗi thường gặp

### 1. Không thấy form đánh giá

**Nguyên nhân**: Chưa đăng nhập

**Giải pháp**: Đăng nhập tại `login.php`

### 2. Tên người đánh giá hiển thị NULL

**Nguyên nhân**: `ma_user` trong comments không khớp với bảng `user`

**Giải pháp**: 
```sql
-- Kiểm tra
SELECT c.*, u.ho_ten 
FROM comments c 
LEFT JOIN user u ON c.ma_user = u.ma_user 
WHERE u.ma_user IS NULL;
```

### 3. Không xóa được đánh giá

**Nguyên nhân**: Lỗi quyền hoặc ID không tồn tại

**Giải pháp**: Kiểm tra session nhân viên và ID đánh giá

### 4. Thống kê không chính xác

**Nguyên nhân**: Query tính toán sai

**Giải pháp**: Kiểm tra lại điều kiện `trang_thai = 'hien'`

---

## 🚀 Tính năng đã hoàn thành

✅ **Khách hàng:**
- Đăng nhập để đánh giá
- Form đánh giá với số sao và nội dung
- Xem danh sách đánh giá đã duyệt
- Hiển thị trung bình số sao
- Hiển thị tên từ bảng `user` (JOIN)

✅ **Nhân viên:**
- Menu "Quản lý Đánh giá"
- Thống kê tổng quan (4 cards)
- Bộ lọc: Tìm kiếm, số sao, trạng thái
- Ẩn/Hiện đánh giá
- Xóa đánh giá
- Phân trang 10 items/page
- JOIN với bảng `user` và `san_pham`

---

**Hệ thống đã sẵn sàng sử dụng!** 🎉
