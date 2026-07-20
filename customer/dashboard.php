<?php
include("../auth/customer.php");
include("../config/database.php");

$user_id = $_SESSION['user_id'];

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
<title>Customer Dashboard</title>
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

<div class="dash-container">
<div class="dash-welcome">
<h1>Welcome back, <?php echo $_SESSION['fullname']; ?> 🌿</h1>
<p>Here's what's happening with your account today.</p>
</div>

<div class="dash-section">
<h2>Quick Actions</h2>
<div class="dash-actions-grid">

<a href="product_list.php" class="dash-action-card">
<span class="dash-action-icon">🛍️</span>
<span class="dash-action-title">Browse Products</span>
<span class="dash-action-desc">Explore everything on Eco Market</span>
</a>

<a href="cart.php" class="dash-action-card">
<span class="dash-action-icon">🛒</span>
<span class="dash-action-title">My Cart</span>
<span class="dash-action-desc">Edit your cart and proceed to checkout</span>
</a>

<a href="order_history.php" class="dash-action-card">
<span class="dash-action-icon">📦</span>
<span class="dash-action-title">Order History</span>
<span class="dash-action-desc">Track and review past orders</span>
</a>

<a href="../profile.php" class="dash-action-card">
<span class="dash-action-icon">👤</span>
<span class="dash-action-title">My Profile</span>
<span class="dash-action-desc">Update your account details</span>
</a>

</div>
</div>
</div>

<footer>
<p>© 2026 Eco Market Sdn. Bhd.</p>
<p>This website is fictitious and developed solely for academic purposes as part of a university course.</p>
</footer>

</body>
</html>
