<?php
$page_title = 'Dashboard';
include("vendor_header.php");

// Fetch Metrics
// 1. Total Products
$prod_count_query = $conn->query("SELECT COUNT(*) as cnt FROM products WHERE vendor_id = '$vendor_id'");
$prod_count = $prod_count_query->fetch_assoc()['cnt'];

// 2. Out of Stock Products
$out_stock_query = $conn->query("SELECT COUNT(*) as cnt FROM products WHERE vendor_id = '$vendor_id' AND stock = 0");
$out_stock_count = $out_stock_query->fetch_assoc()['cnt'];

// 3. Total Inventory
$total_stock_query = $conn->query("SELECT SUM(stock) as total FROM products WHERE vendor_id = '$vendor_id'");
$total_stock = $total_stock_query->fetch_assoc()['total'];
$total_stock = $total_stock ? $total_stock : 0;

// Fetch Recent Products
$recent_products = $conn->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.vendor_id = '$vendor_id' 
    ORDER BY p.id DESC 
    LIMIT 5
");
?>

<div class="vendor-container">

    <div class="vendor-header-section">
        <div class="vendor-title">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?>!</h1>
            <p>Here is an overview of your store's performance today.</p>
        </div>
        <div>
            <a href="add_product.php" class="btn btn-primary">➕ Add Product</a>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="vendor-metrics-grid">
        <div class="metric-card">
            <div>
                <div class="label">Total Products</div>
                <div class="value"><?php echo $prod_count; ?></div>
            </div>
            <div>
                <?php if ($vendor['subscription_tier'] == 'basic'): ?>
                    <span class="badge badge-basic">Limit: 5 Products (Basic)</span>
                <?php else: ?>
                    <span class="badge badge-premium">Unlimited (Premium)</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="metric-card">
            <div>
                <div class="label">Out of Stock</div>
                <div class="value" style="color: <?php echo $out_stock_count > 0 ? '#d32f2f' : '#1b5e20'; ?>;">
                    <?php echo $out_stock_count; ?>
                </div>
            </div>
            <div>
                <?php if ($out_stock_count > 0): ?>
                    <span class="badge badge-warning">Needs Attention</span>
                <?php else: ?>
                    <span class="badge badge-basic" style="background:#e8f5e9; color:#2e7d32;">Inventory Healthy</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="metric-card">
            <div>
                <div class="label">Total Stock Quantity</div>
                <div class="value"><?php echo $total_stock; ?></div>
            </div>
            <div style="font-size:12px; color:#667e65; margin-top:10px;">
                Items currently in warehouse
            </div>
        </div>

        <div class="metric-card">
            <div>
                <div class="label">Subscription Plan</div>
                <div class="value" style="text-transform: capitalize;"><?php echo $vendor['subscription_tier']; ?></div>
            </div>
            <div>
                <a href="subscription.php" class="btn btn-outline btn-sm" style="margin-top: 10px;">Upgrade/Change</a>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="vendor-section-card">
        <h2>Quick Actions</h2>
        <div class="quick-actions-grid">
            <a href="products.php" class="action-card">
                <span class="icon">📦</span>
                <span>Manage Products</span>
            </a>
            <a href="add_product.php" class="action-card">
                <span class="icon">➕</span>
                <span>Add Product</span>
            </a>
            <a href="inventory.php" class="action-card">
                <span class="icon">📊</span>
                <span>Inventory Stock</span>
            </a>
            <a href="categories.php" class="action-card">
                <span class="icon">🏷️</span>
                <span>Product Categories</span>
            </a>
            <a href="bulk_upload.php" class="action-card">
                <span class="icon">📥</span>
                <span>Bulk Upload</span>
            </a>
            <a href="profile.php" class="action-card">
                <span class="icon">⚙️</span>
                <span>Store Settings</span>
            </a>
        </div>
    </div>

    <!-- Recent Products Table -->
    <div class="vendor-section-card">
        <h2>Recent Products Added</h2>
        <?php if ($recent_products->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="vendor-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $recent_products->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if ($row['image_url']): ?>
                                        <img src="../<?php echo htmlspecialchars($row['image_url']); ?>" class="product-img-th" alt="Product">
                                    <?php else: ?>
                                        <div class="product-img-th" style="display:flex; align-items:center; justify-content:center; font-size:20px;">fallback</div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['category_name'] ? $row['category_name'] : 'Uncategorized'); ?></td>
                                <td>RM <?php echo number_format($row['price'], 2); ?></td>
                                <td><?php echo $row['stock']; ?></td>
                                <td>
                                    <?php if ($row['stock'] == 0): ?>
                                        <span class="status-badge status-outstock">Out of Stock</span>
                                    <?php elseif ($row['stock'] <= 5): ?>
                                        <span class="status-badge status-lowstock">Low Stock</span>
                                    <?php else: ?>
                                        <span class="status-badge status-instock">In Stock</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="btn btn-outline btn-sm">Edit</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div style="margin-top: 15px; text-align: right;">
                <a href="products.php" style="color: #2e7d32; font-weight: 600; font-size: 14px; text-decoration: none;">View All Products →</a>
            </div>
        <?php else: ?>
            <p style="color: #667e65; text-align: center; padding: 20px 0;">You haven't added any products yet. <a href="add_product.php" style="color: #2e7d32; font-weight: 600;">Add your first product now!</a></p>
        <?php endif; ?>
    </div>

</div>

<?php
include("vendor_footer.php");
?>