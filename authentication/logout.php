<?php
session_start();


if (empty($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

session_destroy();


header("Location: login.php");
exit();
?>