<?php
session_start();


$_SESSION = [];


session_destroy();


// Redirect to login page
header("Location: login.php");
exit();