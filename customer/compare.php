<?php
include("../auth/customer.php");
include("../config/database.php");

$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['compare_list'])) {
    $_SESSION['compare_list'] = [];
}

$compare_ids = $_SESSION['compare_list'];
$compare_count = count($compare_ids);

$cart_count_query = $conn->query("SELECT SUM(quantity) as cnt FROM cart WHERE user_id = '$user_id'");
$cart_count = $cart_count_query->fetch_assoc()['cnt'];
$cart_count = $cart_count ? (int)$cart_count : 0;
$current_page = basename($_SERVER['PHP_SELF']);

$products = [];

if (!empty($compare_ids)) {
    $placeholders = implode(',', array_fill(0, count($compare_ids), '?'));
    $types = str_repeat('i', count($compare_ids));

    $sql = "SELECT products.id, products.name, products.description, products.price,
                   products.stock, products.image_url, vendors.store_name,
                   categories.name AS category_name
            FROM products
            LEFT JOIN vendors ON products.vendor_id = vendors.id
            LEFT JOIN categories ON products.category_id = categories.id
            WHERE products.id IN ($placeholders)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$compare_ids);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Compare Products</title>
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
<h1>Compare Products</h1>

<?php if (count($products) == 0) { ?>
<div class="empty-cart">
<p>You haven't added any products to compare yet.</p>
<a href="product_list.php" class="btn-primary">Browse Products</a>
</div>
<?php } else { ?>

<div class="compare-table-wrap">
<table class="compare-table">
<tbody>

<tr class="compare-row-image">
<th>Product</th>
<?php foreach ($products as $item) { ?>
<td>
<?php if (!empty($item['image_url'])) { ?>
<img src="../<?php echo $item['image_url']; ?>" alt="<?php echo $item['name']; ?>" class="compare-img">
<?php } else { ?>
<div class="compare-img compare-img-placeholder">🌿</div>
<?php } ?>
<div class="compare-product-name"><?php echo $item['name']; ?></div>
</td>
<?php } ?>
</tr>

<tr>
<th>Vendor</th>
<?php foreach ($products as $item) { ?>
<td><?php echo $item['store_name'] ? $item['store_name'] : 'Eco Market'; ?></td>
<?php } ?>
</tr>

<tr>
<th>Category</th>
<?php foreach ($products as $item) { ?>
<td><?php echo !empty($item['category_name']) ? htmlspecialchars($item['category_name']) : '-'; ?></td>
<?php } ?>
</tr>

<tr>
<th>Price</th>
<?php foreach ($products as $item) { ?>
<td class="compare-price">RM <?php echo number_format($item['price'], 2); ?></td>
<?php } ?>
</tr>

<tr>
<th>Availability</th>
<?php foreach ($products as $item) { ?>
<td>
<?php if ($item['stock'] > 5) { ?>
<span class="stock-badge stock-instock">In Stock</span>
<?php } elseif ($item['stock'] > 0) { ?>
<span class="stock-badge stock-low">Low Stock (<?php echo $item['stock']; ?>)</span>
<?php } else { ?>
<span class="stock-badge stock-out">Out of Stock</span>
<?php } ?>
</td>
<?php } ?>
</tr>

<tr>
<th>Description</th>
<?php foreach ($products as $item) { ?>
<td class="compare-desc"><?php echo nl2br($item['description']); ?></td>
<?php } ?>
</tr>

<tr>
<th></th>
<?php foreach ($products as $item) { ?>
<td>
<?php if ($item['stock'] > 0) { ?>
<form method="POST" action="cart_add.php">
<input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
<input type="hidden" name="quantity" value="1">
<button type="submit" class="btn-add-cart">🛒 Add to Cart</button>
</form>
<?php } ?>
<form method="POST" action="compare_toggle.php">
<input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
<input type="hidden" name="redirect" value="compare.php">
<button type="submit" class="btn-remove">Remove</button>
</form>
</td>
<?php } ?>
</tr>

</tbody>
</table>
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
