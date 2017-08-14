<?php
session_start();

require_once __DIR__ . "/constants.php";

try {
    $db = new PDO("mysql:host=" . DB_HOST .";dbname=" . DB_NAME, DB_USER, DB_PASS);
} catch (PDOException $e) {
    die("Error connecting to database: " . $e->getMessage());
}

require_once __DIR__ . "/functions.php";
require_once __DIR__ . "/classes/Autoloader.php";
require_once __DIR__ . "/../vendor/autoload.php";

$site = new Site($db);
