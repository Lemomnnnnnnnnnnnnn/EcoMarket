<?php
$page_title = 'Store Settings';
include("vendor_header.php");

$message = "";
$message_type = "success";

if (isset($_POST['update_profile'])) {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    
    $store_name = $conn->real_escape_string($_POST['store_name']);
    $store_description = $conn->real_escape_string($_POST['store_description']);

    // Check email uniqueness
    $check_email = $conn->query("SELECT * FROM users WHERE email='$email' AND id != '$user_id'");
    if ($check_email->num_rows > 0) {
        $message = "Error: The email address is already in use by another user.";
        $message_type = "danger";
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        $update_user = $conn->query("
            UPDATE users 
            SET fullname='$fullname', email='$email', phone='$phone', address='$address' 
            WHERE id='$user_id'
        ");
        
        $update_vendor = $conn->query("
            UPDATE vendors 
            SET store_name='$store_name', store_description='$store_description' 
            WHERE user_id='$user_id'
        ");

        if ($update_user && $update_vendor) {
            $conn->commit();
            $_SESSION['fullname'] = $fullname; // Update session
            $message = "Profile and Store settings updated successfully!";
            $message_type = "success";
            
            // Re-fetch vendor info
            $check = $conn->query("SELECT * FROM vendors WHERE user_id = '$user_id'");
            $vendor = $check->fetch_assoc();
        } else {
            $conn->rollback();
            $message = "Error: Failed to save changes. " . $conn->error;
            $message_type = "danger";
        }
    }
}

// Fetch user data
$user_query = $conn->query("SELECT * FROM users WHERE id = '$user_id'");
$user_data = $user_query->fetch_assoc();
?>

<div class="vendor-container">

    <div class="vendor-header-section">
        <div class="vendor-title">
            <h1>Store Settings</h1>
            <p>Manage your personal profile and store branding details.</p>
        </div>
    </div>

    <?php if ($message != ""): ?>
        <div class="notice-banner <?php echo $message_type == 'danger' ? 'danger' : ''; ?>">
            <h4>System Message</h4>
            <p><?php echo $message; ?></p>
        </div>
    <?php endif; ?>

    <div class="vendor-section-card">
        <h2>Store & Profile Details</h2>
        <form method="POST" action="profile.php" class="vendor-form">
            
            <h3 style="color:#2e7d32; font-size:16px; margin-bottom:10px; border-bottom:1px solid #e0ebd9; padding-bottom:5px;">🛍️ Shop Settings</h3>
            
            <div class="form-group">
                <label>Store Name</label>
                <input type="text" name="store_name" value="<?php echo htmlspecialchars($vendor['store_name']); ?>" required>
            </div>

            <div class="form-group">
                <label>Store Description</label>
                <textarea name="store_description" required><?php echo htmlspecialchars($vendor['store_description']); ?></textarea>
            </div>

            <h3 style="color:#2e7d32; font-size:16px; margin:20px 0 10px; border-bottom:1px solid #e0ebd9; padding-bottom:5px;">👤 Personal Account Settings</h3>

            <div class="form-row">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="fullname" value="<?php echo htmlspecialchars($user_data['fullname']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Username (Read-only)</label>
                    <input type="text" value="<?php echo htmlspecialchars($user_data['username']); ?>" readonly style="background:#f3f7f2; color:#777;">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($user_data['phone']); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Address</label>
                <textarea name="address" required><?php echo htmlspecialchars($user_data['address']); ?></textarea>
            </div>

            <div style="margin-top:10px;">
                <button type="submit" name="update_profile" class="btn btn-primary">💾 Save Settings</button>
                <a href="dashboard.php" class="btn btn-outline">Cancel</a>
            </div>

        </form>
    </div>

</div>

<?php
include("vendor_footer.php");
?>
