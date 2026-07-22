<?php

//CREDENTIALS

    $db_server = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "pabili_db";
    $conn = "";
    $db_port = 3309;

    try{
    $conn = mysqli_connect($db_server,$db_user,$db_pass,$db_name, $db_port);
    }catch(mysqli_sql_exception){
       
    }

    
?>