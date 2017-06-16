<?php

session_start();

$db_host = "localhost";
$db_user = "hermit";
$db_pass = "9LGmAIdbWi6KgmWK";
$db_name = "test";

try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
} catch (PDOException $e) {
    die("Error connecting to database: " . $e->getMessage());
}

require_once "functions.php";
require_once 'User.php';

$user = new User($db);