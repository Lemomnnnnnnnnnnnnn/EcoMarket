<?php
include("../auth/admin.php");
include("../config/database.php");

// 1. Most Popular Products
$popular_products_query = "
    SELECT p.name, SUM(oi.quantity) as total_sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY p.id, p.name
    ORDER BY total_sold DESC
    LIMIT 5
";
$popular_products = $conn->query($popular_products_query);

// 2. Most Searched Products
$searched_products_query = "
    SELECT search_query, search_count
    FROM search_logs
    ORDER BY search_count DESC
    LIMIT 5
";
$searched_products = $conn->query($searched_products_query);

// 3. Most Visited Pages
$visited_pages_query = "
    SELECT page_url, visit_count
    FROM page_visits
    ORDER BY visit_count DESC
    LIMIT 5
";
$visited_pages = $conn->query($visited_pages_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Analytics - Admin Dashboard</title>
<link rel="stylesheet" href="../css/style.css">
<!-- Include Chart.js for visualizations -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.chart-container {
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
}
.analytics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}
</style>
</head>
<body class="dashboard-body">

<div class="admin-layout">
    <?php include("includes/admin_nav.php"); ?>

    <main class="admin-main">
        <div class="admin-header">
            <h1>Analytics Dashboard</h1>
        </div>

        <div class="analytics-grid">
            <!-- Most Popular Products -->
            <div class="admin-card">
                <h2>Most Popular Products</h2>
                <div class="chart-container">
                    <canvas id="popularChart"></canvas>
                </div>
            </div>

            <!-- Most Searched Products -->
            <div class="admin-card">
                <h2>Most Searched Terms</h2>
                <div class="chart-container">
                    <canvas id="searchChart"></canvas>
                </div>
            </div>

            <!-- Most Visited Pages -->
            <div class="admin-card">
                <h2>Most Visited Pages</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Page URL</th>
                            <th>Visits</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $visit_labels = []; $visit_data = [];
                        if ($visited_pages->num_rows > 0): 
                            while($row = $visited_pages->fetch_assoc()): 
                                $visit_labels[] = $row['page_url'];
                                $visit_data[] = $row['visit_count'];
                        ?>
                            <tr>
                                <td style="word-break: break-all; font-size:14px;"><?php echo htmlspecialchars($row['page_url']); ?></td>
                                <td><strong><?php echo $row['visit_count']; ?></strong></td>
                            </tr>
                        <?php 
                            endwhile; 
                        else: 
                        ?>
                            <tr><td colspan="2">No data</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<?php
// Prepare chart data
$pop_labels = []; $pop_data = [];
if($popular_products) {
    while($row = $popular_products->fetch_assoc()) {
        $pop_labels[] = $row['name'];
        $pop_data[] = $row['total_sold'];
    }
}

$search_labels = []; $search_data = [];
if($searched_products) {
    while($row = $searched_products->fetch_assoc()) {
        $search_labels[] = $row['search_query'];
        $search_data[] = $row['search_count'];
    }
}
?>

<script>
const popCtx = document.getElementById('popularChart');
new Chart(popCtx, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($pop_labels); ?>,
        datasets: [{
            data: <?php echo json_encode($pop_data); ?>,
            backgroundColor: ['#4caf50', '#81c784', '#c8e6c9', '#388e3c', '#1b5e20']
        }]
    }
});

const searchCtx = document.getElementById('searchChart');
new Chart(searchCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($search_labels); ?>,
        datasets: [{
            label: 'Search Count',
            data: <?php echo json_encode($search_data); ?>,
            backgroundColor: '#2e7d32'
        }]
    }
});
</script>

</body>
</html>
