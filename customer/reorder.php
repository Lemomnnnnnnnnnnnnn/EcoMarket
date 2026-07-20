<?php
include("../auth/customer.php");
include("../config/database.php");

$user_id = $_SESSION['user_id'];
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

$stmt = $conn->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$owns_order = $stmt->get_result()->num_rows === 1;

$added = 0;
$skipped = 0;

if ($owns_order) {
    $items_stmt = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $order_items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($order_items as $item) {
        if (empty($item['product_id'])) {
            $skipped++;
            continue;
        }

        $stock_check = $conn->query("SELECT stock FROM products WHERE id = " . intval($item['product_id']));

        if ($stock_check && $stock_check->num_rows == 1) {
            $stock = $stock_check->fetch_assoc()['stock'];

            if ($stock > 0) {
                $qty_to_add = min($item['quantity'], $stock);

                $cart_stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)");
                $cart_stmt->bind_param("iii", $user_id, $item['product_id'], $qty_to_add);
                $cart_stmt->execute();
                $added++;
            } else {
                $skipped++;
            }
        } else {
            $skipped++;
        }
    }
}

$note = $skipped > 0 ? "reordered=1&skipped=" . $skipped : "reordered=1";
header("Location: cart.php?" . $note);
exit();
?>
