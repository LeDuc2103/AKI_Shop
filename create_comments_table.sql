-- Tạo lại bảng comments với cấu trúc đúng
DROP TABLE IF EXISTS `comments`;

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
  PRIMARY KEY  (`id_danh_gia`),
  KEY `id_sanpham` (`id_sanpham`),
  KEY `ma_user` (`ma_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
