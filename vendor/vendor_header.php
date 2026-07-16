<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include("../config/database.php");
include("../auth/vendor.php");

// Fetch vendor info
$user_id = $_SESSION['user_id'];
$fullname = $_SESSION['fullname'];
$check = $conn->query("SELECT * FROM vendors WHERE user_id = '$user_id'");
if ($check->num_rows == 0) {
    $store_name = $conn->real_escape_string(trim($fullname) . "'s Shop");
    $conn->query("INSERT INTO vendors (user_id, store_name, store_description) VALUES ('$user_id', '$store_name', 'Fresh produce and high-quality items.')");
    $check = $conn->query("SELECT * FROM vendors WHERE user_id = '$user_id'");
}
$vendor = $check->fetch_assoc();
$vendor_id = $vendor['id'];

// Helper to check active nav item
function is_active($page_name) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return ($current_page == $page_name) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Vendor Panel'; ?> | Eco Market</title>
    <link rel="stylesheet" href="../css/vendor.css">
</head>
<body class="vendor-body">

<header class="vendor-topbar">
    <a href="dashboard.php" class="vendor-logo">🌿 Eco Market <span style="font-size: 14px; font-weight: normal; color: #667e65;">Vendor Panel</span></a>
    <nav class="vendor-nav">
        <a href="dashboard.php" class="<?php echo is_active('dashboard.php'); ?>">Dashboard</a>
        <a href="products.php" class="<?php echo is_active('products.php'); ?> <?php echo is_active('add_product.php'); ?> <?php echo is_active('edit_product.php'); ?>">Products</a>
        <a href="categories.php" class="<?php echo is_active('categories.php'); ?>">Categories</a>
        <a href="inventory.php" class="<?php echo is_active('inventory.php'); ?>">Inventory</a>
        <a href="bulk_upload.php" class="<?php echo is_active('bulk_upload.php'); ?>">Bulk Upload</a>
        <a href="subscription.php" class="<?php echo is_active('subscription.php'); ?>">Subscription</a>
        <a href="profile.php" class="<?php echo is_active('profile.php'); ?>">Store Settings</a>
        <a href="../logout.php">Logout</a>
    </nav>
    <div class="vendor-user-badge">
        🛍️ <?php echo htmlspecialchars($vendor['store_name']); ?>
    </div>
</header>
