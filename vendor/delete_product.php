<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include("../config/database.php");
include("../auth/vendor.php");

// Fetch vendor info to secure deletion
$user_id = $_SESSION['user_id'];
$vendor_query = $conn->query("SELECT id FROM vendors WHERE user_id = '$user_id'");
if ($vendor_query->num_rows == 0) {
    header("Location: ../login.php");
    exit();
}
$vendor_id = $vendor_query->fetch_assoc()['id'];

if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    
    // Check ownership
    $prod_query = $conn->query("SELECT image_url FROM products WHERE id = '$product_id' AND vendor_id = '$vendor_id'");
    if ($prod_query->num_rows > 0) {
        $product = $prod_query->fetch_assoc();
        
        // Delete image file from server if it exists
        if ($product['image_url'] && file_exists('../' . $product['image_url'])) {
            unlink('../' . $product['image_url']);
        }
        
        // Delete record
        $conn->query("DELETE FROM products WHERE id = '$product_id' AND vendor_id = '$vendor_id'");
    }
}

header("Location: products.php");
exit();
?>
