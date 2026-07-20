<?php
include("../auth/customer.php");
include("../config/database.php");

$user_id = $_SESSION['user_id'];
$error = "";

/* ---------- Load cart items ---------- */
$sql = "SELECT cart.id AS cart_id, cart.quantity, products.id AS product_id,
               products.name, products.price, products.stock, products.image_url
        FROM cart
        JOIN products ON cart.product_id = products.id
        WHERE cart.user_id = '$user_id'";

$result = $conn->query($sql);

$cart_items = [];
$total = 0;

while ($row = $result->fetch_assoc()) {
    $row['line_total'] = $row['price'] * $row['quantity'];
    $total += $row['line_total'];
    $cart_items[] = $row;
}

/* Nothing to checkout, or something is out of stock -> bounce back to cart */
if (count($cart_items) == 0) {
    header("Location: cart.php");
    exit();
}

foreach ($cart_items as $item) {
    if ($item['stock'] <= 0 || $item['quantity'] > $item['stock']) {
        header("Location: cart.php");
        exit();
    }
}

/* ---------- Handle order placement ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $shipping_name = trim($_POST['shipping_name']);
    $shipping_address = trim($_POST['shipping_address']);
    $shipping_phone = trim($_POST['shipping_phone']);
    $payment_method = in_array($_POST['payment_method'], ['card', 'ewallet', 'cod']) ? $_POST['payment_method'] : 'card';

    if ($shipping_name === '' || $shipping_address === '' || $shipping_phone === '') {
        $error = "Please fill in all shipping details.";
    } else {

        $order_number = 'ECO' . date('Ymd') . strtoupper(substr(uniqid(), -6));
        $payment_status = ($payment_method === 'cod') ? 'unpaid' : 'unpaid';
        $order_status = ($payment_method === 'cod') ? 'processing' : 'pending';

        $stmt = $conn->prepare("INSERT INTO orders
            (user_id, order_number, total_amount, status, shipping_name, shipping_address, shipping_phone, payment_method, payment_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isdssssss", $user_id, $order_number, $total, $order_status,
            $shipping_name, $shipping_address, $shipping_phone, $payment_method, $payment_status);
        $stmt->execute();
        $order_id = $stmt->insert_id;

        /* Snapshot each cart item into order_items and decrement stock */
        foreach ($cart_items as $item) {
            $item_stmt = $conn->prepare("INSERT INTO order_items
                (order_id, product_id, product_name, price, quantity, line_total)
                VALUES (?, ?, ?, ?, ?, ?)");
            $item_stmt->bind_param("iisdid", $order_id, $item['product_id'], $item['name'],
                $item['price'], $item['quantity'], $item['line_total']);
            $item_stmt->execute();

            $conn->query("UPDATE products SET stock = stock - " . intval($item['quantity']) .
                " WHERE id = " . intval($item['product_id']));
        }

        /* Clear the cart now that the order has been placed */
        $conn->query("DELETE FROM cart WHERE user_id = '$user_id'");

        if ($payment_method === 'cod') {
            /* Cash on delivery - no payment gateway step needed */
            header("Location: order_tracking.php?order_id=" . $order_id . "&placed=1");
            exit();
        } else {
            header("Location: payment.php?order_id=" . $order_id);
            exit();
        }
    }
}

if (!isset($_SESSION['compare_list'])) {
    $_SESSION['compare_list'] = [];
}
$compare_count = count($_SESSION['compare_list']);

$cart_count_query = $conn->query("SELECT SUM(quantity) as cnt FROM cart WHERE user_id = '$user_id'");
$cart_count = $cart_count_query->fetch_assoc()['cnt'];
$cart_count = $cart_count ? (int)$cart_count : 0;
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Checkout</title>

<link rel="stylesheet" href="../css/style.css">

</head>

<body class="dashboard-body">

<header class="customer-topbar">

<div class="customer-logo">

🌿 Eco Market

</div>

<nav class="customer-nav">

<a href="dashboard.php" class="<?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">🏠 Dashboard</a>

<a href="product_list.php" class="<?php echo $current_page === 'product_list.php' ? 'active' : ''; ?>">🛍️ Products</a>

<a href="compare.php" class="<?php echo $current_page === 'compare.php' ? 'active' : ''; ?>">⚖️ Compare<?php echo $compare_count > 0 ? " (" . $compare_count . ")" : ""; ?></a>

<a href="order_history.php" class="<?php echo in_array($current_page, ['order_history.php', 'order_tracking.php']) ? 'active' : ''; ?>">📦 Orders</a>

<a href="../profile.php">My Profile</a>

<a href="../logout.php">Logout</a>

</nav>

<div class="customer-user">

<a href="cart.php" class="header-cart-btn" aria-label="My Cart">
<svg class="cart-icon" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
<span class="cart-count-badge" style="<?php echo $cart_count > 0 ? '' : 'display:none;'; ?>"><?php echo $cart_count; ?></span>
</a>

<span><?php echo $_SESSION['fullname']; ?></span>

</div>

</header>

<div class="checkout-container">

<h1>Checkout</h1>

<?php if ($error != "") { ?>
<div class="message message-error"><?php echo htmlspecialchars($error); ?></div>
<?php } ?>

<div class="checkout-grid">

<form method="POST" class="checkout-form">

<h2>Shipping Details</h2>

<label for="shipping_name">Full Name</label>
<input type="text" id="shipping_name" name="shipping_name" value="<?php echo htmlspecialchars($_SESSION['fullname']); ?>" required>

<label for="shipping_address">Delivery Address</label>
<textarea id="shipping_address" name="shipping_address" required></textarea>

<label for="shipping_phone">Phone Number</label>
<input type="text" id="shipping_phone" name="shipping_phone" required>

<h2>Payment Method</h2>

<div class="payment-options">

<label class="payment-option">
<input type="radio" name="payment_method" value="card" checked>
Credit / Debit Card
</label>

<label class="payment-option">
<input type="radio" name="payment_method" value="ewallet">
E-Wallet
</label>

<label class="payment-option">
<input type="radio" name="payment_method" value="cod">
Cash on Delivery
</label>

</div>

<button type="submit" class="btn-primary checkout-submit">Place Order - RM <?php echo number_format($total, 2); ?></button>

</form>

<div class="checkout-summary">

<h2>Order Summary</h2>

<?php foreach ($cart_items as $item) { ?>

<div class="checkout-summary-row">

<span><?php echo $item['name']; ?> &times; <?php echo $item['quantity']; ?></span>

<span>RM <?php echo number_format($item['line_total'], 2); ?></span>

</div>

<?php } ?>

<div class="checkout-summary-total">

<span>Total</span>

<span>RM <?php echo number_format($total, 2); ?></span>

</div>

</div>

</div>

</div>

<footer>

<p>© 2026 Eco Market Sdn. Bhd.</p>

<p>This website is fictitious and developed solely for academic purposes as part of a university course.</p>

</footer>

</body>

</html>
