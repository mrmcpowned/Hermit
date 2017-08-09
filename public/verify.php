<?php
require_once '../common/config.php';
/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 7/26/2017
 * Time: 10:37 PM
 */

$user = new Hacker($db);
$loader = new Twig_Loader_Filesystem(PUBLIC_TEMPLATES_PATH);
$twig = new Twig_Environment($loader);

$context = [
    "site" => $site,
    "user" => $user
];

if(isset($_GET['key']))
    $context['key'] = $_GET['key'];

$template = "verify-email.html.twig";

if(isset($_GET['type']) && $_GET['type'] == "pass")
    $template = "verify-password.html.twig";

echo $twig->render($template, $context);