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
    "user" => $user,
    "key" => $_POST['key']
];

$template = "verfy-email.html.twig";

if(($_GET['type'] == "pass"))
    $template = "verify-password.html.twig";

echo $twig->render($template, $context);