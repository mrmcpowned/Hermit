<?php

define("DB_HOST", "");
define("DB_USER", "");
define("DB_PASS", "");
define("DB_NAME", "");

define("RECAPTCHA_SECRET", "");

define('SESSION_EXPIRATION_SECONDS', 3600); //1 Hour Expiration


try {
    $db = new PDO("mysql:host=" . DB_HOST .";dbname=" . DB_NAME, DB_USER, DB_PASS);
} catch (PDOException $e) {
    die("Error connecting to database: " . $e->getMessage());
}

session_start();

require_once "functions.php";
spl_autoload_register(function ($class){
    require_once "classes/$class.class";
});

$site = new Site($db);