<?php 
// Force no cachee
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Nhúng file kết nối cơ sở dữ liệuu
include 'db_connect.php';

// Lấy ID cây từ URL
$plant_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($plant_id <= 0) {
    header('Location: products.php');
    exit;
}

// Lấy thông tin chi tiết cây
$plant_sql = "SELECT p.*, GROUP_CONCAT(c.CategoryName SEPARATOR ', ') as Categories,
              GROUP_CONCAT(t.TagName SEPARATOR ', ') as Tags
              FROM Plants p 
              LEFT JOIN Plant_Categories pc ON p.PlantID = pc.PlantID
              LEFT JOIN Categories c ON pc.CategoryID = c.CategoryID 
              LEFT JOIN Plant_Tags pt ON p.PlantID = pt.PlantID
              LEFT JOIN Tags t ON pt.TagID = t.TagID
              WHERE p.PlantID = $plant_id
              GROUP BY p.PlantID";

$plant_result = mysqli_query($conn, $plant_sql);

if (!$plant_result || mysqli_num_rows($plant_result) == 0) {
    header('Location: products.php');
    exit;
}

$plant = mysqli_fetch_assoc($plant_result);

// Lấy 4 sản phẩm khác
$related_sql = "SELECT PlantID, PlantName, imageURL, Summary, Price 
                FROM Plants 
                WHERE PlantID != $plant_id 
                ORDER BY PlantID 
                LIMIT 4";
$related_result = mysqli_query($conn, $related_sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($plant['PlantName']); ?> - Cây Cảnh Văn Phòng</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
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
        
        .nav-link:hover {
            color: var(--light-green) !important;
            font-weight: 600;
        }
        
        .main-content {
            margin-top: 80px;
            padding: 2rem 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        }
        
        .breadcrumb-section {
            background: white;
            padding: 1rem 0;
            border-radius: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            border: 2px solid rgba(127, 176, 105, 0.2);
        }
        
        .breadcrumb {
            margin-bottom: 0;
            background: none;
            padding: 0;
        }
        
        .breadcrumb-item a {
            color: var(--accent-green);
            text-decoration: none;
            font-weight: 500;
        }
        
        .breadcrumb-item a:hover {
            color: var(--primary-green);
        }
        
        .breadcrumb-item.active {
            color: var(--primary-green);
            font-weight: 600;
        }
        
        .product-detail-section {
            background: #f4f1e8;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            border: 1px solid rgba(127, 176, 105, 0.2);
            margin-bottom: 2rem;
        }
        
        .product-title {
            color: var(--primary-green);
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .product-subtitle {
            color: #666;
            font-size: 1.1rem;
            text-align: justify;
            margin-bottom: 2rem;
            line-height: 1.6;
            max-height: none;
            overflow: visible;
            padding: 1rem;
            background: rgba(127, 176, 105, 0.05);
            border-radius: 10px;
            border-left: 4px solid var(--accent-green);
        }
        
        .image-container {
            line-height: 0;
            font-size: 0;
            height: 100%;
            display: flex;
            align-items: stretch;
        }
        
        .plant-image {
            width: 100%;
            height: 100%;
            min-height: 450px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: block;
        }
        
        .plant-title {
            color: var(--primary-green);
            font-weight: bold;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .plant-code {
            color: #666;
            font-size: 1rem;
            margin-bottom: 1rem;
        }
        
        .price {
            color: var(--accent-green);
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        
        .plant-description {
            color: #555;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        
        .info-table {
            background: white;
            border-radius: 15px;
            padding: 0;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            border: 1px solid rgba(127, 176, 105, 0.2);
            overflow: hidden;
        }
        
        .info-table-header {
            background: linear-gradient(135deg, var(--primary-green), var(--accent-green));
            color: white;
            padding: 1rem 1.5rem;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .info-table-body {
            padding: 1.5rem;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--primary-green);
            flex: 0 0 45%;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            color: #333;
            flex: 1;
            font-weight: 500;
        }
        
        .price-value {
            color: var(--accent-green);
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .category-tags {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px dashed rgba(127, 176, 105, 0.3);
        }
        
        .category-tags strong {
            color: var(--primary-green);
            display: block;
            margin-bottom: 0.5rem;
        }
        
        .tag-badge {
            background: linear-gradient(135deg, var(--accent-green), #8bc34a);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.85rem;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            display: inline-block;
            font-weight: 500;
        }
        
        .btn-back {
            background-color: var(--accent-green);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            background-color: var(--primary-green);
            color: white;
            transform: translateY(-2px);
        }
        
        .tag-badge {
            background-color: var(--accent-green);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.85rem;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            display: inline-block;
        }
        
        .category-badge {
            background-color: var(--primary-green);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.85rem;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            display: inline-block;
        }
        
        .footer {
            color: white !important;
            padding: 4rem 0 !important;
            margin-top: 4rem !important;
            position: relative !important;
            overflow: hidden !important;
            box-shadow: 0 -5px 20px rgba(0,0,0,0.1) !important;
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
        
        .related-products {
            background: #f4f1e8;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .related-title {
            color: var(--primary-green);
            font-weight: bold;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .product-info {
            padding: 1rem;
        }
        
        .product-name {
            color: var(--primary-green);
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .product-card:hover .product-name {
            color: var(--accent-green);
        }
        
        .product-summary {
            color: #666;
            font-size: 0.85rem;
            line-height: 1.4;
            margin-bottom: 0.75rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-price {
            color: var(--accent-green);
            font-weight: bold;
            font-size: 1rem;
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
                        <a class="nav-link active" href="products.php"><i class="bi bi-list-ul me-1"></i>Danh sách cây</a>
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

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Breadcrumb -->
            <div class="breadcrumb-section">
                <div class="container">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                            <li class="breadcrumb-item"><a href="products.php">Danh sách cây</a></li>
                            <li class="breadcrumb-item active"><?php echo htmlspecialchars($plant['PlantName']); ?></li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Product Detail Section -->
            <div class="product-detail-section">
                <h1 class="product-title"><?php echo htmlspecialchars($plant['PlantName']); ?></h1>
                <p class="product-subtitle">
                    <?php if (!empty($plant['DetailedDescription']) && trim($plant['DetailedDescription']) != ''): ?>
                        <?php echo nl2br(htmlspecialchars($plant['DetailedDescription'])); ?>
                    <?php elseif (!empty($plant['Summary']) && trim($plant['Summary']) != ''): ?>
                        <?php echo nl2br(htmlspecialchars($plant['Summary'])); ?>
                    <?php else: ?>
                        <em>Chưa có mô tả chi tiết cho sản phẩm này. Vui lòng liên hệ để biết thêm thông tin.</em>
                    <?php endif; ?>
                </p>

                <div class="row align-items-stretch">
                    <!-- Hình ảnh sản phẩm -->
                    <div class="col-lg-6 mb-4 d-flex">
                        <div class="image-container w-100">
                            <?php 
                            $image_path = !empty($plant['imageURL']) && file_exists('images/' . $plant['imageURL']) 
                                         ? 'images/' . $plant['imageURL'] 
                                         : 'images/default-plant.jpg';
                            ?>
                            <img src="<?php echo htmlspecialchars($image_path); ?>" 
                                 alt="<?php echo htmlspecialchars($plant['PlantName']); ?>" 
                                 class="plant-image">
                        </div>
                    </div>

                    <!-- Thông tin sản phẩm -->
                    <div class="col-lg-6">
                        <div class="info-table">
                            <div class="info-table-header">
                                Thông tin chi tiết sản phẩm
                            </div>
                            <div class="info-table-body">
                                <div class="info-row">
                                    <span class="info-label">Tên khoa học</span>
                                    <span class="info-value"><?php echo htmlspecialchars($plant['ScientificName'] ?: 'Philodendron Imperial Red'); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Tên thông thường</span>
                                    <span class="info-value"><?php echo htmlspecialchars($plant['CommonName'] ?: $plant['PlantName']); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Kích thước cây</span>
                                    <span class="info-value"><?php echo htmlspecialchars($plant['TotalHeight'] ?: '30 cm'); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Độ khó chăm sóc</span>
                                    <span class="info-value"><?php echo htmlspecialchars($plant['DifficultyLevel'] ?: 'Trung bình'); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Yêu cầu ánh sáng</span>
                                    <span class="info-value"><?php echo htmlspecialchars($plant['LightRequirement'] ?: 'Nắng tán xạ, chịu được nắng trực tiếp'); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Nhu cầu nước</span>
                                    <span class="info-value"><?php echo htmlspecialchars($plant['WateringFrequency'] ?: 'Tưới nước 2-3 lần/tuần'); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Giá tiền</span>
                                    <span class="info-value price-value">
                                        <?php echo number_format($plant['Price'] ?: 300000, 0, ',', '.'); ?>₫
                                    </span>
                                </div>
                                
                                <!-- Danh mục và Tags -->
                                <div class="category-tags">
                                    <?php if (!empty($plant['Categories'])): ?>
                                    <strong>Danh mục:</strong>
                                    <?php 
                                    $categories = explode(', ', $plant['Categories']);
                                    foreach ($categories as $category): 
                                    ?>
                                    <span class="tag-badge"><?php echo htmlspecialchars($category); ?></span>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                    <strong>Danh mục:</strong>
                                    <span class="tag-badge">Cây dễ chăm sóc</span>
                                    <span class="tag-badge">Cây có màu sắc</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sản phẩm khác -->
            <?php if ($related_result && mysqli_num_rows($related_result) > 0): ?>
            <div class="related-products">
                <h2 class="related-title">
                    <i class="bi bi-grid me-2"></i>Sản Phẩm Khác
                </h2>
                <div class="row">
                    <?php while($related = mysqli_fetch_assoc($related_result)): ?>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <a href="plant_detail.php?id=<?php echo $related['PlantID']; ?>" class="text-decoration-none">
                            <div class="product-card">
                                <?php 
                                $related_image = !empty($related['imageURL']) && file_exists('images/' . $related['imageURL']) 
                                               ? 'images/' . $related['imageURL'] 
                                               : 'images/default-plant.jpg';
                                ?>
                                <img src="<?php echo htmlspecialchars($related_image); ?>" 
                                     alt="<?php echo htmlspecialchars($related['PlantName']); ?>" 
                                     class="product-image">
                                <div class="product-info">
                                    <div class="product-name">
                                        <?php echo htmlspecialchars($related['PlantName']); ?>
                                    </div>
                                    <div class="product-summary">
                                        <?php echo htmlspecialchars($related['Summary'] ?: 'Cây cảnh văn phòng đẹp, dễ chăm sóc'); ?>
                                    </div>
                                    <div class="product-price">
                                        <?php echo number_format($related['Price'] ?: 500000, 0, ',', '.'); ?>₫
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>
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
    </script>
</body>
</html>

<?php
// Đóng kết nối cơ sở dữ liệu
mysqli_close($conn);
?>