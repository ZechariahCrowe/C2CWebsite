<?php

    $db_host = "localhost";
    $db_user = "root";
    $db_password = "";
    $db_name = "c2c_db";
    $conn ="";

    try{
        $conn = mysqli_connect($db_host, $db_user, $db_password, $db_name);
    }catch(mysqli_sql_exception $e){
        echo "Server is not available. Please try again later.<br>";
    }

    /*if($conn){
        echo "Database connection successful!";
    }else{
        echo "We will try to get the server up as soon as possible. Please be patient.";
    }
*/
    // I watched the following youtube video to stop having to repeatedly write the database connection code in every file: https://www.youtube.com/watch?v=-1DTYAQ25bY

?>