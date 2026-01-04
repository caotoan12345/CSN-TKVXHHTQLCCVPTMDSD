<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include 'db_connect.php';

// Xử lý các action
$action = isset($_GET['action']) ? $_GET['action'] : 'homepage';
$message = '';

// Xử lý cập nhật cài đặt trang chủ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_homepage_settings'])) {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'setting_') === 0) {
            $setting_key = str_replace('setting_', '', $key);
            $setting_value = mysqli_real_escape_string($conn, $value);
            
            $update_sql = "UPDATE HomepageSettings SET SettingValue = '$setting_value' WHERE SettingKey = '$setting_key'";
            mysqli_query($conn, $update_sql);
        }
    }
    $message = '<div class="alert alert-success">✅ Đã cập nhật cài đặt trang chủ thành công!</div>';
}

// Xử lý thêm cây nổi bật
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_featured'])) {
    $plant_id = (int)$_POST['plant_id'];
    
    // Lấy thứ tự tiếp theo
    $order_sql = "SELECT MAX(DisplayOrder) as max_order FROM FeaturedPlants";
    $order_result = mysqli_query($conn, $order_sql);
    $max_order = mysqli_fetch_assoc($order_result)['max_order'] ?? 0;
    $new_order = $max_order + 1;
    
    $insert_sql = "INSERT INTO FeaturedPlants (PlantID, DisplayOrder) VALUES ($plant_id, $new_order)";
    if (mysqli_query($conn, $insert_sql)) {
        $message = '<div class="alert alert-success">✅ Đã thêm cây nổi bật thành công!</div>';
    } else {
        $message = '<div class="alert alert-danger">❌ Lỗi: ' . mysqli_error($conn) . '</div>';
    }
}

// Xử lý xóa cây nổi bật
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_featured'])) {
    $featured_id = (int)$_POST['remove_featured'];
    
    $delete_sql = "DELETE FROM FeaturedPlants WHERE FeaturedID = $featured_id";
    if (mysqli_query($conn, $delete_sql)) {
        $message = '<div class="alert alert-success">✅ Đã xóa cây khỏi danh sách nổi bật!</div>';
    } else {
        $message = '<div class="alert alert-danger">❌ Lỗi: ' . mysqli_error($conn) . '</div>';
    }
}

// Xử lý cập nhật trang chủ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apply_changes'])) {
    // Không cần làm gì đặc biệt vì trang chủ đã tự động lấy dữ liệu từ database
    // Chỉ cần thông báo thành công
    $message = '<div class="alert alert-success">✅ Đã cập nhật trang chủ thành công! Các thay đổi đã được áp dụng.</div>';
}

// Xử lý xóa tư vấn (chỉ cho trạng thái completed)
if (isset($_GET['delete_consultation'])) {
    $consultation_id = (int)$_GET['delete_consultation'];
    
    // Kiểm tra trạng thái trước khi xóa
    $check_sql = "SELECT Status FROM Consultations WHERE ConsultationID = $consultation_id";
    $check_result = mysqli_query($conn, $check_sql);
    
    if ($check_result && mysqli_num_rows($check_result) > 0) {
        $consultation = mysqli_fetch_assoc($check_result);
        
        if ($consultation['Status'] == 'completed') {
            // Chỉ xóa nếu trạng thái là completed
            $delete_sql = "DELETE FROM Consultations WHERE ConsultationID = $consultation_id";
            if (mysqli_query($conn, $delete_sql)) {
                $message = '<div class="alert alert-success">✅ Đã xóa yêu cầu tư vấn thành công!</div>';
            } else {
                $message = '<div class="alert alert-danger">❌ Lỗi: ' . mysqli_error($conn) . '</div>';
            }
        } else {
            $message = '<div class="alert alert-warning">⚠️ Chỉ có thể xóa yêu cầu tư vấn đã hoàn thành!</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">❌ Không tìm thấy yêu cầu tư vấn!</div>';
    }
}

// Xử lý cập nhật trạng thái tư vấn
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_consultation_status'])) {
    $consultation_id = (int)$_POST['consultation_id'];
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $sql = "UPDATE Consultations SET Status = '$new_status' WHERE ConsultationID = $consultation_id";
    if (mysqli_query($conn, $sql)) {
        $message = '<div class="alert alert-success">✅ Đã cập nhật trạng thái tư vấn thành công!</div>';
    } else {
        $message = '<div class="alert alert-danger">❌ Lỗi: ' . mysqli_error($conn) . '</div>';
    }
}

// Xử lý xóa danh mục
if (isset($_GET['delete_category'])) {
    $category_id = (int)$_GET['delete_category'];
    
    // Kiểm tra xem danh mục có cây nào không
    $check_plants_sql = "SELECT COUNT(*) as total FROM Plant_Categories WHERE CategoryID = $category_id";
    $check_plants_result = mysqli_query($conn, $check_plants_sql);
    $plant_count = mysqli_fetch_assoc($check_plants_result)['total'];
    
    if ($plant_count > 0) {
        $message = '<div class="alert alert-warning">⚠️ Không thể xóa danh mục này vì đang có ' . $plant_count . ' cây thuộc danh mục này!</div>';
    } else {
        // Lấy tên danh mục trước khi xóa
        $get_name_sql = "SELECT CategoryName FROM Categories WHERE CategoryID = $category_id";
        $get_name_result = mysqli_query($conn, $get_name_sql);
        $category_name = '';
        if ($get_name_result && mysqli_num_rows($get_name_result) > 0) {
            $category_name = mysqli_fetch_assoc($get_name_result)['CategoryName'];
        }
        
        // Xóa danh mục
        $delete_sql = "DELETE FROM Categories WHERE CategoryID = $category_id";
        if (mysqli_query($conn, $delete_sql)) {
            $message = '<div class="alert alert-success">✅ Đã xóa danh mục "' . htmlspecialchars($category_name) . '" thành công!</div>';
        } else {
            $message = '<div class="alert alert-danger">❌ Lỗi: ' . mysqli_error($conn) . '</div>';
        }
    }
}

// Xử lý thêm danh mục mới
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $category_name = mysqli_real_escape_string($conn, trim($_POST['category_name']));
    
    if (!empty($category_name)) {
        // Kiểm tra danh mục đã tồn tại chưa
        $check_sql = "SELECT CategoryID FROM Categories WHERE CategoryName = '$category_name'";
        $check_result = mysqli_query($conn, $check_sql);
        
        if (mysqli_num_rows($check_result) > 0) {
            $message = '<div class="alert alert-warning">⚠️ Danh mục "' . htmlspecialchars($category_name) . '" đã tồn tại!</div>';
        } else {
            $sql = "INSERT INTO Categories (CategoryName) VALUES ('$category_name')";
            if (mysqli_query($conn, $sql)) {
                $message = '<div class="alert alert-success">✅ Đã thêm danh mục "' . htmlspecialchars($category_name) . '" thành công!</div>';
            } else {
                $message = '<div class="alert alert-danger">❌ Lỗi: ' . mysqli_error($conn) . '</div>';
            }
        }
    } else {
        $message = '<div class="alert alert-danger">❌ Tên danh mục không được để trống!</div>';
    }
}

// Xử lý thêm cây mới
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_plant'])) {
    $plant_name = mysqli_real_escape_string($conn, $_POST['plant_name']);
    $image_url = mysqli_real_escape_string($conn, $_POST['image_url']);
    $summary = mysqli_real_escape_string($conn, $_POST['summary']);
    $detailed_description = mysqli_real_escape_string($conn, $_POST['detailed_description']);
    $categories = isset($_POST['categories']) ? $_POST['categories'] : [];
    $tags = isset($_POST['tags']) ? $_POST['tags'] : [];
    
    $sql = "INSERT INTO Plants (PlantName, imageURL, Summary, DetailedDescription) VALUES ('$plant_name', '$image_url', '$summary', '$detailed_description')";
    if (mysqli_query($conn, $sql)) {
        $new_plant_id = mysqli_insert_id($conn);
        
        // Thêm danh mục cho cây mới
        if (!empty($categories)) {
            foreach ($categories as $category_id) {
                $category_id = (int)$category_id;
                mysqli_query($conn, "INSERT INTO Plant_Categories (PlantID, CategoryID) VALUES ($new_plant_id, $category_id)");
            }
        }
        
        // Thêm tags cho cây mới
        if (!empty($tags)) {
            foreach ($tags as $tag_id) {
                $tag_id = (int)$tag_id;
                mysqli_query($conn, "INSERT INTO Plant_Tags (PlantID, TagID) VALUES ($new_plant_id, $tag_id)");
            }
        }
        
        $message = '<div class="alert alert-success">✅ Đã thêm cây mới thành công!</div>';
    } else {
        $message = '<div class="alert alert-danger">❌ Lỗi: ' . mysqli_error($conn) . '</div>';
    }
}

// Xử lý cập nhật cây
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_plant'])) {
    $plant_id = (int)$_POST['plant_id'];
    $plant_name = mysqli_real_escape_string($conn, $_POST['plant_name']);
    $image_url = mysqli_real_escape_string($conn, $_POST['image_url']);
    $summary = mysqli_real_escape_string($conn, $_POST['summary']);
    $detailed_description = mysqli_real_escape_string($conn, $_POST['detailed_description']);
    $categories = isset($_POST['categories']) ? $_POST['categories'] : [];
    $tags = isset($_POST['tags']) ? $_POST['tags'] : [];
    
    // Thông tin chi tiết mới
    $scientific_name = mysqli_real_escape_string($conn, $_POST['scientific_name']);
    $common_name = mysqli_real_escape_string($conn, $_POST['common_name']);
    $pot_size = mysqli_real_escape_string($conn, $_POST['pot_size']);
    $total_height = mysqli_real_escape_string($conn, $_POST['total_height']);
    $difficulty_level = mysqli_real_escape_string($conn, $_POST['difficulty_level']);
    $light_requirement = mysqli_real_escape_string($conn, $_POST['light_requirement']);
    $watering_frequency = mysqli_real_escape_string($conn, $_POST['watering_frequency']);
    $price = (int)$_POST['price'];
    
    // Cập nhật thông tin cây
    $sql = "UPDATE Plants SET 
            PlantName = '$plant_name', 
            imageURL = '$image_url', 
            Summary = '$summary',
            DetailedDescription = '$detailed_description',
            ScientificName = '$scientific_name',
            CommonName = '$common_name',
            PotSize = '$pot_size',
            TotalHeight = '$total_height',
            DifficultyLevel = '$difficulty_level',
            LightRequirement = '$light_requirement',
            WateringFrequency = '$watering_frequency',
            Price = $price
            WHERE PlantID = $plant_id";
    
    if (mysqli_query($conn, $sql)) {
        // Xóa các danh mục cũ
        mysqli_query($conn, "DELETE FROM Plant_Categories WHERE PlantID = $plant_id");
        // Xóa các tags cũ
        mysqli_query($conn, "DELETE FROM Plant_Tags WHERE PlantID = $plant_id");
        
        // Thêm các danh mục mới
        if (!empty($categories)) {
            foreach ($categories as $category_id) {
                $category_id = (int)$category_id;
                mysqli_query($conn, "INSERT INTO Plant_Categories (PlantID, CategoryID) VALUES ($plant_id, $category_id)");
            }
        }
        
        // Thêm các tags mới
        if (!empty($tags)) {
            foreach ($tags as $tag_id) {
                $tag_id = (int)$tag_id;
                mysqli_query($conn, "INSERT INTO Plant_Tags (PlantID, TagID) VALUES ($plant_id, $tag_id)");
            }
        }
        
        $message = '<div class="alert alert-success">✅ Đã cập nhật cây, danh mục và thông tin chi tiết thành công!</div>';
        // Redirect để tránh resubmit
        header('Location: ?action=plants');
        exit;
    } else {
        $message = '<div class="alert alert-danger">❌ Lỗi: ' . mysqli_error($conn) . '</div>';
    }
}

// Xử lý xóa cây
if (isset($_GET['delete_plant'])) {
    $plant_id = (int)$_GET['delete_plant'];
    $sql = "DELETE FROM Plants WHERE PlantID = $plant_id";
    if (mysqli_query($conn, $sql)) {
        $message = '<div class="alert alert-success">✅ Đã xóa cây thành công!</div>';
    } else {
        $message = '<div class="alert alert-danger">❌ Lỗi: ' . mysqli_error($conn) . '</div>';
    }
}

// Lấy thông tin cây để sửa
$edit_plant = null;
$edit_plant_categories = [];
$edit_plant_tags = [];
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_sql = "SELECT * FROM Plants WHERE PlantID = $edit_id";
    $edit_result = mysqli_query($conn, $edit_sql);
    if ($edit_result && mysqli_num_rows($edit_result) > 0) {
        $edit_plant = mysqli_fetch_assoc($edit_result);
        
        // Lấy danh mục hiện tại của cây
        $cat_sql = "SELECT CategoryID FROM Plant_Categories WHERE PlantID = $edit_id";
        $cat_result = mysqli_query($conn, $cat_sql);
        while ($cat_row = mysqli_fetch_assoc($cat_result)) {
            $edit_plant_categories[] = $cat_row['CategoryID'];
        }
        
        // Lấy tags hiện tại của cây
        $tag_sql = "SELECT TagID FROM Plant_Tags WHERE PlantID = $edit_id";
        $tag_result = mysqli_query($conn, $tag_sql);
        while ($tag_row = mysqli_fetch_assoc($tag_result)) {
            $edit_plant_tags[] = $tag_row['TagID'];
        }
    }
}

// Lấy danh sách cây
$plants_sql = "SELECT p.*, GROUP_CONCAT(c.CategoryName SEPARATOR ', ') as Categories 
               FROM Plants p 
               LEFT JOIN Plant_Categories pc ON p.PlantID = pc.PlantID
               LEFT JOIN Categories c ON pc.CategoryID = c.CategoryID 
               GROUP BY p.PlantID 
               ORDER BY p.PlantID DESC";
$plants_result = mysqli_query($conn, $plants_sql);

// Lấy danh sách danh mục
$categories_sql = "SELECT * FROM Categories ORDER BY CategoryName";
$categories_result = mysqli_query($conn, $categories_sql);

// Lấy danh sách tags
$tags_sql = "SELECT * FROM Tags ORDER BY TagName";
$tags_result = mysqli_query($conn, $tags_sql);

// Lấy danh sách tư vấn nếu đang ở trang consultations
$consultations_result = null;
if ($action == 'consultations') {
    $consultations_sql = "SELECT * FROM Consultations ORDER BY CreatedAt DESC";
    $consultations_result = mysqli_query($conn, $consultations_sql);
}

// Thống kê
$stats_plants = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM Plants"))['total'];
$stats_categories = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM Categories"))['total'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Quản Trị - Cây Cảnh Văn Phòng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-green: #2d5a27;
            --accent-green: #7fb069;
            --light-green: #a7c957;
            --cream-bg: #f4f1e8;
        }
        
        .sidebar {
            background: var(--primary-green);
            min-height: 100vh;
            color: white;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: var(--accent-green);
            color: white;
        }
        
        .main-content {
            background: var(--cream-bg);
            min-height: 100vh;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-left: 4px solid var(--accent-green);
        }
        
        .btn-primary {
            background: var(--accent-green);
            border-color: var(--accent-green);
        }
        
        .btn-primary:hover {
            background: var(--primary-green);
            border-color: var(--primary-green);
        }
        
        .table th {
            background: var(--light-green);
            color: var(--primary-green);
            font-weight: 600;
        }
        
        .plant-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-3">
                    <h4 class="text-center mb-4">
                        <i class="bi bi-gear-fill me-2"></i>
                        Quản Trị
                    </h4>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link <?php echo $action == 'homepage' ? 'active' : ''; ?>" href="?action=homepage">
                            <i class="bi bi-house-gear me-2"></i>Quản lý trang chủ
                        </a>
                        <a class="nav-link <?php echo $action == 'plants' ? 'active' : ''; ?>" href="?action=plants">
                            <i class="bi bi-flower1 me-2"></i>Quản lý cây
                        </a>
                        <a class="nav-link <?php echo $action == 'categories' ? 'active' : ''; ?>" href="?action=categories">
                            <i class="bi bi-tags me-2"></i>Danh mục
                        </a>
                        <a class="nav-link <?php echo $action == 'consultations' ? 'active' : ''; ?>" href="?action=consultations">
                            <i class="bi bi-chat-dots me-2"></i>Tư vấn
                        </a>
                        <hr class="my-3">
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-house me-2"></i>Về trang chủ
                        </a>
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>
                        <?php 
                        switch($action) {
                            case 'homepage': echo '<i class="bi bi-house-gear me-2"></i>Quản lý trang chủ'; break;
                            case 'plants': echo '<i class="bi bi-flower1 me-2"></i>Quản lý cây cảnh'; break;
                            case 'categories': echo '<i class="bi bi-tags me-2"></i>Quản lý danh mục'; break;
                            case 'consultations': echo '<i class="bi bi-chat-dots me-2"></i>Quản lý tư vấn'; break;
                            default: echo '<i class="bi bi-house-gear me-2"></i>Quản lý trang chủ'; break;
                        }
                        ?>
                    </h2>
                    <div class="text-muted">
                        Xin chào, <strong><?php echo $_SESSION['user_name']; ?></strong>
                    </div>
                </div>
                
                <?php echo $message; ?>
                
                <?php if ($action == 'homepage'): ?>
                    <!-- Quản lý trang chủ -->
                    <div class="row">
                        <!-- Quản lý tiêu đề -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="bi bi-type me-2"></i>Chỉnh sửa tiêu đề</h5>
                                </div>
                                <div class="card-body">
                                    <?php
                                    // Lấy cài đặt hiện tại
                                    $settings_sql = "SELECT * FROM HomepageSettings ORDER BY SettingKey";
                                    $settings_result = mysqli_query($conn, $settings_sql);
                                    
                                    if ($settings_result && mysqli_num_rows($settings_result) > 0):
                                    ?>
                                    <form method="POST">
                                        <?php while($setting = mysqli_fetch_assoc($settings_result)): ?>
                                        <div class="mb-3">
                                            <label class="form-label">
                                                <strong><?php echo htmlspecialchars($setting['Description']); ?></strong>
                                            </label>
                                            <input type="text" class="form-control" 
                                                   name="setting_<?php echo $setting['SettingKey']; ?>" 
                                                   value="<?php echo htmlspecialchars($setting['SettingValue']); ?>"
                                                   placeholder="<?php echo htmlspecialchars($setting['Description']); ?>">
                                        </div>
                                        <?php endwhile; ?>
                                        <button type="submit" name="update_homepage_settings" class="btn btn-primary">
                                            <i class="bi bi-check-circle me-2"></i>Cập nhật tiêu đề
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        <strong>Chưa thiết lập:</strong> Vui lòng chạy <a href="create_homepage_settings.php" target="_blank">create_homepage_settings.php</a> trước để tạo bảng cài đặt.
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quản lý cây nổi bật -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="bi bi-star me-2"></i>Cây nổi bật</h5>
                                </div>
                                <div class="card-body">
                                    <?php
                                    // Kiểm tra bảng FeaturedPlants
                                    $featured_check = mysqli_query($conn, "SHOW TABLES LIKE 'FeaturedPlants'");
                                    if ($featured_check && mysqli_num_rows($featured_check) > 0):
                                        // Lấy danh sách cây nổi bật
                                        $featured_sql = "SELECT fp.*, p.PlantName, p.imageURL 
                                                        FROM FeaturedPlants fp 
                                                        JOIN Plants p ON fp.PlantID = p.PlantID 
                                                        WHERE fp.IsActive = 1 
                                                        ORDER BY fp.DisplayOrder";
                                        $featured_result = mysqli_query($conn, $featured_sql);
                                    ?>
                                    <div class="mb-3">
                                        <strong>Cây đang hiển thị trên trang chủ:</strong>
                                    </div>
                                    <?php if ($featured_result && mysqli_num_rows($featured_result) > 0): ?>
                                    <div class="list-group">
                                        <?php while($featured = mysqli_fetch_assoc($featured_result)): ?>
                                        <div class="list-group-item d-flex align-items-center">
                                            <img src="images/<?php echo htmlspecialchars($featured['imageURL'] ?: 'default-plant.jpg'); ?>" 
                                                 class="me-3" style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px;">
                                            <div class="flex-grow-1">
                                                <strong><?php echo htmlspecialchars($featured['PlantName']); ?></strong>
                                                <small class="text-muted d-block">Thứ tự: <?php echo $featured['DisplayOrder']; ?></small>
                                            </div>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="remove_featured" value="<?php echo $featured['FeaturedID']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('Bỏ cây này khỏi danh sách nổi bật?')">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </form>
                                        </div>
                                        <?php endwhile; ?>
                                    </div>
                                    <?php else: ?>
                                    <p class="text-muted">Chưa có cây nổi bật nào.</p>
                                    <?php endif; ?>
                                    
                                    <hr>
                                    <div class="mb-3">
                                        <strong>Thêm cây nổi bật:</strong>
                                    </div>
                                    <form method="POST">
                                        <div class="input-group">
                                            <select name="plant_id" class="form-select" required>
                                                <option value="">Chọn cây...</option>
                                                <?php
                                                // Lấy danh sách cây chưa nổi bật
                                                $available_sql = "SELECT p.PlantID, p.PlantName 
                                                                 FROM Plants p 
                                                                 LEFT JOIN FeaturedPlants fp ON p.PlantID = fp.PlantID AND fp.IsActive = 1
                                                                 WHERE fp.PlantID IS NULL 
                                                                 ORDER BY p.PlantName";
                                                $available_result = mysqli_query($conn, $available_sql);
                                                while($plant = mysqli_fetch_assoc($available_result)):
                                                ?>
                                                <option value="<?php echo $plant['PlantID']; ?>">
                                                    <?php echo htmlspecialchars($plant['PlantName']); ?>
                                                </option>
                                                <?php endwhile; ?>
                                            </select>
                                            <button type="submit" name="add_featured" class="btn btn-success">
                                                <i class="bi bi-plus"></i> Thêm
                                            </button>
                                        </div>
                                    </form>
                                    
                                    <hr>
                                    <div class="text-center">
                                        <form method="POST">
                                            <button type="submit" name="apply_changes" class="btn btn-primary btn-lg">
                                                <i class="bi bi-arrow-clockwise me-2"></i>Cập nhật trang chủ
                                            </button>
                                        </form>
                                        <small class="text-muted d-block mt-2">
                                            Nhấn để áp dụng thay đổi lên trang chủ
                                        </small>
                                    </div>
                                    <?php else: ?>
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        <strong>Chưa thiết lập:</strong> Vui lòng chạy <a href="create_homepage_settings.php" target="_blank">create_homepage_settings.php</a> trước để tạo bảng quản lý.
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                <?php elseif ($action == 'plants'): ?>
                    <!-- Quản lý cây -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <?php echo $edit_plant ? 'Sửa thông tin cây' : 'Thêm cây mới'; ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if ($edit_plant): ?>
                                        <!-- Form sửa cây -->
                                        <form method="POST">
                                            <input type="hidden" name="plant_id" value="<?php echo $edit_plant['PlantID']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Tên cây</label>
                                                <input type="text" class="form-control" name="plant_name" 
                                                       value="<?php echo htmlspecialchars($edit_plant['PlantName']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Tên file hình ảnh</label>
                                                <input type="text" class="form-control" name="image_url" 
                                                       value="<?php echo htmlspecialchars($edit_plant['imageURL']); ?>"
                                                       placeholder="vd: caykimtien.jpg">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Mô tả sơ bộ</label>
                                                <textarea class="form-control" name="summary" rows="3" placeholder="Mô tả ngắn gọn hiển thị ở trang danh sách"><?php echo htmlspecialchars($edit_plant['Summary']); ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Mô tả chi tiết</label>
                                                <textarea class="form-control" name="detailed_description" rows="8" 
                                                          placeholder="Nhập mô tả chi tiết về cây cảnh này. Mô tả sẽ hiển thị đầy đủ ở trang chi tiết sản phẩm..."
                                                          style="min-height: 200px;"><?php echo htmlspecialchars($edit_plant['DetailedDescription'] ?? ''); ?></textarea>
                                                <small class="text-muted">
                                                    <i class="bi bi-info-circle me-1"></i>
                                                    Mô tả chi tiết sẽ hiển thị ở trang sản phẩm. Bạn có thể viết nhiều đoạn văn, mỗi dòng mới sẽ tự động xuống hàng.
                                                </small>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Danh mục</label>
                                                <div class="border rounded p-2" style="max-height: 150px; overflow-y: auto;">
                                                    <?php 
                                                    mysqli_data_seek($categories_result, 0);
                                                    while($category = mysqli_fetch_assoc($categories_result)): 
                                                        $is_checked = in_array($category['CategoryID'], $edit_plant_categories);
                                                    ?>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" 
                                                               name="categories[]" value="<?php echo $category['CategoryID']; ?>"
                                                               id="cat_<?php echo $category['CategoryID']; ?>"
                                                               <?php echo $is_checked ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="cat_<?php echo $category['CategoryID']; ?>">
                                                            <?php echo htmlspecialchars($category['CategoryName']); ?>
                                                        </label>
                                                    </div>
                                                    <?php endwhile; ?>
                                                </div>
                                                <small class="text-muted">Chọn một hoặc nhiều danh mục cho cây này</small>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Tags đặc điểm</label>
                                                <div class="border rounded p-2" style="max-height: 120px; overflow-y: auto;">
                                                    <?php 
                                                    mysqli_data_seek($tags_result, 0);
                                                    while($tag = mysqli_fetch_assoc($tags_result)): 
                                                        $is_checked = in_array($tag['TagID'], $edit_plant_tags);
                                                    ?>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" 
                                                               name="tags[]" value="<?php echo $tag['TagID']; ?>"
                                                               id="tag_<?php echo $tag['TagID']; ?>"
                                                               <?php echo $is_checked ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="tag_<?php echo $tag['TagID']; ?>">
                                                            <span class="badge bg-info text-dark"><?php echo htmlspecialchars($tag['TagName']); ?></span>
                                                        </label>
                                                    </div>
                                                    <?php endwhile; ?>
                                                </div>
                                                <small class="text-muted">Chọn các đặc điểm phù hợp với cây này</small>
                                            </div>
                                            
                                            <!-- Thông tin chi tiết -->
                                            <hr class="my-4">
                                            <h6 class="text-primary mb-3">Thông tin chi tiết hiển thị</h6>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Tên khoa học</label>
                                                    <input type="text" class="form-control" name="scientific_name" 
                                                           value="<?php echo htmlspecialchars($edit_plant['ScientificName'] ?? ''); ?>"
                                                           placeholder="Ví dụ: Zamioculcas zamiifolia">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Tên thông thường</label>
                                                    <input type="text" class="form-control" name="common_name" 
                                                           value="<?php echo htmlspecialchars($edit_plant['CommonName'] ?? ''); ?>"
                                                           placeholder="Ví dụ: Cây Kim Tiền">
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">Chiều cao tổng</label>
                                                    <input type="text" class="form-control" name="total_height" 
                                                           value="<?php echo htmlspecialchars($edit_plant['TotalHeight'] ?? '50 cm'); ?>">
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Độ khó</label>
                                                    <input type="text" class="form-control" name="difficulty_level" 
                                                           value="<?php echo htmlspecialchars($edit_plant['DifficultyLevel'] ?? 'Trung bình'); ?>">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Yêu cầu ánh sáng</label>
                                                    <input type="text" class="form-control" name="light_requirement" 
                                                           value="<?php echo htmlspecialchars($edit_plant['LightRequirement'] ?? 'Nắng trực tiếp / nắng tán xạ'); ?>">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Nhu cầu nước</label>
                                                    <input type="text" class="form-control" name="watering_frequency" 
                                                           value="<?php echo htmlspecialchars($edit_plant['WateringFrequency'] ?? 'Tưới nước 1-2 lần/tuần'); ?>">
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Giá tiền (VNĐ)</label>
                                                <input type="number" class="form-control" name="price" 
                                                       value="<?php echo $edit_plant['Price'] ?? 500000; ?>" min="0" step="1000">
                                            </div>
                                            
                                            <div class="d-grid gap-2">
                                                <button type="submit" name="update_plant" class="btn btn-success">
                                                    <i class="bi bi-check-circle me-2"></i>Cập nhật cây
                                                </button>
                                                <a href="?action=plants" class="btn btn-secondary">
                                                    <i class="bi bi-x-circle me-2"></i>Hủy
                                                </a>
                                            </div>
                                        </form>
                                    <?php else: ?>
                                        <!-- Form thêm cây mới -->
                                        <form method="POST">
                                            <div class="mb-3">
                                                <label class="form-label">Tên cây</label>
                                                <input type="text" class="form-control" name="plant_name" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Tên file hình ảnh</label>
                                                <input type="text" class="form-control" name="image_url" placeholder="vd: caykimtien.jpg">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Mô tả sơ bộ</label>
                                                <textarea class="form-control" name="summary" rows="3" placeholder="Mô tả ngắn gọn hiển thị ở trang danh sách"></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Mô tả chi tiết</label>
                                                <textarea class="form-control" name="detailed_description" rows="8" 
                                                          placeholder="Nhập mô tả chi tiết về cây cảnh này. Mô tả sẽ hiển thị đầy đủ ở trang chi tiết sản phẩm..."
                                                          style="min-height: 200px;"></textarea>
                                                <small class="text-muted">
                                                    <i class="bi bi-info-circle me-1"></i>
                                                    Mô tả chi tiết sẽ hiển thị ở trang sản phẩm. Bạn có thể viết nhiều đoạn văn, mỗi dòng mới sẽ tự động xuống hàng.
                                                </small>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Danh mục</label>
                                                <div class="border rounded p-2" style="max-height: 150px; overflow-y: auto;">
                                                    <?php 
                                                    mysqli_data_seek($categories_result, 0);
                                                    while($category = mysqli_fetch_assoc($categories_result)): 
                                                    ?>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" 
                                                               name="categories[]" value="<?php echo $category['CategoryID']; ?>"
                                                               id="new_cat_<?php echo $category['CategoryID']; ?>">
                                                        <label class="form-check-label" for="new_cat_<?php echo $category['CategoryID']; ?>">
                                                            <?php echo htmlspecialchars($category['CategoryName']); ?>
                                                        </label>
                                                    </div>
                                                    <?php endwhile; ?>
                                                </div>
                                                <small class="text-muted">Chọn một hoặc nhiều danh mục cho cây này</small>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Tags đặc điểm</label>
                                                <div class="border rounded p-2" style="max-height: 120px; overflow-y: auto;">
                                                    <?php 
                                                    mysqli_data_seek($tags_result, 0);
                                                    while($tag = mysqli_fetch_assoc($tags_result)): 
                                                    ?>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" 
                                                               name="tags[]" value="<?php echo $tag['TagID']; ?>"
                                                               id="new_tag_<?php echo $tag['TagID']; ?>">
                                                        <label class="form-check-label" for="new_tag_<?php echo $tag['TagID']; ?>">
                                                            <span class="badge bg-info text-dark"><?php echo htmlspecialchars($tag['TagName']); ?></span>
                                                        </label>
                                                    </div>
                                                    <?php endwhile; ?>
                                                </div>
                                                <small class="text-muted">Chọn các đặc điểm phù hợp với cây này</small>
                                            </div>
                                            <button type="submit" name="add_plant" class="btn btn-primary w-100">
                                                <i class="bi bi-plus-circle me-2"></i>Thêm cây
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Danh sách cây (<?php echo mysqli_num_rows($plants_result); ?>)</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Hình ảnh</th>
                                                    <th>Tên cây</th>
                                                    <th>Danh mục</th>
                                                    <th>Thao tác</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                mysqli_data_seek($plants_result, 0);
                                                while($plant = mysqli_fetch_assoc($plants_result)): 
                                                ?>
                                                <tr>
                                                    <td><?php echo $plant['PlantID']; ?></td>
                                                    <td>
                                                        <?php 
                                                        $image_path = isset($plant['imageURL']) && !empty($plant['imageURL']) ? $plant['imageURL'] : 'default-plant.jpg';
                                                        if (file_exists('images/' . $image_path)): 
                                                        ?>
                                                            <img src="images/<?php echo htmlspecialchars($image_path); ?>" class="plant-image" alt="<?php echo htmlspecialchars($plant['PlantName']); ?>">
                                                        <?php else: ?>
                                                            <img src="images/default-plant.jpg" class="plant-image" alt="Hình ảnh mặc định">
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($plant['PlantName']); ?></td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <?php echo htmlspecialchars($plant['Categories'] ?: 'Chưa phân loại'); ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <a href="?action=plants&edit=<?php echo $plant['PlantID']; ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <a href="?action=plants&delete_plant=<?php echo $plant['PlantID']; ?>" 
                                                           class="btn btn-sm btn-outline-danger"
                                                           onclick="return confirm('Bạn có chắc muốn xóa cây này?')">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                <?php elseif ($action == 'categories'): ?>
                    <!-- Quản lý danh mục -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Thêm danh mục mới</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <div class="mb-3">
                                            <label class="form-label">Tên danh mục</label>
                                            <input type="text" class="form-control" name="category_name" 
                                                   placeholder="Ví dụ: Cây văn phòng" required>
                                            <small class="text-muted">Tên danh mục phải là duy nhất</small>
                                        </div>
                                        <button type="submit" name="add_category" class="btn btn-primary w-100">
                                            <i class="bi bi-plus-circle me-2"></i>Thêm danh mục
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Danh sách danh mục (<?php echo mysqli_num_rows($categories_result); ?>)</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Tên danh mục</th>
                                                    <th>Số lượng cây</th>
                                                    <th>Thao tác</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                mysqli_data_seek($categories_result, 0);
                                                while($category = mysqli_fetch_assoc($categories_result)): 
                                                ?>
                                                <?php
                                                $count_sql = "SELECT COUNT(*) as total FROM Plant_Categories WHERE CategoryID = " . $category['CategoryID'];
                                                $count_result = mysqli_query($conn, $count_sql);
                                                $plant_count = mysqli_fetch_assoc($count_result)['total'];
                                                ?>
                                                <tr>
                                                    <td><?php echo $category['CategoryID']; ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($category['CategoryName']); ?></strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success"><?php echo $plant_count; ?> cây</span>
                                                    </td>
                                                    <td>
                                                        <a href="products.php?category=<?php echo urlencode($category['CategoryName']); ?>" 
                                                           class="btn btn-sm btn-outline-info me-1" target="_blank">
                                                            <i class="bi bi-eye"></i> Xem
                                                        </a>
                                                        <?php if ($plant_count == 0): ?>
                                                        <a href="?action=categories&delete_category=<?php echo $category['CategoryID']; ?>" 
                                                           class="btn btn-sm btn-outline-danger"
                                                           onclick="return confirm('Bạn có chắc muốn xóa danh mục \'<?php echo htmlspecialchars($category['CategoryName']); ?>\'?')">
                                                            <i class="bi bi-trash"></i> Xóa
                                                        </a>
                                                        <?php else: ?>
                                                        <button class="btn btn-sm btn-outline-secondary" disabled 
                                                                title="Không thể xóa danh mục có cây">
                                                            <i class="bi bi-lock"></i> Khóa
                                                        </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                <?php elseif ($action == 'consultations'): ?>
                    <!-- Quản lý tư vấn -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Danh sách yêu cầu tư vấn</h5>
                            <span class="badge bg-info">
                                <?php echo $consultations_result ? mysqli_num_rows($consultations_result) : 0; ?> yêu cầu
                            </span>
                        </div>
                        <div class="card-body">
                            <?php if ($consultations_result && mysqli_num_rows($consultations_result) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Thông tin khách hàng</th>
                                            <th>Liên hệ</th>
                                            <th>Nội dung tư vấn</th>
                                            <th>Trạng thái</th>
                                            <th>Ngày gửi</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($consultation = mysqli_fetch_assoc($consultations_result)): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary">#<?php echo $consultation['ConsultationID']; ?></span>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($consultation['FullName']); ?></strong>
                                                    <br><small class="text-muted">
                                                        <i class="bi bi-geo-alt me-1"></i>
                                                        <?php echo htmlspecialchars($consultation['Address']); ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <a href="mailto:<?php echo htmlspecialchars($consultation['Email']); ?>" 
                                                       class="btn btn-sm btn-outline-primary mb-1">
                                                        <i class="bi bi-envelope me-1"></i>Email
                                                    </a>
                                                    <br>
                                                    <a href="tel:<?php echo htmlspecialchars($consultation['Phone']); ?>" 
                                                       class="btn btn-sm btn-outline-success">
                                                        <i class="bi bi-telephone me-1"></i>Gọi
                                                    </a>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="max-width: 250px;">
                                                    <p class="mb-1">
                                                        <?php 
                                                        $msg = htmlspecialchars($consultation['Message']);
                                                        echo strlen($msg) > 80 ? substr($msg, 0, 80) . '...' : $msg;
                                                        ?>
                                                    </p>
                                                    <button class="btn btn-sm btn-outline-info" 
                                                            onclick="showFullMessage(<?php echo $consultation['ConsultationID']; ?>)">
                                                        <i class="bi bi-eye me-1"></i>Xem đầy đủ
                                                    </button>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                $status_colors = [
                                                    'pending' => 'warning',
                                                    'processing' => 'info', 
                                                    'completed' => 'success'
                                                ];
                                                $status_texts = [
                                                    'pending' => 'Chờ xử lý',
                                                    'processing' => 'Đang xử lý',
                                                    'completed' => 'Hoàn thành'
                                                ];
                                                $status = $consultation['Status'];
                                                ?>
                                                <span class="badge bg-<?php echo $status_colors[$status]; ?>">
                                                    <?php echo $status_texts[$status]; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small>
                                                    <?php echo date('d/m/Y', strtotime($consultation['CreatedAt'])); ?>
                                                    <br>
                                                    <?php echo date('H:i', strtotime($consultation['CreatedAt'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="consultation_id" value="<?php echo $consultation['ConsultationID']; ?>">
                                                    <select name="status" class="form-select form-select-sm mb-1" onchange="this.form.submit()">
                                                        <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                                                        <option value="processing" <?php echo $status == 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                                                        <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                                                    </select>
                                                    <input type="hidden" name="update_consultation_status" value="1">
                                                </form>
                                                
                                                <?php if ($status == 'completed'): ?>
                                                <!-- Nút xóa chỉ hiện với trạng thái hoàn thành -->
                                                <a href="?action=consultations&delete_consultation=<?php echo $consultation['ConsultationID']; ?>" 
                                                   class="btn btn-sm btn-outline-danger mt-1"
                                                   onclick="return confirm('Bạn có chắc muốn xóa yêu cầu tư vấn này?\n\nLưu ý: Chỉ có thể xóa yêu cầu đã hoàn thành.')">
                                                    <i class="bi bi-trash me-1"></i>Xóa
                                                </a>
                                                <?php else: ?>
                                                <!-- Placeholder để giữ layout đồng đều -->
                                                <div class="mt-1" style="height: 31px;"></div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        
                                        <!-- Hidden row for full message -->
                                        <tr id="fullMessage<?php echo $consultation['ConsultationID']; ?>" style="display: none;">
                                            <td colspan="7">
                                                <div class="alert alert-light">
                                                    <h6><i class="bi bi-chat-quote me-2"></i>Nội dung tư vấn đầy đủ:</h6>
                                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($consultation['Message'])); ?></p>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                <p class="text-muted mt-2">Chưa có yêu cầu tư vấn nào</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showFullMessage(consultationId) {
            const row = document.getElementById('fullMessage' + consultationId);
            if (row.style.display === 'none') {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }
        }
    </script>
</body>
</html>