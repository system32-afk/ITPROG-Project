<?php
require_once __DIR__ . "/../database.php";

header('Content-Type: application/json');
session_start();
/* ============================
   VALID OPTIONS
   Kept server-side too, not just in the <select> markup, so a
   crafted request can't slip in an arbitrary category/station.
============================ */

const VALID_CATEGORIES = ['Main Course', 'Appetizers', 'Salads', 'Desserts'];
const VALID_STATIONS = ['GRILL', 'FRYER', 'COLD', 'PREP'];

// swap for $_SESSION['vendor_id'] once login/auth is wired up.
// Hardcoded to match the pattern already used in admindashboard.php / inventory_api.php.
$vendorId = $_SESSION['vendor_id'];

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        listMenuItems($conn, $vendorId);
        break;
    case 'add':
        addMenuItem($conn, $vendorId);
        break;
    case 'update':
        updateMenuItem($conn, $vendorId);
        break;
    case 'delete':
        deleteMenuItem($conn, $vendorId);
        break;
    case 'toggle':
        toggleMenuItem($conn, $vendorId);
        break;
    default:
        http_response_code(400);
        echo json_encode(["error" => "Unknown or missing action."]);
}

/* ============================
   LIST (paginated)
============================ */

function listMenuItems(mysqli $conn, int $vendorId): void
{
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $perPage = 4;
    $offset = ($page - 1) * $perPage;

    $countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM menuitems_tbl WHERE vendor_id = ?");
    $countStmt->bind_param("i", $vendorId);
    $countStmt->execute();
    $total = (int) $countStmt->get_result()->fetch_assoc()['total'];

    $stmt = $conn->prepare(
        "SELECT item_id, name, category, price, description, image_url, station, is_enabled
         FROM menuitems_tbl
         WHERE vendor_id = ?
         ORDER BY item_id DESC
         LIMIT ? OFFSET ?"
    );
    $stmt->bind_param("iii", $vendorId, $perPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    echo json_encode([
        "items" => $items,
        "page" => $page,
        "per_page" => $perPage,
        "total_items" => $total,
        "total_pages" => max(1, (int) ceil($total / $perPage)),
    ]);
}

/* ============================
   VALIDATION HELPER
============================ */

function validateMenuInput(array $data): ?string
{
    if (trim($data['name'] ?? '') === '') {
        return "Item name is required.";
    }
    if (!in_array($data['category'] ?? '', VALID_CATEGORIES, true)) {
        return "Please select a valid category.";
    }
    if (!is_numeric($data['price'] ?? null) || (float) $data['price'] < 0) {
        return "Please enter a valid price.";
    }
    if (!empty($data['station']) && !in_array($data['station'], VALID_STATIONS, true)) {
        return "Please select a valid kitchen station.";
    }
    return null;
}

/* ============================
   ADD
============================ */

function addMenuItem(mysqli $conn, int $vendorId): void
{
    $error = validateMenuInput($_POST);
    if ($error) {
        http_response_code(422);
        echo json_encode(["error" => $error]);
        return;
    }

    $name = trim($_POST['name']);
    $category = $_POST['category'];
    $price = (float) $_POST['price'];
    $description = trim($_POST['description'] ?? '');
    $imageUrl = trim($_POST['image_url'] ?? '');
    $station = !empty($_POST['station']) ? $_POST['station'] : null;

    $stmt = $conn->prepare(
        "INSERT INTO menuitems_tbl (vendor_id, name, category, price, description, image_url, station, is_enabled)
         VALUES (?, ?, ?, ?, ?, ?, ?, 1)"
    );
    $stmt->bind_param("issdsss", $vendorId, $name, $category, $price, $description, $imageUrl, $station);
    $stmt->execute();

    echo json_encode(["success" => true, "item_id" => $stmt->insert_id]);
}

/* ============================
   UPDATE
============================ */

function updateMenuItem(mysqli $conn, int $vendorId): void
{
    $id = (int) ($_POST['item_id'] ?? 0);
    if (!$id) {
        http_response_code(422);
        echo json_encode(["error" => "Missing item_id."]);
        return;
    }

    $error = validateMenuInput($_POST);
    if ($error) {
        http_response_code(422);
        echo json_encode(["error" => $error]);
        return;
    }

    $name = trim($_POST['name']);
    $category = $_POST['category'];
    $price = (float) $_POST['price'];
    $description = trim($_POST['description'] ?? '');
    $imageUrl = trim($_POST['image_url'] ?? '');
    $station = !empty($_POST['station']) ? $_POST['station'] : null;

    $stmt = $conn->prepare(
        "UPDATE menuitems_tbl
         SET name = ?, category = ?, price = ?, description = ?, image_url = ?, station = ?
         WHERE item_id = ? AND vendor_id = ?"
    );
    $stmt->bind_param("ssdsssii", $name, $category, $price, $description, $imageUrl, $station, $id, $vendorId);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        http_response_code(404);
        echo json_encode(["error" => "Item not found."]);
        return;
    }

    echo json_encode(["success" => true]);
}

/* ============================
   DELETE
============================ */

function deleteMenuItem(mysqli $conn, int $vendorId): void
{
    $id = (int) ($_POST['item_id'] ?? 0);
    if (!$id) {
        http_response_code(422);
        echo json_encode(["error" => "Missing item_id."]);
        return;
    }

    $stmt = $conn->prepare("DELETE FROM menuitems_tbl WHERE item_id = ? AND vendor_id = ?");
    $stmt->bind_param("ii", $id, $vendorId);
    $stmt->execute();

    echo json_encode(["success" => true, "deleted" => $stmt->affected_rows > 0]);
}

/* ============================
   TOGGLE (enable/disable)
============================ */

function toggleMenuItem(mysqli $conn, int $vendorId): void
{
    $id = (int) ($_POST['item_id'] ?? 0);
    $enabled = isset($_POST['enabled']) ? (int) $_POST['enabled'] : null;

    if (!$id || $enabled === null) {
        http_response_code(422);
        echo json_encode(["error" => "Missing item_id or enabled state."]);
        return;
    }

    $stmt = $conn->prepare("UPDATE menuitems_tbl SET is_enabled = ? WHERE item_id = ? AND vendor_id = ?");
    $stmt->bind_param("iii", $enabled, $id, $vendorId);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        http_response_code(404);
        echo json_encode(["error" => "Item not found."]);
        return;
    }

    echo json_encode(["success" => true, "is_enabled" => (bool) $enabled]);
}