<?php
require_once '../../common/config.php';
/**
 * This page displays info about the currently logged in hacker
 */
$user = new Hacker($db);
$loader = new Twig_Loader_Filesystem(PUBLIC_TEMPLATES_PATH);
$twig = new Twig_Environment($loader);

$context = [
    "site" => $site,
    "user" => $user
];

$template = "dash-me.html.twig";

if (!$user->isLoggedIn()){
    $template = "login.html.twig";
}

echo $twig->render($template, $context);