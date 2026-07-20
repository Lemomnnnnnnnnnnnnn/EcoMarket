<?php
include("../auth/customer.php");
include("../config/database.php");

$user_id = $_SESSION['user_id'];

$vendor_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conn->prepare("SELECT id, store_name, store_description FROM vendors WHERE id = ?");
$stmt->bind_param("i", $vendor_id);
$stmt->execute();
$vendor_result = $stmt->get_result();

if (!$vendor_result || $vendor_result->num_rows == 0) {
    header("Location: product_list.php");
    exit();
}

$vendor = $vendor_result->fetch_assoc();

$prod_stmt = $conn->prepare("SELECT products.id, products.name, products.description, products.price,
                                     products.stock, products.image_url,
                                     categories.name AS category_name
                              FROM products
                              LEFT JOIN categories ON products.category_id = categories.id
                              WHERE products.vendor_id = ?
                              ORDER BY products.created_at DESC");
$prod_stmt->bind_param("i", $vendor_id);
$prod_stmt->execute();
$products = $prod_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

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
<title><?php echo htmlspecialchars($vendor['store_name']); ?> - Store</title>
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

<div class="product-list-container">
<a href="product_list.php" class="back-link">&larr; Back to All Products</a>

<div class="store-header">
<h1>🏪 <?php echo htmlspecialchars($vendor['store_name']); ?></h1>
<p class="store-description"><?php echo !empty($vendor['store_description']) ? nl2br(htmlspecialchars($vendor['store_description'])) : 'No store description provided.'; ?></p>
<p class="store-product-count"><?php echo count($products); ?> product<?php echo count($products) != 1 ? 's' : ''; ?> listed</p>
</div>

<?php if (count($products) == 0) { ?>
<div class="empty-cart">
<p>This vendor hasn't listed any products yet.</p>
</div>
<?php } else { ?>

<div class="product-grid">
<?php foreach ($products as $item) { ?>
<div class="product-card">
<?php if (!empty($item['image_url'])) { ?>
<img src="../<?php echo $item['image_url']; ?>" alt="<?php echo $item['name']; ?>">
<?php } else { ?>
<div class="product-card-placeholder">🌿</div>
<?php } ?>

<div class="product-card-body">
<h3><?php echo $item['name']; ?></h3>
<?php if (!empty($item['category_name'])) { ?>
<p class="product-vendor"><?php echo htmlspecialchars($item['category_name']); ?></p>
<?php } ?>
<p class="product-price">RM <?php echo number_format($item['price'], 2); ?></p>

<?php if ($item['stock'] > 5) { ?>
<span class="stock-badge stock-instock">In Stock</span>
<?php } elseif ($item['stock'] > 0) { ?>
<span class="stock-badge stock-low">Low Stock (<?php echo $item['stock']; ?>)</span>
<?php } else { ?>
<span class="stock-badge stock-out">Out of Stock</span>
<?php } ?>

<div class="product-card-actions">
<a href="product_details.php?id=<?php echo $item['id']; ?>" class="btn-view-details">👁 View Details</a>
<?php if ($item['stock'] > 0) { ?>
<form method="POST" action="cart_add.php">
<input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
<input type="hidden" name="quantity" value="1">
<button type="submit" class="btn-add-cart">🛒 Add to Cart</button>
</form>
<?php } ?>
</div>
</div>
</div>
<?php } ?>
</div>

<?php } ?>
</div>

<footer>
<p>© 2026 Eco Market Sdn. Bhd.</p>
<p>This website is fictitious and developed solely for academic purposes as part of a university course.</p>
</footer>

<script>
document.querySelectorAll('form[action="cart_add.php"]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        var btn = form.querySelector('button[type="submit"]');
        var originalText = btn ? btn.innerHTML : '';

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '⏳ Adding...';
        }

        fetch('cart_add.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form)
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            var badge = document.querySelector('.cart-count-badge');
            if (badge) {
                if (data.cart_count > 0) {
                    badge.textContent = data.cart_count;
                    badge.style.display = 'flex';
                } else {
                    badge.textContent = '';
                    badge.style.display = 'none';
                }
            }

            if (btn) {
                btn.innerHTML = data.success ? '✓ Added' : originalText;
            }
            if (!data.success && data.message) {
                alert(data.message);
            }

            setTimeout(function () {
                if (btn) {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            }, 1200);
        })
        .catch(function () {
            if (btn) {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
            alert('Something went wrong adding this to your cart. Please try again.');
        });
    });
});
</script>

</body>
</html>
