<?php
require_once '../common/config.php';
$user = new Hacker($db);
$loader = new Twig_Loader_Filesystem(PUBLIC_TEMPLATES_PATH);
$twig = new Twig_Environment($loader);

$context = [
    "user" => $user,
    "site" => $site
];

if($user->isLoggedIn())
    $user->logout();

echo $twig->render('logout.html.twig', $context);