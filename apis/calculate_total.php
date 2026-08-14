<?php

require "/database.php";
require "/apis/cart_functions.php";

$data = json_decode(file_get_contents("php://input"), true);

$total = calculateCartTotal($conn, $data['cart']);

echo json_encode([
    "total" => $total
]);
?>