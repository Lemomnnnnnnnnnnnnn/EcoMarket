<?php
$page_title = 'Inventory Management';
include("vendor_header.php");

$message = "";
$message_type = "success";

// Update stocks in bulk
if (isset($_POST['update_stocks'])) {
    if (isset($_POST['stocks']) && is_array($_POST['stocks'])) {
        $conn->begin_transaction();
        $all_success = true;
        
        foreach ($_POST['stocks'] as $prod_id => $stock_value) {
            $prod_id = intval($prod_id);
            $stock_value = intval($stock_value);
            
            // Secure update: ensure the product belongs to this vendor
            $update = $conn->query("
                UPDATE products 
                SET stock = '$stock_value' 
                WHERE id = '$prod_id' AND vendor_id = '$vendor_id'
            ");
            if (!$update) {
                $all_success = false;
            }
        }

        if ($all_success) {
            $conn->commit();
            $message = "Inventory stock levels updated successfully!";
            $message_type = "success";
        } else {
            $conn->rollback();
            $message = "Error: Some stock levels failed to update. " . $conn->error;
            $message_type = "danger";
        }
    }
}

// Fetch all vendor products
$products = $conn->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.vendor_id = '$vendor_id' 
    ORDER BY p.name ASC
");
?>

<div class="vendor-container">

    <div class="vendor-header-section">
        <div class="vendor-title">
            <h1>Inventory Management</h1>
            <p>Monitor stock levels, set alerts, and update inventory counts in bulk.</p>
        </div>
        <div>
            <a href="products.php" class="btn btn-outline">📦 View Product Details</a>
        </div>
    </div>

    <?php if ($message != ""): ?>
        <div class="notice-banner <?php echo $message_type == 'danger' ? 'danger' : ''; ?>">
            <h4>System Message</h4>
            <p><?php echo $message; ?></p>
        </div>
    <?php endif; ?>

    <div class="vendor-section-card">
        <h2>Stock Level Controls</h2>
        
        <?php if ($products->num_rows > 0): ?>
            <form method="POST" action="inventory.php">
                <div class="table-responsive">
                    <table class="vendor-table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Current Stock</th>
                                <th>Update Quantity</th>
                                <th>Stock Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $products->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['category_name'] ? $row['category_name'] : 'Uncategorized'); ?></td>
                                    <td>RM <?php echo number_format($row['price'], 2); ?></td>
                                    <td>
                                        <span style="font-size: 16px; font-weight:700; color: #1b5e20;">
                                            <?php echo $row['stock']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <input type="number" name="stocks[<?php echo $row['id']; ?>]" value="<?php echo $row['stock']; ?>" min="0" required style="width: 100px; padding: 6px; border-radius: 6px; border: 1.5px solid #ccd9c7; text-align: center;">
                                    </td>
                                    <td>
                                        <?php if ($row['stock'] == 0): ?>
                                            <span class="status-badge status-outstock">Out of Stock</span>
                                        <?php elseif ($row['stock'] <= 5): ?>
                                            <span class="status-badge status-lowstock">Low Stock (Alert)</span>
                                        <?php else: ?>
                                            <span class="status-badge status-instock">Healthy Stock</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <div style="margin-top: 25px; display:flex; justify-content:space-between; align-items:center;">
                    <div style="font-size:14px; color:#667e65;">
                        💡 Adjust values under <strong>Update Quantity</strong> and click save to apply all changes instantly.
                    </div>
                    <button type="submit" name="update_stocks" class="btn btn-primary">💾 Save Stock Changes</button>
                </div>
            </form>
        <?php else: ?>
            <p style="color: #667e65; text-align: center; padding: 40px 0; font-size: 15px;">
                You don't have any products in your inventory. <a href="add_product.php" style="color: #2e7d32; font-weight: 600;">Add your first product now!</a>
            </p>
        <?php endif; ?>
    </div>

</div>

<?php
include("vendor_footer.php");
?>
