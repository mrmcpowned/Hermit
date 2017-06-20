<?php

define("DB_HOST", "localhost");
define("DB_USER", "hermit");
define("DB_PASS", "9LGmAIdbWi6KgmWK");
define("DB_NAME", "test");

define('SESSION_EXPIRATION_SECONDS', 3600); //1 Hour Expiration


try {
    $db = new PDO("mysql:host=" . DB_HOST .";dbname=" . DB_NAME, DB_USER, DB_PASS);
} catch (PDOException $e) {
    die("Error connecting to database: " . $e->getMessage());
}

session_start();

require_once "functions.php";