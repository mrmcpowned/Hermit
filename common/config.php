<?php

$db_host = "localhost";
$db_user = "hermit";
$db_pass = "9LGmAIdbWi6KgmWK";
$db_name = "test";

define('SESSION_EXPIRATION_SECONDS', 3600); //1 Hour Expiration

try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
} catch (PDOException $e) {
    die("Error connecting to database: " . $e->getMessage());
}

session_start();

require_once "functions.php";