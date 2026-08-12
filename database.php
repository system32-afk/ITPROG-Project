<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
//CREDENTIALS


try {
    $conn = new mysqli(
        getenv("MYSQLHOST"),
        getenv("MYSQLUSER"),
        getenv("MYSQLPASSWORD"),
        getenv("MYSQLDATABASE"),
        getenv("MYSQLPORT")
    );
} catch (mysqli_sql_exception $e) {
    die("Database connection failed: " . $e->getMessage());
}


?>