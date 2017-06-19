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

require_once "functions.php";
require_once 'Hacker.php';

session_start();
$user = new Hacker($db);


//Only logout a user if they were logged in and their session has expired
if ($user->isLoggedIn() && $user->hasSessionExpired()) {
    // session has taken too long between requests
    $user->logout();
}

$user->extendSession();


