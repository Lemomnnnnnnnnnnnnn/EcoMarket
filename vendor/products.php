<?php
$page_title = 'Manage Products';
include("vendor_header.php");

// Search & Filter inputs
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$cat_filter = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

// Base query
$query_str = "
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.vendor_id = '$vendor_id'
";

if ($search != '') {
    $query_str .= " AND p.name LIKE '%$search%'";
}
if ($cat_filter > 0) {
    $query_str .= " AND p.category_id = '$cat_filter'";
}

$query_str .= " ORDER BY p.id DESC";
$products_result = $conn->query($query_str);

// Fetch categories for filtering
$categories_list = $conn->query("SELECT * FROM categories ORDER BY name ASC");
?>

<div class="vendor-container">

    <div class="vendor-header-section">
        <div class="vendor-title">
            <h1>Product Management</h1>
            <p>View, search, and manage your store's items.</p>
        </div>
        <div>
            <a href="add_product.php" class="btn btn-primary">➕ Add New Product</a>
        </div>
    </div>

    <!-- Search and Filter Bar -->
    <div class="vendor-section-card" style="padding: 20px;">
        <form method="GET" action="products.php" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                <input type="text" name="search" placeholder="Search by product name..." value="<?php echo htmlspecialchars($search); ?>" style="width: 100%;">
            </div>
            
            <div class="form-group" style="min-width: 180px; margin-bottom: 0;">
                <select name="category_id" style="width: 100%;">
                    <option value="0">All Categories</option>
                    <?php 
                    $categories_list->data_seek(0);
                    while ($cat = $categories_list->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $cat_filter == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">🔍 Filter</button>
            <?php if ($search != '' || $cat_filter > 0): ?>
                <a href="products.php" class="btn btn-outline">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Products Table -->
    <div class="vendor-section-card">
        <h2>Your Products (<?php echo $products_result->num_rows; ?> found)</h2>
        <?php if ($products_result->num_rows > 0): ?>
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
                            <th>Date Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $products_result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if ($row['image_url']): ?>
                                        <img src="../<?php echo htmlspecialchars($row['image_url']); ?>" class="product-img-th" alt="Product Image">
                                    <?php else: ?>
                                        <div class="product-img-th" style="display:flex; align-items:center; justify-content:center; font-size:18px; background:#e8f5e9; color:#2e7d32;">📦</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: #1b5e20;"><?php echo htmlspecialchars($row['name']); ?></div>
                                    <div style="font-size: 12px; color: #667e65; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?php echo htmlspecialchars($row['description']); ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($row['category_name'] ? $row['category_name'] : 'Uncategorized'); ?></td>
                                <td><strong>RM <?php echo number_format($row['price'], 2); ?></strong></td>
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
                                <td style="font-size:12px; color:#667e65;">
                                    <?php echo date('Y-m-d', strtotime($row['created_at'])); ?>
                                </td>
                                <td>
                                    <div style="display:flex; gap: 8px;">
                                        <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="btn btn-outline btn-sm">Edit</a>
                                        <a href="delete_product.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="color: #667e65; text-align: center; padding: 40px 0; font-size: 15px;">
                No products found matching your search. <a href="add_product.php" style="color: #2e7d32; font-weight: 600;">Add a new product</a> or reset the filters.
            </p>
        <?php endif; ?>
    </div>

</div>

<?php
include("vendor_footer.php");
?>
