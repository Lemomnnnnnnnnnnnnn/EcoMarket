<?php
include("../auth/admin.php");
include("../config/database.php");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Check if it's a staff user
    $check = $conn->prepare("SELECT id FROM users WHERE id=? AND role='staff'");
    $check->bind_param("i", $id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            header("Location: staff_list.php?msg=Staff deleted successfully");
            exit();
        }
    }
}
header("Location: staff_list.php?msg=Error deleting staff");
exit();
?>
