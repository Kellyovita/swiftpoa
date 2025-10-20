<?php
function db() {
    $host = "localhost";
    $user = "root"; // default in XAMPP
    $pass = "";     // default is empty
    $dbname = "swiftpoa"; // your database name

    $conn = new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    return $conn;
}
?>
