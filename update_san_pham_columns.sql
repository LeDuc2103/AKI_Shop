-- Migration SQL: Thêm cột anh_con và ct_sp vào bảng san_pham
-- Ngày tạo: 08/12/2025
-- Mô tả: 
-- - anh_con: Lưu trữ nhiều ảnh phụ của sản phẩm (dạng JSON array hoặc phân cách bởi |)
-- - ct_sp: Lưu trữ thông số sản phẩm chi tiết (HTML từ TinyMCE editor)

USE tuc;

-- Thêm cột anh_con (TEXT) để lưu nhiều ảnh phụ
-- Format: JSON array hoặc phân cách bởi dấu | 
-- Ví dụ: img/products/ao/ao1_sub1.jpg|img/products/ao/ao1_sub2.jpg
ALTER TABLE san_pham 
ADD COLUMN anh_con TEXT NULL 
COMMENT 'Danh sách ảnh phụ của sản phẩm (cách nhau bởi |)' 
AFTER hinh_anh;

-- Thêm cột ct_sp (LONGTEXT) để lưu thông số chi tiết sản phẩm
-- Lưu nội dung HTML từ TinyMCE Editor (có thể chứa hình ảnh, video, bảng biểu...)
ALTER TABLE san_pham 
ADD COLUMN ct_sp LONGTEXT NULL 
COMMENT 'Thông số chi tiết sản phẩm (HTML từ TinyMCE)' 
AFTER mo_ta;

-- Kiểm tra kết quả
SELECT 'Migration completed successfully!' AS status;
DESCRIBE san_pham;
