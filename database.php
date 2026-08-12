<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
//CREDENTIALS

$db_server = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "pabili_db";
$conn = "";
$db_port = 3309;

try {
    $conn = new mysqli(
        getenv("MYSQLHOST"),
        getenv("MYSQLUSER"),
        getenv("MYSQLPASSWORD"),
        getenv("MYSQLDATABASE"),
        getenv("MYSQLPORT")
    );
} catch (mysqli_sql_exception) {
    die("Database connection failed: " . $e->getMessage());
}


?>