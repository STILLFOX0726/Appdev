<?php 
session_start();

require "dbconnection.php";

// Mock products data
$products = [
    1 => [
        'name' => 'sunscree',
        'price' => 44.00,
        'platform' => 'Light Lotion'
    ],
    2 => [
        'name' => 'Aveeno',
        'price' => 249.99,
        'platform' => 'Body Wash'
    ],
    3 => [
        'name' => 'Anti-acne',
        'price' => 249.99,
        'platform' => 'Facial wash'
    ]
];

// Initialize variables
$uploadOk = 0;
$target_dir = "uploads/";

// Handle adding to cart from index page
if (isset($_POST['add_to_cart'])) {
    $productId = $_POST['id'];
    $quantity = 1; // Default quantity
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
}

// dito naman yung Handle avatar upload
if (isset($_POST['upload_avatar']) && isset($_FILES["avatar"])) {
    if (!file_exists($target_dir)) {
        if (!mkdir($target_dir, 0755, true)) {
            $_SESSION['avatar_error'] = "Failed to create upload directory.";
        }
    }
    
    $target_file = $target_dir . basename($_FILES["avatar"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["avatar"]["tmp_name"]);
    if($check === false) {
        $uploadOk = 0;
        $_SESSION['avatar_error'] = "File is not an image.";
    }
    
    // Check file size (max 2MB)
    if ($_FILES["avatar"]["size"] > 432000) { // 2MB in bytes
        $uploadOk = 0;
        $_SESSION['avatar_error'] = "File is too large (max 2MB).";
    }
    
    // Allow certain file formats
    $allowedExtensions = ["jpg", "png", "jpeg", "gif"];
    if(!in_array($imageFileType, $allowedExtensions)) {
        $uploadOk = 0;
        $_SESSION['avatar_error'] = "Only JPG, JPEG, PNG & GIF files are allowed.";
    }
    
    // If everything is ok, try to upload file
    if ($uploadOk == 1) {
        // Delete old avatar if exists
        if (isset($_SESSION['avatar_path']) && file_exists($_SESSION['avatar_path'])) {
            unlink($_SESSION['avatar_path']);
        }
        
        // Generate unique filename
        $new_filename = uniqid() . '.' . $imageFileType;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
            $_SESSION['avatar_path'] = $target_file;
            $_SESSION['avatar_success'] = "Avatar uploaded successfully!";
            
            // Update database if user is logged in
            if (isset($_SESSION['username'])) {
                $stmt = $conn->prepare("UPDATE user_table SET profile_image = ? WHERE username = ?");
                $stmt->bind_param("ss", $target_file, $_SESSION['username']);
                if (!$stmt->execute()) {
                    $_SESSION['avatar_error'] = "Failed to update profile in database.";
                }
                $stmt->close();
            }
        } else {
            $_SESSION['avatar_error'] = "Sorry, there was an error uploading your file.";
        }
    }
}

// Handle avatar deletion
if (isset($_POST['delete_avatar'])) {
    if (isset($_SESSION['avatar_path']) && file_exists($_SESSION['avatar_path'])) {
        if (unlink($_SESSION['avatar_path'])) {
            unset($_SESSION['avatar_path']);
            $_SESSION['avatar_success'] = "Avatar removed successfully!";
            
            // Update database if user is logged in
            if (isset($_SESSION['username'])) {
                $stmt = $conn->prepare("UPDATE user_table SET profile_image = NULL WHERE username = ?");
                $stmt->bind_param("s", $_SESSION['username']);
                if (!$stmt->execute()) {
                    $_SESSION['avatar_error'] = "Failed to update profile in database.";
                }
                $stmt->close();
            }
        } else {
            $_SESSION['avatar_error'] = "Failed to delete avatar file.";
        }
    }
}

// Handle avatar deletion
if (isset($_POST['delete_avatar'])) {
    if (isset($_SESSION['avatar_path']) && file_exists($_SESSION['avatar_path'])) {
        unlink($_SESSION['avatar_path']);
        unset($_SESSION['avatar_path']);
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        /* Previous styles remain the same */
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #fff;
        }

        .dashboard {
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: linear-gradient(to bottom, #78f5c5, #0056b3);
            color: white;
            padding: 30px;
        }

        .sidebar .user {
            margin-bottom: 30px;
        }

        .sidebar .menu {
            list-style: none;
            padding: 0;
        }

        .sidebar .menu li {
            padding: 10px 0;
            cursor: pointer;
            opacity: 0.8;
        }

        .sidebar .menu li:hover {
            font-weight: bold;
            opacity: 1;
        }

        .main {
            flex-grow: 1;
            padding: 30px;
            background: #e6eaed;
            display: flex;
            flex-direction: column;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .search-container {
            display: flex;
            align-items: center;
            width: 60%;
        }

        .search-container input {
            flex-grow: 1;
            padding: 10px;
            border-radius: 5px 0 0 5px;
            border: 1px solid #ddd;
            border-right: none;
        }

        .filter-btn {
            background: #f8f9fa;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 0 5px 5px 0;
            cursor: pointer;
            display: flex;
            align-items: center;
        }

        .filter-btn:hover {
            background: #e9ecef;
        }

        .filter-dropdown {
            position: absolute;
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            margin-top: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            z-index: 100;
        }

        .filter-dropdown div {
            padding: 5px 10px;
            cursor: pointer;
        }

        .filter-dropdown div:hover {
            background: #f8f9fa;
        }

        .top-bar button {
            background: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .product-card {
            background: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .product-card img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin-bottom: 10px;
        }

        .product-card button {
            background: #007bff;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .cart-summary {
            margin-top: 30px;
            background: white;
            padding: 20px;
            border-radius: 10px;
        }

        .cart-item {
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .go-to-cart {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 15px;
            background-color: #5f4dee;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
        }
        
        .avatar-upload-form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        
        .avatar-actions {
            display: flex;
            gap: 10px;
        }
        
        .avatar-actions button {
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .delete-avatar {
            background-color: #dc3545 !important;
        }

        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
        }
        
        .avatar-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid white;
            font-size: 24px;
        }
        
        .avatar-upload-form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        
        .avatar-actions {
            display: flex;
            gap: 10px;
        }
        
        .avatar-actions button {
            padding: 5px 10px;
            font-size: 12px;
            cursor: pointer;
        }
        
        .delete-avatar {
            background-color: #dc3545 !important;
        }
        
        #avatar {
            display: none;
        }
        
        .error-message {
            color: #ff6b6b;
            font-size: 12px;
            margin-top: 5px;
        }
    .bi-search{

        display: flex;
        justify-content: left;
        width:  16px;
        height: 16px;
        float: left;
        position: relative;
        left: -20px;
    }
    .bi-gear{
        display: flex;
        justify-content: left;
        width:  16px;
        height: 16px;
        float: left;
        position: relative;
        left: -20px;
        }
    .bi-box-arrow-in-left{
        display: flex;
        justify-content: left;
        width:  16px;
        height: 16px;
        float: left;
        position: relative;
        left: -20px;
    }
    .bi-cart4{
        display: flex;
        justify-content: left;
        width:  16px;
        height: 16px;
        float: left;
        position: relative;
        left: -20px;
    }
    .bi-check2-circle{
        display: flex;
        justify-content: left;
        width:  16px;
        height: 16px;
        float: left;
        position: relative;
        left: -20px;
    }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="user">
            <div class="avatar-container" style="position: relative; display: inline-block;">
            <?php if (isset($_SESSION['avatar_path']) && file_exists($_SESSION['avatar_path'])): ?>
    <img src="<?= $_SESSION['avatar_path'] ?>" alt="Avatar" class="avatar">
    <img src="<?= htmlspecialchars($target_dir . $new_filename) ?>" alt="Avatar" class="avatar">
    <img src="/uploads/<?= htmlspecialchars(basename(path: $_SESSION['avatar_path'])) ?>" alt="Avatar" class="avatar">

<?php else: ?>
    <div class="avatar-placeholder">JP</div>
    <button type="button" onclick="document.getElementById('avatar').click()" style="position: absolute;bottom: 0;right: 0;background: #007bff;border: none;border-radius: 50%;padding: 6px;cursor: pointer;color: white;"title="Change avatar">
    <i class="bi bi-pencil-square"></i>
    </button>
    <?php endif; ?>
                </div>
                <form class="avatar-upload-form" method="post" enctype="multipart/form-data">
                    <input type="file" name="avatar" id="avatar" accept="image/*">
                    <div class="avatar-actions">
                        <?php if (isset($_SESSION['avatar_path'])): ?>
                            <button type="submit" name="delete_avatar" class="delete-avatar">Delete</button>
                        <?php endif; ?>
                    </div>
                    <button type="submit" name="upload_avatar" style="display: none;" id="upload-btn"></button>
                    <?php if (isset($_POST['upload_avatar']) && isset($uploadOk) && $uploadOk == 0): ?>
                        <div class="error-message">Error: Invalid image file (max 2MB, JPG/PNG/GIF only)</div>
                    <?php endif; ?>
                </form>
            </div>
            <ul class="menu">
            <li>Updates
            <svg xmlns="http://www.w3.org/2000/svg"  fill="currentColor" class="bi-check2-circle" viewBox="0 0 16 16">
            <path d="M2.5 8a5.5 5.5 0 0 1 8.25-4.764.5.5 0 0 0 .5-.866A6.5 6.5 0 1 0 14.5 8a.5.5 0 0 0-1 0 5.5 5.5 0 1 1-11 0"/>
            <path d="M15.354 3.354a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0z"/>
            </svg>
            </li>
                <li>View Orders
                <svg xmlns="http://www.w3.org/2000/svg"fill="currentColor" class="bi-cart4" viewBox="0 0 16 16">
                <path d="M0 2.5A.5.5 0 0 1 .5 2H2a.5.5 0 0 1 .485.379L2.89 4H14.5a.5.5 0 0 1 .485.621l-1.5 6A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.485-.379L1.61 3H.5a.5.5 0 0 1-.5-.5M3.14 5l.5 2H5V5zM6 5v2h2V5zm3 0v2h2V5zm3 0v2h1.36l.5-2zm1.11 3H12v2h.61zM11 8H9v2h2zM8 8H6v2h2zM5 8H3.89l.5 2H5zm0 5a1 1 0 1 0 0 2 1 1 0 0 0 0-2m-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0m9-1a1 1 0 1 0 0 2 1 1 0 0 0 0-2m-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0"/>
                </svg>
                </li>
                
                    <li><a href="Account_setting.php" style="color: white; text-decoration: none;">Account Setting
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi-gear" viewBox="0 0 16 16">
                <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0"/>
                <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z"/>
                </svg></li>
                        <li><a href="dash.php" style="color: white; text-decoration: none;">Logout</a>
                        <svg xmlns="http://www.w3.org/2000/svg"fill="currentColor" class="bi-box-arrow-in-left" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M10 3.5a.5.5 0 0 0-.5-.5h-8a.5.5 0 0 0-.5.5v9a.5.5 0 0 0 .5.5h8a.5.5 0 0 0 .5-.5v-2a.5.5 0 0 1 1 0v2A1.5 1.5 0 0 1 9.5 14h-8A1.5 1.5 0 0 1 0 12.5v-9A1.5 1.5 0 0 1 1.5 2h8A1.5 1.5 0 0 1 11 3.5v2a.5.5 0 0 1-1 0z"/>
                        <path fill-rule="evenodd" d="M4.146 8.354a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H14.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708z"/>
                        </svg></li>
                        </ul>

        </aside>

        <!-- Main Content -->
        <main class="main">
            <div class="top-bar">
                <div class="search-container">
                    <input type="text" placeholder="Search..."id="search-input">
                    <svg xmlns="http://www.w3.org/2000/svg"fill="currentColor" class="bi-search" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                            </svg>
                    <div class="filter-container" style="position: relative;">
                        <button class="filter-btn" id="filter-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-filter-square-fill" viewBox="0 0 16 16"style= "left: -30px;">
                                <path d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zm.5 5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1 0-1M4 8.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m2 3a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5"/>
                            </svg>
                            <div style="display: flex; align-items: center;">
                            
    <!-- Other content -->
                        </div>
                        </button>
                        <div class="filter-dropdown" id="filter-dropdown" style="display: none;">
                            <div onclick="sortProducts('name', 'asc')">Ascending (A-Z)</div>
                            <div onclick="sortProducts('name', 'desc')">Descending (Z-A)</div>
                            <div onclick="sortProducts('price', 'asc')">Ascending (₱)</div>
                            <div onclick="sortProducts('price', 'desc')">Descending (₱₱₱)</div>
                        </div>
                    </div>
                </div>
                <button>+ Add New List</button>
            </div>

            <div class="products" id="products-container">
                <?php 
                // Get sorting parameters from URL or use defaults
                $sortBy = $_GET['sort_by'] ?? 'name';
                $sortOrder = $_GET['sort_order'] ?? 'asc';
                
                // Sort products array
                usort($products, function($a, $b) use ($sortBy, $sortOrder) {
                    if ($sortOrder === 'asc') {
                        return $a[$sortBy] <=> $b[$sortBy];
                    } else {
                        return $b[$sortBy] <=> $a[$sortBy];
                    }
                });
                
                foreach ($products as $id => $product): ?>
                    <div class="product-card">
                        <h4><?= htmlspecialchars($product['name']) ?></h4>
                        <p>₱<?= number_format($product['price'], 2) ?></p>
                        <p><?= htmlspecialchars($product['platform']) ?></p>
                        <form method="post" action="">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <button type="submit" name="add_to_cart">Buy Now</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Cart Summary -->
            <div class="cart-summary">
                <h2>🛒 Your Cart</h2>
                <?php if (!empty($_SESSION['cart'])): ?>
                    <?php 
                        $total = 0;
                        foreach ($_SESSION['cart'] as $id => $qty):
                            if (!isset($products[$id])) continue;
                            $item = $products[$id];
                            $subtotal = $item['price'] * $qty;
                            $total += $subtotal;
                    ?>
                        <div class="cart-item">
                            <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                            Quantity: <?= $qty ?><br>
                            Subtotal: ₱<?= number_format($subtotal, 2) ?><br>
                            <a href="cart.php?action=remove&id=<?= $id ?>" style="color:red;">Remove</a>
                        </div>
                    <?php endforeach; ?>
                    <p><strong>Total: ₱<?= number_format($total, 2) ?></strong></p>
                    <a href="cart.php" class="go-to-cart">Go to Cart</a>
                <?php else: ?>
                    <p>Your cart is empty</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Toggle filter dropdown
        document.getElementById('filter-btn').addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdown = document.getElementById('filter-dropdown');
            dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            document.getElementById('filter-dropdown').style.display = 'none';
        });

        // Sorting function
        function sortProducts(sortBy, sortOrder) {
            window.location.href = window.location.pathname + `?sort_by=${sortBy}&sort_order=${sortOrder}`;
        }

        // ito naman yung Auto-submit form when file is selected
        document.getElementById('avatar').addEventListener('change', function() {
            if (this.files && this.files[0]) {
                if (this.files[0].size > 2432,000) {
                    alert('File size exceeds 3MB limit');
                    this.value = '';
                } else {
                    document.getElementById('upload-btn').click();
                }
            }
        });

        // ito ay Search functionality
        document.getElementById('search-input').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const productCards = document.querySelectorAll('.product-card');
            
            productCards.forEach(card => {
                const name = card.querySelector('h4').textContent.toLowerCase();
                const price = card.querySelector('p:nth-of-type(1)').textContent.toLowerCase();
                const platform = card.querySelector('p:nth-of-type(2)').textContent.toLowerCase();
                
                if (name.includes(searchTerm) || price.includes(searchTerm) || platform.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
