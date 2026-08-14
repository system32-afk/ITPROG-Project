<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$db_server = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "pabili_db";
$conn = "";
$db_port = 3309;
try {
    $conn = new mysqli(
        $db_server,
        $db_user,
        $db_pass,
        $db_name,
        $db_port
    );
} catch (mysqli_sql_exception $e) {
    die("Database connection failed: " . $e->getMessage());
}


?>