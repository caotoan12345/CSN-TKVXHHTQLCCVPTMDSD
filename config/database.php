<?php
// =====================================================
// HỆ THỐNG QUẢN LÝ CÂY CẢNH VĂN PPHÒNG
// File kết nối cơ sở dữ liệu
// Tác giả: Đặng Cao Toàn - MSSV: 110123187
// =====================================================

$servername = "localhost";
$username = "root";
$password = ""; // Mặc định của XAMPP là để trống
$dbname = "quanly_caycanh";
 
// Tạo kết nối sử dụng mysqli
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Đặt font chữ hiển thị tiếng Việt chính xác
mysqli_set_charset($conn, "utf8mb4");

// Thông báo kết nối thành công (có thể bỏ comment khi cần debug)
// echo "Kết nối cơ sở dữ liệu thành công!";
?>