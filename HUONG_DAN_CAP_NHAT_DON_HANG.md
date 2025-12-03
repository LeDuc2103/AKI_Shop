# HƯỚNG DẪN SỬ DỤNG CHỨC NĂNG CẬP NHẬT ĐƠN HÀNG

## Tổng quan
Đã tạo chức năng "Cập nhật trạng thái đơn hàng" cho nhân viên bán hàng tại trang `nhanvienbanhang.php`. Chức năng này giống với trang "Đơn hàng" ở admin.php nhưng có thêm khả năng cập nhật trạng thái thanh toán.

## Files đã tạo/cập nhật

### 1. File mới: `nhanvien/donhang.php`
**Chức năng:**
- Hiển thị danh sách tất cả đơn hàng
- Tìm kiếm đơn hàng theo ID, tên người nhận, hoặc email
- Xem chi tiết đơn hàng
- **Cập nhật trạng thái đơn hàng** (5 trạng thái):
  - Chờ xử lý (`cho_xu_ly`)
  - Đã xác nhận (`xac_nhan`)
  - Đã xuất kho (`da_xuat_kho`)
  - Hoàn thành (`hoan_thanh`)
  - Đã hủy (`huy`)
- **Cập nhật trạng thái thanh toán** (2 trạng thái - TÍNH NĂNG MỚI):
  - Chưa thanh toán (`chua_thanh_toan`)
  - Đã thanh toán (`da_thanh_toan`)

**Cách hoạt động:**
- Khi nhân viên click vào nút "Xem", họ sẽ thấy chi tiết đơn hàng
- Có 2 form riêng biệt:
  1. **Form cập nhật trạng thái đơn hàng**: Bên trái, nút màu xanh dương
  2. **Form cập nhật trạng thái thanh toán**: Bên phải, nút màu xanh lá (MỚI)
- Cả 2 form đều cập nhật trực tiếp vào bảng `don_hang` trong database `tuc.sql`

### 2. File đã cập nhật: `nhanvienbanhang.php`
**Thay đổi:**
- Thêm menu "Cập nhật đơn hàng" vào sidebar
- Thay đổi action mặc định từ `doi_tra` thành `donhang`
- Thêm routing cho action `donhang` để include file `nhanvien/donhang.php`

## Cấu trúc Database

### Bảng: `don_hang`
Cột được cập nhật:
1. **`trang_thai`** - Trạng thái đơn hàng
   - Kiểu: ENUM('cho_xu_ly','xac_nhan','da_xuat_kho','hoan_thanh','huy')
   
2. **`trangthai_thanhtoan`** - Trạng thái thanh toán (CẬP NHẬT MỚI)
   - Kiểu: ENUM('chua_thanh_toan','da_thanh_toan')
   
3. **`thanh_toan`** - Text mô tả trạng thái thanh toán
   - Kiểu: VARCHAR(50)
   - Giá trị: "đã thanh toán" hoặc "chưa thanh toán"

## Hướng dẫn sử dụng

### Đăng nhập
1. Truy cập: `http://localhost/KLTN_AKISTORE/login.php`
2. Đăng nhập với tài khoản nhân viên bán hàng (vai_tro = 'nhanvien')

### Quản lý đơn hàng
1. Sau khi đăng nhập, trang sẽ tự động hiển thị "Quản lý Đơn hàng"
2. Hoặc click vào menu "Cập nhật đơn hàng" ở sidebar bên trái

### Tìm kiếm đơn hàng
- **Tìm theo ID**: Nhập số ID đơn hàng (VD: 1, 2, 3) → Tìm chính xác
- **Tìm theo tên/email**: Nhập tên hoặc email → Tìm tương đối (LIKE)

### Xem chi tiết & Cập nhật
1. Click nút **"Xem"** ở cột "Hành động"
2. Trang chi tiết hiển thị:
   - Thông tin đơn hàng
   - Thông tin người nhận
   - Danh sách sản phẩm
   - Chi tiết thanh toán

3. **Cập nhật trạng thái đơn hàng** (Form bên trái):
   - Chọn trạng thái mới từ dropdown
   - Click nút "Cập nhật" (màu xanh dương)
   - Thông báo thành công sẽ hiển thị

4. **Cập nhật trạng thái thanh toán** (Form bên phải - TÍNH NĂNG MỚI):
   - Chọn: "Chưa thanh toán" hoặc "Đã thanh toán"
   - Click nút "Cập nhật" (màu xanh lá)
   - Dữ liệu sẽ lưu vào 2 cột:
     - `trangthai_thanhtoan`: 'chua_thanh_toan' hoặc 'da_thanh_toan'
     - `thanh_toan`: 'chưa thanh toán' hoặc 'đã thanh toán'

### In phiếu
- Click nút "In phiếu" để in chi tiết đơn hàng

## Khác biệt so với Admin

| Tính năng | Admin | Nhân viên bán hàng |
|-----------|-------|-------------------|
| Xem danh sách đơn hàng | ✓ | ✓ |
| Tìm kiếm đơn hàng | ✓ | ✓ |
| Cập nhật trạng thái đơn hàng | ✓ | ✓ |
| Cập nhật trạng thái thanh toán | ✗ | ✓ (MỚI) |
| Xem yêu cầu đổi trả | ✓ | ✗ |
| Quản lý sản phẩm | ✓ | ✗ |

## Lưu ý kỹ thuật
- **Tương thích PHP 5.2**: Code không sử dụng null coalescing operator (??)
- **Bảo mật**: Sử dụng prepared statements để tránh SQL injection
- **Validation**: Kiểm tra dữ liệu đầu vào trước khi cập nhật
- **User-friendly**: Hiển thị thông báo thành công/lỗi rõ ràng

## SQL Query cập nhật trạng thái thanh toán
```sql
UPDATE don_hang 
SET trangthai_thanhtoan = ?, thanh_toan = ? 
WHERE ma_donhang = ?
```

**Ví dụ:**
- Đã thanh toán: `('da_thanh_toan', 'đã thanh toán', 1)`
- Chưa thanh toán: `('chua_thanh_toan', 'chưa thanh toán', 1)`

## Kiểm tra kết quả
Sau khi cập nhật, bạn có thể kiểm tra trong database:
```sql
SELECT ma_donhang, trangthai_thanhtoan, thanh_toan, trang_thai 
FROM don_hang 
WHERE ma_donhang = [ID_DON_HANG];
```

## Hỗ trợ
Nếu có lỗi, kiểm tra:
1. Kết nối database trong `config/database.php`
2. Session đăng nhập có hợp lệ không
3. Quyền truy cập của nhân viên (vai_tro = 'nhanvien')

---
**Ngày tạo:** 30/11/2025
**Phiên bản:** 1.0
**Tương thích:** PHP 5.2.6, MySQL 5.0.51
