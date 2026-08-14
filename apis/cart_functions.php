<?php

function calculateCartTotal(mysqli $conn, array $cart)
{
    if (empty($cart)) {
        return 0;
    }

    $ids = array_column($cart, 'id');

    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $types = str_repeat('i', count($ids));

    $stmt = $conn->prepare("
        SELECT item_id, price
        FROM menuitems_tbl
        WHERE item_id IN ($placeholders)
    ");

    $stmt->bind_param($types, ...$ids);
    $stmt->execute();

    $result = $stmt->get_result();

    $prices = [];

    while ($row = $result->fetch_assoc()) {
        $prices[$row['item_id']] = $row['price'];
    }

    $total = 0;

    foreach ($cart as $item) {
        if (isset($prices[$item['id']])) {
            $total += $prices[$item['id']] * $item['quantity'];
        }
    }

    return $total;
}