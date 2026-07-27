<?php
require_once __DIR__ . "/../database.php";

header('Content-Type: application/json');

// TODO: swap for $_SESSION['vendor_id'] once login/auth is wired up.
// Hardcoded to match the pattern already used in admindashboard.php.
$vendorId = 1;

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        listInventory($conn, $vendorId);
        break;
    case 'add':
        addInventory($conn, $vendorId);
        break;
    case 'update':
        updateInventory($conn, $vendorId);
        break;
    case 'delete':
        deleteInventory($conn, $vendorId);
        break;
    case 'history':
        getHistory($conn, $vendorId);
        break;
    default:
        http_response_code(400);
        echo json_encode(["error" => "Unknown or missing action."]);
}

/* ============================
   HELPERS
============================ */

function computeStatus(float $stock, float $threshold): array
{
    if ($stock <= 0) {
        return ["status" => "Out of Stock", "class" => "out"];
    }
    if ($stock <= $threshold) {
        return ["status" => "Low Stock", "class" => "low"];
    }
    return ["status" => "In Stock", "class" => "good"];
}

function logHistory(mysqli $conn, int $inventoryId, int $vendorId, string $action, string $description): void
{
    $stmt = $conn->prepare(
        "INSERT INTO inventory_history (inventory_id, vendor_id, action, change_description)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("iiss", $inventoryId, $vendorId, $action, $description);
    $stmt->execute();
}

/* ============================
   LIST
============================ */

function listInventory(mysqli $conn, int $vendorId): void
{
    $stmt = $conn->prepare(
        "SELECT inventory_id, item_name, unit, qty_on_hand, reorder_threshold, expiry_date, is_perishable
         FROM inventory
         WHERE vendor_id = ?
         ORDER BY item_name ASC"
    );
    $stmt->bind_param("i", $vendorId);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    $lowStock = 0;
    $expiringSoon = 0;
    $thirtyDaysOut = strtotime("+30 days");

    while ($row = $result->fetch_assoc()) {
        $statusInfo = computeStatus((float)$row['qty_on_hand'], (float)$row['reorder_threshold']);
        $row['status'] = $statusInfo['status'];
        $row['status_class'] = $statusInfo['class'];

        if ($statusInfo['class'] !== 'good') {
            $lowStock++;
        }
        if ($row['expiry_date'] && strtotime($row['expiry_date']) <= $thirtyDaysOut) {
            $expiringSoon++;
        }

        $items[] = $row;
    }

    echo json_encode([
        "items" => $items,
        "stats" => ["lowStock" => $lowStock, "expiringSoon" => $expiringSoon],
    ]);
}

/* ============================
   ADD
============================ */

function addInventory(mysqli $conn, int $vendorId): void
{
    $name         = trim($_POST['name'] ?? '');
    $unit         = trim($_POST['unit'] ?? '');
    $stock        = $_POST['stock'] ?? null;
    $threshold    = $_POST['threshold'] ?? 0;
    $expiry       = $_POST['expiry'] ?? null;
    $isPerishable = isset($_POST['is_perishable']) ? (int)$_POST['is_perishable'] : 1;

    if ($name === '' || $unit === '' || $stock === null || $stock === '' || !is_numeric($stock)) {
        http_response_code(422);
        echo json_encode(["error" => "Name, unit, and a numeric quantity are required."]);
        return;
    }

    if (!$isPerishable || $expiry === '') {
        $expiry = null;
    }

    $stmt = $conn->prepare(
        "INSERT INTO inventory (vendor_id, item_name, unit, qty_on_hand, reorder_threshold, expiry_date, is_perishable)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("issddsi", $vendorId, $name, $unit, $stock, $threshold, $expiry, $isPerishable);
    $stmt->execute();

    $newId = $stmt->insert_id;
    logHistory($conn, $newId, $vendorId, "created", "Ingredient created");

    $statusInfo = computeStatus((float)$stock, (float)$threshold);

    echo json_encode([
        "success" => true,
        "inventory_id" => $newId,
        "status" => $statusInfo['status'],
        "status_class" => $statusInfo['class'],
    ]);
}

/* ============================
   UPDATE
============================ */

function updateInventory(mysqli $conn, int $vendorId): void
{
    $id        = (int)($_POST['inventory_id'] ?? 0);
    $name      = trim($_POST['name'] ?? '');
    $unit      = trim($_POST['unit'] ?? '');
    $stock     = $_POST['stock'] ?? null;
    $threshold = $_POST['threshold'] ?? null;
    $expiry    = $_POST['expiry'] ?? null;
    $reason    = trim($_POST['reason'] ?? '');

    if (!$id || $name === '' || $stock === null || $stock === '' || !is_numeric($stock) || $reason === '') {
        http_response_code(422);
        echo json_encode(["error" => "Name, stock, and a reason for the change are required."]);
        return;
    }

    // Pull current values first — needed for the vendor-ownership check
    // and to write a meaningful "before -> after" line in the history log.
    $check = $conn->prepare(
        "SELECT qty_on_hand, unit FROM inventory WHERE inventory_id = ? AND vendor_id = ?"
    );
    $check->bind_param("ii", $id, $vendorId);
    $check->execute();
    $current = $check->get_result()->fetch_assoc();

    if (!$current) {
        http_response_code(404);
        echo json_encode(["error" => "Item not found."]);
        return;
    }

    if ($expiry === '') {
        $expiry = null;
    }
    if ($threshold === null || $threshold === '') {
        $threshold = 0;
    }

    $stmt = $conn->prepare(
        "UPDATE inventory
         SET item_name = ?, unit = ?, qty_on_hand = ?, reorder_threshold = ?, expiry_date = ?
         WHERE inventory_id = ? AND vendor_id = ?"
    );
    $stmt->bind_param("ssddsii", $name, $unit, $stock, $threshold, $expiry, $id, $vendorId);
    $stmt->execute();

    $description = sprintf(
        "Stock changed from %s %s → %s %s. Reason: %s",
        $current['qty_on_hand'],
        $current['unit'],
        $stock,
        $unit,
        $reason
    );
    logHistory($conn, $id, $vendorId, "updated", $description);

    $statusInfo = computeStatus((float)$stock, (float)$threshold);

    echo json_encode([
        "success" => true,
        "status" => $statusInfo['status'],
        "status_class" => $statusInfo['class'],
    ]);
}

/* ============================
   DELETE
   (No delete button wired up in the UI yet — endpoint is ready
   for whenever you add one to the Actions column.)
============================ */

function deleteInventory(mysqli $conn, int $vendorId): void
{
    $id = (int)($_POST['inventory_id'] ?? 0);

    if (!$id) {
        http_response_code(422);
        echo json_encode(["error" => "Missing inventory_id."]);
        return;
    }

    // Log before deleting since history has no FK back to inventory.
    logHistory($conn, $id, $vendorId, "deleted", "Ingredient removed from inventory");

    $stmt = $conn->prepare("DELETE FROM inventory WHERE inventory_id = ? AND vendor_id = ?");
    $stmt->bind_param("ii", $id, $vendorId);
    $stmt->execute();

    echo json_encode(["success" => true, "deleted" => $stmt->affected_rows > 0]);
}

/* ============================
   HISTORY
============================ */

function getHistory(mysqli $conn, int $vendorId): void
{
    $id = (int)($_GET['inventory_id'] ?? 0);

    if (!$id) {
        http_response_code(422);
        echo json_encode(["error" => "Missing inventory_id."]);
        return;
    }

    $stmt = $conn->prepare(
        "SELECT action, change_description, changed_at
         FROM inventory_history
         WHERE inventory_id = ? AND vendor_id = ?
         ORDER BY changed_at DESC"
    );
    $stmt->bind_param("ii", $id, $vendorId);
    $stmt->execute();
    $result = $stmt->get_result();

    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }

    echo json_encode(["history" => $history]);
}
