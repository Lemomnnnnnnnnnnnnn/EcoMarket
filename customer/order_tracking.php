<?php
include("../auth/customer.php");
include("../config/database.php");

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: order_history.php");
    exit();
}

$items_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$order_items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* Timeline stages - cancelled orders are shown separately */
$stages = ['pending' => 'Order Placed', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered'];
$stage_keys = array_keys($stages);
$current_index = array_search($order['status'], $stage_keys);

if (!isset($_SESSION['compare_list'])) {
    $_SESSION['compare_list'] = [];
}
$compare_count = count($_SESSION['compare_list']);

$cart_count_query = $conn->query("SELECT SUM(quantity) as cnt FROM cart WHERE user_id = '$user_id'");
$cart_count = $cart_count_query->fetch_assoc()['cnt'];
$cart_count = $cart_count ? (int)$cart_count : 0;
$current_page = basename($_SERVER['PHP_SELF']);

$just_placed = isset($_GET['placed']);
$just_paid = isset($_GET['paid']);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Track Order</title>

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

<a href="order_history.php" class="back-link">&larr; Back to Orders</a>

<h1>Order <?php echo htmlspecialchars($order['order_number']); ?></h1>

<?php if ($just_placed) { ?>
<div class="message">Order placed successfully! Pay on delivery.</div>
<?php } ?>

<?php if ($just_paid) { ?>
<div class="message">Payment received. Your order is now being processed.</div>
<?php } ?>

<?php if ($just_placed || $just_paid) { ?>
<a href="dashboard.php" class="btn-primary order-complete-dashboard-btn">🏠 Back to Dashboard</a>
<?php } ?>

<?php if ($order['status'] === 'cancelled') { ?>

<div class="message message-error">This order was cancelled.</div>

<?php } else { ?>

<div class="order-timeline">

<?php foreach ($stage_keys as $i => $key) { ?>

<div class="timeline-step <?php echo ($i <= $current_index) ? 'timeline-done' : ''; ?> <?php echo ($i === $current_index) ? 'timeline-current' : ''; ?>">

<span class="timeline-dot"></span>

<span class="timeline-label"><?php echo $stages[$key]; ?></span>

</div>

<?php } ?>

</div>

<?php } ?>

<div class="checkout-grid">

<div class="checkout-summary">

<h2>Items</h2>

<?php foreach ($order_items as $item) { ?>

<div class="checkout-summary-row">

<span><?php echo htmlspecialchars($item['product_name']); ?> &times; <?php echo $item['quantity']; ?></span>

<span>RM <?php echo number_format($item['line_total'], 2); ?></span>

</div>

<?php } ?>

<div class="checkout-summary-total">

<span>Total</span>

<span>RM <?php echo number_format($order['total_amount'], 2); ?></span>

</div>

</div>

<div class="checkout-summary">

<h2>Order Details</h2>

<p>Status: <span class="stock-badge stock-instock"><?php echo ucfirst($order['status']); ?></span></p>

<p>Payment: <span class="stock-badge <?php echo $order['payment_status'] === 'paid' ? 'stock-instock' : 'stock-low'; ?>"><?php echo ucfirst($order['payment_status']); ?></span></p>

<p>Method: <?php echo strtoupper($order['payment_method']); ?></p>

<p>Placed on: <?php echo date('d M Y, g:i A', strtotime($order['created_at'])); ?></p>

<h2>Shipping To</h2>

<p><?php echo htmlspecialchars($order['shipping_name']); ?></p>

<p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>

<p><?php echo htmlspecialchars($order['shipping_phone']); ?></p>

<?php if ($order['payment_status'] === 'unpaid' && $order['payment_method'] !== 'cod') { ?>

<a href="payment.php?order_id=<?php echo $order['id']; ?>" class="btn-primary">Complete Payment</a>

<?php } ?>

<form method="POST" action="reorder.php" style="margin-top:15px;">

<input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">

<button type="submit" class="btn-small">Reorder These Items</button>

</form>

</div>

</div>

</div>

<footer>

<p>© 2026 Eco Market Sdn. Bhd.</p>

<p>This website is fictitious and developed solely for academic purposes as part of a university course.</p>

</footer>

</body>

</html>