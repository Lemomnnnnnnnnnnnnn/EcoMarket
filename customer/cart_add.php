<?php
include("../auth/customer.php");
include("../config/database.php");

$user_id = $_SESSION['user_id'];

$product_id = isset($_REQUEST['product_id']) ? intval($_REQUEST['product_id']) : 0;
$quantity = isset($_REQUEST['quantity']) ? intval($_REQUEST['quantity']) : 1;

$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

if ($quantity < 1) {
    $quantity = 1;
}

$success = false;
$error_message = "";

if ($product_id > 0) {
    $check = $conn->query("SELECT stock FROM products WHERE id='$product_id'");

    if ($check && $check->num_rows == 1) {
        $product = $check->fetch_assoc();

        if ($product['stock'] > 0) {
            $sql = "INSERT INTO cart (user_id, product_id, quantity)
                    VALUES ('$user_id', '$product_id', '$quantity')
                    ON DUPLICATE KEY UPDATE quantity = quantity + '$quantity'";

            $conn->query($sql);
            $success = true;
        } else {
            $error_message = "This product is out of stock.";
        }
    } else {
        $error_message = "Product not found.";
    }
} else {
    $error_message = "Invalid product.";
}

if ($is_ajax) {
    $cart_count_query = $conn->query("SELECT SUM(quantity) as cnt FROM cart WHERE user_id = '$user_id'");
    $cart_count = $cart_count_query->fetch_assoc()['cnt'];
    $cart_count = $cart_count ? (int)$cart_count : 0;

    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'cart_count' => $cart_count,
        'message' => $success ? 'Added to cart.' : $error_message
    ]);
    exit();
}

header("Location: cart.php");
exit();
?>
