<?php

define("DB_HOST", "localhost");
define("DB_USER", "hermit");
define("DB_PASS", "9LGmAIdbWi6KgmWK");
define("DB_NAME", "test");

define("RECAPTCHA_SECRET", "6LcIOikUAAAAAKOqTEqRnC-S1O2W-3m2vBISyzhq");

define('SESSION_EXPIRATION_SECONDS', 3600); //1 Hour Expiration


try {
    $db = new PDO("mysql:host=" . DB_HOST .";dbname=" . DB_NAME, DB_USER, DB_PASS);
} catch (PDOException $e) {
    die("Error connecting to database: " . $e->getMessage());
}

session_start();

require_once "functions.php";
require_once "Site.php";

$site = new Site($db);