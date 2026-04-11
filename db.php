<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['SERVER_NAME'] == 'localhost') {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "bussiness_website";
} else {
    $host = "localhost";
    $user = "u918387447_user";
    $pass = "Ebad2005@12";
    $db = "u918387447_myuser";
}

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("ERROR: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

echo "Connected OK"; // ✅ IMPORTANT

?>