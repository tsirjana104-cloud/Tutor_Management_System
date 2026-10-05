<?php

// PDO use garera database connection establish garne code

$host = "localhost";
$user = "root";
$dbname = "tutor_system";
$password = "12345678";

try {
  $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}

if(session_status() == PHP_SESSION_NONE){
  session_start();
}

?>