<?php
require_once '../common/config.php';
$user = new Hacker($db);
$loader = new Twig_Loader_Filesystem(PUBLIC_TEMPLATES_PATH);
$twig = new Twig_Environment($loader);
$twig->getExtension('Twig_Extension_Core')->setTimezone("America/New_York");

/**
 *
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 6/14/2017
 * Time: 11:09 PM
 */
$context = [
    "user" => $user,
    "site" => $site
];

echo $twig->render('index.html.twig', $context);