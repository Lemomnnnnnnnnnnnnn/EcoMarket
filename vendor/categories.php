<?php
$page_title = 'Product Categories';
include("vendor_header.php");

// Fetch categories and count products for this vendor in each category
$categories_query = "
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id AND p.vendor_id = '$vendor_id' 
    GROUP BY c.id 
    ORDER BY c.name ASC
";
$categories_result = $conn->query($categories_query);

// Emojis mapping for categories
$category_emojis = [
    'Vegetables' => '🥬',
    'Fruits' => '🍎',
    'Livestock' => '🐄',
    'Fishery' => '🐟',
    'Grains' => '🌾',
    'Farming Tools' => '🛠️'
];
?>

<div class="vendor-container">

    <div class="vendor-header-section">
        <div class="vendor-title">
            <h1>Product Categories</h1>
            <p>Review the marketplace categories and see where your listings are categorized.</p>
        </div>
        <div>
            <a href="products.php" class="btn btn-primary">📦 View My Products</a>
        </div>
    </div>

    <div class="vendor-section-card">
        <h2>Marketplace Categories Overview</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 15px;">
            <?php while ($row = $categories_result->fetch_assoc()): 
                $emoji = isset($category_emojis[$row['name']]) ? $category_emojis[$row['name']] : '🏷️';
            ?>
                <div style="background:#f7faf6; border: 1px solid #e0ebd9; border-radius:12px; padding: 25px; transition: transform 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                        <span style="font-size:32px;"><?php echo $emoji; ?></span>
                        <span class="status-badge" style="background:#e8f5e9; color:#2e7d32; font-size:12px;">
                            <?php echo $row['product_count']; ?> Listings
                        </span>
                    </div>
                    
                    <h3 style="color:#1b5e20; font-size:18px; margin: 15px 0 8px 0;"><?php echo htmlspecialchars($row['name']); ?></h3>
                    <p style="color:#667e65; font-size:14px; line-height:1.5; margin:0;"><?php echo htmlspecialchars($row['description']); ?></p>
                    
                    <div style="margin-top: 15px; text-align:right;">
                        <a href="products.php?category_id=<?php echo $row['id']; ?>" style="color:#2e7d32; font-weight:600; font-size:13px; text-decoration:none;">View Products →</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

</div>

<?php
include("vendor_footer.php");
?>
