<?php 
// Force no cachee
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Nhúng file kết nối cơ sở dữ liệu
include 'db_connect.php';

$message = '';

// Xử lý form tư vấn
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_consultation'])) {
    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $address = mysqli_real_escape_string($conn, trim($_POST['address']));
    $message_content = mysqli_real_escape_string($conn, trim($_POST['message']));
    
    // Validation - Yêu cầu tất cả trường
    if (empty($full_name) || empty($email) || empty($phone) || empty($address) || empty($message_content)) {
        $message = '<div class="alert alert-danger">❌ Vui lòng điền đầy đủ tất cả thông tin!</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="alert alert-danger">❌ Email không hợp lệ!</div>';
    } elseif (!preg_match('/^[0-9\s\-\+\(\)]+$/', $phone)) {
        $message = '<div class="alert alert-danger">❌ Số điện thoại không hợp lệ!</div>';
    } else {
        // Kiểm tra kết nối database
        if (!$conn) {
            $message = '<div class="alert alert-danger">❌ Lỗi kết nối database!</div>';
        } else {
            // Kiểm tra bảng tồn tại
            $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'Consultations'");
            if (mysqli_num_rows($check_table) == 0) {
                $message = '<div class="alert alert-danger">❌ Bảng Consultations chưa tồn tại! Vui lòng liên hệ admin.</div>';
            } else {
                // Thêm vào database
                $sql = "INSERT INTO Consultations (FullName, Email, Phone, Address, Message) 
                        VALUES ('$full_name', '$email', '$phone', '$address', '$message_content')";
                
                if (mysqli_query($conn, $sql)) {
                    $message = '<div class="alert alert-success">
                        <h5>✅ Gửi yêu cầu thành công!</h5>
                        <p class="mb-0">Cảm ơn <strong>' . htmlspecialchars($full_name) . '</strong>! Chúng tôi đã nhận được yêu cầu tư vấn của bạn và sẽ liên hệ qua email <strong>' . htmlspecialchars($email) . '</strong> hoặc số điện thoại <strong>' . htmlspecialchars($phone) . '</strong> trong thời gian sớm nhất.</p>
                    </div>';
                    
                    // Reset form sau khi gửi thành công
                    $_POST = array();
                } else {
                    $message = '<div class="alert alert-danger">❌ Có lỗi xảy ra khi lưu dữ liệu. Vui lòng thử lại sau!</div>';
                }
            }
        }
    }
}

// Lấy danh sách cây phổ biến để gợi ý
$popular_plants_sql = "SELECT p.*, GROUP_CONCAT(c.CategoryName SEPARATOR ', ') as Categories 
                       FROM Plants p 
                       LEFT JOIN Plant_Categories pc ON p.PlantID = pc.PlantID
                       LEFT JOIN Categories c ON pc.CategoryID = c.CategoryID 
                       GROUP BY p.PlantID 
                       ORDER BY p.PlantID DESC 
                       LIMIT 6";
$popular_plants_result = mysqli_query($conn, $popular_plants_sql);

// Lấy danh sách danh mục để gợi ý
$categories_sql = "SELECT * FROM Categories ORDER BY CategoryName";
$categories_result = mysqli_query($conn, $categories_sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tư vấn chọn cây - Hệ thống quản lý cây cảnh văn phòng</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-green: #2d5a27;
            --light-green: #4a7c59;
            --navbar-light: #5a8a55;
            --navbar-lighter: #6b9b66;
            --accent-green: #7fb069;
            --bg-light: #f4f1e8;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f1e8;
            padding-top: 0;
        }
        
        .navbar {
            background: #f4f1e8;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1030;
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(45, 90, 39, 0.1);
            padding: 1rem 0;
            min-height: 80px;
        }
        
        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-weight: 800;
            color: var(--primary-green) !important;
            font-size: 2rem;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }
        
        .nav-link {
            font-family: 'Poppins', sans-serif;
            color: var(--primary-green) !important;
            transition: color 0.3s ease;
            font-size: 1.1rem;
            font-weight: 500;
            padding: 0.7rem 1rem !important;
        }
        
        .nav-link:hover, .nav-link.active {
            color: var(--light-green) !important;
            font-weight: 600;
        }
        
        .hero-section {
            position: relative;
            background: url('images/bannertrangtuvan.png') no-repeat center center;
            background-size: cover;
            color: white;
            padding: 120px 0 80px 0;
            text-align: center;
            margin-top: 0;
            min-height: 400px;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 1;
        }
        
        .hero-section .container {
            position: relative;
            z-index: 2;
        }
        
        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: 3.2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 4px 4px 8px rgba(0, 0, 0, 0.9), 2px 2px 4px rgba(0, 0, 0, 1), 0 0 10px rgba(0, 0, 0, 0.8);
            letter-spacing: 1px;
            color: #ffffff;
        }
        
        .hero-subtitle {
            font-family: 'Poppins', sans-serif !important;
            font-size: 1.4rem !important;
            font-weight: 400 !important;
            margin-bottom: 2rem;
            opacity: 0.95;
            letter-spacing: 0.3px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8), 1px 1px 2px rgba(0, 0, 0, 0.9);
            color: #ffffff !important;
        }
        
        
        
        .consultation-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }
        
        .consultation-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--accent-green);
            box-shadow: 0 0 0 0.2rem rgba(127, 176, 105, 0.25);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--primary-green);
            margin-bottom: 0.5rem;
        }
        
        .text-danger {
            color: #dc3545 !important;
        }
        
        .consultation-icon {
            font-size: 3rem;
            color: var(--accent-green);
            margin-bottom: 1rem;
        }
        
        .consultation-title {
            color: var(--primary-green);
            font-weight: bold;
            margin-bottom: 1rem;
        }
        
        .btn-consultation {
            background-color: var(--accent-green);
            border: none;
            border-radius: 25px;
            padding: 10px 25px;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-consultation:hover {
            background-color: var(--light-green);
            color: white;
            transform: translateY(-2px);
        }
        
        .footer {
            /* Minimal CSS - let inline styles handle everything */
            position: relative !important;
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.2rem;
            }
            
            .hero-subtitle {
                font-size: 1.2rem !important;
                font-weight: 400 !important;
            }
        }
    </style>
</head>
<body>
    <!-- Header & Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                Cây Cảnh Văn Phòng
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="bi bi-house me-1"></i>Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php"><i class="bi bi-list-ul me-1"></i>Danh sách cây</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="tuvan.php"><i class="bi bi-chat-dots me-1"></i>Tư vấn</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="tagDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-tags me-1"></i>Tag gợi ý
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="tagDropdown">
                            <li><a class="dropdown-item" href="products.php?tag=Giá rẻ">Giá rẻ</a></li>
                            <li><a class="dropdown-item" href="products.php?tag=Làm quà tặng">Làm quà tặng</a></li>
                            <li><a class="dropdown-item" href="products.php?tag=Có hoa">Có hoa</a></li>
                            <li><a class="dropdown-item" href="products.php?tag=Trồng thủy sinh">Trồng thủy sinh</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php"><i class="bi bi-person-circle me-1"></i>Đăng nhập</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="hero-title">Tư vấn chọn cây</h1>
            <p class="hero-subtitle">Chúng tôi sẽ giúp bạn tìm ra loại cây cảnh phù hợp nhất với không gian và nhu cầu của bạn</p>
        </div>
    </section>

    <!-- Main Content -->
    <main class="container my-5">
        <?php echo $message; ?>
        
        <!-- Consultation Form -->
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="text-center mb-4" style="color: var(--primary-green); font-weight: bold;">
                    Đăng ký tư vấn miễn phí
                </h2>
                <p class="text-center text-muted mb-4">
                    Vui lòng điền thông tin để chúng tôi có thể tư vấn cây cảnh phù hợp nhất cho bạn
                </p>
            </div>
            
            <div class="col-lg-8 mx-auto">
                <div class="consultation-card">
                    <form method="POST" id="consultationForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-person me-2"></i>Họ và tên <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" name="full_name" required 
                                       value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                                       placeholder="Nhập họ và tên của bạn">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-envelope me-2"></i>Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" class="form-control" name="email" required 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                       placeholder="example@email.com">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-telephone me-2"></i>Số điện thoại <span class="text-danger">*</span>
                                </label>
                                <input type="tel" class="form-control" name="phone" required
                                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                       placeholder="0123 456 789">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-geo-alt me-2"></i>Địa chỉ <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" name="address" required
                                       value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>"
                                       placeholder="Thành phố, quận/huyện">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="bi bi-chat-dots me-2"></i>Chúng tôi có thể giúp gì cho bạn? <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" name="message" rows="5" required 
                                      placeholder="Ví dụ: Tôi muốn tìm cây cảnh phù hợp cho văn phòng có ánh sáng yếu, dễ chăm sóc và có tác dụng lọc không khí..."><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                            <small class="text-muted">
                                Hãy mô tả chi tiết về không gian, nhu cầu và mong muốn của bạn để chúng tôi tư vấn chính xác nhất
                            </small>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" name="submit_consultation" class="btn btn-consultation btn-lg">
                                <i class="bi bi-send me-2"></i>Gửi yêu cầu tư vấn
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="footer" style="background: url('images/footer.png') center center no-repeat; background-size: cover; position: relative;">
        <div class="container" style="position: relative; z-index: 2;">
            <div class="row">
                <!-- Cột 1: Thông tin hệ thống -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="footer-brand">
                        <h4 class="footer-title" style="color: #ffffff; text-shadow: 4px 4px 8px rgba(0,0,0,0.9), 2px 2px 4px rgba(0,0,0,1), 0 0 10px rgba(0,0,0,0.8); font-family: 'Playfair Display', serif; font-size: 2.2rem; font-weight: 700;">
                            <a href="index.php" style="color: #ffffff; text-decoration: none; transition: color 0.3s ease;" onmouseover="this.style.color='#7fb069'" onmouseout="this.style.color='#ffffff'">Cây Cảnh Văn Phòng</a>
                        </h4>
                        <p class="footer-description" style="color: #ffffff; text-shadow: 3px 3px 6px rgba(0,0,0,0.9), 1px 1px 3px rgba(0,0,0,1); font-family: 'Poppins', sans-serif; font-size: 1.1rem; line-height: 1.8;">
                            Chuyên cung cấp các loại cây cảnh phù hợp cho không gian làm việc. 
                            Tạo môi trường xanh, sạch và tích cực với những loài cây cảnh chất lượng cao.
                        </p>
                        <div class="footer-author" style="background: rgba(0, 0, 0, 0.7); padding: 1rem 1.2rem; border-radius: 10px; border-left: 5px solid #7fb069; box-shadow: 0 4px 15px rgba(0,0,0,0.5);">
                            <small style="color: #ffffff; text-shadow: 3px 3px 6px rgba(0,0,0,0.9), 1px 1px 3px rgba(0,0,0,1); font-size: 1rem; font-weight: 500;">
                                <i class="bi bi-c-circle me-1"></i>
                                Bản quyền thuộc về <strong>Đặng Cao Toàn</strong>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Cột 2: Menu chính -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="footer-heading" style="color: #ffffff; text-shadow: 4px 4px 8px rgba(0,0,0,0.9), 2px 2px 4px rgba(0,0,0,1), 0 0 10px rgba(0,0,0,0.8); font-family: 'Playfair Display', serif; font-size: 1.6rem; font-weight: 600;">Menu</h5>
                    <ul class="footer-links" style="list-style: none; padding: 0; margin: 0;">
                        <li style="list-style: none;"><a href="index.php" style="color: #ffffff; text-shadow: 3px 3px 6px rgba(0,0,0,0.9), 1px 1px 3px rgba(0,0,0,1); background: rgba(0, 0, 0, 0.4); padding: 0.5rem 0.8rem; border-radius: 6px; text-decoration: none; display: flex; align-items: center; margin-bottom: 0.5rem;"><i class="bi bi-house me-2" style="color: #7fb069;"></i>Trang chủ</a></li>
                        <li style="list-style: none;"><a href="products.php" style="color: #ffffff; text-shadow: 3px 3px 6px rgba(0,0,0,0.9), 1px 1px 3px rgba(0,0,0,1); background: rgba(0, 0, 0, 0.4); padding: 0.5rem 0.8rem; border-radius: 6px; text-decoration: none; display: flex; align-items: center; margin-bottom: 0.5rem;"><i class="bi bi-list-ul me-2" style="color: #7fb069;"></i>Danh sách cây</a></li>
                        <li style="list-style: none;"><a href="tuvan.php" style="color: #ffffff; text-shadow: 3px 3px 6px rgba(0,0,0,0.9), 1px 1px 3px rgba(0,0,0,1); background: rgba(0, 0, 0, 0.4); padding: 0.5rem 0.8rem; border-radius: 6px; text-decoration: none; display: flex; align-items: center; margin-bottom: 0.5rem;"><i class="bi bi-chat-dots me-2" style="color: #7fb069;"></i>Tư vấn</a></li>
                        <li style="list-style: none;"><a href="login.php" style="color: #ffffff; text-shadow: 3px 3px 6px rgba(0,0,0,0.9), 1px 1px 3px rgba(0,0,0,1); background: rgba(0, 0, 0, 0.4); padding: 0.5rem 0.8rem; border-radius: 6px; text-decoration: none; display: flex; align-items: center; margin-bottom: 0.5rem;"><i class="bi bi-person-circle me-2" style="color: #7fb069;"></i>Đăng nhập</a></li>
                    </ul>
                </div>

                <!-- Cột 3: Danh mục cây -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="footer-heading" style="color: #ffffff; text-shadow: 4px 4px 8px rgba(0,0,0,0.9), 2px 2px 4px rgba(0,0,0,1), 0 0 10px rgba(0,0,0,0.8); font-family: 'Playfair Display', serif; font-size: 1.6rem; font-weight: 600;">Danh mục cây cảnh</h5>
                    <ul class="footer-links" style="list-style: none; padding: 0; margin: 0;">
                        <li style="list-style: none;"><a href="products.php?category=Cây dễ chăm sóc" style="color: #ffffff; text-shadow: 3px 3px 6px rgba(0,0,0,0.9), 1px 1px 3px rgba(0,0,0,1); background: rgba(0, 0, 0, 0.4); padding: 0.5rem 0.8rem; border-radius: 6px; text-decoration: none; display: flex; align-items: center; margin-bottom: 0.5rem;">Cây dễ chăm sóc</a></li>
                        <li style="list-style: none;"><a href="products.php?category=Cây phong thủy" style="color: #ffffff; text-shadow: 3px 3px 6px rgba(0,0,0,0.9), 1px 1px 3px rgba(0,0,0,1); background: rgba(0, 0, 0, 0.4); padding: 0.5rem 0.8rem; border-radius: 6px; text-decoration: none; display: flex; align-items: center; margin-bottom: 0.5rem;">Cây phong thủy</a></li>
                        <li style="list-style: none;"><a href="products.php?category=Cây lọc không khí" style="color: #ffffff; text-shadow: 3px 3px 6px rgba(0,0,0,0.9), 1px 1px 3px rgba(0,0,0,1); background: rgba(0, 0, 0, 0.4); padding: 0.5rem 0.8rem; border-radius: 6px; text-decoration: none; display: flex; align-items: center; margin-bottom: 0.5rem;">Cây lọc không khí</a></li>
                        <li style="list-style: none;"><a href="products.php?category=Cây có màu sắc" style="color: #ffffff; text-shadow: 3px 3px 6px rgba(0,0,0,0.9), 1px 1px 3px rgba(0,0,0,1); background: rgba(0, 0, 0, 0.4); padding: 0.5rem 0.8rem; border-radius: 6px; text-decoration: none; display: flex; align-items: center; margin-bottom: 0.5rem;">Cây có màu sắc</a></li>
                    </ul>
                </div>

                <!-- Cột 4: Liên hệ -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="footer-heading" style="color: #ffffff; text-shadow: 4px 4px 8px rgba(0,0,0,0.9), 2px 2px 4px rgba(0,0,0,1), 0 0 10px rgba(0,0,0,0.8); font-family: 'Playfair Display', serif; font-size: 1.6rem; font-weight: 600;">Liên hệ</h5>
                    <div class="footer-contact">
                        <div class="contact-item" style="display: flex; align-items: center; margin-bottom: 1rem; background: rgba(0, 0, 0, 0.4); padding: 0.5rem 0.8rem; border-radius: 6px;">
                            <i class="bi bi-telephone me-3" style="color: #7fb069; font-size: 1.2rem; width: 24px;"></i>
                            <span style="color: #ffffff; text-shadow: 3px 3px 6px rgba(0,0,0,0.9), 1px 1px 3px rgba(0,0,0,1); font-family: 'Poppins', sans-serif; font-size: 1rem;">Hotline: 0368160258</span>
                        </div>
                        <div class="contact-item" style="display: flex; align-items: center; margin-bottom: 1rem; background: rgba(0, 0, 0, 0.4); padding: 0.5rem 0.8rem; border-radius: 6px;">
                            <i class="bi bi-envelope me-3" style="color: #7fb069; font-size: 1.2rem; width: 24px;"></i>
                            <span style="color: #ffffff; text-shadow: 3px 3px 6px rgba(0,0,0,0.9), 1px 1px 3px rgba(0,0,0,1); font-family: 'Poppins', sans-serif; font-size: 1rem;">Email: caycanhvanphong@gmail.com</span>
                        </div>
                        <div class="contact-item" style="display: flex; align-items: flex-start; margin-bottom: 1rem; background: rgba(0, 0, 0, 0.4); padding: 0.5rem 0.8rem; border-radius: 6px;">
                            <i class="bi bi-geo-alt me-3" style="color: #7fb069; font-size: 1.2rem; width: 24px; margin-top: 2px;"></i>
                            <span style="color: #ffffff; text-shadow: 3px 3px 6px rgba(0,0,0,0.9), 1px 1px 3px rgba(0,0,0,1); font-family: 'Poppins', sans-serif; font-size: 1rem; line-height: 1.5;">Địa chỉ: Ấp Sóc, Xã Bình Phú, Tỉnh Vĩnh Long</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Navbar scroll effect - giữ màu nền nhất quán
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.backgroundColor = '#f4f1e8';
                navbar.style.backdropFilter = 'blur(10px)';
                navbar.style.boxShadow = '0 2px 15px rgba(0, 0, 0, 0.15)';
            } else {
                navbar.style.backgroundColor = '#f4f1e8';
                navbar.style.backdropFilter = 'none';
                navbar.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
            }
        });
        
        // Force footer styling on load
        window.addEventListener('load', function() {
            const footer = document.querySelector('.footer');
            if (footer) {
                footer.style.backgroundImage = "url('images/footer.png?v=" + Date.now() + "')";
                footer.style.backgroundSize = 'cover';
                footer.style.backgroundPosition = 'center center';
                footer.style.backgroundRepeat = 'no-repeat';
            }
        });
    </script>
</body>
</html>

<?php
// Đóng kết nối cơ sở dữ liệu
mysqli_close($conn);
?>