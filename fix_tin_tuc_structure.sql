-- Sửa cấu trúc bảng tin_tuc để thêm AUTO_INCREMENT cho ma_tintuc
ALTER TABLE `tin_tuc` MODIFY `ma_tintuc` bigint(20) NOT NULL AUTO_INCREMENT;
