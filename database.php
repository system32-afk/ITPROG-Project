<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


try {
    $conn = new mysqli(
        getenv("MYSQLHOST"),
        getenv("MYSQLUSER"),
        getenv("MYSQLPASSWORD"),
        getenv("MYSQL_DATABASE"),
        getenv("MYSQL_PORT")
    );
} catch (mysqli_sql_exception) {
    die("Database connection failed: " . $e->getMessage());
}


?>