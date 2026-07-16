<?php
$page_title = 'Edit Product';
include("vendor_header.php");

$message = "";
$message_type = "success";

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = intval($_GET['id']);

// Fetch product & verify ownership
$prod_query = $conn->query("SELECT * FROM products WHERE id = '$product_id' AND vendor_id = '$vendor_id'");
if ($prod_query->num_rows == 0) {
    header("Location: products.php");
    exit();
}

$product = $prod_query->fetch_assoc();

if (isset($_POST['update_product'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);
    
    $image_url = $product['image_url'];

    // Process new image upload if provided
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
                    // Delete old image if it exists
                    if ($product['image_url'] && file_exists('../' . $product['image_url'])) {
                        unlink('../' . $product['image_url']);
                    }
                    $image_url = 'images/products/' . $new_file_name;
                } else {
                    $message = "Failed to upload new image.";
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
        $update_query = "
            UPDATE products 
            SET category_id = '$category_id', 
                name = '$name', 
                description = '$description', 
                price = '$price', 
                stock = '$stock', 
                image_url = '$image_url' 
            WHERE id = '$product_id' AND vendor_id = '$vendor_id'
        ";
        
        if ($conn->query($update_query)) {
            $message = "Product updated successfully! <a href='products.php' style='color:#2e7d32; font-weight:600;'>View all products</a>";
            $message_type = "success";
            
            // Re-fetch product data
            $prod_query = $conn->query("SELECT * FROM products WHERE id = '$product_id' AND vendor_id = '$vendor_id'");
            $product = $prod_query->fetch_assoc();
        } else {
            $message = "Error: Failed to save changes. " . $conn->error;
            $message_type = "danger";
        }
    }
}

// Fetch categories
$categories_list = $conn->query("SELECT * FROM categories ORDER BY name ASC");
?>

<div class="vendor-container">

    <div class="vendor-header-section">
        <div class="vendor-title">
            <h1>Edit Product</h1>
            <p>Update listings in the Eco Market catalog.</p>
        </div>
        <div>
            <a href="products.php" class="btn btn-outline">⬅️ Back to Products</a>
        </div>
    </div>

    <?php if ($message != ""): ?>
        <div class="notice-banner <?php echo $message_type == 'danger' ? 'danger' : ''; ?>">
            <h4>System Message</h4>
            <p><?php echo $message; ?></p>
        </div>
    <?php endif; ?>

    <div class="vendor-section-card">
        <h2>Product Details</h2>
        
        <form method="POST" action="edit_product.php?id=<?php echo $product_id; ?>" enctype="multipart/form-data" class="vendor-form">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" required>
                        <option value="">-- Select Category --</option>
                        <?php while ($cat = $categories_list->fetch_assoc()): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $product['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Price (RM)</label>
                    <input type="number" name="price" step="0.01" min="0.01" value="<?php echo $product['price']; ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Stock Level</label>
                    <input type="number" name="stock" min="0" value="<?php echo $product['stock']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Replace Image (Optional)</label>
                    <input type="file" name="image" accept="image/*">
                </div>
            </div>

            <?php if ($product['image_url']): ?>
                <div class="form-group">
                    <label>Current Product Image</label>
                    <div style="margin-top:5px;">
                        <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" style="max-width:180px; border-radius:10px; border:1px solid #e0ebd9; box-shadow:0 2px 6px rgba(0,0,0,0.05);" alt="Current Image">
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Product Description</label>
                <textarea name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>

            <div style="margin-top:10px;">
                <button type="submit" name="update_product" class="btn btn-primary">💾 Save Changes</button>
                <a href="products.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>

</div>

<?php
include("vendor_footer.php");
?>
