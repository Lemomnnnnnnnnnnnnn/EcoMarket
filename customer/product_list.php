<?php
include("../auth/customer.php");
include("../config/database.php");

$user_id = $_SESSION['user_id'];

/* ---------- Read search / filter / sort inputs ---------- */
$search   = isset($_GET['q']) ? trim($_GET['q']) : '';
$category_id = isset($_GET['category']) && $_GET['category'] !== '' ? intval($_GET['category']) : null;
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? floatval($_GET['min_price']) : null;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? floatval($_GET['max_price']) : null;
$sort     = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

/* ---------- Get category list for filter dropdown ---------- */
$categories = [];
$cat_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
if ($cat_result) {
    while ($row = $cat_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

/* ---------- Build query with prepared statement ---------- */
$sql = "SELECT products.id, products.name, products.description, products.price,
               products.stock, products.image_url, products.vendor_id, vendors.store_name,
               categories.name AS category_name
        FROM products
        LEFT JOIN vendors ON products.vendor_id = vendors.id
        LEFT JOIN categories ON products.category_id = categories.id
        WHERE 1=1";

$params = [];
$types = "";

if ($search !== '') {
    $sql .= " AND (products.name LIKE ? OR products.description LIKE ?)";
    $like = "%" . $search . "%";
    $params[] = $like;
    $params[] = $like;
    $types .= "ss";
}

if ($category_id !== null) {
    $sql .= " AND products.category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}

if ($min_price !== null) {
    $sql .= " AND products.price >= ?";
    $params[] = $min_price;
    $types .= "d";
}

if ($max_price !== null) {
    $sql .= " AND products.price <= ?";
    $params[] = $max_price;
    $types .= "d";
}

switch ($sort) {
    case 'price_asc':
        $sql .= " ORDER BY products.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY products.price DESC";
        break;
    case 'name_asc':
        $sql .= " ORDER BY products.name ASC";
        break;
    default:
        $sql .= " ORDER BY products.created_at DESC";
        break;
}

$stmt = $conn->prepare($sql);

if ($types !== "") {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

/* ---------- Compare list from session (badge on nav) ---------- */
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

<title>Products</title>

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

<div class="product-list-container">

<h1>Our Products</h1>

<form method="GET" class="filter-bar">

<input type="text" name="q" class="filter-search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">

<select name="category" class="filter-select">

<option value="">All Categories</option>

<?php foreach ($categories as $cat) { ?>

<option value="<?php echo $cat['id']; ?>" <?php echo ($category_id === (int)$cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>

<?php } ?>

</select>

<input type="number" name="min_price" step="0.01" min="0" class="filter-price" placeholder="Min RM" value="<?php echo $min_price !== null ? htmlspecialchars($min_price) : ''; ?>">

<input type="number" name="max_price" step="0.01" min="0" class="filter-price" placeholder="Max RM" value="<?php echo $max_price !== null ? htmlspecialchars($max_price) : ''; ?>">

<select name="sort" class="filter-select">

<option value="newest" <?php echo ($sort === 'newest') ? 'selected' : ''; ?>>Newest</option>

<option value="price_asc" <?php echo ($sort === 'price_asc') ? 'selected' : ''; ?>>Price: Low to High</option>

<option value="price_desc" <?php echo ($sort === 'price_desc') ? 'selected' : ''; ?>>Price: High to Low</option>

<option value="name_asc" <?php echo ($sort === 'name_asc') ? 'selected' : ''; ?>>Name: A-Z</option>

</select>

<button type="submit" class="btn-apply">🔍 Apply</button>

<a href="product_list.php" class="btn-reset">↺ Reset</a>

</form>

<?php if (count($products) == 0) { ?>

<div class="empty-cart">

<p>No products match your search.</p>

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

<p class="product-vendor"><?php if (!empty($item['vendor_id'])) { ?><a href="vendor_store.php?id=<?php echo $item['vendor_id']; ?>" class="vendor-store-link"><?php echo $item['store_name'] ? $item['store_name'] : 'Eco Market'; ?></a><?php } else { ?><?php echo 'Eco Market'; ?><?php } ?></p>

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

<form method="POST" action="compare_toggle.php" class="compare-form">

<input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">

<input type="hidden" name="redirect" value="product_list.php">

<?php $is_comparing = in_array($item['id'], $_SESSION['compare_list']); ?>

<button type="submit" class="btn-compare-chip<?php echo $is_comparing ? ' active' : ''; ?>">

<?php echo $is_comparing ? '✓ Added to Compare' : '⚖ Add to Compare'; ?>

</button>

</form>

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