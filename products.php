<?php
// Force no cache
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Nhúng file kết nối cơ sở dữ liệu
include 'db_connect.php';

// Lấy tham số tìm kiếm và phân loại
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$tag = isset($_GET['tag']) ? mysqli_real_escape_string($conn, $_GET['tag']) : '';

// Định nghĩa danh sách cây theo tag
$tag_plants = [
    'Giá rẻ' => [
        'Cây Lưỡi Hổ',
        'Cây Lưỡi Mèo', 
        'Cây Ngũ Gia Bì',
        'Cây Trường Sinh',
        'Cây Cọ Lá Xẻ',
        'Cây Giữ Tiền',
        'Cây Trầu Bà Cẩm Thạch',
        'Cây Trầu Bà Tỳ Phú'
    ],
    'Làm quà tặng' => [
        'Cây Kim Tiền',
        'Cây Kim Ngân',
        'Cây Hồng Môn',
        'Cây Bao Thanh Thiên',
        'Cây Cung Điện Vàng',
        'Cây Tùng Thơm',
        'Cây Thông Mini'
    ],
    'Có hoa' => [
        'Cây Hồng Môn',
        'Cây Lan Ý',
        'Cây Dứa Hồng Phụng'
    ],
    'Trồng thủy sinh' => [
        'Cây Thanh Lam',
        'Cây Sao Sáng',
        'Cây Lan Ý',
        'Cây Như Ý',
        'Cây Trường Sinh',
        'Cây Hồng Môn'
    ]
];

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12; // Số sản phẩm trên mỗi trang
$offset = ($page - 1) * $limit;

// Sử dụng query với bảng liên kết Plant_Categories
$sql = "SELECT DISTINCT p.*, GROUP_CONCAT(c.CategoryName SEPARATOR ', ') as CategoryNames 
        FROM Plants p 
        LEFT JOIN Plant_Categories pc ON p.PlantID = pc.PlantID
        LEFT JOIN Categories c ON pc.CategoryID = c.CategoryID 
        WHERE 1=1";

if (!empty($search)) {
    $sql .= " AND (p.PlantName LIKE '%$search%' OR p.Summary LIKE '%$search%')";
}

if (!empty($category)) {
    $sql .= " AND c.CategoryName = '$category'";
}

if (!empty($tag) && isset($tag_plants[$tag])) {
    $plant_names = array_map(function($name) use ($conn) {
        return "'" . mysqli_real_escape_string($conn, $name) . "'";
    }, $tag_plants[$tag]);
    $sql .= " AND p.PlantName IN (" . implode(',', $plant_names) . ")";
}

// Thêm GROUP BY để tránh duplicate
$sql .= " GROUP BY p.PlantID";

$sql .= " ORDER BY p.PlantName LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $sql);

// Kiểm tra lỗi truy vấn
if (!$result) {
    die("Lỗi truy vấn: " . mysqli_error($conn));
}

// Đếm tổng số sản phẩm
$count_sql = "SELECT COUNT(DISTINCT p.PlantID) as total FROM Plants p 
              LEFT JOIN Plant_Categories pc ON p.PlantID = pc.PlantID
              LEFT JOIN Categories c ON pc.CategoryID = c.CategoryID 
              WHERE 1=1";

if (!empty($search)) {
    $count_sql .= " AND (p.PlantName LIKE '%$search%' OR p.Summary LIKE '%$search%')";
}

if (!empty($category)) {
    $count_sql .= " AND c.CategoryName = '$category'";
}

if (!empty($tag) && isset($tag_plants[$tag])) {
    $plant_names = array_map(function($name) use ($conn) {
        return "'" . mysqli_real_escape_string($conn, $name) . "'";
    }, $tag_plants[$tag]);
    $count_sql .= " AND p.PlantName IN (" . implode(',', $plant_names) . ")";
}

$count_result = mysqli_query($conn, $count_sql);
$total_products = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_products / $limit);

// Lấy danh sách categories cho filter
$categories_sql = "SELECT * FROM Categories ORDER BY CategoryName";
$categories_result = mysqli_query($conn, $categories_sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách cây cảnh - Hệ thống quản lý cây cảnh văn phòng</title>
    
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
        
        .page-header {
            position: relative;
            background: url('images/bannertrangdanhmuc.png') no-repeat center center;
            background-size: cover;
            color: white;
            padding: 120px 0 60px 0;
            text-align: center;
            min-height: 300px;
        }
        
        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 1;
        }
        
        .page-header .container {
            position: relative;
            z-index: 2;
        }
        
        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 4px 4px 8px rgba(0, 0, 0, 0.9), 2px 2px 4px rgba(0, 0, 0, 1), 0 0 10px rgba(0, 0, 0, 0.8);
            letter-spacing: 1px;
            color: #ffffff;
        }
        
        .page-subtitle, .lead {
            font-family: 'Poppins', sans-serif !important;
            font-size: 1.4rem !important;
            font-weight: 400 !important;
            margin-bottom: 2rem;
            opacity: 0.95;
            letter-spacing: 0.3px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8), 1px 1px 2px rgba(0, 0, 0, 0.9);
            color: #ffffff !important;
        }
        
        .filters-section {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            padding: 2.5rem 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            border-bottom: 3px solid var(--accent-green);
        }
        
        .search-box {
            border-radius: 25px;
            border: 3px solid var(--accent-green);
            padding: 12px 20px;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(127, 176, 105, 0.2);
        }
        
        .search-box:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 0.2rem rgba(45, 90, 39, 0.25), 0 4px 15px rgba(127, 176, 105, 0.3);
            transform: translateY(-1px);
        }
        
        .btn-search {
            background: linear-gradient(135deg, var(--accent-green), #8bc34a);
            border: none;
            border-radius: 25px;
            padding: 12px 25px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 3px 12px rgba(127, 176, 105, 0.4);
        }
        
        .btn-search:hover {
            background: linear-gradient(135deg, var(--primary-green), var(--accent-green));
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 18px rgba(45, 90, 39, 0.4);
        }
        
        .filter-btn {
            border: 2px solid var(--accent-green);
            color: var(--primary-green);
            border-radius: 20px;
            padding: 10px 18px;
            margin: 5px;
            transition: all 0.3s ease;
            text-decoration: none;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(127, 176, 105, 0.2);
        }
        
        .filter-btn:hover, .filter-btn.active {
            background: linear-gradient(135deg, var(--accent-green), #8bc34a);
            color: white;
            border-color: var(--accent-green);
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(127, 176, 105, 0.4);
        }
        
        .products-section {
            padding: 4rem 0;
        }
        
        .plant-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            overflow: hidden;
            margin-bottom: 3rem;
            height: 100%;
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
            display: flex;
            flex-direction: column;
            height: calc(100% - 200px);
        }
        
        .plant-name {
            color: var(--primary-green);
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }
        
        .plant-price {
            color: var(--primary-green);
            font-size: 1.2rem;
            font-weight: 900;
            text-align: center;
            margin-bottom: 0.8rem;
            background: linear-gradient(135deg, #f4f1e8, #e8e2d5);
            padding: 6px 12px;
            border-radius: 18px;
            border: 2px solid var(--accent-green);
            box-shadow: 0 2px 6px rgba(45, 90, 39, 0.2);
        }
        
        .plant-summary {
            font-size: 0.9rem;
            color: #666;
            flex-grow: 1;
            margin-bottom: 1rem;
        }
        
        .btn-detail {
            background-color: var(--accent-green);
            border: none;
            border-radius: 25px;
            padding: 8px 20px;
            color: white;
            transition: all 0.3s ease;
            margin-top: auto;
            display: block;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
        }
        
        .btn-detail:hover {
            background-color: var(--primary-green);
            color: white;
        }
        
        .pagination {
            justify-content: center;
            margin-top: 3rem;
        }
        
        .page-link {
            color: var(--primary-green);
            border-color: var(--accent-green);
        }
        
        .page-link:hover {
            color: white;
            background-color: var(--accent-green);
            border-color: var(--accent-green);
        }
        
        .page-item.active .page-link {
            background-color: var(--primary-green);
            border-color: var(--primary-green);
        }
        
        .results-info {
            color: var(--primary-green);
            font-weight: 500;
            margin-bottom: 2rem;
        }
        
        .footer {
            background-color: var(--primary-green);
            color: white;
            padding: 2rem 0;
            margin-top: 4rem;
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

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1 class="page-title">Danh sách cây cảnh văn phòng</h1>
            <p class="lead">Khám phá bộ sưu tập đa dạng các loại cây cảnh phù hợp cho không gian làm việc</p>
        </div>
    </section>

    <!-- Filters Section -->
    <section class="filters-section">
        <div class="container">
            <form method="GET" action="products.php" class="row align-items-center">
                <div class="col-md-6 mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control search-box" name="search" 
                               placeholder="Tìm kiếm cây cảnh..." value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-search" type="submit">
                            <i class="bi bi-search"></i> Tìm kiếm
                        </button>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="d-flex flex-wrap">
                        <a href="products.php" class="filter-btn <?php echo empty($category) ? 'active' : ''; ?>">
                            Tất cả
                        </a>
                        <?php 
                        // Hiển thị filter buttons từ database
                        mysqli_data_seek($categories_result, 0);
                        while($cat = mysqli_fetch_assoc($categories_result)): 
                        ?>
                        <a href="products.php?category=<?php echo urlencode($cat['CategoryName']); ?>" 
                           class="filter-btn <?php echo $category == $cat['CategoryName'] ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($cat['CategoryName']); ?>
                        </a>
                        <?php endwhile; ?>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products-section">
        <div class="container">
            <div class="results-info">
                <i class="bi bi-info-circle me-1"></i>
                Hiển thị <?php echo mysqli_num_rows($result); ?> trong tổng số <?php echo $total_products; ?> cây cảnh
                <?php if (!empty($search)): ?>
                    cho từ khóa "<strong><?php echo htmlspecialchars($search); ?></strong>"
                <?php endif; ?>
                <?php if (!empty($category)): ?>
                    trong danh mục "<strong><?php echo htmlspecialchars($category); ?></strong>"
                <?php endif; ?>
                <?php if (!empty($tag)): ?>
                    với tag "<strong><?php echo htmlspecialchars($tag); ?></strong>"
                <?php endif; ?>
            </div>
            
            <div class="row">
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        // Logic ảnh đơn giản
                        $plantId = $row['PlantID'];
                        $imageURL = trim($row['imageURL']);
                        
                        if (!empty($imageURL) && file_exists('images/' . $imageURL)) {
                            $image_path = 'images/' . $imageURL . '?v=' . $plantId;
                        } else {
                            $image_path = 'images/caykimtien.jpg?v=default';
                        }
                ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card plant-card">
                        <img src="<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($row['PlantName']); ?>" class="card-img-top">
                        <div class="card-body plant-card-body">
                            <h5 class="plant-name"><?php echo htmlspecialchars($row['PlantName']); ?></h5>
                            <p class="plant-summary"><?php echo htmlspecialchars(substr($row['Summary'], 0, 100)) . (strlen($row['Summary']) > 100 ? '...' : ''); ?></p>
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
                    echo '<div class="col-12"><div class="text-center py-5">
                            <i class="bi bi-search" style="font-size: 3rem; color: var(--accent-green);"></i>
                            <h4 class="mt-3">Không tìm thấy cây cảnh nào</h4>
                            <p>Hãy thử tìm kiếm với từ khóa khác hoặc chọn danh mục khác.</p>
                          </div></div>';
                }
                ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Phân trang">
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&tag=<?php echo urlencode($tag); ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&tag=<?php echo urlencode($tag); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&tag=<?php echo urlencode($tag); ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </section>

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
        // Hàm lọc cây theo danh mục
        function filterByCategory(categoryName) {
            window.location.href = 'products.php?category=' + encodeURIComponent(categoryName);
        }
    </script>
</body>
</html>

<?php
// Đóng kết nối cơ sở dữ liệu
mysqli_close($conn);
?>