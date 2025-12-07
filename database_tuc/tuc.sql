-- phpMyAdmin SQL Dump
-- version 2.11.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 06, 2025 at 11:19 PM
-- Server version: 5.0.51
-- PHP Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `tuc`
--

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `ma_banner` bigint(20) NOT NULL auto_increment,
  `id_sanpham` bigint(20) NOT NULL,
  `hinh_anh` varchar(250) collate utf8_unicode_ci default NULL,
  `ngay_dang` date default NULL,
  `created_at` timestamp NULL default NULL,
  `update_at` timestamp NULL default NULL,
  `loai_banner` enum('Trang_chu','tin_tuc') character set utf8 NOT NULL,
  PRIMARY KEY  (`ma_banner`),
  KEY `fk_banners_san_pham` (`id_sanpham`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`ma_banner`, `id_sanpham`, `hinh_anh`, `ngay_dang`, `created_at`, `update_at`, `loai_banner`) VALUES
(1, 0, 'img/banner/banner_4_1.png', '2025-12-02', '2025-12-02 22:06:15', '2025-12-02 23:08:28', 'Trang_chu'),
(3, 0, 'img/banner/banner_3.png', '2025-12-02', '2025-12-02 23:17:36', NULL, 'Trang_chu'),
(4, 0, 'img/banner/banner_5.png', '2025-12-02', '2025-12-02 23:17:45', NULL, 'Trang_chu'),
(5, 0, 'img/banner/banner_6.png', '2025-12-02', '2025-12-02 23:17:51', NULL, 'Trang_chu'),
(6, 0, 'img/banner/banner_shop.png', '2025-12-02', '2025-12-02 23:17:58', NULL, 'Trang_chu'),
(7, 0, 'img/banner/bn1.png', '2025-12-03', '2025-12-03 23:57:19', '2025-12-03 23:57:19', 'Trang_chu'),
(8, 0, 'img/banner/bn2.png', '2025-12-03', '2025-12-03 23:57:29', '2025-12-03 23:57:29', 'Trang_chu');

-- --------------------------------------------------------

--
-- Table structure for table `chitiet_donhang`
--

CREATE TABLE `chitiet_donhang` (
  `id_ctdh` bigint(20) NOT NULL auto_increment,
  `ma_donhang` bigint(20) NOT NULL,
  `id_sanpham` bigint(20) NOT NULL,
  `so_luong` int(11) NOT NULL,
  `don_gia` double NOT NULL,
  PRIMARY KEY  (`id_ctdh`),
  KEY `ma_donhang` (`ma_donhang`),
  KEY `id_sanpham` (`id_sanpham`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=23 ;

--
-- Dumping data for table `chitiet_donhang`
--

INSERT INTO `chitiet_donhang` (`id_ctdh`, `ma_donhang`, `id_sanpham`, `so_luong`, `don_gia`) VALUES
(1, 4, 15, 1, 5390000),
(2, 4, 4, 1, 21321),
(3, 5, 15, 1, 5390000),
(4, 6, 15, 1, 5390000),
(5, 7, 15, 1, 5390000),
(6, 8, 15, 3, 5390000),
(7, 9, 17, 2, 8990000),
(8, 10, 12, 1, 8490000),
(9, 10, 15, 1, 5390000),
(10, 11, 10, 11, 5490000),
(11, 12, 11, 1, 7500000),
(12, 13, 10, 1, 5490000),
(13, 14, 10, 10, 5490000),
(14, 15, 1, 10, 10),
(15, 16, 16, 100, 7790000),
(16, 17, 16, 100, 7790000),
(17, 18, 11, 10, 7500000),
(18, 19, 11, 10, 7500000),
(19, 20, 16, 2, 7790000),
(20, 21, 16, 2, 7790000),
(21, 22, 16, 2, 7790000),
(22, 23, 16, 2, 7790000);

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id_danh_gia` bigint(20) unsigned NOT NULL auto_increment,
  `id_sanpham` bigint(20) unsigned NOT NULL,
  `ma_user` bigint(20) unsigned default NULL,
  `ho_ten` varchar(255) NOT NULL,
  `so_sao` tinyint(1) NOT NULL default '5',
  `noi_dung` text,
  `ngay_danh_gia` datetime default NULL,
  `trang_thai` enum('hien','an') default 'hien',
  `phan_hoi` text,
  `nguoi_phan_hoi` varchar(255) default NULL,
  `ngay_phan_hoi` datetime default NULL,
  `created_at` timestamp NULL default NULL,
  `updated_at` timestamp NULL default NULL,
  `seen` enum('Da_doc','Chua_doc') NOT NULL,
  PRIMARY KEY  (`id_danh_gia`),
  KEY `id_sanpham` (`id_sanpham`),
  KEY `ma_user` (`ma_user`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id_danh_gia`, `id_sanpham`, `ma_user`, `ho_ten`, `so_sao`, `noi_dung`, `ngay_danh_gia`, `trang_thai`, `phan_hoi`, `nguoi_phan_hoi`, `ngay_phan_hoi`, `created_at`, `updated_at`, `seen`) VALUES
(1, 13, 34, 'Lê Văn Túc', 5, 'fkạlfjsalfjlsàládf', '2025-12-03 22:04:29', 'hien', NULL, NULL, NULL, '2025-12-03 22:04:29', NULL, 'Chua_doc'),
(2, 12, 34, 'Lê Văn Túc', 1, 'sản phẩm này tệ quá v tôi muốn trả hàng thì làm sao', '2025-12-03 22:09:27', 'hien', 'xin hỏi sản phẩm bị j ạ. Tôi có thể liên lạc call bạnn đc ko?', 'Nhân viên', '2025-12-03 22:26:22', '2025-12-03 22:09:27', NULL, 'Da_doc'),
(3, 11, 34, 'Lê Văn Túc', 1, 'sao mà tệ quá đi à nha', '2025-12-03 22:26:16', 'hien', 'ok bạn nha\r\naaaaaa', 'Nhân viên', '2025-12-03 22:26:32', '2025-12-03 22:26:16', NULL, 'Chua_doc'),
(4, 11, 34, 'Lê Văn Túc', 4, 'fsadfsàááadfsagfgfgfghghfgjhghfjhgf', '2025-12-03 22:41:09', 'hien', NULL, NULL, NULL, '2025-12-03 22:41:09', NULL, 'Chua_doc'),
(5, 14, 34, 'Lê Văn Túc', 5, 'tại vì sao anh lại như v', '2025-12-03 23:06:09', 'hien', NULL, NULL, NULL, '2025-12-03 23:06:09', NULL, 'Da_doc'),
(6, 14, 34, 'Lê Văn Túc', 2, 'rất đẹp', '2025-12-03 23:06:21', 'hien', NULL, NULL, NULL, '2025-12-03 23:06:21', NULL, 'Da_doc'),
(7, 14, 34, 'Lê Văn Túc', 5, 'sản phẩm số 1', '2025-12-03 23:06:32', 'hien', NULL, NULL, NULL, '2025-12-03 23:06:32', NULL, 'Da_doc'),
(8, 14, 34, 'Lê Văn Túc', 4, 'aaaaaaaaaaaaaaaaaaaaaaa', '2025-12-03 23:06:42', 'hien', NULL, NULL, NULL, '2025-12-03 23:06:42', NULL, 'Da_doc'),
(9, 14, 34, 'Lê Văn Túc', 5, 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbb', '2025-12-03 23:06:48', 'hien', NULL, NULL, NULL, '2025-12-03 23:06:48', NULL, 'Da_doc'),
(10, 14, 34, 'Lê Văn Túc', 5, 'ccccccccccccccccccccccccccccccccccc', '2025-12-03 23:06:54', 'hien', NULL, NULL, NULL, '2025-12-03 23:06:54', NULL, 'Da_doc'),
(11, 14, 34, 'Lê Văn Túc', 3, 'dddddddddddđe', '2025-12-03 23:07:02', 'hien', NULL, NULL, NULL, '2025-12-03 23:07:02', NULL, 'Da_doc'),
(12, 16, 34, 'Lê Văn Túc', 5, 'sản phẩm rất tốt, tôi rất hài lòng khi mua', '2025-12-04 10:41:25', 'hien', 'Cảm ơn bạn đã ủng hộ AKI', 'Nhân viên', '2025-12-04 10:59:11', '2025-12-04 10:41:25', NULL, 'Da_doc');

-- --------------------------------------------------------

--
-- Table structure for table `danh_gia`
--

CREATE TABLE `danh_gia` (
  `id_danhgia` bigint(20) NOT NULL auto_increment,
  `ma_user` bigint(20) NOT NULL,
  `id_sanpham` bigint(20) NOT NULL,
  `noi_dung` varchar(500) collate utf8_unicode_ci default NULL,
  `ngay_binhluan` date default NULL,
  `created_at` timestamp NULL default NULL,
  `update_at` timestamp NULL default NULL,
  `trang_thai` tinyint(4) default NULL,
  PRIMARY KEY  (`id_danhgia`),
  KEY `fk_danh_gia_san_pham` (`id_sanpham`),
  KEY `fk_danh_gia_khach_hang` (`ma_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `danh_gia`
--


-- --------------------------------------------------------

--
-- Table structure for table `danh_muc`
--

CREATE TABLE `danh_muc` (
  `id_danhmuc` bigint(20) NOT NULL auto_increment,
  `ten_danhmuc` varchar(250) collate utf8_unicode_ci default NULL,
  `created_at` timestamp NULL default NULL,
  `update_at` timestamp NULL default NULL,
  PRIMARY KEY  (`id_danhmuc`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

--
-- Dumping data for table `danh_muc`
--

INSERT INTO `danh_muc` (`id_danhmuc`, `ten_danhmuc`, `created_at`, `update_at`) VALUES
(1, 'Máy đọc sách Boox', '2025-10-14 16:33:44', NULL),
(2, 'Máy đọc sách Savi', '2025-10-14 16:33:44', NULL),
(3, 'Máy đọc sách Kindle', '2025-10-14 16:33:44', NULL),
(4, 'Máy đọc sách reMarkable', '2025-10-14 16:33:44', NULL),
(5, 'Máy đọc sách Kobo', '2025-10-14 16:33:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `don_hang`
--

CREATE TABLE `don_hang` (
  `ma_donhang` bigint(20) NOT NULL auto_increment,
  `ten_nguoinhan` varchar(50) collate utf8_unicode_ci NOT NULL,
  `diachi_nhan` varchar(250) collate utf8_unicode_ci NOT NULL,
  `email_nguoinhan` varchar(50) collate utf8_unicode_ci NOT NULL,
  `so_dienthoai` varchar(50) collate utf8_unicode_ci NOT NULL,
  `trangthai_thanhtoan` enum('chua_thanh_toan','da_thanh_toan') collate utf8_unicode_ci NOT NULL,
  `phuongthuc_thanhtoan` varchar(50) collate utf8_unicode_ci NOT NULL,
  `thanh_toan` varchar(50) collate utf8_unicode_ci NOT NULL,
  `tien_hang` double NOT NULL,
  `tien_ship` double NOT NULL,
  `tong_tien` double NOT NULL,
  `ma_user` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `update_at` timestamp NOT NULL default '0000-00-00 00:00:00',
  `trang_thai` enum('cho_xu_ly','xac_nhan','da_xuat_kho','hoan_thanh','huy') collate utf8_unicode_ci default 'cho_xu_ly',
  `order_code` varchar(10) character set utf8 NOT NULL,
  PRIMARY KEY  (`ma_donhang`),
  KEY `fk_don_hang_khach_hang` (`ma_user`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=24 ;

--
-- Dumping data for table `don_hang`
--

INSERT INTO `don_hang` (`ma_donhang`, `ten_nguoinhan`, `diachi_nhan`, `email_nguoinhan`, `so_dienthoai`, `trangthai_thanhtoan`, `phuongthuc_thanhtoan`, `thanh_toan`, `tien_hang`, `tien_ship`, `tong_tien`, `ma_user`, `created_at`, `update_at`, `trang_thai`, `order_code`) VALUES
(1, 'Huỳnh Đình Chiểu', 'hùng vương', 'dinhchieu1k11@gmail.com', '1234567890', 'chua_thanh_toan', 'qr', 'chưa thanh toán', 1500000, 15000, 1515000, 13, '2025-12-04 10:57:48', '2025-11-15 12:28:23', 'hoan_thanh', ''),
(2, 'Huỳnh Đình Chiểu', 'hùng vương', 'dinhchieu1k11@gmail.com', '1234567890', 'da_thanh_toan', 'qr', 'đã thanh toán', 1500000, 15000, 1515000, 13, '2025-11-30 22:55:45', '0000-00-00 00:00:00', 'hoan_thanh', ''),
(3, 'Huỳnh Đình A', 'hùng vương', 'dinhchieu1k1@gmail.com', '1234567890', 'chua_thanh_toan', 'qr', 'chưa thanh toán', 20000000, 15000, 20015000, 18, '2025-12-03 19:33:47', '2025-11-15 12:31:47', 'hoan_thanh', ''),
(4, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'cod', 'đã hủy', 5411321, 15000, 5426321, 34, '2025-11-28 00:38:32', '0000-00-00 00:00:00', 'hoan_thanh', ''),
(5, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'vnpay', 'đã thanh toán', 5390000, 15000, 5405000, 34, '2025-11-28 00:09:41', '0000-00-00 00:00:00', 'hoan_thanh', ''),
(6, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'vnpay', 'đã thanh toán', 5390000, 15000, 5405000, 34, '2025-11-28 00:38:44', '0000-00-00 00:00:00', 'hoan_thanh', ''),
(7, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'vnpay', 'đã thanh toán', 5390000, 15000, 5405000, 34, '2025-11-27 23:38:39', '0000-00-00 00:00:00', 'hoan_thanh', ''),
(8, 'bảo ngọc', '43 vườn lài quận 12', 'ngoctuc@gmail.com', '0702312817', 'da_thanh_toan', 'vnpay', 'đã thanh toán', 16170000, 15000, 16185000, 38, '2025-12-03 19:35:25', '2025-11-30 21:30:47', 'xac_nhan', ''),
(9, 'bảo ngọc', '43 vườn lài quận 12', 'ngoctuc@gmail.com', '0702312817', 'da_thanh_toan', 'cod', 'đã thanh toán', 17980000, 15000, 17995000, 38, '2025-11-30 21:55:09', '0000-00-00 00:00:00', 'hoan_thanh', ''),
(10, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'vnpay', 'đã thanh toán', 13880000, 15000, 13895000, 34, '2025-12-04 11:02:32', '2025-12-04 11:02:32', 'da_xuat_kho', ''),
(11, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'cod', 'đã hủy', 60390000, 15000, 60405000, 34, '2025-12-01 21:39:00', '0000-00-00 00:00:00', 'huy', ''),
(12, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'cod', 'đã thanh toán', 7500000, 15000, 7515000, 34, '2025-12-04 11:03:12', '2025-12-04 11:02:36', 'hoan_thanh', ''),
(13, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'cod', 'đã thanh toán', 5490000, 15000, 5505000, 34, '2025-12-03 23:39:49', '0000-00-00 00:00:00', 'hoan_thanh', ''),
(14, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'cod', 'đã thanh toán', 54900000, 15000, 54915000, 34, '2025-12-03 23:40:54', '0000-00-00 00:00:00', 'hoan_thanh', ''),
(15, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'cod', 'đã hủy', 100, 15000, 15100, 34, '2025-12-04 10:37:07', '0000-00-00 00:00:00', 'huy', ''),
(16, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'cod', 'đã hủy', 779000000, 15000, 779015000, 34, '2025-12-03 23:50:37', '0000-00-00 00:00:00', 'huy', ''),
(17, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'cod', 'đã thanh toán', 779000000, 15000, 779015000, 34, '2025-12-03 23:53:48', '0000-00-00 00:00:00', 'hoan_thanh', ''),
(18, 'Phạm Băng', '12 nguyễn văn nghi, gò vấp', 'phambang@gmail.com', '0777777777', 'chua_thanh_toan', 'vnpay', 'chưa thanh toán', 75000000, 15000, 75015000, 40, '2025-12-04 10:26:31', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(19, 'Phạm Băng', '12 nguyễn văn nghi, gò vấp', 'phambang@gmail.com', '0777777777', 'chua_thanh_toan', 'vnpay', 'chưa thanh toán', 75000000, 15000, 75015000, 40, '2025-12-04 10:31:34', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(20, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'vnpay', 'chưa thanh toán', 15580000, 15000, 15595000, 34, '2025-12-04 11:19:09', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(21, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'vnpay', 'chưa thanh toán', 15580000, 15000, 15595000, 34, '2025-12-04 11:21:37', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(22, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'vnpay', 'chưa thanh toán', 15580000, 15000, 15595000, 34, '2025-12-04 22:37:13', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(23, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'vnpay', 'chưa thanh toán', 15580000, 15000, 15595000, 34, '2025-12-04 22:37:51', '0000-00-00 00:00:00', 'cho_xu_ly', '');

-- --------------------------------------------------------

--
-- Table structure for table `don_hang_doi_tra`
--

CREATE TABLE `don_hang_doi_tra` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `ma_donhang` bigint(20) NOT NULL,
  `ma_user` bigint(20) NOT NULL,
  `ly_do` text collate utf8_unicode_ci,
  `status` enum('pending','approved','rejected') collate utf8_unicode_ci default 'pending',
  `created_at` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `idx_return_user` (`ma_user`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=10 ;

--
-- Dumping data for table `don_hang_doi_tra`
--

INSERT INTO `don_hang_doi_tra` (`id`, `ma_donhang`, `ma_user`, `ly_do`, `status`, `created_at`, `updated_at`) VALUES
(1, 7, 34, 'Hàng ko vừa ý tôi á', 'approved', '2025-11-27 23:41:10', '2025-11-28 00:37:07'),
(2, 9, 38, 'bbbb', 'approved', '2025-11-28 00:06:37', '2025-11-28 00:37:12'),
(3, 5, 34, 'jjjjj', 'approved', '2025-11-28 00:09:52', '2025-11-28 00:40:23'),
(7, 4, 34, 'sos 2', 'approved', '2025-11-28 00:46:46', '2025-12-02 02:20:28'),
(8, 6, 34, 'tesst 11111111', 'pending', '2025-12-02 02:39:45', '0000-00-00 00:00:00'),
(9, 12, 34, 'tôi muốn đổi trả hàng do ko hài lòng', 'pending', '2025-12-04 11:04:14', '2025-12-04 11:05:10');

-- --------------------------------------------------------

--
-- Table structure for table `gio_hang`
--

CREATE TABLE `gio_hang` (
  `id_giohang` bigint(20) NOT NULL auto_increment,
  `ma_user` bigint(20) NOT NULL,
  `id_sanpham` bigint(20) NOT NULL,
  `so_luong` int(11) NOT NULL,
  `thanh_tien` double NOT NULL,
  `created_at` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `update_at` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id_giohang`),
  KEY `fk_gio_hang_san_pham` (`id_sanpham`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=11 ;

--
-- Dumping data for table `gio_hang`
--

INSERT INTO `gio_hang` (`id_giohang`, `ma_user`, `id_sanpham`, `so_luong`, `thanh_tien`, `created_at`, `update_at`) VALUES
(3, 36, 15, 200, 1078000000, '2025-11-26 16:12:26', '0000-00-00 00:00:00'),
(5, 36, 12, 3, 25470000, '2025-11-26 16:17:06', '0000-00-00 00:00:00'),
(6, 36, 13, 4, 78360000, '2025-11-26 16:45:32', '0000-00-00 00:00:00'),
(7, 37, 15, 1, 5390000, '2025-11-26 16:58:15', '0000-00-00 00:00:00'),
(8, 40, 11, 10, 75000000, '2025-12-04 10:25:47', '2025-12-04 10:25:30'),
(10, 34, 16, 2, 15580000, '2025-12-04 11:18:58', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `hotro`
--

CREATE TABLE `hotro` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `ho_va_ten` varchar(255) default NULL,
  `email` varchar(255) default NULL,
  `so_dien_thoai` varchar(255) default NULL,
  `noi_dung` varchar(255) default NULL,
  `ngay_gui` date default NULL,
  `created_at` timestamp NULL default NULL,
  `updated_at` timestamp NULL default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `hotro`
--

INSERT INTO `hotro` (`id`, `ho_va_ten`, `email`, `so_dien_thoai`, `noi_dung`, `ngay_gui`, `created_at`, `updated_at`) VALUES
(1, 'Nguyên Khoa', 'leduc2103@gmail.com', '0981523222', 'atest test', '2025-12-03', '2025-12-03 00:06:42', '2025-12-03 00:06:42'),
(2, 'Nguyên Khoa', 'leduc2103@gmail.com', '0981523130', 'Tôi muốn bạn hỗ trợ tôi về vấn đề sản phẩm này có được không', '2025-12-04', '2025-12-04 10:39:44', '2025-12-04 10:39:44');

-- --------------------------------------------------------

--
-- Table structure for table `khuyen_mai`
--

CREATE TABLE `khuyen_mai` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `ten_km` varchar(100) NOT NULL,
  `ngay_bat_dau` datetime NOT NULL,
  `ngay_ket_thuc` datetime NOT NULL,
  `phan_tram_km` int(10) NOT NULL,
  `so_luog_toi_da` double NOT NULL,
  `so_luong_su_dung` double NOT NULL,
  `created_at` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `up[dated_at` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `khuyen_mai`
--


-- --------------------------------------------------------

--
-- Table structure for table `mau_sac`
--

CREATE TABLE `mau_sac` (
  `ma_mau` bigint(20) unsigned NOT NULL auto_increment,
  `ten_mau` varchar(255) collate utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `update_at` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`ma_mau`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

--
-- Dumping data for table `mau_sac`
--

INSERT INTO `mau_sac` (`ma_mau`, `ten_mau`, `created_at`, `update_at`) VALUES
(1, 'Màu trắng', '2025-10-14 18:17:03', '0000-00-00 00:00:00'),
(2, 'Màu đen', '2025-10-14 18:17:03', '0000-00-00 00:00:00'),
(3, 'Màu xám', '2025-10-14 18:17:03', '0000-00-00 00:00:00'),
(4, 'Màu đỏ tía', '2025-10-14 18:17:03', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `password_forgot`
--

CREATE TABLE `password_forgot` (
  `email` varchar(50) collate utf8_unicode_ci NOT NULL,
  `token` varchar(255) collate utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `update_at` timestamp NOT NULL default '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `password_forgot`
--

INSERT INTO `password_forgot` (`email`, `token`, `created_at`, `update_at`) VALUES
('95.levantuc.toky@gmail.com', '469612', '2025-11-30 15:36:38', '0000-00-00 00:00:00'),
('95.levantuc.toky@gmail.com', '717745', '2025-11-30 15:53:28', '0000-00-00 00:00:00'),
('leduc2103@gmail.com', '241725', '2025-11-30 16:34:12', '0000-00-00 00:00:00'),
('leduc2103@gmail.com', '111866', '2025-11-30 16:36:27', '0000-00-00 00:00:00'),
('leduc2103@gmail.com', 'f409754fcd033a31a6fd062238f713a1', '2025-11-30 16:54:08', '0000-00-00 00:00:00'),
('95.levantuc.toky@gmail.com', 'c4d7b20e7c2be56b534971d47ed77fb7', '2025-11-30 16:54:43', '0000-00-00 00:00:00'),
('95.levantuc.toky@gmail.com', 'b48bdea399abc670e34ab630f0a6f308', '2025-11-30 16:54:55', '0000-00-00 00:00:00'),
('95.levantuc.toky@gmail.com', '26e6e35f8102382ecf0b554f598c06a2', '2025-11-30 16:56:15', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `san_pham`
--

CREATE TABLE `san_pham` (
  `id_sanpham` bigint(20) NOT NULL auto_increment,
  `ten_sanpham` varchar(250) character set utf8 default NULL,
  `gia` double default NULL,
  `hinh_anh` varchar(250) collate utf8_unicode_ci default NULL,
  `mau_sac` varchar(250) collate utf8_unicode_ci default NULL,
  `so_luong` int(50) default NULL,
  `mo_ta` text collate utf8_unicode_ci NOT NULL,
  `id_danhmuc` bigint(20) NOT NULL,
  `created_at` timestamp NULL default NULL,
  `update_at` timestamp NULL default NULL,
  `gia_khuyen_mai` float default NULL,
  PRIMARY KEY  (`id_sanpham`),
  KEY `fk_san_pham_danh_muc` (`id_danhmuc`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=20 ;

--
-- Dumping data for table `san_pham`
--

INSERT INTO `san_pham` (`id_sanpham`, `ten_sanpham`, `gia`, `hinh_anh`, `mau_sac`, `so_luong`, `mo_ta`, `id_danhmuc`, `created_at`, `update_at`, `gia_khuyen_mai`) VALUES
(1, 'Máy đọc sách màu Boox Note Air 3 C', 14390000, 'img/products/boox/noteair3C.jpg', 'Màu đen', 200, 'ƯU ĐÃI VÀ QUÀ TẶNG   - Kho sách tích hợp sẵn trong máy, đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...  - Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi  AKISHOP CAM KẾT  - Bảo hành 12 tháng   - Cài đặt MIỄN PHÍ các bộ từ điển (Anh - Việt, Việt - Anh, Nhật - Việt, Việt - Nhật, Đức - Việt, Việt - Đức, Trung - Việt...)  - Hỗ trợ tìm kiếm sách theo yêu cầu cho Khách hàng  - Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.  - Mọi thắc mắc vui lòng liên hệ Hotline: 0', 1, '2025-10-14 22:22:19', '2025-12-04 10:55:20', 10),
(2, 'Máy đọc sách navi', 10000000, 'img/products/savi/test.jpg', 'Đen', 100, 'máy ok lắm', 2, '2025-11-09 14:12:29', '2025-11-15 09:15:59', 100000),
(4, 'Máy đọc sách kiki', 321321312, 'img/products/savi/kiki.jpg', 'Màu đen', 322, 'dsadsadsad', 2, '2025-11-15 09:30:13', '2025-11-15 09:56:44', 21321),
(6, 'Máy đọc sách reMarkable Paper Pro Move + Plus Pen', 17490000, 'img/products/remarkable/PaperProMove_1.png', 'Trắng', 1000, 'ƯU ĐÃI VÀ QUÀ TẶNG \r\n\r\n- Tặng kho sách đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\nAKISHOP CAM KẾT\r\n\r\n- Bảo hành 12 tháng \r\n\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu cho Khách hàng\r\n\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.\r\n\r\n- Mọi thắc mắc vui lòng liên hệ Hotline: 0856 87 88 89', 4, '2025-11-25 22:27:49', '2025-11-25 22:27:49', 0),
(7, 'Máy đọc sách reMarkable 2', 13100000, 'img/products/remarkable/remarkable2.jpg', 'Trắng', 200, 'ƯU ĐÃI VÀ QUÀ TẶNG \r\n\r\n- Tặng kho sách đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\nAKISHOP CAM KẾT\r\n\r\n- Bảo hành 12 tháng \r\n\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu cho Khách hàng\r\n\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.\r\n\r\n- Mọi thắc mắc vui lòng liên hệ Hotline: 0856 87 88 89', 4, '2025-11-25 22:37:18', '2025-11-25 23:02:15', 100000),
(8, 'Máy đọc sách Kobo Clara Colour 2024', 6490000, 'img/products/kobo/claraColor2024.jpg', 'Đen', 100, 'ƯU ĐÃI VÀ QUÀ TẶNG\r\n\r\n- Tặng kho sách đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\n\r\nAKISHOP CAM KẾT\r\n\r\n- Bảo hành 12 tháng \r\n\r\n- Cài đặt MIỄN PHÍ các bộ từ điển (Anh - Việt, Việt - Anh, Nhật - Việt, Việt - Nhật, Đức - Việt, Việt - Đức, Trung - Việt...)\r\n\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu cho Khách hàng\r\n\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.\r\n\r\n- Mọi thắc mắc vui lòng liên hệ Hotline: 0856 87 88 89', 5, '2025-11-25 22:37:18', '2025-11-25 22:34:25', 5.49e+006),
(9, 'Máy đọc sách reMarkable 2', 13100000, 'img/products/remarkable/remarkable2.jpg', 'Trắng', 200, 'ƯU ĐÃI VÀ QUÀ TẶNG \r\n\r\n- Tặng kho sách đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\nAKISHOP CAM KẾT\r\n\r\n- Bảo hành 12 tháng \r\n\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu cho Khách hàng\r\n\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.\r\n\r\n- Mọi thắc mắc vui lòng liên hệ Hotline: 0856 87 88 89', 4, '2025-11-25 22:37:25', '2025-11-25 22:32:14', 0),
(10, 'Máy đọc sách Kobo Clara Colour 2024', 6490000, 'img/products/kobo/claraColor2024.jpg', 'Đen', 100, 'ƯU ĐÃI VÀ QUÀ TẶNG\r\n\r\n- Tặng kho sách đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\n\r\nAKISHOP CAM KẾT\r\n\r\n- Bảo hành 12 tháng \r\n\r\n- Cài đặt MIỄN PHÍ các bộ từ điển (Anh - Việt, Việt - Anh, Nhật - Việt, Việt - Nhật, Đức - Việt, Việt - Đức, Trung - Việt...)\r\n\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu cho Khách hàng\r\n\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.\r\n\r\n- Mọi thắc mắc vui lòng liên hệ Hotline: 0856 87 88 89', 5, '2025-11-25 22:37:25', '2025-11-25 22:34:25', 5.49e+006),
(11, 'Máy đọc sách Kobo Libra 2 Colour', 8500000, 'img/products/kobo/libra2colour.jpg', 'Đen', 180, 'Màn hình E Ink Kaleido 3, độ phân giải 300 ppi trắng đen và 150 ppi màu. Bộ nhớ trong 32GB Hỗ trợ Kobo Audiobooks USB Type C Hỗ trợ bút Kobo Stylus 2 (bán rời)\r\nƯU ĐÃI VÀ QUÀ TẶNG\r\n- Tặng kho sách đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\nAKISHOP CAM KẾT\r\n- Bảo hành 12 tháng \r\n- Cài đặt MIỄN PHÍ các bộ từ điển (Anh - Việt, Việt - Anh...)\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu cho Khách hàng\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.', 5, '2025-11-26 01:49:07', '2025-11-25 22:42:22', 7.5e+006),
(12, 'Máy đọc sách Kobo Sage', 9990000, 'img/products/kobo/kobosage.jpg', 'Đen', 100, 'KHUYẾN MÃI\r\n- Giảm giá tới 40% (giá trên website là giá đã giảm)\r\n- Giảm ngay 100k cho cover khi mua kèm máy (kindle là giảm 50k)\r\n\r\nHỗ trợ bút ghi chú (bán rời)\r\n\r\nCPU mới 4 nhân x 1.8GHz\r\nMàn hình E Ink mới Carta 1200, độ phân giải 300 ppi, có Dark Mode\r\nBộ nhớ trong 32GB\r\nChống nước IPX8\r\nĐèn nền 2 tông màu\r\nUSB Type C\r\nƯU ĐÃI VÀ QUÀ TẶNG\r\n\r\n- Tặng kho sách đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\nBẢO HÀNH VÀ HỖ TRỢ\r\n\r\n- Bảo hành 12 tháng \r\n\r\n- Hỗ trợ cài đặt font tiếng Việt, KoReader\r\n\r\n- Hỗ trợ cài đặt MIỄN PHÍ các bộ từ điển (Anh - Việt, Việt - Anh,...)\r\n\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu và gửi lại cho khách hàng \r\n\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.', 5, '2025-11-25 22:45:21', '2025-11-25 22:45:05', 8.49e+006),
(13, 'Kindle Scribe 2025 - 64Gb', 19590000, 'img/products/kindle/Scribe2025.png', 'Xám', 200, 'ƯU ĐÃI VÀ QUÀ TẶNG \r\n\r\n- Tặng kho sách đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\nAKISHOP CAM KẾT\r\n\r\n- Bảo hành 12 tháng \r\n\r\n- Cài đặt MIỄN PHÍ các bộ từ điển (Anh - Việt, Việt - Anh, Nhật - Việt, Việt - Nhật, Đức - Việt, Việt - Đức, Trung - Việt...)\r\n\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu cho Khách hàng\r\n\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.', 3, '2025-11-25 22:48:12', '2025-11-25 22:48:07', 0),
(14, 'Máy đọc sách Kindle Paperwhite 6 16Gb', 6590000, 'img/products/kindle/Paperwhite6.jpg', 'Đen', 200, 'ƯU ĐÃI VÀ QUÀ TẶNG\r\n\r\n- Tặng kho sách đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\nAKISHOP CAM KẾT\r\n\r\n- Bảo hành 12 tháng \r\n\r\n- Cài đặt MIỄN PHÍ các bộ từ điển (Anh - Việt, Việt - Anh, Nhật - Việt, Việt - Nhật, Đức - Việt, Việt - Đức, Trung - Việt...)\r\n\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu cho Khách hàng\r\n\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.', 3, '2025-11-25 23:06:48', '2025-11-25 23:06:37', 5.59e+006),
(15, 'Máy đọc sách Boox Go 6', 5390000, 'img/products/boox/go6.jpg', 'Đen', 201, 'ƯU ĐÃI VÀ QUÀ TẶNG\r\n\r\n- Kho sách tích hợp sẵn trong máy, đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\nAKISHOP CAM KẾT\r\n\r\n- Bảo hành 12 tháng \r\n\r\n- Cài đặt MIỄN PHÍ các bộ từ điển (Anh - Việt, Việt - Anh, Nhật - Việt, Việt - Nhật, Đức - Việt, Việt - Đức, Trung - Việt...)\r\n\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu cho Khách hàng\r\n\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.', 1, '2025-11-25 23:52:29', '2025-11-25 23:46:27', 0),
(16, 'Máy đọc sách Boox Go 7', 7790000, 'img/products/boox/Go7_2.jpg', 'Trắng', 192, 'ƯU ĐÃI VÀ QUÀ TẶNG\r\n- Kho sách tích hợp sẵn trong máy, đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\nAKISHOP CAM KẾT\r\n- Bảo hành 12 tháng \r\n\r\n- Cài đặt MIỄN PHÍ các bộ từ điển (Anh - Việt, Việt - Anh, Nhật - Việt, Việt - Nhật, Đức - Việt, Việt - Đức, Trung - Việt...)\r\n\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu cho Khách hàng\r\n\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.', 1, '2025-12-03 19:41:19', '2025-12-03 23:54:35', 0),
(17, 'Máy đọc sách Boox Go 7 Color (Gen II)', 8990000, 'img/products/boox/Go7color(gen2).jpg', 'Đen', 100, 'ƯU ĐÃI VÀ QUÀ TẶNG\r\n- Kho sách tích hợp sẵn trong máy, đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\nAKISHOP CAM KẾT\r\n- Bảo hành 12 tháng \r\n\r\n- Cài đặt MIỄN PHÍ các bộ từ điển (Anh - Việt, Việt - Anh, Nhật - Việt, Việt - Nhật, Đức - Việt, Việt - Đức, Trung - Việt...)\r\n\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu cho Khách hàng\r\n\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.', 1, '2025-11-25 23:52:29', NULL, 0),
(18, 'Máy đọc sách Boox Go 10.3', 13390000, 'img/products/boox/Go10.3.jpg', 'Trắng', 100, 'ƯU ĐÃI VÀ QUÀ TẶNG \r\n\r\n- Kho sách tích hợp sẵn trong máy, đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\nAKISHOP CAM KẾT\r\n\r\n- Bảo hành 12 tháng \r\n\r\n- Cài đặt MIỄN PHÍ các bộ từ điển (Anh - Việt, Việt - Anh, Nhật - Việt, Việt - Nhật, Đức - Việt, Việt - Đức, Trung - Việt...)\r\n\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu cho Khách hàng\r\n\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.', 1, '2025-11-25 23:52:29', NULL, 0),
(19, 'Máy đọc sách Boox Tab Ultra C Pro', 19300000, 'img/products/boox/tabultraCpro.jpg', 'Đen', 199, 'ƯU ĐÃI VÀ QUÀ TẶNG\r\n\r\n- Kho sách tích hợp sẵn trong máy, đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\nAKISHOP CAM KẾT\r\n\r\n- Bảo hành 12 tháng \r\n\r\n- Cài đặt MIỄN PHÍ các bộ từ điển (Anh - Việt, Việt - Anh, Nhật - Việt, Việt - Nhật, Đức - Việt, Việt - Đức, Trung - Việt...)\r\n\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu cho Khách hàng\r\n\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.', 1, '2025-11-25 23:52:29', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `san_pham_mau_sac`
--

CREATE TABLE `san_pham_mau_sac` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `id_sanpham` bigint(20) NOT NULL,
  `mau_sac_id` bigint(20) unsigned NOT NULL,
  `so_luong` int(11) NOT NULL default '0',
  `gia` double NOT NULL,
  `created_at` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id_sanpham` (`id_sanpham`),
  UNIQUE KEY `mau_sac_id_2` (`mau_sac_id`),
  KEY `san_pham_id` (`id_sanpham`),
  KEY `mau_sac_id` (`mau_sac_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `san_pham_mau_sac`
--


-- --------------------------------------------------------

--
-- Table structure for table `tin_tuc`
--

CREATE TABLE `tin_tuc` (
  `ma_tintuc` bigint(20) NOT NULL auto_increment,
  `hinh_anh` varchar(250) collate utf8_unicode_ci default NULL,
  `ngay_tao` date default NULL,
  `nguoi_tao` varchar(250) collate utf8_unicode_ci default NULL,
  `noi_dung` longtext collate utf8_unicode_ci,
  `ma_user` bigint(20) NOT NULL,
  `tieu_de` text collate utf8_unicode_ci,
  `created_at` timestamp NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `update_at` timestamp NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`ma_tintuc`),
  KEY `fk_tin_tuc_nhan_vien` (`ma_user`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

--
-- Dumping data for table `tin_tuc`
--

INSERT INTO `tin_tuc` (`ma_tintuc`, `hinh_anh`, `ngay_tao`, `nguoi_tao`, `noi_dung`, `ma_user`, `tieu_de`, `created_at`, `update_at`) VALUES
(1, 'img/blog/bl2_1_1.png', '2025-11-30', 'Nhân viên', '<p><strong>Giới thiệu chung về tầm quan trọng của việc học luật Học luật mang đến nền tảng kiến thức vững chắc để bạn xử l&yacute; nhiều t&igrave;nh huống trong c&ocirc;ng việc v&agrave; cuộc sống.</strong></p>\r\n<p><img src="img/banner/banner_5.png" alt="" width="600" height="399"></p>\r\n<p>V&igrave; sao kỹ năng ph&aacute;p l&yacute; ng&agrave;y c&agrave;ng quan trọng? Trong thời đại hiện nay, hầu như mọi lĩnh vực đều li&ecirc;n quan đến ph&aacute;p luật: kinh doanh, hợp đồng, sở hữu tr&iacute; tuệ, lao động, thuế&hellip; Nắm được luật gi&uacute;p bạn tự tin hơn, hạn chế rủi ro v&agrave; đưa ra quyết định ch&iacute;nh x&aacute;c. Những đối tượng cần nắm vững phương ph&aacute;p học luật - Sinh vi&ecirc;n ng&agrave;nh luật - Nh&acirc;n vi&ecirc;n ph&aacute;p chế, h&agrave;nh ch&iacute;nh &ndash; nh&acirc;n sự - Người kinh doanh - C&aacute; nh&acirc;n muốn hiểu biết luật để bảo vệ quyền lợi bản th&acirc;n Những kh&oacute; khăn phổ biến khi học luật Việc học luật kh&ocirc;ng phải l&uacute;c n&agrave;o cũng dễ d&agrave;ng. Khối lượng kiến thức lớn, ng&ocirc;n ngữ ph&aacute;p l&yacute; phức tạp v&agrave; y&ecirc;u cầu tư duy logic khiến nhiều người cho&aacute;ng ngợp. Nhưng nếu nắm được những kh&oacute; khăn n&agrave;y, bạn sẽ dễ d&agrave;ng t&igrave;m ra hướng học ph&ugrave; hợp. Lượng kiến thức lớn v&agrave; kh&oacute; nhớ Ph&aacute;p luật thay đổi li&ecirc;n tục, nhiều văn bản v&agrave; lĩnh vực kh&aacute;c nhau khiến người học cho&aacute;ng ngợp nếu kh&ocirc;ng c&oacute; phương ph&aacute;p học đ&uacute;ng. Ng&ocirc;n ngữ ph&aacute;p l&yacute; phức tạp C&aacute;c thuật ngữ ph&aacute;p l&yacute; thường kh&ocirc; khan, mang t&iacute;nh ch&iacute;nh x&aacute;c cao n&ecirc;n dễ g&acirc;y kh&oacute; hiểu với người mới. Kh&oacute; &aacute;p dụng v&agrave;o thực tế nếu chỉ học l&yacute; thuyết Hiểu điều luật nhưng kh&ocirc;ng biết vận dụng v&agrave;o t&igrave;nh huống thực tế l&agrave; vấn đề phổ biến. Phương ph&aacute;p học luật hiệu quả &ndash; Tổng hợp c&aacute;c bước thực chiến Để chinh phục ng&agrave;nh luật, bạn cần một chiến lược r&otilde; r&agrave;ng, logic v&agrave; bền vững. Dưới đ&acirc;y l&agrave; c&aacute;c phương ph&aacute;p học luật hiệu quả, gi&uacute;p bạn tiến bộ nhanh ch&oacute;ng.</p>\r\n<p>https://www.youtube.com/watch?v=q6mCBqJmWCk</p>', 0, 'Phương pháp học luật – Bí quyết nâng cao hiệu quả học tập cho sinh viên và người đi làm27/11/2025 11:25', '2025-12-02 21:50:36', '2025-12-02 21:50:36'),
(2, 'img/blog/bl2_1.png', '2025-12-01', 'Nhân viên', '<p>ONYX c&oacute; lẽ l&agrave; c&ocirc;ng ty năng động thuộc loại bậc nhất trong c&aacute;c c&ocirc;ng ty sản xuất&nbsp;<a href="https://akishop.com.vn/">m&aacute;y đọc s&aacute;ch</a>&nbsp;sử dụng e-ink hay c&ograve;n gọi l&agrave;&nbsp;giấy điện tử, họ c&oacute; một loạt sản phẩm với nhiều k&iacute;ch cỡ m&agrave;n h&igrave;nh v&agrave; c&ocirc;ng dụng kh&aacute;c nhau, trải d&agrave;i từ 6 inch đến tận 13.3 inch cũng c&oacute; lu&ocirc;n.&nbsp;<a href="https://akishop.com.vn/may-doc-sach-boox-nova-air-pd174000.html">BOOX Nova Air</a> l&agrave; sản phẩm mới nhất trong ph&acirc;n kh&uacute;c 7.8 inch.</p>\r\n<p><iframe src="https://www.youtube.com/embed/Va0j310OJg4" width="560" height="314" allowfullscreen="allowfullscreen"></iframe></p>', 38, 'Trên tay Boox Nova Air: máy đọc sách kiêm sổ tay điện tử', '2025-12-02 21:50:12', '2025-12-02 21:50:12'),
(3, 'img/blog/bn_bl3.png', '2025-12-01', 'Nhân viên', '<p dir="ltr">Kobo l&agrave; một trong những thương hiệu m&aacute;y đọc s&aacute;ch điện tử lớn nhất tr&ecirc;n thế giới, cạnh tranh trực tiếp với Amazon Kindle. Với thiết kế hiện đại, hỗ trợ nhiều định dạng s&aacute;ch v&agrave; giao diện th&acirc;n thiện, c&aacute;c d&ograve;ng m&aacute;y đọc s&aacute;ch Kobo ng&agrave;y c&agrave;ng được người d&ugrave;ng Việt Nam ưa chuộng. Trong b&agrave;i viết n&agrave;y, ch&uacute;ng ta sẽ c&ugrave;ng kh&aacute;m ph&aacute; chi tiết v&agrave; so s&aacute;nh c&aacute;c mẫu m&aacute;y Kobo mới nhất năm 2024 để t&igrave;m ra chiếc m&aacute;y ph&ugrave; hợp nhất với bạn.</p>\r\n<h2 dir="ltr"><strong>L&yacute; do chọn m&aacute;y đọc s&aacute;ch&nbsp;Kobo&nbsp;</strong></h2>\r\n<p dir="ltr">Việc chọn<a href="https://akishop.com.vn/may-doc-sach-dien-tu-la-gi-kham-pha-nhung-cong-dung-tuyet-voi-ban-chua-biet-nd313567.html">&nbsp;m&aacute;y đọc s&aacute;ch</a> phụ thuộc nhiều v&agrave;o nhu cầu c&aacute; nh&acirc;n, nhưng Kobo thường l&agrave; lựa chọn h&agrave;ng đầu cho những ai y&ecirc;u th&iacute;ch sự linh hoạt v&agrave; tự do. Kobo c&oacute; giao diện dễ sử dụng v&agrave; được trang bị c&aacute;c t&iacute;nh năng bảo vệ mắt vượt trội. Đặc biệt, người d&ugrave;ng c&oacute; thể dễ d&agrave;ng tải s&aacute;ch từ nhiều nguồn kh&aacute;c nhau m&agrave; kh&ocirc;ng cần chuyển đổi phức tạp.</p>\r\n<p dir="ltr">&nbsp;</p>', 38, 'Các Dòng Máy Đọc Sách Kobo: Đánh Giá & So Sánh Chi Tiết', '2025-12-02 21:50:50', '2025-12-02 21:50:50'),
(5, 'img/blog/news_1764527454.png', '2025-12-01', 'túc', '<p>aaaaa</p>', 38, 'test', '2025-12-01 01:30:54', '0000-00-00 00:00:00'),
(6, 'img/blog/BG_1.png', '2025-12-02', 'túc', '<p>test 11111 ---55555</p>', 38, 'teest 1', '2025-12-02 21:41:37', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(10) NOT NULL auto_increment,
  `ma_donhang` bigint(20) NOT NULL,
  `account_number` varchar(20) NOT NULL,
  `amount_in` decimal(10,0) NOT NULL,
  `transaction_content` text NOT NULL,
  `bank_brand_name` varchar(50) NOT NULL,
  `transaction_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `is_processed` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `transactions`
--


-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `ma_user` bigint(20) NOT NULL auto_increment,
  `ho_ten` varchar(255) collate utf8_unicode_ci default NULL,
  `email` varchar(250) collate utf8_unicode_ci default NULL,
  `password` varchar(255) collate utf8_unicode_ci default NULL,
  `phone` varchar(50) collate utf8_unicode_ci default NULL,
  `dia_chi` varchar(255) collate utf8_unicode_ci default NULL,
  `vai_tro` enum('quanly','nhanvien','khachhang','nhanvienkho') collate utf8_unicode_ci default NULL,
  `created_at` timestamp NULL default NULL,
  `update_at` timestamp NULL default NULL,
  `ma_vaitro` bigint(20) default NULL,
  `trang_thai` enum('active','locked') collate utf8_unicode_ci default 'active',
  PRIMARY KEY  (`ma_user`),
  KEY `fk_nhan_vien_vai_tro` (`ma_vaitro`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=41 ;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`ma_user`, `ho_ten`, `email`, `password`, `phone`, `dia_chi`, `vai_tro`, `created_at`, `update_at`, `ma_vaitro`, `trang_thai`) VALUES
(13, 'Huỳnh Bá A', 'chieu2k2@gmail.com', '9cbf8a4dcb8e30682b927f352d6559a0', '1234567890', '11111', 'khachhang', '2025-11-08 13:40:52', NULL, 0, 'active'),
(18, 'Nguyễn Thị A', 'chieu3@gmail.com', '9cbf8a4dcb8e30682b927f352d6559a0', '1234567899', 'qqqqq', 'khachhang', '2025-11-08 15:04:12', NULL, 0, 'active'),
(25, 'Huỳnh Bá A', 'dinhchieu2k3@gmail.com', '9cbf8a4dcb8e30682b927f352d6559a0', '1234567890', '11111111111', 'nhanvienkho', '2025-11-15 10:51:44', '2025-11-15 11:01:08', 3, 'active'),
(26, 'Nguyễn Thị A', 'chieu123@gmail.com', '9cbf8a4dcb8e30682b927f352d6559a0', '1234567890', 'eqweqweqwe', 'quanly', '2025-11-15 10:57:04', NULL, 1, 'active'),
(27, 'Huỳnh Bá An', 'dinh@gmail.com', '9cbf8a4dcb8e30682b927f352d6559a0', '9999999999', 'qqqqq', 'quanly', '2025-11-15 10:58:10', '2025-11-15 10:58:47', 1, 'active'),
(29, 'Huỳnh Bá An', 'chieu21@gmail.com', 'de88e3e4ab202d87754078cbb2df6063', '9999999999', 'qqqqq', 'khachhang', '2025-11-15 11:12:10', '2025-11-15 11:22:26', 0, 'active'),
(33, 'Huỳnh B', 'chieu21@gmail.com', 'de88e3e4ab202d87754078cbb2df6063', '1234567890', 'aaaaaaaaaa', 'nhanvienkho', '2025-11-15 12:19:45', '2025-11-15 12:20:16', 3, 'active'),
(34, 'Lê Văn Túc', 'leduc2103@gmail.com', '830b400604e70a745d3b7b95a7cca5ba', '0981523030', '1 ABC, Lý thường kiệt , TPHCM', 'quanly', '2025-11-16 08:03:02', '2025-10-14 18:44:55', 1, 'active'),
(35, 'Bảo ngọc', 'ngocle@gmail.com', '25d55ad283aa400af464c76d713c07ad', '0708216837', '43 vườn lài, q12', 'khachhang', '2025-11-26 13:48:36', NULL, NULL, 'active'),
(36, 'Phạm trang', 'ngocle1@gmail.com', '8ae55a27db51f5df4ad2971216fda63d', '0999999999', '152 nam kỳ, quận 3', 'khachhang', '2025-11-26 13:53:15', NULL, NULL, 'active'),
(37, 'Staff', 'staff@gmail.com', '830b400604e70a745d3b7b95a7cca5ba', '0702312817', 'TP.HCM', 'nhanvienkho', '2025-11-27 23:01:13', NULL, NULL, 'active'),
(38, 'bảo ngọc', 'ngoctuc@gmail.com', '830b400604e70a745d3b7b95a7cca5ba', '0702312817', '43 vườn lài quận 12', 'nhanvien', '2025-11-30 21:24:09', NULL, 2, 'active'),
(39, 'Lê Nguyễn Bảo Ngọc', '95.levantuc.toky@gmail.com', '5704f55d3f505f1edb9d192fcd5e2dc9', '0981523130', '100 ngõ láng, hà nội', 'khachhang', '2025-11-30 17:30:19', NULL, NULL, 'active'),
(40, 'Phạm Băng', 'phambang@gmail.com', '8ae55a27db51f5df4ad2971216fda63d', '0777777777', '12 nguyễn văn nghi, gò vấp', 'khachhang', '2025-12-04 10:23:05', NULL, NULL, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `vai_tro`
--

CREATE TABLE `vai_tro` (
  `ma_vaitro` bigint(20) NOT NULL auto_increment,
  `ten_vaitro` varchar(255) collate utf8_unicode_ci NOT NULL,
  `mota_vaitro` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ma_vaitro`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

--
-- Dumping data for table `vai_tro`
--

INSERT INTO `vai_tro` (`ma_vaitro`, `ten_vaitro`, `mota_vaitro`) VALUES
(0, 'Khách hàng', 'Khách mua hàng online'),
(1, 'Quản lý', 'Quản lý website'),
(2, 'Nhân viên', 'Thực hiện bán hàng'),
(3, 'Nhân viên kho', 'Quản lý việc xuất kho');

-- --------------------------------------------------------

--
-- Table structure for table `xuat_kho`
--

CREATE TABLE `xuat_kho` (
  `id_xuat` bigint(20) NOT NULL auto_increment,
  `ma_donhang` bigint(20) NOT NULL,
  `id_sanpham` bigint(20) NOT NULL,
  `so_luong` int(11) NOT NULL,
  `ngay_xuat` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `ma_user` bigint(20) NOT NULL,
  PRIMARY KEY  (`id_xuat`),
  KEY `fk_xuat_kho_donhang` (`ma_donhang`),
  KEY `fk_xuat_kho_sanpham` (`id_sanpham`),
  KEY `fk_xuat_kho_nhanvien` (`ma_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `xuat_kho`
--


--
-- Constraints for dumped tables
--

--
-- Constraints for table `chitiet_donhang`
--
ALTER TABLE `chitiet_donhang`
  ADD CONSTRAINT `chitiet_donhang_ibfk_1` FOREIGN KEY (`ma_donhang`) REFERENCES `don_hang` (`ma_donhang`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `chitiet_donhang_ibfk_2` FOREIGN KEY (`id_sanpham`) REFERENCES `san_pham` (`id_sanpham`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `san_pham`
--
ALTER TABLE `san_pham`
  ADD CONSTRAINT `fk_san_pham_danh_muc` FOREIGN KEY (`id_danhmuc`) REFERENCES `danh_muc` (`id_danhmuc`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `san_pham_mau_sac`
--
ALTER TABLE `san_pham_mau_sac`
  ADD CONSTRAINT `san_pham_mau_sac_ibfk_2` FOREIGN KEY (`id_sanpham`) REFERENCES `san_pham` (`id_sanpham`) ON DELETE CASCADE,
  ADD CONSTRAINT `san_pham_mau_sac_ibfk_3` FOREIGN KEY (`mau_sac_id`) REFERENCES `mau_sac` (`ma_mau`) ON DELETE CASCADE;

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `fk_nhan_vien_vai_tro` FOREIGN KEY (`ma_vaitro`) REFERENCES `vai_tro` (`ma_vaitro`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `xuat_kho`
--
ALTER TABLE `xuat_kho`
  ADD CONSTRAINT `fk_xuat_kho_donhang` FOREIGN KEY (`ma_donhang`) REFERENCES `don_hang` (`ma_donhang`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_xuat_kho_sanpham` FOREIGN KEY (`id_sanpham`) REFERENCES `san_pham` (`id_sanpham`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `xuat_kho_ibfk_1` FOREIGN KEY (`ma_user`) REFERENCES `user` (`ma_user`) ON DELETE CASCADE ON UPDATE CASCADE;
