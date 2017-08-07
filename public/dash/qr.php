<?php
require_once '../../common/config.php';
/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 7/26/2017
 * Time: 10:39 PM
 */

$user = new Hacker($db);
$loader = new Twig_Loader_Filesystem(PUBLIC_TEMPLATES_PATH);
$twig = new Twig_Environment($loader);

$context = [
    "site" => $site,
    "user" => $user
];

$template = "dash-qr.html.twig";

if (!$user->isLoggedIn()){
    $template = "login.html.twig";
}

echo $twig->render($template, $context);