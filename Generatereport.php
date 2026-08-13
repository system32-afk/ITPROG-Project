<?php

require "vendor/autoload.php";
require_once "database.php";
require "vendor/setasign/fpdf/fpdf.php";

// swap for $_SESSION['vendor_id'] once login/auth is wired up.
// Hardcoded to match the pattern already used across the admin pages.
$vendorID = 1;

/* ============================
   RESOLVE DATE RANGE
============================ */

$range = $_GET['range'] ?? 'today';

switch ($range) {
    case 'week':
        $startDate = date('Y-m-d', strtotime('monday this week'));
        $endDate = date('Y-m-d', strtotime('sunday this week'));
        $rangeLabel = 'This Week';
        break;
    case 'month':
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');
        $rangeLabel = 'This Month';
        break;
    case 'today':
    default:
        $range = 'today';
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
        $rangeLabel = 'Today';
        break;
}

/* ============================
   VENDOR INFO
============================ */

$getVendorInfo = $conn->prepare("SELECT store_name FROM vendor_tbl WHERE vendor_id = ?");
$getVendorInfo->bind_param("i", $vendorID);
$getVendorInfo->execute();
$vendorInfo = $getVendorInfo->get_result()->fetch_assoc();
$storeName = $vendorInfo['store_name'] ?? 'Store';

/* ============================
   SUMMARY STATS
   Revenue is computed from order line items (quantity * menu price)
   rather than orders_tbl.total, same approach as the dashboard --
   orderitems_tbl doesn't store its own price, so it always needs the
   join back to menuitems_tbl.
============================ */

$ordersStmt = $conn->prepare(
    "SELECT COUNT(*) AS total FROM orders_tbl
     WHERE vendor_id = ? AND DATE(created_at) BETWEEN ? AND ? AND status != 'canceled'"
);
$ordersStmt->bind_param("iss", $vendorID, $startDate, $endDate);
$ordersStmt->execute();
$totalOrders = (int) $ordersStmt->get_result()->fetch_assoc()['total'];

$canceledStmt = $conn->prepare(
    "SELECT COUNT(*) AS total FROM orders_tbl
     WHERE vendor_id = ? AND DATE(created_at) BETWEEN ? AND ? AND status = 'canceled'"
);
$canceledStmt->bind_param("iss", $vendorID, $startDate, $endDate);
$canceledStmt->execute();
$canceledOrders = (int) $canceledStmt->get_result()->fetch_assoc()['total'];

$revenueStmt = $conn->prepare(
    "SELECT COALESCE(SUM(oi.quantity * m.price), 0) AS revenue
     FROM orderitems_tbl oi
     JOIN menuitems_tbl m ON oi.item_id = m.item_id
     JOIN orders_tbl o ON oi.order_id = o.order_id
     WHERE o.vendor_id = ? AND DATE(o.created_at) BETWEEN ? AND ? AND o.status != 'canceled'"
);
$revenueStmt->bind_param("iss", $vendorID, $startDate, $endDate);
$revenueStmt->execute();
$totalRevenue = (float) $revenueStmt->get_result()->fetch_assoc()['revenue'];

$avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

/* ============================
   REVENUE BY DAY
============================ */

$dailyStmt = $conn->prepare(
    "SELECT DATE(o.created_at) AS day,
            COUNT(DISTINCT o.order_id) AS orders,
            COALESCE(SUM(oi.quantity * m.price), 0) AS revenue
     FROM orderitems_tbl oi
     JOIN menuitems_tbl m ON oi.item_id = m.item_id
     JOIN orders_tbl o ON oi.order_id = o.order_id
     WHERE o.vendor_id = ? AND DATE(o.created_at) BETWEEN ? AND ? AND o.status != 'canceled'
     GROUP BY DATE(o.created_at)
     ORDER BY day ASC"
);
$dailyStmt->bind_param("iss", $vendorID, $startDate, $endDate);
$dailyStmt->execute();
$dailyBreakdown = $dailyStmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* ============================
   REVENUE BY PAYMENT METHOD
============================ */

$paymentStmt = $conn->prepare(
    "SELECT o.payment_method,
            COUNT(DISTINCT o.order_id) AS orders,
            COALESCE(SUM(oi.quantity * m.price), 0) AS revenue
     FROM orderitems_tbl oi
     JOIN menuitems_tbl m ON oi.item_id = m.item_id
     JOIN orders_tbl o ON oi.order_id = o.order_id
     WHERE o.vendor_id = ? AND DATE(o.created_at) BETWEEN ? AND ? AND o.status != 'canceled'
     GROUP BY o.payment_method"
);
$paymentStmt->bind_param("iss", $vendorID, $startDate, $endDate);
$paymentStmt->execute();
$paymentBreakdown = $paymentStmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* ============================
   PDF
   FPDF's core fonts don't cover the ₱ glyph (it's outside
   Windows-1252), so amounts are labeled "PHP" instead of ₱ here --
   the rest of the app keeps using ₱ since browsers render UTF-8 fine.
============================ */

function money($amount)
{
    return "PHP " . number_format((float) $amount, 2);
}

class ReportPDF extends FPDF
{
    public $storeName = '';
    public $rangeLabel = '';

    function Header()
    {
        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(50, 114, 253);
        $this->Cell(0, 10, $this->storeName, 0, 1, 'C');

        $this->SetFont('Arial', '', 13);
        $this->SetTextColor(30, 41, 59);
        $this->Cell(0, 8, 'Sales Report - ' . $this->rangeLabel, 0, 1, 'C');

        $this->SetFont('Arial', 'I', 9);
        $this->SetTextColor(100, 116, 139);
        $this->Cell(0, 6, 'Generated ' . date('M d, Y g:i A'), 0, 1, 'C');

        $this->Ln(6);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 10, 'Powered by Pabili - Page ' . $this->PageNo(), 0, 0, 'C');
    }

    function SummaryBox($label, $value, $x, $y, $w)
    {
        $this->SetFillColor(245, 247, 255);
        $this->Rect($x, $y, $w, 22, 'F');

        $this->SetXY($x, $y + 4);
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(100, 116, 139);
        $this->Cell($w, 5, $label, 0, 2, 'C');

        $this->SetX($x);
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(30, 41, 59);
        $this->Cell($w, 8, $value, 0, 2, 'C');
    }

    function SectionTitle($title)
    {
        $this->Ln(4);
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(30, 41, 59);
        $this->Cell(0, 8, $title, 0, 1, 'L');
        $this->SetDrawColor(221, 229, 245);
        $this->Line($this->GetX(), $this->GetY(), $this->GetX() + 190, $this->GetY());
        $this->Ln(3);
    }

    function TableHeader($headers, $widths)
    {
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(238, 242, 255);
        $this->SetTextColor(67, 56, 202);
        foreach ($headers as $i => $header) {
            $this->Cell($widths[$i], 8, $header, 0, 0, 'L', true);
        }
        $this->Ln();
    }

    function TableRow($cells, $widths, $aligns = [])
    {
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(30, 41, 59);
        foreach ($cells as $i => $cell) {
            $align = $aligns[$i] ?? 'L';
            $this->Cell($widths[$i], 8, (string) $cell, 0, 0, $align);
        }
        $this->Ln();
    }

    function EmptyNotice($text)
    {
        $this->SetFont('Arial', 'I', 10);
        $this->SetTextColor(100, 116, 139);
        $this->Cell(0, 8, $text, 0, 1);
    }
}

$pdf = new ReportPDF();
$pdf->storeName = $storeName;
$pdf->rangeLabel = $rangeLabel;
$pdf->AddPage();

// Summary boxes
$boxWidth = 44;
$gap = 4;
$startX = 10;
$boxY = $pdf->GetY();
$pdf->SummaryBox('TOTAL ORDERS', (string) $totalOrders, $startX, $boxY, $boxWidth);
$pdf->SummaryBox('TOTAL REVENUE', money($totalRevenue), $startX + ($boxWidth + $gap), $boxY, $boxWidth);
$pdf->SummaryBox('AVG ORDER VALUE', money($avgOrderValue), $startX + ($boxWidth + $gap) * 2, $boxY, $boxWidth);
$pdf->SummaryBox('CANCELED ORDERS', (string) $canceledOrders, $startX + ($boxWidth + $gap) * 3, $boxY, $boxWidth);

$pdf->SetY($boxY + 30);

// Revenue by day
$pdf->SectionTitle('Revenue by Day');
if (empty($dailyBreakdown)) {
    $pdf->EmptyNotice('No sales recorded in this period.');
} else {
    $pdf->TableHeader(['Date', 'Orders', 'Revenue'], [90, 40, 60]);
    foreach ($dailyBreakdown as $row) {
        $pdf->TableRow(
            [date('M d, Y', strtotime($row['day'])), $row['orders'], money($row['revenue'])],
            [90, 40, 60],
            ['L', 'L', 'R']
        );
    }
}

// Revenue by payment method
$pdf->SectionTitle('Revenue by Payment Method');
if (empty($paymentBreakdown)) {
    $pdf->EmptyNotice('No sales recorded in this period.');
} else {
    $pdf->TableHeader(['Method', 'Orders', 'Revenue'], [90, 40, 60]);
    foreach ($paymentBreakdown as $row) {
        $pdf->TableRow(
            [$row['payment_method'], $row['orders'], money($row['revenue'])],
            [90, 40, 60],
            ['L', 'L', 'R']
        );
    }
}

$filename = 'SalesReport_' . ucfirst($range) . '_' . date('Ymd') . '.pdf';
$pdf->Output('D', $filename);
?>