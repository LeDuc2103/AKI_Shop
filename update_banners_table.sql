-- Script để cập nhật bảng banners
-- Thêm cột loai_banner và cập nhật cấu trúc

-- Bước 1: Thêm cột loai_banner (nếu chưa có)
ALTER TABLE `banners` 
ADD COLUMN `loai_banner` VARCHAR(50) NULL DEFAULT 'Trang_chu' AFTER `ma_banner`;

-- Bước 2: Cập nhật dữ liệu cũ (nếu có) - mặc định là Banner trang chủ
UPDATE `banners` SET `loai_banner` = 'Trang_chu' WHERE `loai_banner` IS NULL OR `loai_banner` = '';

-- Bước 3: (Tùy chọn) Nếu không cần cột id_sanpham nữa, có thể xóa foreign key và cột
-- Lưu ý: Chỉ chạy nếu bạn chắc chắn không cần liên kết với sản phẩm
-- ALTER TABLE `banners` DROP FOREIGN KEY `fk_banners_san_pham`;
-- ALTER TABLE `banners` DROP COLUMN `id_sanpham`;
