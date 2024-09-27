<?php 
$hostName = "localhost";
$dbAdmin = "root";
$dbPassword = "";
$dbName = "pms";

$connection = new mysqli($hostName, $dbAdmin, $dbPassword, $dbName);

if ($connection->connect_error) {
  die("Something went wrong; " . $connection->connect_error);
}


?>