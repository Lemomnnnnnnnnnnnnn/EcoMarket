<?php
include("../auth/customer.php");

if (!isset($_SESSION['compare_list'])) {
    $_SESSION['compare_list'] = [];
}

$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$redirect = isset($_POST['redirect']) && $_POST['redirect'] !== '' ? $_POST['redirect'] : 'product_list.php';

// only allow safe local redirects, no external urls
if (preg_match('/^[a-zA-Z0-9_\.\?\=&]+$/', $redirect) !== 1) {
    $redirect = 'product_list.php';
}

$max_compare = 4;

if ($product_id > 0) {
    $key = array_search($product_id, $_SESSION['compare_list']);

    if ($key !== false) {
        unset($_SESSION['compare_list'][$key]);
        $_SESSION['compare_list'] = array_values($_SESSION['compare_list']);
    } elseif (count($_SESSION['compare_list']) < $max_compare) {
        $_SESSION['compare_list'][] = $product_id;
    }
}

header("Location: " . $redirect);
exit();
?>
