<?php
include("../auth/customer.php");
include("../config/database.php");

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : (isset($_POST['order_id']) ? intval($_POST['order_id']) : 0);

$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: order_history.php");
    exit();
}

if ($order['payment_status'] === 'paid') {
    header("Location: order_tracking.php?order_id=" . $order_id);
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simulate_pay'])) {
    $card_number = isset($_POST['card_number']) ? preg_replace('/\D/', '', $_POST['card_number']) : '';

    // a card ending in 0000 simulates a declined payment for demo purposes
    $will_succeed = !(strlen($card_number) >= 4 && substr($card_number, -4) === '0000');

    $payment_ref = 'PAY' . strtoupper(substr(uniqid(), -8));
    $status = $will_succeed ? 'success' : 'failed';

    $pay_stmt = $conn->prepare("INSERT INTO payments (order_id, payment_ref, method, amount, status) VALUES (?, ?, ?, ?, ?)");
    $pay_stmt->bind_param("issds", $order_id, $payment_ref, $order['payment_method'], $order['total_amount'], $status);
    $pay_stmt->execute();

    if ($will_succeed) {
        $conn->query("UPDATE orders SET payment_status = 'paid', status = 'processing' WHERE id = " . intval($order_id));
        header("Location: order_tracking.php?order_id=" . $order_id . "&paid=1");
        exit();
    } else {
        $conn->query("UPDATE orders SET payment_status = 'failed' WHERE id = " . intval($order_id));
        $error = "Payment declined by the simulated gateway. Please check your card details and try again.";
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
<title>Payment</title>
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

<div class="checkout-container">
<h1>Payment</h1>
<p class="payment-note">This is a simulated payment gateway for academic demonstration only. No real transaction takes place.</p>

<?php if ($error != "") { ?>
<div class="message message-error"><?php echo htmlspecialchars($error); ?></div>
<?php } ?>

<div class="checkout-grid">

<form method="POST" class="checkout-form">
<input type="hidden" name="order_id" value="<?php echo $order_id; ?>">

<h2>Order <?php echo htmlspecialchars($order['order_number']); ?></h2>
<p>Amount due: <strong>RM <?php echo number_format($order['total_amount'], 2); ?></strong></p>
<p>Method: <strong><?php echo strtoupper($order['payment_method']); ?></strong></p>

<?php if ($order['payment_method'] === 'card') { ?>
<label for="card_name">Name on Card</label>
<input type="text" id="card_name" name="card_name" placeholder="e.g. Ahmad bin Ali" required>

<label for="card_number">Card Number</label>
<input type="text" id="card_number" name="card_number" placeholder="4111 1111 1111 1111" maxlength="19" required>

<label for="card_expiry">Expiry (MM/YY)</label>
<input type="text" id="card_expiry" name="card_expiry" placeholder="MM/YY" maxlength="5" required>
<?php } else { ?>
<label for="ewallet_id">E-Wallet ID / Phone Number</label>
<input type="text" id="ewallet_id" name="ewallet_id" placeholder="e.g. 012-3456789" required>
<?php } ?>

<button type="submit" name="simulate_pay" class="btn-primary checkout-submit">Pay RM <?php echo number_format($order['total_amount'], 2); ?></button>
<p class="payment-demo-hint">Demo tip: use a card/e-wallet number ending in <strong>0000</strong> to simulate a declined payment.</p>
</form>

<div class="checkout-summary">
<h2>Order Summary</h2>
<p>Order Number: <?php echo htmlspecialchars($order['order_number']); ?></p>
<p>Shipping to: <?php echo htmlspecialchars($order['shipping_name']); ?></p>
<p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
<p>Phone: <?php echo htmlspecialchars($order['shipping_phone']); ?></p>
</div>

</div>
</div>

<footer>
<p>© 2026 Eco Market Sdn. Bhd.</p>
<p>This website is fictitious and developed solely for academic purposes as part of a university course.</p>
</footer>

</body>
</html>
