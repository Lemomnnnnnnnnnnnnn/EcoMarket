<?php
include("../auth/customer.php");
include("../config/database.php");

$user_id = $_SESSION['user_id'];
$message = "";

if (isset($_POST['update'])) {
    $cart_id = intval($_POST['cart_id']);
    $quantity = intval($_POST['quantity']);

    if ($quantity < 1) {
        $quantity = 1;
    }

    $stock_check = $conn->query("SELECT products.stock
                                  FROM cart
                                  JOIN products ON cart.product_id = products.id
                                  WHERE cart.id='$cart_id' AND cart.user_id='$user_id'");

    if ($stock_check && $stock_check->num_rows == 1) {
        $stock = $stock_check->fetch_assoc()['stock'];

        if ($quantity > $stock) {
            $quantity = $stock;
            $message = "Only $stock in stock, quantity adjusted.";
        } else {
            $message = "Cart updated.";
        }

        $conn->query("UPDATE cart SET quantity='$quantity' WHERE id='$cart_id' AND user_id='$user_id'");
    }
}

if (isset($_POST['remove'])) {
    $cart_id = intval($_POST['cart_id']);
    $conn->query("DELETE FROM cart WHERE id='$cart_id' AND user_id='$user_id'");
    $message = "Item removed from cart.";
}

if (isset($_GET['reordered'])) {
    $message = "Items added back to your cart.";
    if (isset($_GET['skipped'])) {
        $message .= " " . intval($_GET['skipped']) . " item(s) could not be added (out of stock).";
    }
}

$sql = "SELECT cart.id AS cart_id, cart.quantity, products.id AS product_id,
               products.name, products.price, products.stock, products.image_url,
               vendors.store_name
        FROM cart
        JOIN products ON cart.product_id = products.id
        LEFT JOIN vendors ON products.vendor_id = vendors.id
        WHERE cart.user_id = '$user_id'
        ORDER BY cart.added_at DESC";

$result = $conn->query($sql);

$cart_items = [];
$total = 0;
$has_oos_item = false;

while ($row = $result->fetch_assoc()) {
    $row['line_total'] = $row['price'] * $row['quantity'];
    $total += $row['line_total'];
    if ($row['stock'] <= 0) {
        $has_oos_item = true;
    }
    $cart_items[] = $row;
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
<title>My Cart</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body class="dashboard-body">

<header class="customer-topbar">
<div class="customer-logo">🌿 Eco Market</div>
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

<div class="cart-container">
<h1>My Cart</h1>

<?php if ($message != "") { ?>
<div class="message"><?php echo $message; ?></div>
<?php } ?>

<?php if (count($cart_items) == 0) { ?>
<div class="empty-cart">
<p>Your cart is empty.</p>
<a href="product_list.php" class="btn-primary">Continue Shopping</a>
</div>
<?php } else { ?>

<div class="cart-table-wrap">
<table class="cart-table">
<thead>
<tr>
<th>Product</th>
<th>Vendor</th>
<th>Price</th>
<th>Quantity</th>
<th>Subtotal</th>
<th></th>
</tr>
</thead>
<tbody>
<?php foreach ($cart_items as $item) { ?>
<tr>
<td class="cart-product-cell">
<?php if (!empty($item['image_url'])) { ?>
<img src="../<?php echo $item['image_url']; ?>" alt="<?php echo $item['name']; ?>" class="cart-product-img">
<?php } else { ?>
<div class="cart-product-img cart-product-placeholder">🌿</div>
<?php } ?>
<span><?php echo $item['name']; ?><?php if ($item['stock'] <= 0) { ?><br><span class="cart-oos-note">Out of stock - remove to checkout</span><?php } ?></span>
</td>
<td><?php echo $item['store_name'] ? $item['store_name'] : '-'; ?></td>
<td>RM <?php echo number_format($item['price'], 2); ?></td>
<td>
<form method="POST" class="qty-form">
<input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
<input type="number" name="quantity" min="1" max="<?php echo max($item['stock'], 1); ?>" value="<?php echo $item['quantity']; ?>" class="qty-input" <?php echo $item['stock'] <= 0 ? 'disabled' : ''; ?>>
<button type="submit" name="update" class="btn-small" <?php echo $item['stock'] <= 0 ? 'disabled' : ''; ?>>Update</button>
</form>
</td>
<td>RM <?php echo number_format($item['line_total'], 2); ?></td>
<td>
<form method="POST">
<input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
<button type="submit" name="remove" class="btn-remove">Remove</button>
</form>
</td>
</tr>
<?php } ?>
</tbody>
</table>
</div>

<div class="cart-summary">
<p>Total: <strong>RM <?php echo number_format($total, 2); ?></strong></p>
<?php if ($has_oos_item) { ?>
<span class="cart-oos-warning">Remove out-of-stock items before checkout</span>
<?php } else { ?>
<a href="checkout.php" class="btn-primary">Proceed to Checkout</a>
<?php } ?>
</div>

<?php } ?>
</div>

<footer>
<p>© 2026 Eco Market Sdn. Bhd.</p>
<p>This website is fictitious and developed solely for academic purposes as part of a university course.</p>
</footer>

</body>
</html>
