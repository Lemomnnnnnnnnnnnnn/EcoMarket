<?php
$page_title = 'Subscription';
include("vendor_header.php");

$message = "";
$message_type = "success";

if (isset($_POST['change_plan'])) {
    $new_tier = $_POST['tier'] == 'premium' ? 'premium' : 'basic';
    
    // If downgrading to basic, check if they currently have more than 5 products
    $can_change = true;
    if ($new_tier == 'basic') {
        $prod_count_query = $conn->query("SELECT COUNT(*) as cnt FROM products WHERE vendor_id = '$vendor_id'");
        $prod_count = $prod_count_query->fetch_assoc()['cnt'];
        if ($prod_count > 5) {
            $message = "Error: You currently have <strong>$prod_count</strong> products. To downgrade to the Basic plan, you must delete some products until you have 5 or fewer.";
            $message_type = "danger";
            $can_change = false;
        }
    }
    
    if ($can_change) {
        $update = $conn->query("
            UPDATE vendors 
            SET subscription_tier = '$new_tier' 
            WHERE id = '$vendor_id'
        ");
        
        if ($update) {
            $message = "Subscription plan updated to <strong>" . ($new_tier == 'premium' ? 'Premium' : 'Basic') . "</strong> successfully!";
            $message_type = "success";
            
            // Re-fetch vendor info
            $check = $conn->query("SELECT * FROM vendors WHERE user_id = '$user_id'");
            $vendor = $check->fetch_assoc();
        } else {
            $message = "Error: Failed to change subscription plan. " . $conn->error;
            $message_type = "danger";
        }
    }
}
?>

<div class="vendor-container">

    <div class="vendor-header-section">
        <div class="vendor-title">
            <h1>Subscription Management</h1>
            <p>Select the plan that fits your business scale and unlocks extra capabilities.</p>
        </div>
    </div>

    <?php if ($message != ""): ?>
        <div class="notice-banner <?php echo $message_type == 'danger' ? 'danger' : ''; ?>">
            <h4>System Message</h4>
            <p><?php echo $message; ?></p>
        </div>
    <?php endif; ?>

    <div class="vendor-section-card">
        <h2>Choose Your Plan</h2>
        <p style="color: #667e65; margin-bottom: 20px;">Unlock full features like bulk spreadsheet uploads and unlimited listings by choosing the Premium plan.</p>
        
        <div class="sub-cards-container">
            <!-- Basic Plan -->
            <div class="sub-card <?php echo $vendor['subscription_tier'] == 'basic' ? 'active-sub' : ''; ?>">
                <?php if ($vendor['subscription_tier'] == 'basic'): ?>
                    <span class="current-plan-badge">Current Plan</span>
                <?php endif; ?>
                
                <div class="sub-card-header">
                    <h3>Basic Tier</h3>
                    <p style="color:#667e65; font-size:14px;">Great for small home gardeners</p>
                </div>
                
                <div class="sub-price">
                    RM 0.00 <span style="font-size:14px; font-weight:normal; color:#667e65;">/ month</span>
                </div>
                
                <ul class="sub-features">
                    <li>List up to 5 products</li>
                    <li>Manual inventory updates</li>
                    <li>Standard vendor profile</li>
                    <li style="color:#a0a0a0; text-decoration:line-through; font-style:italic; list-style-type:none;"><span style="color:#c62828;">✗</span> CSV bulk upload feature</li>
                </ul>
                
                <form method="POST" action="subscription.php">
                    <input type="hidden" name="tier" value="basic">
                    <?php if ($vendor['subscription_tier'] == 'basic'): ?>
                        <button type="button" class="btn btn-outline" style="width:100%;" disabled>Active Plan</button>
                    <?php else: ?>
                        <button type="submit" name="change_plan" class="btn btn-outline" style="width:100%;">Downgrade to Basic</button>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Premium Plan -->
            <div class="sub-card premium-card <?php echo $vendor['subscription_tier'] == 'premium' ? 'active-sub' : ''; ?>">
                <?php if ($vendor['subscription_tier'] == 'premium'): ?>
                    <span class="current-plan-badge">Current Plan</span>
                <?php endif; ?>
                
                <div class="sub-card-header">
                    <h3>Premium Tier</h3>
                    <p style="color:#d97706; font-size:14px; font-weight:600;">Best for commercial farms & retailers</p>
                </div>
                
                <div class="sub-price" style="color: #d97706;">
                    RM 29.90 <span style="font-size:14px; font-weight:normal; color:#667e65;">/ month</span>
                </div>
                
                <ul class="sub-features">
                    <li>List unlimited products</li>
                    <li>Bulk spreadsheet uploads</li>
                    <li>Premium store profile indicator</li>
                    <li>Priority search highlighting</li>
                </ul>
                
                <form method="POST" action="subscription.php">
                    <input type="hidden" name="tier" value="premium">
                    <?php if ($vendor['subscription_tier'] == 'premium'): ?>
                        <button type="button" class="btn btn-primary" style="width:100%; background:#d97706;" disabled>Active Plan</button>
                    <?php else: ?>
                        <button type="submit" name="change_plan" class="btn btn-primary" style="width:100%; background:#d97706; border-color:#d97706;">Upgrade to Premium</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

</div>

<?php
include("vendor_footer.php");
?>
