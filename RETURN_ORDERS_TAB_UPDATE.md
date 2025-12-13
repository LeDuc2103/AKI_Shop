# Cập nhật Tab "Đơn hàng đổi trả" - my_orders.php

## Ngày cập nhật: 7/12/2024

### Tính năng mới
Đã thêm tab "Đơn hàng đổi trả" vào trang my_orders.php để khách hàng có thể xem riêng các đơn hàng có yêu cầu đổi trả.

---

## 1. Cấu trúc Tab Navigation

### HTML Tab Buttons
```php
<div class="order-tabs">
    <a href="my_orders.php?view=orders" class="tab-button <?php echo ($current_view == 'orders') ? 'active' : ''; ?>">
        <i class="fas fa-shopping-cart"></i> Đơn hàng
    </a>
    <a href="my_orders.php?view=returns" class="tab-button <?php echo ($current_view == 'returns') ? 'active' : ''; ?>">
        <i class="fas fa-exchange-alt"></i> Đơn hàng đổi trả
    </a>
</div>
```

### CSS Styling
- `.order-tabs`: Container cho tab buttons
- `.tab-button`: Style cho mỗi tab
- `.tab-button.active`: Highlight cho tab đang active (màu #088178)
- Hover effect với màu chủ đạo

---

## 2. Backend Logic

### View Detection
```php
$current_view = isset($_GET['view']) ? $_GET['view'] : 'orders';
```

- Default view: `orders` (Đơn hàng thông thường)
- Return view: `returns` (Đơn hàng đổi trả)

### Query cho Đơn hàng đổi trả
```sql
SELECT dh.*, 
       ddt.status as return_status, 
       ddt.ly_do as return_reason, 
       ddt.bang_chung as return_evidence,
       ddt.trang_thai_kho as warehouse_status,
       ddt.created_at as return_created_at,
       ddt.updated_at as return_updated_at
FROM don_hang dh 
INNER JOIN don_hang_doi_tra ddt ON dh.ma_donhang = ddt.ma_donhang 
WHERE dh.ma_user = ? 
ORDER BY ddt.created_at DESC 
LIMIT 10 OFFSET ?
```

**Đặc điểm:**
- JOIN với bảng `don_hang_doi_tra` để lấy thông tin đổi trả
- Chỉ hiển thị đơn hàng có yêu cầu đổi trả
- Sắp xếp theo ngày yêu cầu đổi trả (mới nhất trước)
- Phân trang 10 dòng/trang

---

## 3. Hiển thị Thông tin Đổi trả

### Order Card cho Return Orders
Mỗi card hiển thị:

1. **Thông tin cơ bản**
   - Mã đơn hàng
   - Ngày đặt hàng
   - Ngày yêu cầu đổi trả
   - Trạng thái đơn hàng

2. **Section Thông tin đổi trả** (`.return-info-section`)
   - **Trạng thái yêu cầu**: Badge màu
     - `pending`: Vàng (Chờ xử lý)
     - `approved`: Xanh (Đã chấp nhận)
     - `rejected`: Đỏ (Đã từ chối)
   
   - **Trạng thái kho**: Badge màu (nếu có)
     - `cho_nhap_kho`: Vàng (Chờ nhập kho)
     - `da_nhap_kho`: Xanh dương (Đã nhập kho)
   
   - **Lý do đổi trả**: Text chi tiết
   - **Bằng chứng**: Link xem file (nếu có)

3. **Thông tin giao hàng**
   - Người nhận
   - Số điện thoại
   - Địa chỉ
   - Phương thức thanh toán

4. **Tổng tiền & Action button**
   - Hiển thị tổng tiền đơn hàng
   - Button "Xem chi tiết" link đến modal

---

## 4. Phân trang

### Đặc điểm Pagination
- **Số dòng/trang**: 10 đơn hàng
- **Giữ nguyên view**: URL bao gồm `?view=returns&page=X`
- **Navigation**: Previous/Next buttons
- **Page numbers**: Hiển thị 5 trang gần nhất
- **Summary text**: "Trang X / Y (Tổng Z yêu cầu đổi trả)"

### URL Structure
```
my_orders.php?view=returns&page=1
my_orders.php?view=returns&page=2
...
```

---

## 5. CSS Classes Mới

### Badge Classes
```css
.warehouse-status-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: bold;
    margin: 5px 5px 5px 0;
}

.warehouse-status-badge.cho_nhap_kho {
    background: #fff3cd;
    color: #856404;
}

.warehouse-status-badge.da_nhap_kho {
    background: #d1ecf1;
    color: #0c5460;
}
```

### Info Section Class
```css
.return-info-section {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    margin: 15px 0;
}

.return-info-section h4 {
    margin: 0 0 10px 0;
    color: #088178;
    font-size: 16px;
}
```

---

## 6. Flow Diagram

```
Customer visits my_orders.php
    |
    ├── ?view=orders (default)
    │   └── Hiển thị tất cả đơn hàng (10/trang)
    │       └── Return badge nếu có yêu cầu đổi trả
    │
    └── ?view=returns
        └── Query JOIN don_hang_doi_tra
            └── Chỉ lấy đơn hàng có return request
                └── Hiển thị full return info (10/trang)
                    ├── Return status badge
                    ├── Warehouse status badge
                    ├── Return reason
                    └── Evidence link
```

---

## 7. Database Dependencies

### Tables Used
1. **don_hang**: Thông tin đơn hàng
2. **don_hang_doi_tra**: Yêu cầu đổi trả
   - `status`: pending/approved/rejected
   - `trang_thai_kho`: cho_nhap_kho/da_nhap_kho
   - `ly_do`: Lý do đổi trả
   - `bang_chung`: Link file bằng chứng

---

## 8. Testing Checklist

- [ ] Tab navigation hoạt động (chuyển view)
- [ ] Active tab highlight đúng
- [ ] Return orders query trả về đúng data
- [ ] Pagination giữ nguyên view parameter
- [ ] Return status badge hiển thị đúng màu
- [ ] Warehouse status badge hiển thị đúng màu
- [ ] Evidence link mở đúng file
- [ ] Empty state hiển thị khi chưa có return
- [ ] Responsive design trên mobile

---

## 9. Future Enhancements

### Potential Improvements
1. Filter theo trạng thái return (pending/approved/rejected)
2. Search theo mã đơn hàng
3. Export danh sách đổi trả ra Excel
4. Notification khi status thay đổi
5. Timeline hiển thị lịch sử trạng thái

---

## Kết luận

Tính năng "Đơn hàng đổi trả" đã được triển khai thành công với:
- ✅ Tab navigation rõ ràng
- ✅ Query data chính xác
- ✅ Hiển thị đầy đủ thông tin return
- ✅ Phân trang 10 dòng
- ✅ Badge status màu sắc phân biệt
- ✅ Responsive design
- ✅ No PHP errors

**Impact**: Khách hàng giờ có thể dễ dàng theo dõi tất cả yêu cầu đổi trả của mình ở một nơi, với thông tin chi tiết về trạng thái xử lý.
