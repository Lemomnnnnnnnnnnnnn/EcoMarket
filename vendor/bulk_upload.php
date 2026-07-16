<?php
if (isset($_GET['download_template'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="ecomarket_bulk_upload_template.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Product Name', 'Category Name', 'Price', 'Stock', 'Description']);
    fputcsv($output, ['Organic Sweet Corn', 'Vegetables', '4.50', '200', 'Sweet, golden corn freshly harvested this morning.']);
    fputcsv($output, ['Fresh Honey Mango', 'Fruits', '15.00', '75', 'Deliciously sweet local honey mangoes.']);
    fputcsv($output, ['Free-Range Eggs', 'Livestock', '14.80', '30', 'A pack of 30 farm-fresh organic brown eggs.']);
    fclose($output);
    exit();
}

$page_title = 'Bulk Upload Products';
include("vendor_header.php");

$message = "";
$message_type = "success";
$is_premium = ($vendor['subscription_tier'] == 'premium');

if (isset($_POST['upload_csv']) && $is_premium) {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
        $file_tmp = $_FILES['csv_file']['tmp_name'];
        
        // Open file
        if (($handle = fopen($file_tmp, "r")) !== FALSE) {
            // Read headers
            $headers = fgetcsv($handle, 1000, ",");
            
            $inserted_count = 0;
            $error_rows = [];
            $row_idx = 1; // Row tracking (1 is header)
            
            // Pre-fetch categories for lookup
            $cat_lookup = [];
            $cat_res = $conn->query("SELECT id, name FROM categories");
            while ($cat = $cat_res->fetch_assoc()) {
                $cat_lookup[strtolower(trim($cat['name']))] = $cat['id'];
            }
            
            $conn->begin_transaction();
            
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row_idx++;
                // Expecting 5 columns: Name, Category, Price, Stock, Description
                if (count($data) < 4) {
                    $error_rows[] = "Row $row_idx: Insufficient columns (must have Name, Category, Price, Stock)";
                    continue;
                }
                
                $name = $conn->real_escape_string(trim($data[0]));
                $category_name = trim($data[1]);
                $price = floatval($data[2]);
                $stock = intval($data[3]);
                $description = isset($data[4]) ? $conn->real_escape_string(trim($data[4])) : '';
                
                if (empty($name)) {
                    $error_rows[] = "Row $row_idx: Product name cannot be empty.";
                    continue;
                }
                if ($price <= 0) {
                    $error_rows[] = "Row $row_idx: Price must be greater than RM 0.00.";
                    continue;
                }
                if ($stock < 0) {
                    $error_rows[] = "Row $row_idx: Stock level cannot be negative.";
                    continue;
                }
                
                // Map category
                $category_id = 'NULL';
                $cat_key = strtolower($category_name);
                if (isset($cat_lookup[$cat_key])) {
                    $category_id = $cat_lookup[$cat_key];
                }
                
                $insert = $conn->query("
                    INSERT INTO products (vendor_id, category_id, name, description, price, stock, image_url) 
                    VALUES ('$vendor_id', " . ($category_id == 'NULL' ? 'NULL' : "'$category_id'") . ", '$name', '$description', '$price', '$stock', '')
                ");
                
                if ($insert) {
                    $inserted_count++;
                } else {
                    $error_rows[] = "Row $row_idx: SQL Error (" . $conn->error . ")";
                }
            }
            fclose($handle);
            
            if (count($error_rows) == 0) {
                $conn->commit();
                $message = "CSV Bulk Import completed! Successfully added <strong>$inserted_count</strong> products.";
                $message_type = "success";
            } else {
                // If there are partial imports, commit them but warn about errors
                $conn->commit();
                $message = "CSV Bulk Import completed with errors. Added <strong>$inserted_count</strong> products. Warnings:<br><ul><li>" . implode("</li><li>", $error_rows) . "</li></ul>";
                $message_type = "warning";
            }
        } else {
            $message = "Failed to open the uploaded CSV file.";
            $message_type = "danger";
        }
    } else {
        $message = "Please select a valid CSV file.";
        $message_type = "danger";
    }
}
?>

<div class="vendor-container">

    <div class="vendor-header-section">
        <div class="vendor-title">
            <h1>Bulk Upload Products</h1>
            <p>Upload a CSV spreadsheet to add multiple items to your catalog at once.</p>
        </div>
    </div>

    <?php if (!$is_premium): ?>
        <div class="notice-banner danger">
            <h4>⭐ Premium Feature Exclusive</h4>
            <p>CSV Bulk Product Upload is restricted to <strong>Premium</strong> subscribers. Please <a href="subscription.php" style="font-weight:700; text-decoration:underline; color:#991b1b;">upgrade your subscription plan</a> to enable bulk listing capabilities.</p>
        </div>
    <?php endif; ?>

    <?php if ($message != ""): ?>
        <div class="notice-banner <?php echo $message_type == 'danger' ? 'danger' : ($message_type == 'warning' ? 'danger' : ''); ?>" style="<?php echo $message_type == 'warning' ? 'background: #fff3e0; border-left-color: #ef6c00; color: #b78103;' : ''; ?>">
            <h4>System Message</h4>
            <p><?php echo $message; ?></p>
        </div>
    <?php endif; ?>

    <div class="vendor-section-card">
        <h2>CSV Bulk Importer</h2>
        
        <div style="margin-bottom: 25px; line-height: 1.6; color:#556b54;">
            <p>To batch-upload products, please follow these guidelines:</p>
            <ol style="margin-left: 20px; margin-top: 5px;">
                <li>Download the official spreadsheet template below.</li>
                <li>Fill in the rows. Category Name must match one of our categories (Vegetables, Fruits, Livestock, Fishery, Grains, Farming Tools).</li>
                <li>Upload the file below. Duplicate names will be created as new listings.</li>
            </ol>
            <a href="bulk_upload.php?download_template=1" class="btn btn-outline btn-sm" style="margin-top: 15px;">📥 Download CSV Template</a>
        </div>

        <form method="POST" action="bulk_upload.php" enctype="multipart/form-data" class="vendor-form">
            <div class="form-group">
                <label>Select CSV File</label>
                <input type="file" name="csv_file" accept=".csv" required <?php echo !$is_premium ? 'disabled' : ''; ?>>
            </div>

            <div style="margin-top:10px;">
                <button type="submit" name="upload_csv" class="btn btn-primary" <?php echo !$is_premium ? 'disabled' : ''; ?>>📤 Upload and Import</button>
                <a href="dashboard.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>

</div>

<?php
include("vendor_footer.php");
?>
