<?php
function db() {
    // Detect environment
    $is_local = in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1']);

    if ($is_local) {
        // Local XAMPP connection
        $host = "localhost";
        $user = "root";
        $pass = "";
        $dbname = "swiftpoa";
    } else {
        // Render or hosted environment
        $host = getenv("DB_HOST");
        $user = getenv("DB_USER");
        $pass = getenv("DB_PASS");
        $dbname = getenv("DB_NAME");
    }

    // Create connection
    $conn = new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    return $conn;
}
?>
