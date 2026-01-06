--  =====================================================
-- HỆ THỐNG QUẢN LÝ CÂY CẢNH VĂN PHÒNG
-- Tác giả: Đặng Cao Toàn - MSSV: 110123187
-- Cập nhật: 32 loại cây thực tế với phân loại đúng
-- =====================================================

-- 1. Tạo Cơ sở dữ liệu
CREATE DATABASE IF NOT EXISTS quanly_caycanh CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quanly_caycanh;

-- 2. Bảng Categories (Phân loại)
CREATE TABLE Categories (
    CategoryID INT PRIMARY KEY AUTO_INCREMENT,
    CategoryName VARCHAR(100) NOT NULL UNIQUE
);

-- 3. Bảng Tags (Từ khóa đặc điểm)
CREATE TABLE Tags (
    TagID INT PRIMARY KEY AUTO_INCREMENT,
    TagName VARCHAR(60) NOT NULL UNIQUE
);

-- 4. Bảng Users (Quản trị viên/Người dùng)
CREATE TABLE Users (
    UserID INT PRIMARY KEY AUTO_INCREMENT,
    FullName VARCHAR(100) NOT NULL,
    Email VARCHAR(120) NOT NULL UNIQUE,
    PasswordHash VARCHAR(255) NOT NULL,
    Role ENUM('admin', 'editor', 'viewer') DEFAULT 'editor'
);

-- 5. Bảng Plants (Cây cảnh) - Bỏ CategoryID vì sẽ dùng bảng liên kết
CREATE TABLE Plants (
    PlantID INT PRIMARY KEY AUTO_INCREMENT,
    PlantName VARCHAR(100) NOT NULL,
    Summary TEXT NULL,
    imageURL VARCHAR(255) NULL
);

-- 6. Bảng liên kết Plant_Categories (Quan hệ nhiều-nhiều giữa cây và danh mục)
CREATE TABLE Plant_Categories (
    PlantID INT,
    CategoryID INT,
    PRIMARY KEY (PlantID, CategoryID),
    FOREIGN KEY (PlantID) REFERENCES Plants(PlantID) ON DELETE CASCADE,
    FOREIGN KEY (CategoryID) REFERENCES Categories(CategoryID) ON DELETE CASCADE
);

-- 7. Bảng Care_Items (Hướng dẫn chăm sóc)
CREATE TABLE Care_Items (
    ItemID INT PRIMARY KEY AUTO_INCREMENT,
    PlantID INT NOT NULL,
    Topic ENUM('watering', 'light', 'soil', 'temperature', 'humidity', 'fertilizing', 'pests', 'tips') NOT NULL,
    Content TEXT,
    FOREIGN KEY (PlantID) REFERENCES Plants(PlantID) ON DELETE CASCADE
);

-- 8. Bảng liên kết Plants_Tags (Quan hệ nhiều-nhiều giữa cây và tag)
CREATE TABLE Plant_Tags (
    PlantID INT,
    TagID INT,
    PRIMARY KEY (PlantID, TagID),
    FOREIGN KEY (PlantID) REFERENCES Plants(PlantID) ON DELETE CASCADE,
    FOREIGN KEY (TagID) REFERENCES Tags(TagID) ON DELETE CASCADE
);

-- =====================================================
-- CHÈN DỮ LIỆU THỰC TẾ
-- =====================================================

-- Thêm Categories (4 danh mục chính)
INSERT INTO Categories (CategoryName) VALUES
('Cây dễ chăm sóc'),
('Cây phong thủy'),
('Cây lọc không khí'),
('Cây có màu sắc');

-- Thêm Tags (các tag gợi ý)
INSERT INTO Tags (TagName) VALUES
('Giá rẻ'),
('Làm quà tặng'),
('Có hoa'),
('Trồng thủy sinh');

-- Thêm Users
INSERT INTO Users (FullName, Email, PasswordHash, Role) VALUES
('Đặng Cao Toàn', 'admin@caycanh.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Quản trị viên', 'editor@caycanh.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'editor');

-- Thêm Plants (32 cây theo tên đã sửa)
INSERT INTO Plants (PlantName, imageURL) VALUES
('Cây Kim Tiền', 'caykimtien.jpg'),
('Cây Lưỡi Hổ', 'cayluoiho.jpg'),
('Cây Lưỡi Mèo', 'cayluoimeo.jpg'),
('Cây Ngũ Gia Bì', 'cayngugiabi.jpg'),
('Cây Như Ý', 'caynhuy.jpg'),
('Cây Ráng Tổ Phụng', 'cayrangtophung.jpg'),
('Cây Sao Sáng', 'caysaosang.jpg'),
('Cây Thanh Lam', 'caythanhlam.jpg'),
('Cây Thông Mini', 'caythong.jpg'),
('Cây Thường Xuân', 'caythuongxuan.jpg'),
('Cây Trầu Bà Cẩm Thạch', 'caytraubacamthach.jpg'),
('Cây Trầu Bà Đế Vương', 'caytraubadevuong.jpg'),
('Cây Trầu Bà Tỷ Phú', 'caytraubatyphu.jpg'),
('Cây Trúc Nhật Vàng', 'caytrucnhatvang.jpg'),
('Cây Trường Sinh', 'caytruongsinh.jpg'),
('Cây Tùng Bồng Lai', 'caytung.jpg'),
('Cây Tùng Thơm', 'caytungthom.jpg'),
('Cây Tuyết Tùng', 'caytuyettung.jpg'),
('Cây Xương Đuôi Cá', 'cayxuongduoica.jpg'),
('Cây Bàng', 'caybang.jpg'),
('Cây Bao Thanh Thiên', 'caybaothanhthien.jpg'),
('Cây Cọ Lá Xẻ', 'cayco.jpg'),
('Cây Cung Điện Vàng', 'caycungdienvang.jpg'),
('Cây Dứa Hồng Phụng', 'cayduahongphung.jpg'),
('Cây Đa Búp Đỏ', 'caydabupdo.jpg'),
('Cây Đuôi Công', 'cayduoicong.jpg'),
('Cây Giữ Tiền', 'caygiutien.jpg'),
('Cây Hồng Môn', 'cayhongmon.jpg'),
('Cây Huyết Phật Dụ', 'cayhuyetphatdu.jpg'),
('Cây Kim Giao', 'caykimgiao.jpg'),
('Cây Kim Ngân', 'caykimngan.jpg'),
('Cây Cọ', 'cayco.jpg');

-- Liên kết Plants với Categories (quan hệ nhiều-nhiều)
INSERT INTO Plant_Categories (PlantID, CategoryID) VALUES
-- Cây dễ chăm sóc (CategoryID = 1)
(2, 1), -- Cây Lưỡi Hổ
(3, 1), -- Cây Lưỡi Mèo
(1, 1), -- Cây Kim Tiền
(4, 1), -- Cây Ngũ Gia Bì
(12, 1), -- Cây Trầu Bà Đế Vương
(22, 1), -- Cây Cọ Lá Xẻ
(16, 1), -- Cây Tùng Bồng Lai
(17, 1), -- Cây Tùng Thơm
(15, 1), -- Cây Trường Sinh

-- Cây phong thủy (CategoryID = 2)
(1, 2), -- Cây Kim Tiền
(6, 2), -- Cây Ráng Tổ Phụng
(9, 2), -- Cây Thông Mini
(27, 2), -- Cây Giữ Tiền
(25, 2), -- Cây Đa Búp Đỏ
(16, 2), -- Cây Tùng Bồng Lai
(28, 2), -- Cây Hồng Môn

-- Cây lọc không khí (CategoryID = 3)
(2, 3), -- Cây Lưỡi Hổ
(4, 3), -- Cây Ngũ Gia Bì
(31, 3), -- Cây Kim Ngân
(32, 3), -- Cây Lan Ý
(19, 3), -- Cây Xương Đuôi Cá
(7, 3), -- Cây Sao Sáng
(8, 3), -- Cây Thanh Lam
(10, 3), -- Cây Thường Xuân
(15, 3), -- Cây Trường Sinh
(20, 3), -- Cây Bàng

-- Cây có màu sắc (CategoryID = 4)
(5, 4), -- Cây Như Ý
(14, 4), -- Cây Trúc Nhật Vàng
(18, 4), -- Cây Tuyết Tùng
(11, 4), -- Cây Trầu Bà Cẩm Thạch
(21, 4), -- Cây Bao Thanh Thiên
(23, 4), -- Cây Cung Điện Vàng
(24, 4), -- Cây Dứa Hồng Phụng
(26, 4), -- Cây Đuôi Công
(28, 4), -- Cây Hồng Môn
(29, 4), -- Cây Huyết Phật Dụ
(30, 4), -- Cây Kim Giao
(13, 4); -- Cây Trầu Bà Tỳ Phú

-- Thêm Care_Items mẫu cho một số cây
INSERT INTO Care_Items (PlantID, Topic, Content) VALUES
-- Cây Kim Tiền
(1, 'watering', 'Tưới nước khi đất khô, khoảng 2-3 tuần/lần. Tránh để nước đọng.'),
(1, 'light', 'Thích ánh sáng gián tiếp, có thể sống trong điều kiện ánh sáng yếu.'),
(1, 'soil', 'Sử dụng đất thoát nước tốt, có thể trộn perlite hoặc cát.'),
(1, 'tips', 'Cây rất dễ chăm sóc, phù hợp cho người mới bắt đầu.'),

-- Cây Lưỡi Hổ  
(2, 'watering', 'Tưới ít nước, khoảng 2-3 tuần/lần. Để đất khô hoàn toàn giữa các lần tưới.'),
(2, 'light', 'Chịu được ánh sáng yếu đến trung bình, tránh ánh nắng trực tiếp.'),
(2, 'soil', 'Cần đất thoát nước tốt, tránh đất quá ẩm.'),
(2, 'tips', 'Lau lá định kỳ để tăng khả năng quang hợp và lọc không khí.'),

-- Cây Hồng Môn
(28, 'watering', 'Tưới nước đều đặn, giữ đất ẩm nhưng không úng nước.'),
(28, 'light', 'Thích ánh sáng sáng nhưng không trực tiếp.'),
(28, 'soil', 'Đất tơi xốp, thoát nước tốt, giàu chất hữu cơ.'),
(28, 'tips', 'Hoa đỏ tượng trưng cho may mắn và thịnh vượng.');

-- Liên kết Plants với Tags mẫu
INSERT INTO Plant_Tags (PlantID, TagID) VALUES
-- Cây Kim Tiền
(1, 2), -- Phong thủy
(1, 3), -- Dễ chăm sóc
(1, 10), -- Tài lộc
(1, 11), -- Giá rẻ

-- Cây Lưỡi Hổ
(2, 1), -- Lọc không khí
(2, 3), -- Dễ chăm sóc
(2, 6), -- Ít ánh sáng
(2, 9), -- Sản xuất O2

-- Cây Hồng Môn
(28, 2), -- Phong thủy
(28, 8), -- Trang trí đẹp
(28, 13), -- Có hoa
(28, 12); -- Làm quà tặng