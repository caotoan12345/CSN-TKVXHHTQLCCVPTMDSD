<?php 
$servername = "localhost";
$username = "root";
$password = ""; // Mặc định của XAMPP là để trốn
$dbname = "quanly_caycanh";

// Tạo kết nối sử dụng mysqli
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Đặt font chữ hiển thị tiếng Việt chính xác
mysqli_set_charset($conn, "utf8mb4");
?>