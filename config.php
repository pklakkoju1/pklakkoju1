<?php
$servername = "localhost";
$username = "pavan";
$password = "P@1kumar";
$dbname = "cable_network";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
