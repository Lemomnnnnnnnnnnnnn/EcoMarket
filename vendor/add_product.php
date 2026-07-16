<?php
$page_title = 'Add Product';
include("vendor_header.php");

$message = "";
$message_type = "success";

// Check if basic tier limit reached
$prod_count_query = $conn->query("SELECT COUNT(*) as cnt FROM products WHERE vendor_id = '$vendor_id'");
$prod_count = $prod_count_query->fetch_assoc()['cnt'];
$limit_reached = ($vendor['subscription_tier'] == 'basic' && $prod_count >= 5);

if (isset($_POST['add_product'])) {
    if ($limit_reached) {
        $message = "Error: You have reached the limit of 5 products for the Basic tier. Please upgrade to Premium to add more.";
        $message_type = "danger";
    } else {
        $name = $conn->real_escape_string($_POST['name']);
        $description = $conn->real_escape_string($_POST['description']);
        $price = floatval($_POST['price']);
        $stock = intval($_POST['stock']);
        $category_id = intval($_POST['category_id']);
        
        $image_url = "";

        // Process image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $file_name = $_FILES['image']['name'];
            $file_size = $_FILES['image']['size'];
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (in_array($file_ext, $allowed_exts)) {
                if ($file_size <= 2 * 1024 * 1024) { // 2MB limit
                    $upload_dir = '../images/products/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $new_file_name = uniqid('prod_', true) . '.' . $file_ext;
                    $target_file = $upload_dir . $new_file_name;

                    if (move_uploaded_file($file_tmp, $target_file)) {
                        $image_url = 'images/products/' . $new_file_name;
                    } else {
                        $message = "Failed to upload image.";
                        $message_type = "danger";
                    }
                } else {
                    $message = "Image file size must be less than 2MB.";
                    $message_type = "danger";
                }
            } else {
                $message = "Invalid image format. Allowed formats: " . implode(', ', $allowed_exts);
                $message_type = "danger";
            }
        }

        if ($message_type != "danger") {
            $insert_query = "
                INSERT INTO products (vendor_id, category_id, name, description, price, stock, image_url) 
                VALUES ('$vendor_id', '$category_id', '$name', '$description', '$price', '$stock', '$image_url')
            ";
            
            if ($conn->query($insert_query)) {
                $message = "Product added successfully! <a href='products.php' style='color:#2e7d32; font-weight:600;'>View all products</a>";
                $message_type = "success";
                
                // Recalculate product count for limit warning
                $prod_count_query = $conn->query("SELECT COUNT(*) as cnt FROM products WHERE vendor_id = '$vendor_id'");
                $prod_count = $prod_count_query->fetch_assoc()['cnt'];
                $limit_reached = ($vendor['subscription_tier'] == 'basic' && $prod_count >= 5);
            } else {
                $message = "Error: Failed to save product. " . $conn->error;
                $message_type = "danger";
            }
        }
    }
}

// Fetch categories
$categories_list = $conn->query("SELECT * FROM categories ORDER BY name ASC");
?>

<div class="vendor-container">

    <div class="vendor-header-section">
        <div class="vendor-title">
            <h1>Add New Product</h1>
            <p>List a new item in the Eco Market catalog.</p>
        </div>
        <div>
            <a href="products.php" class="btn btn-outline">⬅️ Back to Products</a>
        </div>
    </div>

    <?php if ($limit_reached): ?>
        <div class="notice-banner danger">
            <h4>⚠️ Subscription Limit Reached</h4>
            <p>You are on the <strong>Basic Tier</strong> and have listed <strong><?php echo $prod_count; ?>/5</strong> products. You must <a href="subscription.php" style="font-weight:700; text-decoration:underline; color:#991b1b;">upgrade to Premium</a> to list more items.</p>
        </div>
    <?php endif; ?>

    <?php if ($message != ""): ?>
        <div class="notice-banner <?php echo $message_type == 'danger' ? 'danger' : ''; ?>">
            <h4>System Message</h4>
            <p><?php echo $message; ?></p>
        </div>
    <?php endif; ?>

    <div class="vendor-section-card">
        <h2>Product Details</h2>
        
        <form method="POST" action="add_product.php" enctype="multipart/form-data" class="vendor-form">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" required placeholder="e.g. Organic Spinach" <?php echo $limit_reached ? 'disabled' : ''; ?>>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" required <?php echo $limit_reached ? 'disabled' : ''; ?>>
                        <option value="">-- Select Category --</option>
                        <?php while ($cat = $categories_list->fetch_assoc()): ?>
                            <option value="<?php echo $cat['id']; ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Price (RM)</label>
                    <input type="number" name="price" step="0.01" min="0.01" required placeholder="e.g. 5.50" <?php echo $limit_reached ? 'disabled' : ''; ?>>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Stock Level</label>
                    <input type="number" name="stock" min="0" required placeholder="e.g. 50" <?php echo $limit_reached ? 'disabled' : ''; ?>>
                </div>
                <div class="form-group">
                    <label>Product Image</label>
                    <input type="file" name="image" accept="image/*" required <?php echo $limit_reached ? 'disabled' : ''; ?>>
                </div>
            </div>

            <div class="form-group">
                <label>Product Description</label>
                <textarea name="description" required placeholder="Describe your product's organic origin, nutrition, and details..." <?php echo $limit_reached ? 'disabled' : ''; ?>></textarea>
            </div>

            <div style="margin-top:10px;">
                <button type="submit" name="add_product" class="btn btn-primary" <?php echo $limit_reached ? 'disabled' : ''; ?>>➕ List Product</button>
                <a href="products.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>

</div>

<?php
include("vendor_footer.php");
?>
