<?php 
// Force no cache
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Nhúng file kết nối cơ sở dữ liệuu
include 'db_connect.php';

// Lấy cài đặt trang chủ
$homepage_settings = [];
$settings_sql = "SELECT SettingKey, SettingValue FROM HomepageSettings";
$settings_result = mysqli_query($conn, $settings_sql);
if ($settings_result) {
    while($setting = mysqli_fetch_assoc($settings_result)) {
        $homepage_settings[$setting['SettingKey']] = $setting['SettingValue'];
    }
}

// Viết câu lệnh SQL để lấy danh sách cây nổi bật
$sql = "SELECT p.*, fp.DisplayOrder 
        FROM Plants p 
        INNER JOIN FeaturedPlants fp ON p.PlantID = fp.PlantID 
        WHERE fp.IsActive = 1 
        ORDER BY fp.DisplayOrder ASC";
$result = mysqli_query($conn, $sql);

// Nếu không có cây nổi bật, lấy 8 cây đầu tiên
if (!$result || mysqli_num_rows($result) == 0) {
    $sql = "SELECT * FROM Plants LIMIT 8";
    $result = mysqli_query($conn, $sql);
}

// Lấy danh sách danh mục từ database
$categories_sql = "SELECT c.*, COUNT(pc.PlantID) as plant_count 
                   FROM Categories c 
                   LEFT JOIN Plant_Categories pc ON c.CategoryID = pc.CategoryID 
                   GROUP BY c.CategoryID 
                   ORDER BY c.CategoryName";
$categories_result = mysqli_query($conn, $categories_sql);

// Kiểm tra lỗi truy vấn
if (!$result) {
    die("Lỗi truy vấn: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống quản lý cây cảnh văn phòng</title>
    
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
            padding-top: 0; /* Bỏ padding để navbar và banner gắn liền */
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
        
        .nav-link:hover {
            color: var(--light-green) !important;
        }
        
        .nav-link.active {
            color: var(--light-green) !important;
            font-weight: 600;
        }
        
        .hero-section {
            position: relative;
            background: url('images/bannertrangchu.png?v=2') no-repeat center center;
            background-size: cover;
            color: white;
            text-align: center;
            margin-top: 0;
            min-height: 500px;
            height: 60vh;
            max-height: 700px;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.3));
            z-index: 1;
        }
        
        .hero-section .container {
            position: relative;
            z-index: 2;
            padding-top: 140px;
            padding-bottom: 100px;
        }
        
        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: 3.8rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            letter-spacing: 1px;
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.8), 1px 1px 3px rgba(0, 0, 0, 0.9);
            color: #ffffff;
        }
        
        .hero-subtitle {
            font-family: 'Poppins', sans-serif;
            font-size: 1.4rem;
            font-weight: 400;
            margin-bottom: 2rem;
            opacity: 0.95;
            letter-spacing: 0.3px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8), 1px 1px 2px rgba(0, 0, 0, 0.9);
            color: #ffffff;
        }
        
        .btn-hero {
            background-color: var(--accent-green);
            border: none;
            padding: 15px 30px;
            font-size: 1.1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        
        .btn-hero:hover {
            background-color: #6fa055;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(127, 176, 105, 0.4);
        }
        
        .filter-btn {
            border-radius: 20px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 2px solid var(--primary-green);
            color: var(--primary-green);
        }
        
        .filter-btn:hover {
            background-color: var(--primary-green);
            color: white;
            transform: translateY(-1px);
        }
        
        .section-title {
            font-family: 'Playfair Display', serif;
            color: var(--primary-green);
            font-weight: 600;
            margin-bottom: 3rem;
            text-align: center;
            font-size: 2.5rem;
            letter-spacing: 0.5px;
        }
        
        .plant-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .plant-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        
        .plant-card img {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        
        .plant-card-body {
            padding: 1.5rem;
        }
        
        .plant-name {
            color: var(--primary-green);
            font-weight: bold;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }
        
        .plant-price {
            color: var(--primary-green);
            font-size: 1.3rem;
            font-weight: 900;
            text-align: center;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #f4f1e8, #e8e2d5);
            padding: 8px 15px;
            border-radius: 20px;
            border: 2px solid var(--accent-green);
            box-shadow: 0 2px 8px rgba(45, 90, 39, 0.2);
        }
        
        .btn-detail {
            background-color: var(--accent-green);
            border: none;
            border-radius: 25px;
            padding: 8px 20px;
            color: white;
            transition: all 0.3s ease;
            display: block;
            margin: 0 auto;
            text-align: center;
        }
        
        .btn-detail:hover {
            background-color: var(--light-green);
            color: white;
        }
        
        .categories-section {
            background-color: #faf9f6;
            padding: 60px 0;
            margin: 0;
        }
        
        .categories-title {
            font-family: 'Playfair Display', serif;
            text-align: center;
            color: var(--primary-green);
            font-weight: 600;
            margin-bottom: 3rem;
            font-size: 2.3rem;
            letter-spacing: 0.8px;
        }
        
        .category-card {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 2rem;
            cursor: pointer;
            transition: all 0.3s ease;
            height: 200px;
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: flex-end;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        
        .category-overlay {
            background: linear-gradient(to top, rgba(0,0,0,0.7), rgba(0,0,0,0.3));
            width: 100%;
            padding: 20px;
            color: white;
            text-align: center;
        }
        
        .category-name {
            font-family: 'Poppins', sans-serif;
            font-size: 1.3rem;
            font-weight: 600;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        
        .category-count {
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            font-weight: 400;
            opacity: 0.9;
            margin: 0;

        .footer {
            color: white !important;
            padding: 4rem 0 !important;
            margin-top: 4rem !important;
            position: relative !important;
            overflow: hidden !important;
            box-shadow: 0 -5px 20px rgba(0,0,0,0.1) !important;
        }
        
        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.7), rgba(45, 90, 39, 0.8), rgba(74, 124, 89, 0.7)) !important;
            z-index: 1;
        }
        
        .footer .container {
            position: relative;
            z-index: 3;
        }
        
        .footer-brand {
            margin-bottom: 2rem !important;
        }
        
        .footer-title {
            font-family: 'Playfair Display', serif !important;
            font-size: 2.2rem !important;
            font-weight: 700 !important;
            color: #ffffff !important;
            margin-bottom: 1.5rem !important;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.8), 1px 1px 3px rgba(0,0,0,0.9) !important;
        }
        
        .footer-description {
            font-family: 'Poppins', sans-serif !important;
            font-size: 1.1rem !important;
            line-height: 1.8 !important;
            opacity: 1 !important;
            margin-bottom: 2rem !important;
            text-align: justify !important;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.8), 1px 1px 2px rgba(0,0,0,0.9) !important;
        }
        
        .footer-author {
            background: rgba(0, 0, 0, 0.6) !important;
            padding: 1rem 1.2rem !important;
            border-radius: 10px !important;
            border-left: 5px solid var(--accent-green) !important;
            backdrop-filter: blur(10px) !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3) !important;
        }
        
        .footer-author small {
            font-size: 1rem !important;
            opacity: 1 !important;
            font-weight: 500 !important;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.8) !important;
        }
        
        .footer-heading {
            font-family: 'Playfair Display', serif !important;
            font-size: 1.6rem !important;
            font-weight: 600 !important;
            color: #ffffff !important;
            margin-bottom: 2rem !important;
            position: relative;
            padding-bottom: 0.8rem !important;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.8), 1px 1px 3px rgba(0,0,0,0.9) !important;
        }
        
        .footer-heading::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-green), rgba(127, 176, 105, 0.5)) !important;
            border-radius: 2px;
        }
        
        .footer-links {
            list-style: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        
        .footer-links li {
            margin-bottom: 1rem !important;
        }
        
        .footer-links a {
            color: rgba(255, 255, 255, 0.95) !important;
            text-decoration: none !important;
            font-size: 1.1rem !important;
            font-family: 'Poppins', sans-serif !important;
            font-weight: 500 !important;
            transition: all 0.3s ease !important;
            display: flex !important;
            align-items: center !important;
            padding: 0.5rem 0.8rem !important;
            border-radius: 6px !important;
            background: rgba(0, 0, 0, 0.3) !important;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.8), 1px 1px 2px rgba(0,0,0,0.9) !important;
        }
        
        .footer-links a:hover {
            color: #ffffff !important;
            padding-left: 1.2rem !important;
            background: rgba(0, 0, 0, 0.5) !important;
            transform: translateX(5px) !important;
        }
        
        .footer-links a i {
            width: 24px !important;
            color: var(--accent-green) !important;
            font-size: 1.1rem !important;
        }
        
        @media (max-width: 768px) {
            .footer {
                padding: 2rem 0;
            }
            
            .footer-title {
                font-size: 1.6rem;
            }
            
            .footer-description {
                text-align: left;
            }
            
            .footer-heading {
                font-size: 1.2rem;
            }
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header & Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                CÂy CẢNH VĂN PHÒNG
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><i class="bi bi-house me-1"></i>Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php"><i class="bi bi-list-ul me-1"></i>Danh sách cây</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tuvan.php"><i class="bi bi-chat-dots me-1"></i>Tư vấn</a>
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
            <h1 class="hero-title">Cây cảnh cho không gian làm việc</h1>
            <p class="hero-subtitle">Tạo môi trường làm việc xanh, sạch và tích cực với những loài cây cảnh phù hợp</p>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories-section">
        <div class="container">
            <h2 class="categories-title">DANH MỤC SẢN PHẨM</h2>
            <div class="row">
                <?php 
                // Mảng hình ảnh mặc định cho danh mục
                $category_images = [
                    'Cây dễ chăm sóc' => 'images/caydechamsoc.png',
                    'Cây phong thủy' => 'images/cayphongthuy.png',
                    'Cây lọc không khí' => 'images/caylockhongkhi.png',
                    'Cây có màu sắc' => 'images/caycomausac.png'
                ];
                
                // Hình ảnh mặc định cho danh mục mới
                $default_image = 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=400&h=300&fit=crop';
                
                if (mysqli_num_rows($categories_result) > 0) {
                    while($category = mysqli_fetch_assoc($categories_result)) {
                        $category_name = $category['CategoryName'];
                        $plant_count = $category['plant_count'];
                        
                        // Chọn hình ảnh cho danh mục
                        $image_url = isset($category_images[$category_name]) ? 
                                    $category_images[$category_name] : $default_image;
                        
                        // Tạo mô tả cho danh mục
                        $descriptions = [
                            'Cây dễ chăm sóc' => 'Phù hợp người mới',
                            'Cây phong thủy' => 'Mang lại may mắn',
                            'Cây lọc không khí' => 'Thanh lọc không gian',
                            'Cây có màu sắc' => 'Trang trí đẹp mắt'
                        ];
                        $description = isset($descriptions[$category_name]) ? 
                                     $descriptions[$category_name] : $plant_count . ' loại cây';
                ?>
                <div class="col-lg-3 col-md-6">
                    <div class="category-card" style="background-image: url('<?php echo $image_url; ?>');" onclick="filterByCategory('<?php echo htmlspecialchars($category_name); ?>')">
                        <div class="category-overlay">
                            <h3 class="category-name"><?php echo strtoupper(htmlspecialchars($category_name)); ?></h3>
                            <p class="category-count"><?php echo htmlspecialchars($description); ?></p>
                        </div>
                    </div>
                </div>
                <?php 
                    }
                } else {
                    echo '<div class="col-12"><p class="text-center">Chưa có danh mục nào.</p></div>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="container my-5">
        <h2 class="section-title">
            <?php 
            $featured_title = isset($homepage_settings['popular_plants_title']) ? $homepage_settings['popular_plants_title'] : 'Cây nổi bật';
            echo htmlspecialchars($featured_title);
            ?>
        </h2>
        
        <div class="row">
            <?php
            // Sử dụng vòng lặp while để hiển thị danh sách cây từ cơ sở dữ liệu
            if (mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    // LOGIC HOẠT ĐỘNG 100% - Sử dụng tên cột đúng: imageURL
                    $plantId = $row['PlantID'];
                    $imageURL = trim($row['imageURL']);
                    
                    // Tạo đường dẫn ảnh trực tiếp
                    if (!empty($imageURL) && file_exists('images/' . $imageURL)) {
                        $image_path = 'images/' . $imageURL . '?id=' . $plantId . '&rand=' . rand(1000, 9999);
                    } else {
                        $image_path = 'images/caykimtien.jpg?default=1&rand=' . rand(1000, 9999);
                    }
            ?>
            <!-- Plant Card từ Database -->
            <div class="col-lg-4 col-md-6">
                <div class="card plant-card">
                    <img src="<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($row['PlantName']); ?>" class="card-img-top">
                    <div class="card-body plant-card-body">
                        <h5 class="plant-name"><?php echo htmlspecialchars($row['PlantName']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($row['Summary']); ?></p>
                        <?php if (isset($row['Price']) && $row['Price'] > 0): ?>
                        <p class="plant-price">
                            <?php echo number_format($row['Price']); ?> VNĐ
                        </p>
                        <?php endif; ?>
                        <a href="plant_detail.php?id=<?php echo $row['PlantID']; ?>" class="btn btn-detail">
                            <i class="bi bi-eye me-1"></i>Xem chi tiết
                        </a>
                    </div>
                </div>
            </div>
            <?php
                }
            } else {
                echo '<div class="col-12"><p class="text-center">Không có cây cảnh nào trong cơ sở dữ liệu.</p></div>';
            }
            ?>
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
                            Giúp người dùng có thêm thông tin về các loại cây cảnh văn phòng. 
                            Tạo môi trường xanh, sạch và tích cực với những loài cây cảnh phù hợp với bản thân.
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
        // Hàm lọc cây theo danh mục
        function filterByCategory(categoryName) {
            // Chuyển hướng đến trang danh sách với filter category
            window.location.href = 'products.php?category=' + encodeURIComponent(categoryName);
        }

        // Hiệu ứng navbar khi cuộn trang
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

        // FORCE RELOAD IMAGES - Copy từ final_test.php
        window.addEventListener('load', function() {
            setTimeout(function() {
                var images = document.querySelectorAll('.plant-card img');
                images.forEach(function(img) {
                    var src = img.src;
                    img.src = '';
                    setTimeout(function() {
                        img.src = src;
                    }, 10);
                });
            }, 100);
        });
    </script>
</body>
</html>

<?php
// Đóng kết nối cơ sở dữ liệu
mysqli_close($conn);
?>