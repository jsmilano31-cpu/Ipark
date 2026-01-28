<?php
$servername = "s2100827.helioho.st"; // Your server name
$username = "s2100827_iparkfinal";   // Your database username
$password = "123456";                // Your database password
$dbname = "s2100827_iparkfinal";     // Your database name

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Optional: echo "Connected successfully";
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
