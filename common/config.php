<?php

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

session_start();
$user = new User($db);


//Only logout a user if they were logged in and their session has expired
if ($user->isLoggedIn() && isset($_SESSION['discard_after']) && time() > $_SESSION['discard_after']) {
    // session has taken too long between requests
    $user->logout();
}

extendSession();


