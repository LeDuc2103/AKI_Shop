-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1:3307
-- Thời gian đã tạo: Th12 13, 2025 lúc 04:10 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `tuc`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `banners`
--

CREATE TABLE `banners` (
  `ma_banner` bigint(20) NOT NULL,
  `id_sanpham` bigint(20) NOT NULL,
  `hinh_anh` varchar(250) DEFAULT NULL,
  `ngay_dang` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `update_at` timestamp NULL DEFAULT NULL,
  `loai_banner` enum('Trang_chu','tin_tuc') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `banners`
--

INSERT INTO `banners` (`ma_banner`, `id_sanpham`, `hinh_anh`, `ngay_dang`, `created_at`, `update_at`, `loai_banner`) VALUES
(1, 0, 'img/banner/banner_4_1.png', '2025-12-02', '2025-12-02 15:06:15', '2025-12-02 16:08:28', 'Trang_chu'),
(3, 0, 'img/banner/banner_3.png', '2025-12-02', '2025-12-02 16:17:36', NULL, 'Trang_chu'),
(4, 0, 'img/banner/banner_5.png', '2025-12-02', '2025-12-02 16:17:45', NULL, 'Trang_chu'),
(5, 0, 'img/banner/banner_6.png', '2025-12-02', '2025-12-02 16:17:51', NULL, 'Trang_chu'),
(7, 0, 'img/banner/bn1.png', '2025-12-03', '2025-12-03 16:57:19', '2025-12-03 16:57:19', 'Trang_chu'),
(8, 0, 'img/banner/bn2.png', '2025-12-03', '2025-12-03 16:57:29', '2025-12-03 16:57:29', 'Trang_chu'),
(9, 0, 'img/banner/banner01.png', '2025-12-08', '2025-12-08 05:34:23', '2025-12-08 05:34:23', 'Trang_chu');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitiet_donhang`
--

CREATE TABLE `chitiet_donhang` (
  `id_ctdh` bigint(20) NOT NULL,
  `ma_donhang` bigint(20) NOT NULL,
  `id_sanpham` bigint(20) NOT NULL,
  `so_luong` int(11) NOT NULL,
  `don_gia` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chitiet_donhang`
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
(22, 23, 16, 2, 7790000),
(23, 24, 14, 2, 5590000),
(24, 24, 16, 2, 7790000),
(25, 25, 14, 2, 5590000),
(26, 25, 16, 2, 7790000),
(27, 26, 1, 1, 10),
(28, 27, 1, 1, 10000),
(29, 28, 1, 1, 10000),
(30, 29, 1, 1, 10000),
(31, 30, 1, 1, 10000),
(32, 31, 1, 1, 10000),
(33, 32, 1, 1, 10000),
(34, 33, 1, 1, 10000),
(35, 34, 1, 1, 10000),
(36, 35, 1, 1, 10000),
(37, 36, 1, 1, 10000),
(38, 37, 1, 1, 10000),
(39, 38, 1, 1, 10000),
(40, 39, 1, 1, 10000),
(41, 40, 1, 1, 10000),
(42, 41, 1, 1, 10000),
(43, 42, 1, 1, 10000),
(44, 45, 1, 1, 10000),
(45, 46, 1, 1, 10000),
(46, 47, 1, 1, 10000),
(47, 48, 1, 1, 10000),
(48, 49, 1, 1, 10000),
(49, 50, 1, 1, 10000),
(50, 51, 1, 1, 10000),
(51, 52, 1, 1, 10000),
(52, 53, 1, 1, 10000),
(53, 54, 1, 1, 10000),
(54, 55, 1, 1, 10000),
(55, 56, 16, 1, 7790000),
(56, 57, 1, 1, 10000),
(57, 58, 1, 1, 10000),
(58, 59, 1, 1, 10000),
(59, 60, 1, 1, 10000),
(60, 61, 1, 1, 10000),
(61, 62, 1, 1, 10000),
(62, 63, 1, 1, 10000),
(63, 64, 1, 1, 10000),
(64, 65, 1, 1, 10000),
(65, 66, 1, 3, 2000),
(66, 67, 1, 1, 2000),
(67, 68, 1, 1, 2000),
(68, 69, 1, 1, 2000),
(69, 70, 1, 1, 2000),
(71, 72, 1, 1, 2000),
(72, 73, 1, 1, 2000),
(73, 74, 1, 1, 2000),
(74, 75, 1, 1, 2000),
(75, 76, 1, 1, 2000),
(76, 77, 1, 1, 2000),
(77, 78, 1, 1, 2000),
(78, 91, 1, 1, 2000),
(79, 104, 1, 1, 2000),
(80, 105, 21, 3, 500000),
(81, 106, 21, 1, 500000),
(82, 107, 1, 1, 2000);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `comments`
--

CREATE TABLE `comments` (
  `id_danh_gia` bigint(20) UNSIGNED NOT NULL,
  `id_sanpham` bigint(20) UNSIGNED NOT NULL,
  `ma_user` bigint(20) UNSIGNED DEFAULT NULL,
  `ho_ten` varchar(255) NOT NULL,
  `so_sao` tinyint(1) NOT NULL DEFAULT 5,
  `noi_dung` text DEFAULT NULL,
  `ngay_danh_gia` datetime DEFAULT NULL,
  `trang_thai` enum('hien','an') DEFAULT 'hien',
  `phan_hoi` text DEFAULT NULL,
  `nguoi_phan_hoi` varchar(255) DEFAULT NULL,
  `ngay_phan_hoi` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `seen` enum('Da_doc','Chua_doc') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `comments`
--

INSERT INTO `comments` (`id_danh_gia`, `id_sanpham`, `ma_user`, `ho_ten`, `so_sao`, `noi_dung`, `ngay_danh_gia`, `trang_thai`, `phan_hoi`, `nguoi_phan_hoi`, `ngay_phan_hoi`, `created_at`, `updated_at`, `seen`) VALUES
(1, 13, 34, 'Lê Văn Túc', 5, 'fkạlfjsalfjlsàládf', '2025-12-03 22:04:29', 'hien', NULL, NULL, NULL, '2025-12-03 15:04:29', NULL, 'Chua_doc'),
(2, 12, 34, 'Lê Văn Túc', 1, 'sản phẩm này tệ quá v tôi muốn trả hàng thì làm sao', '2025-12-03 22:09:27', 'hien', 'xin hỏi sản phẩm bị j ạ. Tôi có thể liên lạc call bạnn đc ko?', 'Nhân viên', '2025-12-03 22:26:22', '2025-12-03 15:09:27', NULL, 'Da_doc'),
(3, 11, 34, 'Lê Văn Túc', 1, 'sao mà tệ quá đi à nha', '2025-12-03 22:26:16', 'hien', 'ok bạn nha\r\naaaaaa', 'Nhân viên', '2025-12-03 22:26:32', '2025-12-03 15:26:16', NULL, 'Chua_doc'),
(4, 11, 34, 'Lê Văn Túc', 4, 'fsadfsàááadfsagfgfgfghghfgjhghfjhgf', '2025-12-03 22:41:09', 'hien', NULL, NULL, NULL, '2025-12-03 15:41:09', NULL, 'Chua_doc'),
(5, 14, 34, 'Lê Văn Túc', 5, 'tại vì sao anh lại như v', '2025-12-03 23:06:09', 'hien', NULL, NULL, NULL, '2025-12-03 16:06:09', NULL, 'Da_doc'),
(6, 14, 34, 'Lê Văn Túc', 2, 'rất đẹp', '2025-12-03 23:06:21', 'hien', NULL, NULL, NULL, '2025-12-03 16:06:21', NULL, 'Da_doc'),
(7, 14, 34, 'Lê Văn Túc', 5, 'sản phẩm số 1', '2025-12-03 23:06:32', 'hien', NULL, NULL, NULL, '2025-12-03 16:06:32', NULL, 'Da_doc'),
(8, 14, 34, 'Lê Văn Túc', 4, 'aaaaaaaaaaaaaaaaaaaaaaa', '2025-12-03 23:06:42', 'hien', NULL, NULL, NULL, '2025-12-03 16:06:42', NULL, 'Da_doc'),
(9, 14, 34, 'Lê Văn Túc', 5, 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbb', '2025-12-03 23:06:48', 'hien', NULL, NULL, NULL, '2025-12-03 16:06:48', NULL, 'Da_doc'),
(10, 14, 34, 'Lê Văn Túc', 5, 'ccccccccccccccccccccccccccccccccccc', '2025-12-03 23:06:54', 'hien', NULL, NULL, NULL, '2025-12-03 16:06:54', NULL, 'Da_doc'),
(11, 14, 34, 'Lê Văn Túc', 3, 'dddddddddddđe', '2025-12-03 23:07:02', 'hien', NULL, NULL, NULL, '2025-12-03 16:07:02', NULL, 'Da_doc'),
(12, 16, 34, 'Lê Văn Túc', 5, 'sản phẩm rất tốt, tôi rất hài lòng khi mua', '2025-12-04 10:41:25', 'hien', 'Cảm ơn bạn đã ủng hộ AKI', 'Nhân viên', '2025-12-04 10:59:11', '2025-12-04 03:41:25', NULL, 'Da_doc'),
(13, 17, 34, 'Lê Văn Túc', 5, 'ok mie mie', '2025-12-08 14:45:57', 'hien', NULL, NULL, NULL, '2025-12-08 07:45:57', NULL, 'Da_doc');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danh_gia`
--

CREATE TABLE `danh_gia` (
  `id_danhgia` bigint(20) NOT NULL,
  `ma_user` bigint(20) NOT NULL,
  `id_sanpham` bigint(20) NOT NULL,
  `noi_dung` varchar(500) DEFAULT NULL,
  `ngay_binhluan` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `update_at` timestamp NULL DEFAULT NULL,
  `trang_thai` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danh_muc`
--

CREATE TABLE `danh_muc` (
  `id_danhmuc` bigint(20) NOT NULL,
  `ten_danhmuc` varchar(250) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `update_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `danh_muc`
--

INSERT INTO `danh_muc` (`id_danhmuc`, `ten_danhmuc`, `created_at`, `update_at`) VALUES
(1, 'Máy đọc sách Boox', '2025-10-14 09:33:44', NULL),
(2, 'Máy đọc sách Savi', '2025-10-14 09:33:44', NULL),
(3, 'Máy đọc sách Kindle', '2025-10-14 09:33:44', NULL),
(4, 'Máy đọc sách reMarkable', '2025-10-14 09:33:44', NULL),
(5, 'Máy đọc sách Kobo', '2025-10-14 09:33:44', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `don_hang`
--

CREATE TABLE `don_hang` (
  `ma_donhang` bigint(20) NOT NULL,
  `ten_nguoinhan` varchar(50) NOT NULL,
  `diachi_nhan` varchar(250) NOT NULL,
  `email_nguoinhan` varchar(50) NOT NULL,
  `so_dienthoai` varchar(50) NOT NULL,
  `trangthai_thanhtoan` enum('chua_thanh_toan','da_thanh_toan') NOT NULL,
  `phuongthuc_thanhtoan` varchar(50) NOT NULL,
  `thanh_toan` varchar(50) NOT NULL,
  `tien_hang` double NOT NULL,
  `tien_ship` double NOT NULL,
  `tong_tien` double NOT NULL,
  `ma_user` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `update_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `trang_thai` enum('cho_xu_ly','xac_nhan','da_xuat_kho','da_nhap_kho','hoan_thanh','huy') DEFAULT 'cho_xu_ly',
  `order_code` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `don_hang`
--

INSERT INTO `don_hang` (`ma_donhang`, `ten_nguoinhan`, `diachi_nhan`, `email_nguoinhan`, `so_dienthoai`, `trangthai_thanhtoan`, `phuongthuc_thanhtoan`, `thanh_toan`, `tien_hang`, `tien_ship`, `tong_tien`, `ma_user`, `created_at`, `update_at`, `trang_thai`, `order_code`) VALUES
(1, 'Huỳnh Đình Chiểu', 'hùng vương', 'dinhchieu1k11@gmail.com', '1234567890', 'chua_thanh_toan', 'qr', 'chưa thanh toán', 1500000, 15000, 1515000, 13, '2025-12-04 03:57:48', '2025-11-15 05:28:23', 'hoan_thanh', ''),
(2, 'Huỳnh Đình Chiểu', 'hùng vương', 'dinhchieu1k11@gmail.com', '1234567890', 'chua_thanh_toan', 'qr', 'chưa thanh toán', 1500000, 15000, 1515000, 13, '2025-12-13 14:03:30', '0000-00-00 00:00:00', 'hoan_thanh', ''),
(3, 'Huỳnh Đình A', 'hùng vương', 'dinhchieu1k1@gmail.com', '1234567890', 'chua_thanh_toan', 'qr', 'chưa thanh toán', 20000000, 15000, 20015000, 18, '2025-12-03 12:33:47', '2025-11-15 05:31:47', 'hoan_thanh', ''),
(4, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'cod', 'đã hủy', 5411321, 15000, 5426321, 34, '2025-11-27 17:38:32', '0000-00-00 00:00:00', 'hoan_thanh', ''),
(5, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'vnpay', 'đã thanh toán', 5390000, 15000, 5405000, 34, '2025-11-27 17:09:41', '0000-00-00 00:00:00', 'hoan_thanh', ''),
(6, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'vnpay', 'đã thanh toán', 5390000, 15000, 5405000, 34, '2025-11-27 17:38:44', '0000-00-00 00:00:00', 'hoan_thanh', ''),
(7, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'vnpay', 'đã thanh toán', 5390000, 15000, 5405000, 34, '2025-12-07 06:36:00', '0000-00-00 00:00:00', '', ''),
(8, 'bảo ngọc', '43 vườn lài quận 12', 'ngoctuc@gmail.com', '0702312817', 'da_thanh_toan', 'vnpay', 'đã thanh toán', 16170000, 15000, 16185000, 38, '2025-12-07 07:04:38', '2025-11-30 14:30:47', 'hoan_thanh', ''),
(9, 'bảo ngọc', '43 vườn lài quận 12', 'ngoctuc@gmail.com', '0702312817', 'da_thanh_toan', 'cod', 'đã thanh toán', 17980000, 15000, 17995000, 38, '2025-11-30 14:55:09', '0000-00-00 00:00:00', 'hoan_thanh', ''),
(10, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'vnpay', 'đã thanh toán', 13880000, 15000, 13895000, 34, '2025-12-07 05:57:36', '2025-12-04 04:02:32', 'hoan_thanh', ''),
(11, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'cod', 'đã hủy', 60390000, 15000, 60405000, 34, '2025-12-01 14:39:00', '0000-00-00 00:00:00', 'huy', ''),
(12, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'cod', 'đã thanh toán', 7500000, 15000, 7515000, 34, '2025-12-04 04:03:12', '2025-12-04 04:02:36', 'hoan_thanh', ''),
(13, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'cod', 'đã thanh toán', 5490000, 15000, 5505000, 34, '2025-12-03 16:39:49', '0000-00-00 00:00:00', 'hoan_thanh', ''),
(14, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'cod', 'đã thanh toán', 54900000, 15000, 54915000, 34, '2025-12-07 07:04:30', '0000-00-00 00:00:00', 'hoan_thanh', ''),
(15, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'cod', 'đã hủy', 100, 15000, 15100, 34, '2025-12-04 03:37:07', '0000-00-00 00:00:00', 'huy', ''),
(16, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'cod', 'đã hủy', 779000000, 15000, 779015000, 34, '2025-12-03 16:50:37', '0000-00-00 00:00:00', 'huy', ''),
(17, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'cod', 'đã thanh toán', 779000000, 15000, 779015000, 34, '2025-12-03 16:53:48', '0000-00-00 00:00:00', 'hoan_thanh', ''),
(18, 'Phạm Băng', '12 nguyễn văn nghi, gò vấp', 'phambang@gmail.com', '0777777777', 'da_thanh_toan', 'vnpay', 'đã thanh toán', 75000000, 15000, 75015000, 40, '2025-12-07 07:04:48', '0000-00-00 00:00:00', 'hoan_thanh', ''),
(19, 'Phạm Băng', '12 nguyễn văn nghi, gò vấp', 'phambang@gmail.com', '0777777777', 'chua_thanh_toan', 'vnpay', 'chưa thanh toán', 75000000, 15000, 75015000, 40, '2025-12-04 03:31:34', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(20, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'vnpay', 'chưa thanh toán', 15580000, 15000, 15595000, 34, '2025-12-04 04:19:09', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(21, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'vnpay', 'chưa thanh toán', 15580000, 15000, 15595000, 34, '2025-12-04 04:21:37', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(22, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'vnpay', 'đã thanh toán', 15580000, 15000, 15595000, 34, '2025-12-07 07:11:32', '0000-00-00 00:00:00', 'hoan_thanh', ''),
(23, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'vnpay', 'chưa thanh toán', 15580000, 15000, 15595000, 34, '2025-12-04 15:37:51', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(24, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'sepay', 'chưa thanh toán', 26760000, 15000, 26775000, 34, '2025-12-06 16:30:19', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(25, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'sepay', 'chưa thanh toán', 26760000, 15000, 26775000, 34, '2025-12-06 16:31:07', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(26, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'sepay', 'đã thanh toán', 10, 15000, 15010, 34, '2025-12-07 07:09:26', '0000-00-00 00:00:00', 'hoan_thanh', ''),
(27, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'sepay', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-06 16:39:18', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(28, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'sepay', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-06 16:45:36', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(29, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'sepay', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-06 16:50:48', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(30, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'sepay', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-06 16:56:57', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(31, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'sepay', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-06 17:08:07', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(32, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'sepay', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-06 17:11:50', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(33, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'vnpay', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-06 17:11:53', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(34, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'sepay', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-06 17:12:09', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(35, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'sepay_qr', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-06 17:33:20', '0000-00-00 00:00:00', 'cho_xu_ly', '2124'),
(36, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'sepay_qr', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-06 17:35:26', '0000-00-00 00:00:00', 'cho_xu_ly', '2472'),
(37, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'sepay_qr', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-06 17:41:11', '0000-00-00 00:00:00', 'cho_xu_ly', '7135'),
(38, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'sepay_qr', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-06 17:43:16', '0000-00-00 00:00:00', 'cho_xu_ly', '5623'),
(39, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'sepay_qr', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-06 17:54:48', '0000-00-00 00:00:00', 'cho_xu_ly', '8803'),
(40, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'sepay_qr', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-06 17:55:26', '0000-00-00 00:00:00', 'cho_xu_ly', '8626'),
(41, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'sepay_qr', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-06 18:02:58', '0000-00-00 00:00:00', 'cho_xu_ly', '3312'),
(42, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'sepay_qr', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-06 18:08:26', '0000-00-00 00:00:00', 'cho_xu_ly', '4302'),
(43, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay QR', 'chưa thanh toán', 0, 15000, 15000, 34, '2025-12-06 18:30:16', '0000-00-00 00:00:00', 'cho_xu_ly', '8784'),
(44, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay QR', 'chưa thanh toán', 0, 15000, 15000, 34, '2025-12-06 18:31:15', '0000-00-00 00:00:00', 'cho_xu_ly', '4645'),
(45, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay QR', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-06 18:33:02', '0000-00-00 00:00:00', 'cho_xu_ly', '2556'),
(46, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay QR', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-06 18:33:58', '0000-00-00 00:00:00', 'cho_xu_ly', '4378'),
(47, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'vnpay', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-07 02:35:13', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(48, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'vnpay', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-07 02:48:38', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(49, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay QR', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-07 03:07:29', '0000-00-00 00:00:00', 'cho_xu_ly', '3919'),
(50, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay QR', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-07 03:14:17', '0000-00-00 00:00:00', 'cho_xu_ly', '8976'),
(51, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay QR', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-07 03:30:37', '0000-00-00 00:00:00', 'cho_xu_ly', '1805'),
(52, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay QR', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-07 03:46:12', '0000-00-00 00:00:00', 'cho_xu_ly', '1550'),
(53, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay QR', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-07 03:48:35', '0000-00-00 00:00:00', 'cho_xu_ly', '1049'),
(54, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'vnpay', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-07 04:08:45', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(55, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'vnpay', 'đã thanh toán', 10000, 15000, 25000, 34, '2025-12-07 23:59:50', '2025-12-07 23:59:33', 'hoan_thanh', ''),
(56, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'cod', 'đã thanh toán', 7790000, 15000, 7805000, 34, '2025-12-07 23:51:42', '0000-00-00 00:00:00', 'hoan_thanh', ''),
(57, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'vnpay', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-08 08:42:25', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(58, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay QR', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-08 08:42:48', '0000-00-00 00:00:00', 'cho_xu_ly', '5819'),
(59, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'vnpay', 'đã thanh toán', 10000, 15000, 25000, 34, '2025-12-08 08:53:51', '0000-00-00 00:00:00', 'xac_nhan', ''),
(60, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'vnpay', 'đã thanh toán', 10000, 15000, 25000, 34, '2025-12-08 13:32:51', '0000-00-00 00:00:00', 'xac_nhan', ''),
(61, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay QR', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-08 15:49:33', '0000-00-00 00:00:00', 'cho_xu_ly', '1279'),
(62, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay QR', 'chưa thanh toán', 10000, 15000, 25000, 34, '2025-12-08 15:51:11', '0000-00-00 00:00:00', 'cho_xu_ly', '1961'),
(63, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay QR', 'chưa thanh toán', 10000, 15000, 24000, 34, '2025-12-08 15:54:06', '0000-00-00 00:00:00', 'cho_xu_ly', '2970'),
(64, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay QR', 'chưa thanh toán', 10000, 15000, 24000, 34, '2025-12-08 15:56:41', '0000-00-00 00:00:00', 'cho_xu_ly', '9399'),
(65, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'SePay QR', 'đã thanh toán', 10000, 15000, 24000, 34, '2025-12-08 16:16:08', '2025-12-08 16:16:08', 'xac_nhan', '8074'),
(66, 'Nguyễn Thị Duyên', '123 An Dương Vương', 'duyen@gmail.com', '0369217145', 'chua_thanh_toan', 'SePay QR', 'chưa thanh toán', 6000, 30000, 35400, 41, '2025-12-08 16:31:35', '0000-00-00 00:00:00', 'cho_xu_ly', '9826'),
(67, 'Nguyễn Thị Duyên', '123 An Dương Vương', 'duyen@gmail.com', '0369217145', 'chua_thanh_toan', 'SePay QR', 'chưa thanh toán', 2000, 15000, 16800, 41, '2025-12-08 16:40:01', '0000-00-00 00:00:00', 'cho_xu_ly', '3979'),
(68, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay QR', 'chưa thanh toán', 2000, 15000, 16800, 34, '2025-12-08 16:42:07', '0000-00-00 00:00:00', 'cho_xu_ly', '4140'),
(69, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay QR', 'chưa thanh toán', 2000, 15000, 16800, 34, '2025-12-08 16:55:25', '0000-00-00 00:00:00', 'cho_xu_ly', '8806'),
(70, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay QR', 'chưa thanh toán', 2000, 15000, 16800, 34, '2025-12-08 16:56:38', '0000-00-00 00:00:00', 'cho_xu_ly', '6187'),
(71, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'sepay', '', 2000, 15000, 16800, 34, '2025-12-09 13:36:16', '0000-00-00 00:00:00', '', ''),
(72, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'sepay', '', 2000, 15000, 16800, 34, '2025-12-09 13:38:18', '0000-00-00 00:00:00', '', ''),
(73, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'sepay', '', 2000, 15000, 16800, 34, '2025-12-09 14:16:15', '0000-00-00 00:00:00', '', ''),
(74, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay QR', 'chưa thanh toán', 2000, 15000, 16800, 34, '2025-12-09 14:21:57', '0000-00-00 00:00:00', 'cho_xu_ly', '2983'),
(75, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay QR', 'chưa thanh toán', 2000, 15000, 16800, 34, '2025-12-09 14:26:18', '0000-00-00 00:00:00', 'cho_xu_ly', '5129'),
(76, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'vnpay', 'chưa thanh toán', 2000, 15000, 16800, 34, '2025-12-09 14:26:23', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(77, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay QR', 'chưa thanh toán', 2000, 15000, 16800, 34, '2025-12-09 14:36:57', '0000-00-00 00:00:00', 'cho_xu_ly', '9623'),
(78, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay QR', 'chưa thanh toán', 2000, 15000, 16800, 34, '2025-12-09 14:49:28', '0000-00-00 00:00:00', 'cho_xu_ly', '9324'),
(79, 'Test User', 'Test Address', 'test@example.com', '0123456789', 'chua_thanh_toan', 'SePay', '', 0, 0, 100000, 1, '2025-12-10 17:48:54', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(80, 'Test User', 'Test Address', 'test@example.com', '0123456789', 'chua_thanh_toan', 'SePay', '', 0, 0, 1000, 1, '2025-12-10 17:49:16', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(81, 'Test User', 'Test Address', 'test@example.com', '0123456789', 'chua_thanh_toan', 'SePay', '', 0, 0, 10000, 1, '2025-12-10 17:49:34', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(82, '', '', '', '', 'chua_thanh_toan', '', '', 0, 0, 16800, 0, '2025-12-13 11:06:07', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(83, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay', '', 0, 0, 16800, 34, '2025-12-13 11:08:55', '0000-00-00 00:00:00', '', ''),
(84, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay', '', 0, 0, 16800, 34, '2025-12-13 11:10:27', '0000-00-00 00:00:00', '', ''),
(85, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay', '', 0, 0, 16800, 34, '2025-12-13 11:17:04', '0000-00-00 00:00:00', '', ''),
(86, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay', '', 0, 0, 16800, 34, '2025-12-13 11:20:09', '0000-00-00 00:00:00', '', ''),
(87, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'SePay', '', 0, 0, 16800, 34, '2025-12-13 11:38:46', '2025-12-13 11:38:46', '', ''),
(88, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay', '', 0, 0, 16800, 34, '2025-12-13 11:43:04', '0000-00-00 00:00:00', '', ''),
(89, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay', '', 0, 0, 16800, 34, '2025-12-13 11:50:40', '0000-00-00 00:00:00', '', ''),
(90, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'SePay', '', 0, 0, 16800, 34, '2025-12-13 12:00:12', '2025-12-13 12:00:12', '', ''),
(91, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'cod', 'đã hủy', 2000, 15000, 16800, 34, '2025-12-13 12:54:01', '0000-00-00 00:00:00', 'huy', ''),
(92, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'SePay', '', 0, 0, 16800, 34, '2025-12-13 12:55:50', '2025-12-13 12:55:50', '', ''),
(93, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'SePay', '', 0, 0, 16800, 34, '2025-12-13 13:03:29', '2025-12-13 13:03:29', '', ''),
(94, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'SePay', '', 0, 0, 16800, 34, '2025-12-13 13:03:57', '2025-12-13 13:03:57', '', ''),
(95, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay', '', 0, 0, 18600, 34, '2025-12-13 13:07:56', '0000-00-00 00:00:00', '', ''),
(96, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'SePay', '', 0, 0, 16800, 34, '2025-12-13 13:14:32', '2025-12-13 13:14:32', '', ''),
(97, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay', '', 0, 0, 16800, 34, '2025-12-13 13:25:02', '0000-00-00 00:00:00', '', ''),
(98, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'SePay', '', 0, 0, 16800, 34, '2025-12-13 13:32:29', '0000-00-00 00:00:00', '', ''),
(99, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'SePay', '', 0, 0, 16800, 34, '2025-12-13 13:45:31', '2025-12-13 13:45:31', '', ''),
(100, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'SePay', '', 0, 0, 16800, 34, '2025-12-13 13:44:27', '2025-12-13 13:44:27', '', ''),
(101, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'SePay', '', 0, 0, 16800, 34, '2025-12-13 13:42:42', '2025-12-13 13:42:42', '', ''),
(102, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'SePay', '', 0, 0, 16200, 34, '2025-12-13 13:49:25', '2025-12-13 13:49:25', '', ''),
(103, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'da_thanh_toan', 'SePay', '', 0, 0, 15600, 34, '2025-12-13 13:59:23', '2025-12-13 13:59:23', '', ''),
(104, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'cod', 'chưa thanh toán', 2000, 15000, 15600, 34, '2025-12-13 14:04:42', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(105, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'vnpay', 'chưa thanh toán', 1500000, 15000, 465000, 34, '2025-12-13 14:07:44', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(106, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'vnpay', 'chưa thanh toán', 500000, 15000, 165000, 34, '2025-12-13 14:10:49', '0000-00-00 00:00:00', 'cho_xu_ly', ''),
(107, 'Lê Văn Túc', '1 ABC, Lý thường kiệt , TPHCM', 'leduc2103@gmail.com', '0981523030', 'chua_thanh_toan', 'vnpay', 'chưa thanh toán', 2000, 15000, 15600, 34, '2025-12-13 14:13:32', '0000-00-00 00:00:00', 'cho_xu_ly', '');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `don_hang_doi_tra`
--

CREATE TABLE `don_hang_doi_tra` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ma_donhang` bigint(20) NOT NULL,
  `ma_user` bigint(20) NOT NULL,
  `ly_do` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `trang_thai_kho` enum('cho_nhap_kho','da_nhap_kho') DEFAULT 'cho_nhap_kho',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `bang_chung` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `don_hang_doi_tra`
--

INSERT INTO `don_hang_doi_tra` (`id`, `ma_donhang`, `ma_user`, `ly_do`, `status`, `trang_thai_kho`, `created_at`, `updated_at`, `bang_chung`) VALUES
(1, 7, 34, 'Hàng ko vừa ý tôi á', 'approved', 'cho_nhap_kho', '2025-11-27 16:41:10', '2025-11-27 17:37:07', ''),
(2, 9, 38, 'bbbb', 'approved', 'da_nhap_kho', '2025-11-27 17:06:37', '2025-12-08 00:26:24', ''),
(3, 5, 34, 'jjjjj', 'rejected', 'cho_nhap_kho', '2025-11-27 17:09:52', '2025-12-07 07:02:54', ''),
(7, 4, 34, 'hhhhhhh', 'pending', 'cho_nhap_kho', '2025-11-27 17:46:46', '2025-12-07 07:09:30', 'img/evidence/evidence_4_1765091370.png'),
(8, 6, 34, 'gfdsgfdsgsdgdfs', 'pending', 'cho_nhap_kho', '2025-12-01 19:39:45', '2025-12-07 07:03:00', 'img/evidence/evidence_6_1765090980.png'),
(9, 12, 34, 'tôi muốn đổi trả hàng do ko hài lòng', 'pending', 'cho_nhap_kho', '2025-12-04 04:04:14', '2025-12-04 04:05:10', ''),
(10, 10, 34, 'fdágfagfdag', 'pending', 'cho_nhap_kho', '2025-12-07 05:57:41', '2025-12-07 07:01:31', 'img/evidence/evidence_10_1765087139.mp4'),
(11, 17, 34, 'tôi cần em', 'pending', 'cho_nhap_kho', '2025-12-07 07:03:34', '2025-12-07 07:06:41', ''),
(12, 13, 34, 'llllllllllllllllllll', 'pending', 'cho_nhap_kho', '2025-12-07 07:07:15', '0000-00-00 00:00:00', 'img/evidence/evidence_13_1765091235.png'),
(13, 14, 34, '1414141414141', 'pending', 'cho_nhap_kho', '2025-12-07 07:07:42', '2025-12-07 07:08:40', 'img/evidence/evidence_14_1765091320.png'),
(14, 26, 34, 'đơn hàng số 26', 'pending', 'cho_nhap_kho', '2025-12-07 07:09:42', '2025-12-07 07:11:38', 'img/evidence/evidence_26_1765091427.png'),
(15, 22, 34, 'sửa lịa mới rồi á', 'pending', 'cho_nhap_kho', '2025-12-07 07:11:53', '2025-12-07 07:21:14', 'img/evidence/evidence_22_1765091886.png'),
(16, 56, 34, 'tôi cần trả hàng ngày 8/12/2025', 'pending', 'cho_nhap_kho', '2025-12-08 06:58:42', '0000-00-00 00:00:00', '');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `gio_hang`
--

CREATE TABLE `gio_hang` (
  `id_giohang` bigint(20) NOT NULL,
  `ma_user` bigint(20) NOT NULL,
  `id_sanpham` bigint(20) NOT NULL,
  `so_luong` int(11) NOT NULL,
  `thanh_tien` double NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `update_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `gio_hang`
--

INSERT INTO `gio_hang` (`id_giohang`, `ma_user`, `id_sanpham`, `so_luong`, `thanh_tien`, `created_at`, `update_at`) VALUES
(3, 36, 15, 200, 1078000000, '2025-11-26 09:12:26', '0000-00-00 00:00:00'),
(5, 36, 12, 3, 25470000, '2025-11-26 09:17:06', '0000-00-00 00:00:00'),
(6, 36, 13, 4, 78360000, '2025-11-26 09:45:32', '0000-00-00 00:00:00'),
(7, 37, 15, 1, 5390000, '2025-11-26 09:58:15', '0000-00-00 00:00:00'),
(8, 40, 11, 10, 75000000, '2025-12-04 03:25:47', '2025-12-04 03:25:30'),
(14, 41, 1, 1, 2000, '2025-12-08 16:36:03', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hoan_tien`
--

CREATE TABLE `hoan_tien` (
  `id` bigint(20) NOT NULL,
  `stk` int(20) NOT NULL,
  `ngan_hang` varchar(100) NOT NULL,
  `ly_do_hoan_tien` varchar(250) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ma_donhang` bigint(20) NOT NULL,
  `ma_user` bigint(20) NOT NULL,
  `so_tai_khoan` varchar(50) NOT NULL,
  `ten_ngan_hang` varchar(100) NOT NULL,
  `ly_do` text DEFAULT NULL,
  `so_tien` decimal(15,2) NOT NULL DEFAULT 0.00,
  `trang_thai` enum('da_hoan_tien','chua_hoan_tien') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `hoan_tien`
--

INSERT INTO `hoan_tien` (`id`, `stk`, `ngan_hang`, `ly_do_hoan_tien`, `created_at`, `updated_at`, `ma_donhang`, `ma_user`, `so_tai_khoan`, `ten_ngan_hang`, `ly_do`, `so_tien`, `trang_thai`) VALUES
(1, 0, '', '', '2025-12-07 06:36:00', '2025-12-07 06:54:09', 7, 34, '098567891', 'MB bank', 'tôi suy nghĩ lại', 5405000.00, 'da_hoan_tien'),
(2, 0, '', '', '2025-12-08 08:11:30', '2025-12-08 09:11:22', 55, 34, '0981523120', 'Techcom bank', 'tôi đã đổi trả hàng xong tôi cần được hoàn tiền mã đơn hàng là #54', 25000.00, 'da_hoan_tien');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hotro`
--

CREATE TABLE `hotro` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ho_va_ten` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `so_dien_thoai` varchar(255) DEFAULT NULL,
  `noi_dung` varchar(255) DEFAULT NULL,
  `ngay_gui` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `hotro`
--

INSERT INTO `hotro` (`id`, `ho_va_ten`, `email`, `so_dien_thoai`, `noi_dung`, `ngay_gui`, `created_at`, `updated_at`) VALUES
(1, 'Nguyên Khoa', 'leduc2103@gmail.com', '0981523222', 'atest test', '2025-12-03', '2025-12-02 17:06:42', '2025-12-02 17:06:42'),
(2, 'Nguyên Khoa', 'leduc2103@gmail.com', '0981523130', 'Tôi muốn bạn hỗ trợ tôi về vấn đề sản phẩm này có được không', '2025-12-04', '2025-12-04 03:39:44', '2025-12-04 03:39:44');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khuyen_mai`
--

CREATE TABLE `khuyen_mai` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ten_km` varchar(100) NOT NULL,
  `ngay_bat_dau` datetime NOT NULL,
  `ngay_ket_thuc` datetime NOT NULL,
  `phan_tram_km` int(10) NOT NULL,
  `so_luog_toi_da` double NOT NULL,
  `so_luong_su_dung` double NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `khuyen_mai`
--

INSERT INTO `khuyen_mai` (`id`, `ten_km`, `ngay_bat_dau`, `ngay_ket_thuc`, `phan_tram_km`, `so_luog_toi_da`, `so_luong_su_dung`, `created_at`, `updated_at`) VALUES
(1, 'KLTN', '2025-12-12 00:00:00', '2025-12-31 00:00:00', 70, 100000, 0, '2025-12-13 13:48:08', '2025-12-13 13:48:08');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `mau_sac`
--

CREATE TABLE `mau_sac` (
  `ma_mau` bigint(20) UNSIGNED NOT NULL,
  `ten_mau` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `update_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `mau_sac`
--

INSERT INTO `mau_sac` (`ma_mau`, `ten_mau`, `created_at`, `update_at`) VALUES
(1, 'Màu trắng', '2025-10-14 11:17:03', '0000-00-00 00:00:00'),
(2, 'Màu đen', '2025-10-14 11:17:03', '0000-00-00 00:00:00'),
(3, 'Màu xám', '2025-10-14 11:17:03', '0000-00-00 00:00:00'),
(4, 'Màu đỏ tía', '2025-10-14 11:17:03', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_forgot`
--

CREATE TABLE `password_forgot` (
  `email` varchar(50) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `update_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `password_forgot`
--

INSERT INTO `password_forgot` (`email`, `token`, `created_at`, `update_at`) VALUES
('95.levantuc.toky@gmail.com', '469612', '2025-11-30 08:36:38', '0000-00-00 00:00:00'),
('95.levantuc.toky@gmail.com', '717745', '2025-11-30 08:53:28', '0000-00-00 00:00:00'),
('leduc2103@gmail.com', '241725', '2025-11-30 09:34:12', '0000-00-00 00:00:00'),
('leduc2103@gmail.com', '111866', '2025-11-30 09:36:27', '0000-00-00 00:00:00'),
('leduc2103@gmail.com', 'f409754fcd033a31a6fd062238f713a1', '2025-11-30 09:54:08', '0000-00-00 00:00:00'),
('95.levantuc.toky@gmail.com', 'c4d7b20e7c2be56b534971d47ed77fb7', '2025-11-30 09:54:43', '0000-00-00 00:00:00'),
('95.levantuc.toky@gmail.com', 'b48bdea399abc670e34ab630f0a6f308', '2025-11-30 09:54:55', '0000-00-00 00:00:00'),
('95.levantuc.toky@gmail.com', '26e6e35f8102382ecf0b554f598c06a2', '2025-11-30 09:56:15', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `san_pham`
--

CREATE TABLE `san_pham` (
  `id_sanpham` bigint(20) NOT NULL,
  `ten_sanpham` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `gia` double DEFAULT NULL,
  `hinh_anh` varchar(250) DEFAULT NULL,
  `mau_sac` varchar(250) DEFAULT NULL,
  `so_luong` int(50) DEFAULT NULL,
  `mo_ta` longtext NOT NULL,
  `id_danhmuc` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `update_at` timestamp NULL DEFAULT NULL,
  `gia_khuyen_mai` float DEFAULT NULL,
  `ct_sp` longtext NOT NULL,
  `anh_con` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `san_pham`
--

INSERT INTO `san_pham` (`id_sanpham`, `ten_sanpham`, `gia`, `hinh_anh`, `mau_sac`, `so_luong`, `mo_ta`, `id_danhmuc`, `created_at`, `update_at`, `gia_khuyen_mai`, `ct_sp`, `anh_con`) VALUES
(1, 'Máy đọc sách màu Boox Note Air 3 C', 2000, 'img/products/boox/noteair3C.jpg', 'Màu đen', 147, '<p><strong>ƯU Đ&Atilde;I V&Agrave; QU&Agrave; TẶNG</strong></p>\r\n<p>&nbsp;- Kho s&aacute;ch t&iacute;ch hợp sẵn trong m&aacute;y, đủ c&aacute;c thể loại: tiểu thuyết, văn học, kỹ năng sống, ng&ocirc;n t&igrave;nh, kinh doanh...</p>\r\n<p>&nbsp;- Mua Ebook bản quyền với gi&aacute; ưu đ&atilde;i qua ứng dụng Savi</p>\r\n<p><strong>&nbsp;AKISHOP CAM KẾT - Bảo h&agrave;nh 12 th&aacute;ng</strong></p>\r\n<p>&nbsp;- C&agrave;i đặt MIỄN PH&Iacute; c&aacute;c bộ từ điển (Anh - Việt, Việt - Anh, Nhật - Việt, Việt - Nhật, Đức - Việt, Việt - Đức, Trung - Việt...)</p>\r\n<p>&nbsp;- Hỗ trợ t&igrave;m kiếm s&aacute;ch theo y&ecirc;u cầu cho Kh&aacute;ch h&agrave;ng <br>- Hỗ trợ kỹ thuật v&agrave; sử dụng phần mềm trọn đời.</p>\r\n<p>&nbsp;</p>', 1, '2025-12-06 16:38:04', '2025-12-08 16:20:59', 0, '<p><img src=\"img/products/boox/noteair3C.jpg\" alt=\"\" width=\"600\" height=\"600\"></p>\r\n<p>Đỉnh cao lu&ocirc;n &aacute; kh&aacute;ch h&agrave;ng ơi</p>', 'img/products/boox/3C_2.jpg|img/products/boox/3C_3.jpg|img/products/boox/3C_4.jpg'),
(2, 'Máy đọc sách navi', 10000000, 'img/products/savi/test.jpg', 'Đen', 100, 'máy ok lắm', 2, '2025-11-09 07:12:29', '2025-11-15 02:15:59', 100000, '', ''),
(4, 'Máy đọc sách kiki', 321321312, 'img/products/savi/kiki.jpg', 'Màu đen', 322, 'dsadsadsad', 2, '2025-11-15 02:30:13', '2025-11-15 02:56:44', 21321, '', ''),
(6, 'Máy đọc sách reMarkable Paper Pro Move + Plus Pen', 17490000, 'img/products/remarkable/PaperProMove_1.png', 'Trắng', 1000, 'ƯU ĐÃI VÀ QUÀ TẶNG \r\n\r\n- Tặng kho sách đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\nAKISHOP CAM KẾT\r\n\r\n- Bảo hành 12 tháng \r\n\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu cho Khách hàng\r\n\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.\r\n\r\n- Mọi thắc mắc vui lòng liên hệ Hotline: 0856 87 88 89', 4, '2025-11-25 15:27:49', '2025-11-25 15:27:49', 0, '', ''),
(7, 'Máy đọc sách reMarkable 2', 13100000, 'img/products/remarkable/remarkable2.jpg', 'Trắng', 200, 'ƯU ĐÃI VÀ QUÀ TẶNG \r\n\r\n- Tặng kho sách đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\nAKISHOP CAM KẾT\r\n\r\n- Bảo hành 12 tháng \r\n\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu cho Khách hàng\r\n\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.\r\n\r\n- Mọi thắc mắc vui lòng liên hệ Hotline: 0856 87 88 89', 4, '2025-11-25 15:37:18', '2025-11-25 16:02:15', 100000, '', ''),
(8, 'Máy đọc sách Kobo Clara Colour 2024', 6490000, 'img/products/kobo/claraColor2024.jpg', 'Đen', 100, 'ƯU ĐÃI VÀ QUÀ TẶNG\r\n\r\n- Tặng kho sách đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\n\r\nAKISHOP CAM KẾT\r\n\r\n- Bảo hành 12 tháng \r\n\r\n- Cài đặt MIỄN PHÍ các bộ từ điển (Anh - Việt, Việt - Anh, Nhật - Việt, Việt - Nhật, Đức - Việt, Việt - Đức, Trung - Việt...)\r\n\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu cho Khách hàng\r\n\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.\r\n\r\n- Mọi thắc mắc vui lòng liên hệ Hotline: 0856 87 88 89', 5, '2025-11-25 15:37:18', '2025-11-25 15:34:25', 5490000, '', ''),
(9, 'Máy đọc sách reMarkable 2', 13100000, 'img/products/remarkable/remarkable2.jpg', 'Trắng', 200, 'ƯU ĐÃI VÀ QUÀ TẶNG \r\n\r\n- Tặng kho sách đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\nAKISHOP CAM KẾT\r\n\r\n- Bảo hành 12 tháng \r\n\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu cho Khách hàng\r\n\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.\r\n\r\n- Mọi thắc mắc vui lòng liên hệ Hotline: 0856 87 88 89', 4, '2025-11-25 15:37:25', '2025-11-25 15:32:14', 0, '', ''),
(10, 'Máy đọc sách Kobo Clara Colour 2024', 6490000, 'img/products/kobo/claraColor2024.jpg', 'Đen', 100, 'ƯU ĐÃI VÀ QUÀ TẶNG\r\n\r\n- Tặng kho sách đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\n\r\nAKISHOP CAM KẾT\r\n\r\n- Bảo hành 12 tháng \r\n\r\n- Cài đặt MIỄN PHÍ các bộ từ điển (Anh - Việt, Việt - Anh, Nhật - Việt, Việt - Nhật, Đức - Việt, Việt - Đức, Trung - Việt...)\r\n\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu cho Khách hàng\r\n\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.\r\n\r\n- Mọi thắc mắc vui lòng liên hệ Hotline: 0856 87 88 89', 5, '2025-11-25 15:37:25', '2025-11-25 15:34:25', 5490000, '', ''),
(11, 'Máy đọc sách Kobo Libra 2 Colour', 8500000, 'img/products/kobo/libra2colour.jpg', 'Đen', 180, 'Màn hình E Ink Kaleido 3, độ phân giải 300 ppi trắng đen và 150 ppi màu. Bộ nhớ trong 32GB Hỗ trợ Kobo Audiobooks USB Type C Hỗ trợ bút Kobo Stylus 2 (bán rời)\r\nƯU ĐÃI VÀ QUÀ TẶNG\r\n- Tặng kho sách đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\nAKISHOP CAM KẾT\r\n- Bảo hành 12 tháng \r\n- Cài đặt MIỄN PHÍ các bộ từ điển (Anh - Việt, Việt - Anh...)\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu cho Khách hàng\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.', 5, '2025-11-25 18:49:07', '2025-11-25 15:42:22', 7500000, '', ''),
(12, 'Máy đọc sách Kobo Sage', 9990000, 'img/products/kobo/kobosage.jpg', 'Đen', 100, 'KHUYẾN MÃI\r\n- Giảm giá tới 40% (giá trên website là giá đã giảm)\r\n- Giảm ngay 100k cho cover khi mua kèm máy (kindle là giảm 50k)\r\n\r\nHỗ trợ bút ghi chú (bán rời)\r\n\r\nCPU mới 4 nhân x 1.8GHz\r\nMàn hình E Ink mới Carta 1200, độ phân giải 300 ppi, có Dark Mode\r\nBộ nhớ trong 32GB\r\nChống nước IPX8\r\nĐèn nền 2 tông màu\r\nUSB Type C\r\nƯU ĐÃI VÀ QUÀ TẶNG\r\n\r\n- Tặng kho sách đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\nBẢO HÀNH VÀ HỖ TRỢ\r\n\r\n- Bảo hành 12 tháng \r\n\r\n- Hỗ trợ cài đặt font tiếng Việt, KoReader\r\n\r\n- Hỗ trợ cài đặt MIỄN PHÍ các bộ từ điển (Anh - Việt, Việt - Anh,...)\r\n\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu và gửi lại cho khách hàng \r\n\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.', 5, '2025-11-25 15:45:21', '2025-11-25 15:45:05', 8490000, '', ''),
(13, 'Kindle Scribe 2025 - 64Gb', 19590000, 'img/products/kindle/Scribe2025.png', 'Xám', 200, 'ƯU ĐÃI VÀ QUÀ TẶNG \r\n\r\n- Tặng kho sách đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\nAKISHOP CAM KẾT\r\n\r\n- Bảo hành 12 tháng \r\n\r\n- Cài đặt MIỄN PHÍ các bộ từ điển (Anh - Việt, Việt - Anh, Nhật - Việt, Việt - Nhật, Đức - Việt, Việt - Đức, Trung - Việt...)\r\n\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu cho Khách hàng\r\n\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.', 3, '2025-11-25 15:48:12', '2025-11-25 15:48:07', 0, '', ''),
(14, 'Máy đọc sách Kindle Paperwhite 6 16Gb', 6590000, 'img/products/kindle/Paperwhite6.jpg', 'Đen', 196, 'ƯU ĐÃI VÀ QUÀ TẶNG\r\n\r\n- Tặng kho sách đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\nAKISHOP CAM KẾT\r\n\r\n- Bảo hành 12 tháng \r\n\r\n- Cài đặt MIỄN PHÍ các bộ từ điển (Anh - Việt, Việt - Anh, Nhật - Việt, Việt - Nhật, Đức - Việt, Việt - Đức, Trung - Việt...)\r\n\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu cho Khách hàng\r\n\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.', 3, '2025-11-25 16:06:48', '2025-11-25 16:06:37', 5590000, '', ''),
(15, 'Máy đọc sách Boox Go 6', 5390000, 'img/products/boox/go6.jpg', 'Đen', 202, 'ƯU ĐÃI VÀ QUÀ TẶNG\r\n\r\n- Kho sách tích hợp sẵn trong máy, đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\nAKISHOP CAM KẾT\r\n\r\n- Bảo hành 12 tháng \r\n\r\n- Cài đặt MIỄN PHÍ các bộ từ điển (Anh - Việt, Việt - Anh, Nhật - Việt, Việt - Nhật, Đức - Việt, Việt - Đức, Trung - Việt...)\r\n\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu cho Khách hàng\r\n\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.', 1, '2025-11-25 16:52:29', '2025-11-25 16:46:27', 0, '', ''),
(16, 'Máy đọc sách Boox Go 7', 7790000, 'img/products/boox/Go7_2.jpg', 'Trắng', 187, 'ƯU ĐÃI VÀ QUÀ TẶNG\r\n- Kho sách tích hợp sẵn trong máy, đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\nAKISHOP CAM KẾT\r\n- Bảo hành 12 tháng \r\n\r\n- Cài đặt MIỄN PHÍ các bộ từ điển (Anh - Việt, Việt - Anh, Nhật - Việt, Việt - Nhật, Đức - Việt, Việt - Đức, Trung - Việt...)\r\n\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu cho Khách hàng\r\n\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.', 1, '2025-12-03 12:41:19', '2025-12-03 16:54:35', 0, '', ''),
(17, 'Máy đọc sách Boox Go 7 Color (Gen II)', 8990000, 'img/products/boox/Go7color(gen2).jpg', 'Đen', 100, 'ƯU ĐÃI VÀ QUÀ TẶNG\r\n- Kho sách tích hợp sẵn trong máy, đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\nAKISHOP CAM KẾT\r\n- Bảo hành 12 tháng \r\n\r\n- Cài đặt MIỄN PHÍ các bộ từ điển (Anh - Việt, Việt - Anh, Nhật - Việt, Việt - Nhật, Đức - Việt, Việt - Đức, Trung - Việt...)\r\n\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu cho Khách hàng\r\n\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.', 1, '2025-11-25 16:52:29', NULL, 0, '', ''),
(18, 'Máy đọc sách Boox Go 10.3', 13390000, 'img/products/boox/Go10.3.jpg', 'Trắng', 100, 'ƯU ĐÃI VÀ QUÀ TẶNG \r\n\r\n- Kho sách tích hợp sẵn trong máy, đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\nAKISHOP CAM KẾT\r\n\r\n- Bảo hành 12 tháng \r\n\r\n- Cài đặt MIỄN PHÍ các bộ từ điển (Anh - Việt, Việt - Anh, Nhật - Việt, Việt - Nhật, Đức - Việt, Việt - Đức, Trung - Việt...)\r\n\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu cho Khách hàng\r\n\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.', 1, '2025-11-25 16:52:29', NULL, 0, '', ''),
(19, 'Máy đọc sách Boox Tab Ultra C Pro', 19300000, 'img/products/boox/tabultraCpro.jpg', 'Đen', 199, 'ƯU ĐÃI VÀ QUÀ TẶNG\r\n\r\n- Kho sách tích hợp sẵn trong máy, đủ các thể loại: tiểu thuyết, văn học, kỹ năng sống, ngôn tình, kinh doanh...\r\n\r\n- Mua Ebook bản quyền với giá ưu đãi qua ứng dụng Savi\r\n\r\nAKISHOP CAM KẾT\r\n\r\n- Bảo hành 12 tháng \r\n\r\n- Cài đặt MIỄN PHÍ các bộ từ điển (Anh - Việt, Việt - Anh, Nhật - Việt, Việt - Nhật, Đức - Việt, Việt - Đức, Trung - Việt...)\r\n\r\n- Hỗ trợ tìm kiếm sách theo yêu cầu cho Khách hàng\r\n\r\n- Hỗ trợ kỹ thuật và sử dụng phần mềm trọn đời.', 1, '2025-11-25 16:52:29', NULL, 0, '', ''),
(20, 'Máy đọc sách test Index', 1000000, 'img/products/savi/bn1.png', 'Trắng', 100, '<p>Tại v&igrave; sao lại như v</p>', 2, '2025-12-08 14:19:48', '2025-12-08 14:19:48', 500000, '<p>fsadfdsấdfgsag&aacute;</p>', ''),
(21, 'Máy đọc sách test KM2', 1000000, 'img/products/savi/bn1_1.png', 'Trắng', 6, '<p>aaaa&acirc;</p>', 2, '2025-12-08 14:20:43', '2025-12-08 14:20:43', 500000, '<p>aaaaaaaaaaaa&acirc;</p>', ''),
(22, 'Máy test 3', 99999, 'img/products/savi/bn2.png', 'Màu đen', 2, '<p>số qu&aacute; đẹp</p>', 2, '2025-12-08 14:21:24', '2025-12-08 14:32:55', 0, '<p>đẹp lamws lu&ocirc;n</p>', '');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `san_pham_mau_sac`
--

CREATE TABLE `san_pham_mau_sac` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_sanpham` bigint(20) NOT NULL,
  `mau_sac_id` bigint(20) UNSIGNED NOT NULL,
  `so_luong` int(11) NOT NULL DEFAULT 0,
  `gia` double NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tb_transactions`
--

CREATE TABLE `tb_transactions` (
  `id` int(11) NOT NULL,
  `gateway` varchar(100) NOT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `account_number` varchar(100) DEFAULT NULL,
  `sub_account` varchar(250) DEFAULT NULL,
  `amount_in` decimal(20,2) NOT NULL DEFAULT 0.00,
  `amount_out` decimal(20,2) NOT NULL DEFAULT 0.00,
  `accumulated` decimal(20,2) NOT NULL DEFAULT 0.00,
  `code` varchar(250) DEFAULT NULL,
  `transaction_content` text DEFAULT NULL,
  `reference_number` varchar(255) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tb_transactions`
--

INSERT INTO `tb_transactions` (`id`, `gateway`, `transaction_date`, `account_number`, `sub_account`, `amount_in`, `amount_out`, `accumulated`, `code`, `transaction_content`, `reference_number`, `body`, `created_at`) VALUES
(1, 'MBBank', '2025-12-13 04:38:40', '0981523130', '', 16800.00, 0.00, 16800.00, 'IN', 'Thanh toan don hang DH0087', 'FT1765625920029', 'NGUYEN VAN A chuyen tien', '2025-12-13 11:38:46'),
(2, 'MBBank', '2025-12-13 04:40:43', '0981523130', '', 16800.00, 0.00, 16800.00, 'IN', 'Thanh toan don hang DH0087', 'FT1765626043192', 'NGUYEN VAN A chuyen tien', '2025-12-13 11:40:45'),
(3, 'MBBank', '2025-12-13 04:41:45', '0981523130', '', 16800.00, 0.00, 16800.00, 'IN', 'Thanh toan don hang DH0087', 'FT1765626105700', 'NGUYEN VAN A chuyen tien', '2025-12-13 11:41:47'),
(4, 'MBBank', '2025-12-13 04:38:40', '0981523130', '', 16800.00, 0.00, 16800.00, 'IN', 'Thanh toan don hang DH0087', 'FT1765625920029', 'NGUYEN VAN A chuyen tien', '2025-12-13 11:41:52'),
(5, 'MBBank', '2025-12-13 05:00:10', '0981523130', '', 16800.00, 0.00, 16800.00, 'IN', 'Thanh toan don hang DH0090', 'FT1765627210574', 'NGUYEN VAN A chuyen tien', '2025-12-13 12:00:12'),
(6, 'MBBank', '2025-12-13 05:55:49', '0981523130', '', 16800.00, 0.00, 16800.00, 'IN', 'Thanh toan don hang DH0092', 'FT1765630549744', 'NGUYEN VAN A chuyen tien', '2025-12-13 12:55:50'),
(7, 'MBBank', '2025-12-13 06:03:29', '0981523130', '', 16800.00, 0.00, 16800.00, 'IN', 'Thanh toan don hang DH0093', 'FT1765631009277', 'Lê Văn Túc chuyen tien', '2025-12-13 13:03:29'),
(8, 'MBBank', '2025-12-13 06:03:57', '0981523130', '', 16800.00, 0.00, 16800.00, 'IN', 'Thanh toan don hang DH0094', 'FT1765631037361', 'Lê Văn Túc chuyen tien', '2025-12-13 13:03:57'),
(9, 'MBBank', '2025-12-13 06:14:32', '0981523130', '', 16800.00, 0.00, 16800.00, 'IN', 'Thanh toan don hang DH0096', 'FT1765631672029', 'NGUYEN VAN A chuyen tien', '2025-12-13 13:14:32'),
(10, 'MBBank', '2025-12-13 13:42:00', '0981523130', NULL, 16800.00, 0.00, -2326112.00, 'DH0101', '110769432473-DH0101-CHUYEN TIEN-OQCH0004jpJa-MOMO110769432473MOMO', 'FT25347868627718', 'BankAPINotify 110769432473-DH0101-CHUYEN TIEN-OQCH0004jpJa-MOMO110769432473MOMO', '2025-12-13 13:42:42'),
(11, 'MBBank', '2025-12-13 13:37:00', '0981523130', NULL, 16800.00, 0.00, -2342912.00, 'DH0100', '110768814460-DH0100-CHUYEN TIEN-OQCH0004joDc-MOMO110768814460MOMO', 'FT25347759276206', 'BankAPINotify 110768814460-DH0100-CHUYEN TIEN-OQCH0004joDc-MOMO110768814460MOMO', '2025-12-13 13:44:27'),
(12, 'MBBank', '2025-12-13 13:33:00', '0981523130', NULL, 16800.00, 0.00, -2359712.00, 'DH0099', '110768169661-DH0099-CHUYEN TIEN-OQCH0004jnPx-MOMO110768169661MOMO', 'FT25347522660988', 'BankAPINotify 110768169661-DH0099-CHUYEN TIEN-OQCH0004jnPx-MOMO110768169661MOMO', '2025-12-13 13:45:31'),
(13, 'MBBank', '2025-12-13 13:49:00', '0981523130', NULL, 16200.00, 0.00, -2512912.00, 'DH0102', '110770039369 0981523130 DH0102   Ma giao dich  Trace783586 Trace 78358', 'FT25347550952600', 'BankAPINotify 110770039369 0981523130 DH0102   Ma giao dich  Trace783586 Trace 78358', '2025-12-13 13:49:25'),
(14, 'MBBank', '2025-12-13 14:10:00', '0981523130', NULL, 15600.00, 0.00, -2513912.00, 'DH0103', 'DH0103   Ma giao dich  Trace962534 Trace 962534', 'FT25347475200929', 'BankAPINotify DH0103   Ma giao dich  Trace962534 Trace 962534', '2025-12-13 13:59:23');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tin_tuc`
--

CREATE TABLE `tin_tuc` (
  `ma_tintuc` bigint(20) NOT NULL,
  `hinh_anh` varchar(250) DEFAULT NULL,
  `ngay_tao` date DEFAULT NULL,
  `nguoi_tao` varchar(250) DEFAULT NULL,
  `noi_dung` longtext DEFAULT NULL,
  `ma_user` bigint(20) NOT NULL,
  `tieu_de` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `update_at` timestamp NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tin_tuc`
--

INSERT INTO `tin_tuc` (`ma_tintuc`, `hinh_anh`, `ngay_tao`, `nguoi_tao`, `noi_dung`, `ma_user`, `tieu_de`, `created_at`, `update_at`) VALUES
(1, 'img/blog/bl2_1_1.png', '2025-11-30', 'Nhân viên', '<p><strong>Giới thiệu chung về tầm quan trọng của việc học luật Học luật mang đến nền tảng kiến thức vững chắc để bạn xử l&yacute; nhiều t&igrave;nh huống trong c&ocirc;ng việc v&agrave; cuộc sống.</strong></p>\r\n<p><img src=\"img/banner/banner_5.png\" alt=\"\" width=\"600\" height=\"399\"></p>\r\n<p>V&igrave; sao kỹ năng ph&aacute;p l&yacute; ng&agrave;y c&agrave;ng quan trọng? Trong thời đại hiện nay, hầu như mọi lĩnh vực đều li&ecirc;n quan đến ph&aacute;p luật: kinh doanh, hợp đồng, sở hữu tr&iacute; tuệ, lao động, thuế&hellip; Nắm được luật gi&uacute;p bạn tự tin hơn, hạn chế rủi ro v&agrave; đưa ra quyết định ch&iacute;nh x&aacute;c. Những đối tượng cần nắm vững phương ph&aacute;p học luật - Sinh vi&ecirc;n ng&agrave;nh luật - Nh&acirc;n vi&ecirc;n ph&aacute;p chế, h&agrave;nh ch&iacute;nh &ndash; nh&acirc;n sự - Người kinh doanh - C&aacute; nh&acirc;n muốn hiểu biết luật để bảo vệ quyền lợi bản th&acirc;n Những kh&oacute; khăn phổ biến khi học luật Việc học luật kh&ocirc;ng phải l&uacute;c n&agrave;o cũng dễ d&agrave;ng. Khối lượng kiến thức lớn, ng&ocirc;n ngữ ph&aacute;p l&yacute; phức tạp v&agrave; y&ecirc;u cầu tư duy logic khiến nhiều người cho&aacute;ng ngợp. Nhưng nếu nắm được những kh&oacute; khăn n&agrave;y, bạn sẽ dễ d&agrave;ng t&igrave;m ra hướng học ph&ugrave; hợp. Lượng kiến thức lớn v&agrave; kh&oacute; nhớ Ph&aacute;p luật thay đổi li&ecirc;n tục, nhiều văn bản v&agrave; lĩnh vực kh&aacute;c nhau khiến người học cho&aacute;ng ngợp nếu kh&ocirc;ng c&oacute; phương ph&aacute;p học đ&uacute;ng. Ng&ocirc;n ngữ ph&aacute;p l&yacute; phức tạp C&aacute;c thuật ngữ ph&aacute;p l&yacute; thường kh&ocirc; khan, mang t&iacute;nh ch&iacute;nh x&aacute;c cao n&ecirc;n dễ g&acirc;y kh&oacute; hiểu với người mới. Kh&oacute; &aacute;p dụng v&agrave;o thực tế nếu chỉ học l&yacute; thuyết Hiểu điều luật nhưng kh&ocirc;ng biết vận dụng v&agrave;o t&igrave;nh huống thực tế l&agrave; vấn đề phổ biến. Phương ph&aacute;p học luật hiệu quả &ndash; Tổng hợp c&aacute;c bước thực chiến Để chinh phục ng&agrave;nh luật, bạn cần một chiến lược r&otilde; r&agrave;ng, logic v&agrave; bền vững. Dưới đ&acirc;y l&agrave; c&aacute;c phương ph&aacute;p học luật hiệu quả, gi&uacute;p bạn tiến bộ nhanh ch&oacute;ng.</p>\r\n<p>https://www.youtube.com/watch?v=q6mCBqJmWCk</p>', 0, 'Phương pháp học luật – Bí quyết nâng cao hiệu quả học tập cho sinh viên và người đi làm27/11/2025 11:25', '2025-12-02 14:50:36', '2025-12-02 14:50:36'),
(2, 'img/blog/bl2_1.png', '2025-12-01', 'Nhân viên', '<p>ONYX c&oacute; lẽ l&agrave; c&ocirc;ng ty năng động thuộc loại bậc nhất trong c&aacute;c c&ocirc;ng ty sản xuất&nbsp;<a href=\"https://akishop.com.vn/\">m&aacute;y đọc s&aacute;ch</a>&nbsp;sử dụng e-ink hay c&ograve;n gọi l&agrave;&nbsp;giấy điện tử, họ c&oacute; một loạt sản phẩm với nhiều k&iacute;ch cỡ m&agrave;n h&igrave;nh v&agrave; c&ocirc;ng dụng kh&aacute;c nhau, trải d&agrave;i từ 6 inch đến tận 13.3 inch cũng c&oacute; lu&ocirc;n.&nbsp;<a href=\"https://akishop.com.vn/may-doc-sach-boox-nova-air-pd174000.html\">BOOX Nova Air</a> l&agrave; sản phẩm mới nhất trong ph&acirc;n kh&uacute;c 7.8 inch.</p>\r\n<p><iframe src=\"https://www.youtube.com/embed/Va0j310OJg4\" width=\"560\" height=\"314\" allowfullscreen=\"allowfullscreen\"></iframe></p>', 38, 'Trên tay Boox Nova Air: máy đọc sách kiêm sổ tay điện tử', '2025-12-02 14:50:12', '2025-12-02 14:50:12'),
(3, 'img/blog/bn_bl3.png', '2025-12-01', 'Nhân viên', '<p dir=\"ltr\">Kobo l&agrave; một trong những thương hiệu m&aacute;y đọc s&aacute;ch điện tử lớn nhất tr&ecirc;n thế giới, cạnh tranh trực tiếp với Amazon Kindle. Với thiết kế hiện đại, hỗ trợ nhiều định dạng s&aacute;ch v&agrave; giao diện th&acirc;n thiện, c&aacute;c d&ograve;ng m&aacute;y đọc s&aacute;ch Kobo ng&agrave;y c&agrave;ng được người d&ugrave;ng Việt Nam ưa chuộng. Trong b&agrave;i viết n&agrave;y, ch&uacute;ng ta sẽ c&ugrave;ng kh&aacute;m ph&aacute; chi tiết v&agrave; so s&aacute;nh c&aacute;c mẫu m&aacute;y Kobo mới nhất năm 2024 để t&igrave;m ra chiếc m&aacute;y ph&ugrave; hợp nhất với bạn.</p>\r\n<h2 dir=\"ltr\"><strong>L&yacute; do chọn m&aacute;y đọc s&aacute;ch&nbsp;Kobo&nbsp;</strong></h2>\r\n<p dir=\"ltr\">Việc chọn<a href=\"https://akishop.com.vn/may-doc-sach-dien-tu-la-gi-kham-pha-nhung-cong-dung-tuyet-voi-ban-chua-biet-nd313567.html\">&nbsp;m&aacute;y đọc s&aacute;ch</a> phụ thuộc nhiều v&agrave;o nhu cầu c&aacute; nh&acirc;n, nhưng Kobo thường l&agrave; lựa chọn h&agrave;ng đầu cho những ai y&ecirc;u th&iacute;ch sự linh hoạt v&agrave; tự do. Kobo c&oacute; giao diện dễ sử dụng v&agrave; được trang bị c&aacute;c t&iacute;nh năng bảo vệ mắt vượt trội. Đặc biệt, người d&ugrave;ng c&oacute; thể dễ d&agrave;ng tải s&aacute;ch từ nhiều nguồn kh&aacute;c nhau m&agrave; kh&ocirc;ng cần chuyển đổi phức tạp.</p>\r\n<p dir=\"ltr\">&nbsp;</p>', 38, 'Các Dòng Máy Đọc Sách Kobo: Đánh Giá & So Sánh Chi Tiết', '2025-12-02 14:50:50', '2025-12-02 14:50:50'),
(5, 'img/blog/news_1764527454.png', '2025-12-01', 'túc', '<p>aaaaa</p>', 38, 'test', '2025-11-30 18:30:54', '0000-00-00 00:00:00'),
(6, 'img/blog/BG_1.png', '2025-12-02', 'túc', '<p>test 11111 ---55555</p>', 38, 'teest 1', '2025-12-02 14:41:37', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `transactions`
--

CREATE TABLE `transactions` (
  `id` int(10) NOT NULL,
  `ma_donhang` bigint(20) NOT NULL,
  `account_number` varchar(20) NOT NULL,
  `amount_in` decimal(10,0) NOT NULL,
  `transaction_content` text NOT NULL,
  `bank_brand_name` varchar(50) NOT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_processed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `account_name` varchar(70) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `transactions`
--

INSERT INTO `transactions` (`id`, `ma_donhang`, `account_number`, `amount_in`, `transaction_content`, `bank_brand_name`, `transaction_date`, `is_processed`, `created_at`, `account_name`) VALUES
(1, 24, '0387888199', 26775000, 'Thanh Toan Don Hang 0024', 'SePay QR', '2025-12-06 16:30:19', 0, '2025-12-06 16:30:19', ''),
(2, 25, '0387888199', 26775000, 'Thanh Toan Don Hang 0025', 'SePay QR', '2025-12-06 16:31:07', 0, '2025-12-06 16:31:07', ''),
(3, 26, '0387888199', 15010, 'Thanh Toan Don Hang 0026', 'SePay QR', '2025-12-06 16:34:56', 0, '2025-12-06 16:34:56', ''),
(4, 27, '0387888199', 25000, 'Thanh Toan Don Hang 0027', 'SePay QR', '2025-12-06 16:39:18', 0, '2025-12-06 16:39:18', ''),
(5, 28, '0981523130', 25000, 'Thanh Toan Don Hang 0028', 'SePay QR', '2025-12-06 16:45:36', 0, '2025-12-06 16:45:36', ''),
(6, 29, '0981523130', 25000, 'Thanh Toan Don Hang 0029', 'SePay QR', '2025-12-06 16:50:48', 0, '2025-12-06 16:50:48', ''),
(7, 30, '0981523130', 25000, 'Thanh Toan Don Hang 0030', 'SePay QR', '2025-12-06 16:56:57', 0, '2025-12-06 16:56:57', ''),
(8, 31, '0981523130', 25000, 'Thanh Toan Don Hang 0031', 'SePay QR', '2025-12-06 17:08:07', 0, '2025-12-06 17:08:07', ''),
(9, 32, '0981523130', 25000, 'Thanh Toan Don Hang 0032', 'SePay QR', '2025-12-06 17:11:50', 0, '2025-12-06 17:11:50', ''),
(10, 34, '0981523130', 25000, 'Thanh Toan Don Hang 0034', 'SePay QR', '2025-12-06 17:12:09', 0, '2025-12-06 17:12:09', ''),
(11, 35, '0981523130', 25000, 'Thanh Toan Don Hang 2124', 'MBBank', '2025-12-06 17:33:20', 0, '2025-12-06 17:33:20', ''),
(12, 36, '0981523130', 25000, 'Thanh Toan Don Hang 2472', 'MBBank', '2025-12-06 17:35:26', 0, '2025-12-06 17:35:26', ''),
(13, 37, '0981523130', 25000, 'Thanh Toan Don Hang 7135', 'MBBank', '2025-12-06 17:41:11', 0, '2025-12-06 17:41:11', ''),
(14, 38, '0981523130', 25000, 'Thanh Toan Don Hang 5623', 'MBBank', '2025-12-06 17:43:16', 0, '2025-12-06 17:43:16', ''),
(15, 39, '0981523130', 25000, 'Thanh Toan Don Hang 8803', 'MBBank', '2025-12-06 17:54:49', 0, '2025-12-06 17:54:49', ''),
(16, 40, '0981523130', 25000, 'Thanh Toan Don Hang 8626', 'MBBank', '2025-12-06 17:55:26', 0, '2025-12-06 17:55:26', ''),
(17, 41, '0981523130', 25000, 'Thanh Toan Don Hang 3312', 'MBBank', '2025-12-06 18:02:58', 0, '2025-12-06 18:02:58', ''),
(18, 42, '0981523130', 25000, 'Thanh Toan Don Hang 4302', 'MBBank', '2025-12-06 18:08:27', 0, '2025-12-06 18:08:27', ''),
(19, 46, '0981523130', 0, 'Thanh Toan Don Hang 4378', '', '2025-12-06 18:33:58', 0, '2025-12-06 18:33:58', ''),
(20, 49, '0981523130', 0, 'Thanh Toan Don Hang 3919', '', '2025-12-07 03:07:29', 0, '2025-12-07 03:07:29', ''),
(21, 50, '0981523130', 0, 'Thanh Toan Don Hang 8976', '', '2025-12-07 03:14:17', 0, '2025-12-07 03:14:17', ''),
(22, 51, '0981523130', 0, 'DH1805', '', '2025-12-07 03:30:37', 0, '2025-12-07 03:30:37', ''),
(23, 52, '0981523130', 0, 'DH1550', '', '2025-12-07 03:46:13', 0, '2025-12-07 03:46:13', ''),
(24, 53, '0981523130', 0, 'DH1049', '', '2025-12-07 03:48:36', 0, '2025-12-07 03:48:36', ''),
(25, 58, '0981523130', 0, 'DH5819', '', '2025-12-08 08:42:48', 0, '2025-12-08 08:42:48', ''),
(26, 61, '0981523130', 0, 'DH1279', '', '2025-12-08 15:49:33', 0, '2025-12-08 15:49:33', ''),
(27, 62, '0981523130', 0, 'DH1961', '', '2025-12-08 15:51:11', 0, '2025-12-08 15:51:11', ''),
(28, 63, '0981523130', 0, 'DH2970', '', '2025-12-08 15:54:06', 0, '2025-12-08 15:54:06', ''),
(29, 64, '0981523130', 0, 'DH9399', '', '2025-12-08 15:56:41', 0, '2025-12-08 15:56:41', ''),
(30, 65, '0981523130', 24000, 'DH8074', '', '2025-12-08 16:16:08', 1, '2025-12-08 16:02:48', ''),
(31, 66, '0981523130', 0, 'DH9826', '', '2025-12-08 16:31:36', 0, '2025-12-08 16:31:36', ''),
(32, 67, '0981523130', 0, 'DH3979', '', '2025-12-08 16:40:01', 0, '2025-12-08 16:40:01', ''),
(33, 68, '0981523130', 0, 'DH4140', '', '2025-12-08 16:42:07', 0, '2025-12-08 16:42:07', ''),
(34, 69, '0981523130', 0, 'DH8806', '', '2025-12-08 16:55:25', 0, '2025-12-08 16:55:25', ''),
(35, 70, '0981523130', 0, 'DH6187', '', '2025-12-08 16:56:38', 0, '2025-12-08 16:56:38', ''),
(36, 74, '0981523130', 0, 'DH2983', '', '2025-12-09 14:21:57', 0, '2025-12-09 14:21:57', ''),
(37, 75, '0981523130', 0, 'DH5129', '', '2025-12-09 14:26:18', 0, '2025-12-09 14:26:18', ''),
(38, 77, '0981523130', 0, 'DH9623', '', '2025-12-09 14:36:57', 0, '2025-12-09 14:36:57', ''),
(39, 78, '0981523130', 0, 'DH9324', '', '2025-12-09 14:49:28', 0, '2025-12-09 14:49:28', '');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user`
--

CREATE TABLE `user` (
  `ma_user` bigint(20) NOT NULL,
  `ho_ten` varchar(255) DEFAULT NULL,
  `email` varchar(250) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `dia_chi` varchar(255) DEFAULT NULL,
  `vai_tro` enum('quanly','nhanvien','khachhang','nhanvienkho') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `update_at` timestamp NULL DEFAULT NULL,
  `ma_vaitro` bigint(20) DEFAULT NULL,
  `trang_thai` enum('active','locked') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `user`
--

INSERT INTO `user` (`ma_user`, `ho_ten`, `email`, `password`, `phone`, `dia_chi`, `vai_tro`, `created_at`, `update_at`, `ma_vaitro`, `trang_thai`) VALUES
(13, 'Huỳnh Bá A', 'chieu2k2@gmail.com', '9cbf8a4dcb8e30682b927f352d6559a0', '1234567890', '11111', 'khachhang', '2025-11-08 06:40:52', NULL, 0, 'active'),
(18, 'Nguyễn Thị A', 'chieu3@gmail.com', '9cbf8a4dcb8e30682b927f352d6559a0', '1234567899', 'qqqqq', 'khachhang', '2025-11-08 08:04:12', NULL, 0, 'active'),
(25, 'Huỳnh Bá A', 'dinhchieu2k3@gmail.com', '9cbf8a4dcb8e30682b927f352d6559a0', '1234567890', '11111111111', 'nhanvienkho', '2025-11-15 03:51:44', '2025-11-15 04:01:08', 3, 'active'),
(26, 'Nguyễn Thị A', 'chieu123@gmail.com', '9cbf8a4dcb8e30682b927f352d6559a0', '1234567890', 'eqweqweqwe', 'quanly', '2025-11-15 03:57:04', NULL, 1, 'active'),
(27, 'Huỳnh Bá An', 'dinh@gmail.com', '9cbf8a4dcb8e30682b927f352d6559a0', '9999999999', 'qqqqq', 'quanly', '2025-11-15 03:58:10', '2025-11-15 03:58:47', 1, 'active'),
(29, 'Huỳnh Bá An', 'chieu21@gmail.com', 'de88e3e4ab202d87754078cbb2df6063', '9999999999', 'qqqqq', 'khachhang', '2025-11-15 04:12:10', '2025-11-15 04:22:26', 0, 'active'),
(33, 'Huỳnh B', 'chieu21@gmail.com', 'de88e3e4ab202d87754078cbb2df6063', '1234567890', 'aaaaaaaaaa', 'nhanvienkho', '2025-11-15 05:19:45', '2025-11-15 05:20:16', 3, 'active'),
(34, 'Lê Văn Túc', 'leduc2103@gmail.com', '8dc58084323d9a48d6a84dd5681f44a0', '0981523030', '1 ABC, Lý thường kiệt , TPHCM', 'quanly', '2025-11-16 01:03:02', '2025-10-14 11:44:55', 1, 'active'),
(35, 'Bảo ngọc', 'ngocle@gmail.com', '25d55ad283aa400af464c76d713c07ad', '0708216837', '43 vườn lài, q12', 'khachhang', '2025-11-26 06:48:36', NULL, NULL, 'active'),
(36, 'Phạm trang', 'ngocle1@gmail.com', '8ae55a27db51f5df4ad2971216fda63d', '0999999999', '152 nam kỳ, quận 3', 'khachhang', '2025-11-26 06:53:15', NULL, NULL, 'active'),
(37, 'Staff', 'staff@gmail.com', '830b400604e70a745d3b7b95a7cca5ba', '0702312817', 'TP.HCM', 'nhanvienkho', '2025-11-27 16:01:13', NULL, NULL, 'active'),
(38, 'bảo ngọc', 'ngoctuc@gmail.com', '830b400604e70a745d3b7b95a7cca5ba', '0702312817', '43 vườn lài quận 12', 'nhanvien', '2025-11-30 14:24:09', NULL, 2, 'active'),
(39, 'Lê Nguyễn Bảo Ngọc', '95.levantuc.toky@gmail.com', '5704f55d3f505f1edb9d192fcd5e2dc9', '0981523130', '100 ngõ láng, hà nội', 'khachhang', '2025-11-30 10:30:19', NULL, NULL, 'active'),
(40, 'Phạm Băng', 'phambang@gmail.com', '8ae55a27db51f5df4ad2971216fda63d', '0777777777', '12 nguyễn văn nghi, gò vấp', 'khachhang', '2025-12-04 03:23:05', NULL, NULL, 'active'),
(41, 'Nguyễn Thị Duyên', 'duyen@gmail.com', 'c465e6e6ca4b1ed4df24c276b6713950', '0369217145', '123 An Dương Vương', 'khachhang', '2025-12-08 16:24:49', NULL, NULL, 'active');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `vai_tro`
--

CREATE TABLE `vai_tro` (
  `ma_vaitro` bigint(20) NOT NULL,
  `ten_vaitro` varchar(255) NOT NULL,
  `mota_vaitro` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `vai_tro`
--

INSERT INTO `vai_tro` (`ma_vaitro`, `ten_vaitro`, `mota_vaitro`) VALUES
(0, 'Khách hàng', 'Khách mua hàng online'),
(1, 'Quản lý', 'Quản lý website'),
(2, 'Nhân viên', 'Thực hiện bán hàng'),
(3, 'Nhân viên kho', 'Quản lý việc xuất kho');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `xuat_kho`
--

CREATE TABLE `xuat_kho` (
  `id_xuat` bigint(20) NOT NULL,
  `ma_donhang` bigint(20) NOT NULL,
  `id_sanpham` bigint(20) NOT NULL,
  `so_luong` int(11) NOT NULL,
  `ngay_xuat` timestamp NOT NULL DEFAULT current_timestamp(),
  `ma_user` bigint(20) NOT NULL,
  `ngay_nhap` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`ma_banner`),
  ADD KEY `fk_banners_san_pham` (`id_sanpham`);

--
-- Chỉ mục cho bảng `chitiet_donhang`
--
ALTER TABLE `chitiet_donhang`
  ADD PRIMARY KEY (`id_ctdh`),
  ADD KEY `ma_donhang` (`ma_donhang`),
  ADD KEY `id_sanpham` (`id_sanpham`);

--
-- Chỉ mục cho bảng `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id_danh_gia`),
  ADD KEY `id_sanpham` (`id_sanpham`),
  ADD KEY `ma_user` (`ma_user`);

--
-- Chỉ mục cho bảng `danh_gia`
--
ALTER TABLE `danh_gia`
  ADD PRIMARY KEY (`id_danhgia`),
  ADD KEY `fk_danh_gia_san_pham` (`id_sanpham`),
  ADD KEY `fk_danh_gia_khach_hang` (`ma_user`);

--
-- Chỉ mục cho bảng `danh_muc`
--
ALTER TABLE `danh_muc`
  ADD PRIMARY KEY (`id_danhmuc`);

--
-- Chỉ mục cho bảng `don_hang`
--
ALTER TABLE `don_hang`
  ADD PRIMARY KEY (`ma_donhang`),
  ADD KEY `fk_don_hang_khach_hang` (`ma_user`);

--
-- Chỉ mục cho bảng `don_hang_doi_tra`
--
ALTER TABLE `don_hang_doi_tra`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_return_user` (`ma_user`);

--
-- Chỉ mục cho bảng `gio_hang`
--
ALTER TABLE `gio_hang`
  ADD PRIMARY KEY (`id_giohang`),
  ADD KEY `fk_gio_hang_san_pham` (`id_sanpham`);

--
-- Chỉ mục cho bảng `hoan_tien`
--
ALTER TABLE `hoan_tien`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `hotro`
--
ALTER TABLE `hotro`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `khuyen_mai`
--
ALTER TABLE `khuyen_mai`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `mau_sac`
--
ALTER TABLE `mau_sac`
  ADD PRIMARY KEY (`ma_mau`);

--
-- Chỉ mục cho bảng `san_pham`
--
ALTER TABLE `san_pham`
  ADD PRIMARY KEY (`id_sanpham`),
  ADD KEY `fk_san_pham_danh_muc` (`id_danhmuc`);

--
-- Chỉ mục cho bảng `san_pham_mau_sac`
--
ALTER TABLE `san_pham_mau_sac`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_sanpham` (`id_sanpham`),
  ADD UNIQUE KEY `mau_sac_id_2` (`mau_sac_id`),
  ADD KEY `san_pham_id` (`id_sanpham`),
  ADD KEY `mau_sac_id` (`mau_sac_id`);

--
-- Chỉ mục cho bảng `tb_transactions`
--
ALTER TABLE `tb_transactions`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `tin_tuc`
--
ALTER TABLE `tin_tuc`
  ADD PRIMARY KEY (`ma_tintuc`),
  ADD KEY `fk_tin_tuc_nhan_vien` (`ma_user`);

--
-- Chỉ mục cho bảng `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`ma_user`),
  ADD KEY `fk_nhan_vien_vai_tro` (`ma_vaitro`);

--
-- Chỉ mục cho bảng `vai_tro`
--
ALTER TABLE `vai_tro`
  ADD PRIMARY KEY (`ma_vaitro`);

--
-- Chỉ mục cho bảng `xuat_kho`
--
ALTER TABLE `xuat_kho`
  ADD PRIMARY KEY (`id_xuat`),
  ADD KEY `fk_xuat_kho_donhang` (`ma_donhang`),
  ADD KEY `fk_xuat_kho_sanpham` (`id_sanpham`),
  ADD KEY `fk_xuat_kho_nhanvien` (`ma_user`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `banners`
--
ALTER TABLE `banners`
  MODIFY `ma_banner` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `chitiet_donhang`
--
ALTER TABLE `chitiet_donhang`
  MODIFY `id_ctdh` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT cho bảng `comments`
--
ALTER TABLE `comments`
  MODIFY `id_danh_gia` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `danh_gia`
--
ALTER TABLE `danh_gia`
  MODIFY `id_danhgia` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `danh_muc`
--
ALTER TABLE `danh_muc`
  MODIFY `id_danhmuc` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `don_hang`
--
ALTER TABLE `don_hang`
  MODIFY `ma_donhang` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT cho bảng `don_hang_doi_tra`
--
ALTER TABLE `don_hang_doi_tra`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `gio_hang`
--
ALTER TABLE `gio_hang`
  MODIFY `id_giohang` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT cho bảng `hoan_tien`
--
ALTER TABLE `hoan_tien`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `hotro`
--
ALTER TABLE `hotro`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `khuyen_mai`
--
ALTER TABLE `khuyen_mai`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `mau_sac`
--
ALTER TABLE `mau_sac`
  MODIFY `ma_mau` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `san_pham`
--
ALTER TABLE `san_pham`
  MODIFY `id_sanpham` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT cho bảng `san_pham_mau_sac`
--
ALTER TABLE `san_pham_mau_sac`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `tb_transactions`
--
ALTER TABLE `tb_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `tin_tuc`
--
ALTER TABLE `tin_tuc`
  MODIFY `ma_tintuc` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT cho bảng `user`
--
ALTER TABLE `user`
  MODIFY `ma_user` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT cho bảng `vai_tro`
--
ALTER TABLE `vai_tro`
  MODIFY `ma_vaitro` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `xuat_kho`
--
ALTER TABLE `xuat_kho`
  MODIFY `id_xuat` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `chitiet_donhang`
--
ALTER TABLE `chitiet_donhang`
  ADD CONSTRAINT `chitiet_donhang_ibfk_1` FOREIGN KEY (`ma_donhang`) REFERENCES `don_hang` (`ma_donhang`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `chitiet_donhang_ibfk_2` FOREIGN KEY (`id_sanpham`) REFERENCES `san_pham` (`id_sanpham`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `san_pham`
--
ALTER TABLE `san_pham`
  ADD CONSTRAINT `fk_san_pham_danh_muc` FOREIGN KEY (`id_danhmuc`) REFERENCES `danh_muc` (`id_danhmuc`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `san_pham_mau_sac`
--
ALTER TABLE `san_pham_mau_sac`
  ADD CONSTRAINT `san_pham_mau_sac_ibfk_2` FOREIGN KEY (`id_sanpham`) REFERENCES `san_pham` (`id_sanpham`) ON DELETE CASCADE,
  ADD CONSTRAINT `san_pham_mau_sac_ibfk_3` FOREIGN KEY (`mau_sac_id`) REFERENCES `mau_sac` (`ma_mau`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `fk_nhan_vien_vai_tro` FOREIGN KEY (`ma_vaitro`) REFERENCES `vai_tro` (`ma_vaitro`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `xuat_kho`
--
ALTER TABLE `xuat_kho`
  ADD CONSTRAINT `fk_xuat_kho_donhang` FOREIGN KEY (`ma_donhang`) REFERENCES `don_hang` (`ma_donhang`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_xuat_kho_sanpham` FOREIGN KEY (`id_sanpham`) REFERENCES `san_pham` (`id_sanpham`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `xuat_kho_ibfk_1` FOREIGN KEY (`ma_user`) REFERENCES `user` (`ma_user`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
