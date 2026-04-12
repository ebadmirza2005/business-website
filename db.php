<?php
$conn = mysqli_connect("localhost", "u918387447_users", "Faazpro@123", "u918387447_users");

if ($conn) {
    echo "Connected";
} else {
    echo "Failed: " . mysqli_connect_error();
}
?>