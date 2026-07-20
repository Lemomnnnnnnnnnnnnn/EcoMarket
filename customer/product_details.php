<?php
include("../auth/customer.php");
include("../config/database.php");

$user_id = $_SESSION['user_id'];

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "SELECT products.id, products.name, products.description, products.price,
               products.stock, products.image_url, products.vendor_id, vendors.store_name,
               categories.name AS category_name
        FROM products
        LEFT JOIN vendors ON products.vendor_id = vendors.id
        LEFT JOIN categories ON products.category_id = categories.id
        WHERE products.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows == 0) {
    header("Location: product_list.php");
    exit();
}

$product = $result->fetch_assoc();

if (!isset($_SESSION['compare_list'])) {
    $_SESSION['compare_list'] = [];
}
$compare_count = count($_SESSION['compare_list']);

$cart_count_query = $conn->query("SELECT SUM(quantity) as cnt FROM cart WHERE user_id = '$user_id'");
$cart_count = $cart_count_query->fetch_assoc()['cnt'];
$cart_count = $cart_count ? (int)$cart_count : 0;
$current_page = basename($_SERVER['PHP_SELF']);
$in_compare = in_array($product['id'], $_SESSION['compare_list']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $product['name']; ?></title>
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

<div class="product-details-container">
<a href="product_list.php" class="back-link">&larr; Back to Products</a>

<div class="product-details-grid">
<div class="product-details-image">
<?php if (!empty($product['image_url'])) { ?>
<img src="../<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>">
<?php } else { ?>
<div class="product-card-placeholder large">🌿</div>
<?php } ?>
</div>

<div class="product-details-info">
<h1><?php echo $product['name']; ?></h1>
<p class="product-vendor">Sold by <?php if (!empty($product['vendor_id'])) { ?><a href="vendor_store.php?id=<?php echo $product['vendor_id']; ?>" class="vendor-store-link"><?php echo $product['store_name'] ? $product['store_name'] : 'Eco Market'; ?></a><?php } else { ?><?php echo 'Eco Market'; ?><?php } ?></p>

<?php if (!empty($product['category_name'])) { ?>
<p class="product-category-tag"><?php echo htmlspecialchars($product['category_name']); ?></p>
<?php } ?>

<p class="product-price">RM <?php echo number_format($product['price'], 2); ?></p>

<?php if ($product['stock'] > 5) { ?>
<span class="stock-badge stock-instock">In Stock</span>
<?php } elseif ($product['stock'] > 0) { ?>
<span class="stock-badge stock-low">Low Stock (<?php echo $product['stock']; ?> left)</span>
<?php } else { ?>
<span class="stock-badge stock-out">Out of Stock</span>
<?php } ?>

<p class="product-description"><?php echo nl2br($product['description']); ?></p>

<?php if ($product['stock'] > 0) { ?>
<form method="POST" action="cart_add.php" class="qty-form product-details-form">
<input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
<label for="quantity">Quantity</label>
<input type="number" id="quantity" name="quantity" min="1" max="<?php echo $product['stock']; ?>" value="1" class="qty-input">
<button type="submit" class="btn-add-cart">🛒 Add to Cart</button>
</form>
<?php } else { ?>
<p class="empty-cart-note">This product is currently unavailable.</p>
<?php } ?>

<form method="POST" action="compare_toggle.php" class="compare-form">
<input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
<input type="hidden" name="redirect" value="product_details.php?id=<?php echo $product['id']; ?>">
<button type="submit" class="btn-compare-chip<?php echo $in_compare ? ' active' : ''; ?>">
<?php echo $in_compare ? '✓ Added to Compare' : '⚖ Add to Compare'; ?>
</button>
</form>
</div>
</div>
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
