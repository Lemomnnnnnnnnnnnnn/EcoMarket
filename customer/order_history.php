<?php
include("../auth/customer.php");
include("../config/database.php");

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (!isset($_SESSION['compare_list'])) {
    $_SESSION['compare_list'] = [];
}
$compare_count = count($_SESSION['compare_list']);

$cart_count_query = $conn->query("SELECT SUM(quantity) as cnt FROM cart WHERE user_id = '$user_id'");
$cart_count = $cart_count_query->fetch_assoc()['cnt'];
$cart_count = $cart_count ? (int)$cart_count : 0;
$current_page = basename($_SERVER['PHP_SELF']);

$status_class = [
    'pending' => 'stock-low',
    'processing' => 'stock-low',
    'shipped' => 'stock-instock',
    'delivered' => 'stock-instock',
    'cancelled' => 'stock-out',
];
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Order History</title>

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

<div class="cart-container">

<h1>Order History</h1>

<?php if (count($orders) == 0) { ?>

<div class="empty-cart">

<p>You haven't placed any orders yet.</p>

<a href="product_list.php" class="btn-primary">Start Shopping</a>

</div>

<?php } else { ?>

<div class="cart-table-wrap">

<table class="cart-table">

<thead>

<tr>

<th>Order #</th>

<th>Date</th>

<th>Total</th>

<th>Status</th>

<th>Payment</th>

<th></th>

</tr>

</thead>

<tbody>

<?php foreach ($orders as $order) { ?>

<tr>

<td><?php echo htmlspecialchars($order['order_number']); ?></td>

<td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>

<td>RM <?php echo number_format($order['total_amount'], 2); ?></td>

<td><span class="stock-badge <?php echo $status_class[$order['status']] ?? 'stock-low'; ?>"><?php echo ucfirst($order['status']); ?></span></td>

<td><span class="stock-badge <?php echo $order['payment_status'] === 'paid' ? 'stock-instock' : 'stock-out'; ?>"><?php echo ucfirst($order['payment_status']); ?></span></td>

<td class="order-history-actions">

<a href="order_tracking.php?order_id=<?php echo $order['id']; ?>" class="btn-small btn-outline-small">Track</a>

<form method="POST" action="reorder.php">

<input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">

<button type="submit" class="btn-small">Reorder</button>

</form>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

<?php } ?>

</div>

<footer>

<p>© 2026 Eco Market Sdn. Bhd.</p>

<p>This website is fictitious and developed solely for academic purposes as part of a university course.</p>

</footer>

</body>

</html>
