<?php
require_once '../common/config.php';
$user = new Hacker($db);
$loader = new Twig_Loader_Filesystem(PUBLIC_TEMPLATES_PATH);
$twig = new Twig_Environment($loader);

/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 7/26/2017
 * Time: 10:42 PM
 */

$context = [
    "user" => $user,
    "site" => $site
];

echo $twig->render('forgot.html.twig', $context);