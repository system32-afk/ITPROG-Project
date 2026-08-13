<?php
require_once "database.php";

session_start();
//check if user has loggedin
if (!isset($_SESSION)) {
    header("Location: ./login.php");
}
$vendorID = $_SESSION["vendor_id"];

$getVendorInfo = $conn->prepare("SELECT store_name FROM vendor_tbl WHERE vendor_id = ?");
$getVendorInfo->bind_param("i", $vendorID);
$getVendorInfo->execute();
$vendorResult = $getVendorInfo->get_result();
$vendorInfo = $vendorResult->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pabili Reports</title>

    <link rel="stylesheet" href="css/reports.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap"
        rel="stylesheet">
</head>

<body>

    <div class="sidebar">

        <div class="logo">
            <h2><?php echo htmlspecialchars($vendorInfo["store_name"]); ?></h2>
        </div>

        <ul class="menu">
            <li>
                <a href="admindashboard.php">
                    <i class="fa-solid fa-chart-line"></i>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="livequeue.php">
                    <i class="fa-solid fa-utensils"></i>
                    Live Queue
                </a>
            </li>
            <li>
                <a href="inventory.php">
                    <i class="fa-solid fa-box"></i>
                    Inventory
                </a>
            </li>
            <li>
                <a href="menumanagement.php">
                    <i class="fa-solid fa-clipboard-list"></i>
                    Menu Management
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <a href="reports.php" class="active">
                <i class="fa-solid fa-chart-pie"></i>
                Reports
            </a>
            <a href="#">
                <i class="fa-solid fa-circle-question"></i>
                Help
            </a>
        </div>

    </div>

    <div class="main">

        <div class="page-header">
            <div class="page-header-left">
                <h1 class="page-title">Reports</h1>
                <p class="page-description">Download a sales summary PDF for a given period.</p>
            </div>
        </div>

        <div class="report-cards">

            <div class="report-card">
                <div class="report-icon">
                    <i class="fa-solid fa-sun"></i>
                </div>
                <h3>Today</h3>
                <p>Sales summary for today only.</p>
                <a href="generateReport.php?range=today" class="download-btn" download>
                    <i class="fa-solid fa-download"></i>
                    Download PDF
                </a>
            </div>

            <div class="report-card">
                <div class="report-icon">
                    <i class="fa-solid fa-calendar-week"></i>
                </div>
                <h3>This Week</h3>
                <p>Sales summary from Monday through Sunday, this week.</p>
                <a href="generateReport.php?range=week" class="download-btn" download>
                    <i class="fa-solid fa-download"></i>
                    Download PDF
                </a>
            </div>

            <div class="report-card">
                <div class="report-icon">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>
                <h3>This Month</h3>
                <p>Sales summary for the current calendar month.</p>
                <a href="generateReport.php?range=month" class="download-btn" download>
                    <i class="fa-solid fa-download"></i>
                    Download PDF
                </a>
            </div>

        </div>

    </div>

</body>

</html>