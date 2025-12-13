# 🛒 Luồng Xử Lý Giỏ Hàng - Đã Cập Nhật

## ✅ Nguyên Tắc Mới:
**Giỏ hàng CHỈ được xóa khi thanh toán THÀNH CÔNG!**

---

## 📋 Các Phương Thức Thanh Toán

### 1. **COD (Cash on Delivery)** ✅
**File:** `payment_cod.php`

**Luồng:**
1. Tạo đơn hàng
2. Lưu chi tiết sản phẩm vào `chitiet_donhang`
3. Trừ số lượng kho
4. **XÓA giỏ hàng NGAY** (vì đã xác nhận đặt hàng)
5. Hiển thị trang thành công

**Lý do:** COD là đặt hàng xác nhận, không cần chờ thanh toán online.

---

### 2. **VNPAY** 🔄
**File:** `payment_vnpay.php` → `vnpay_php/vnpay_return.php`

**Luồng:**
1. Tạo đơn hàng
2. Lưu chi tiết sản phẩm vào `chitiet_donhang`
3. Trừ số lượng kho
4. **KHÔNG xóa giỏ hàng** (chờ thanh toán)
5. Redirect sang VNPAY
6. Khách hàng thanh toán trên VNPAY
7. **Nếu thành công:** `vnpay_return.php` → Xóa giỏ hàng
8. **Nếu thất bại/timeout:** Giỏ hàng VẪN CÒN → Khách có thể quay lại

---

### 3. **SePay QR Code** 🔄
**File:** `sepay/order.php` → `sepay/sepay_webhook.php`

**Luồng:**
1. Tạo đơn hàng
2. Lưu chi tiết sản phẩm vào `chitiet_donhang`
3. Trừ số lượng kho
4. **KHÔNG xóa giỏ hàng** (chờ quét QR)
5. Hiển thị mã QR
6. Khách hàng chuyển khoản
7. SePay gửi webhook → `sepay_webhook.php`
8. **Webhook xác nhận:** Xóa giỏ hàng + Cập nhật trạng thái
9. **Nếu không quét:** Giỏ hàng VẪN CÒN → Khách có thể quay lại

---

## 🧪 Kịch Bản Test

### Test Case 1: Thanh Toán VNPAY Thành Công
1. Thêm sản phẩm vào giỏ (VD: 2 sản phẩm)
2. Thanh toán qua VNPAY
3. Hoàn tất thanh toán trên VNPAY
4. **Kỳ vọng:** 
   - ✅ Đơn hàng: `trangthai_thanhtoan = 'da_thanh_toan'`
   - ✅ Giỏ hàng: Trống (đã xóa)
   - ✅ Vào `cart.php`: "Giỏ hàng trống"

### Test Case 2: Thanh Toán VNPAY Thất Bại/Timeout
1. Thêm sản phẩm vào giỏ (VD: 2 sản phẩm)
2. Thanh toán qua VNPAY
3. HỦY hoặc ĐỂ TIMEOUT
4. **Kỳ vọng:**
   - ✅ Đơn hàng: `trangthai_thanhtoan = 'chua_thanh_toan'`
   - ✅ Giỏ hàng: VẪN CÒN 2 sản phẩm
   - ✅ Vào `cart.php`: Thấy 2 sản phẩm như cũ
   - ✅ Có thể thanh toán lại hoặc chọn phương thức khác

### Test Case 3: Thanh Toán SePay Thành Công
1. Thêm sản phẩm vào giỏ
2. Thanh toán qua SePay
3. Quét QR và chuyển khoản đúng
4. **Kỳ vọng:**
   - ✅ Webhook xác nhận
   - ✅ Giỏ hàng: Đã xóa
   - ✅ Đơn hàng: `trangthai_thanhtoan = 'da_thanh_toan'`

### Test Case 4: Thanh Toán SePay - Không Quét QR
1. Thêm sản phẩm vào giỏ
2. Thanh toán qua SePay
3. KHÔNG quét QR, thoát trang
4. **Kỳ vọng:**
   - ✅ Giỏ hàng: VẪN CÒN
   - ✅ Vào `cart.php`: Sản phẩm vẫn còn
   - ✅ Có thể thử lại

### Test Case 5: Thanh Toán COD
1. Thêm sản phẩm vào giỏ
2. Thanh toán COD
3. **Kỳ vọng:**
   - ✅ Giỏ hàng: Đã xóa NGAY
   - ✅ Hiển thị trang thành công
   - ✅ Vào `cart.php`: "Giỏ hàng trống"

---

## 📊 Bảng So Sánh

| Phương Thức | Xóa Giỏ Ngay? | Xóa Giỏ Khi Nào? | File Xử Lý |
|-------------|---------------|-------------------|------------|
| **COD** | ✅ Có | Ngay sau khi tạo đơn | `payment_cod.php` |
| **VNPAY** | ❌ Không | Khi thanh toán thành công | `vnpay_return.php` |
| **SePay** | ❌ Không | Khi webhook xác nhận | `sepay_webhook.php` |

---

## 🔍 Kiểm Tra Database

### Kiểm tra giỏ hàng:
```sql
SELECT * FROM gio_hang WHERE ma_user = [USER_ID];
```

### Kiểm tra đơn hàng:
```sql
SELECT ma_donhang, trangthai_thanhtoan, phuongthuc_thanhtoan, created_at 
FROM don_hang 
WHERE ma_user = [USER_ID] 
ORDER BY created_at DESC;
```

### Kiểm tra chi tiết đơn hàng:
```sql
SELECT * FROM chitiet_donhang WHERE ma_donhang = [ORDER_ID];
```

---

## ✅ Tóm Tắt Các File Đã Sửa

1. **sepay/order.php** - Bỏ xóa giỏ hàng khi tạo đơn
2. **sepay/sepay_webhook.php** - Thêm xóa giỏ hàng khi webhook xác nhận
3. **payment_vnpay.php** - Bỏ xóa giỏ hàng khi tạo đơn
4. **vnpay_php/vnpay_return.php** - Thêm xóa giỏ hàng khi thanh toán thành công
5. **payment_cod.php** - Giữ nguyên (xóa giỏ ngay)

---

## 🎯 Kết Quả Mong Đợi

✅ **Khách hàng thanh toán thành công** → Giỏ hàng trống
✅ **Khách hàng hủy/timeout** → Giỏ hàng vẫn còn, có thể thử lại
✅ **Trải nghiệm tốt hơn** → Không mất sản phẩm nếu chưa thanh toán
