<?php
require_once '../common/config.php';
/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 8/5/2017
 * Time: 8:28 PM
 */
$user = new Hacker($db);
$loader = new Twig_Loader_Filesystem(PUBLIC_TEMPLATES_PATH);
$twig = new Twig_Environment($loader);

$context = [
    "site" => $site,
    "user" => $user
];

$template = "register.html.twig";

if(isset($_GET['success']))
    $template = "register-success.html.twig";

echo $twig->render($template, $context);