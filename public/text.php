<?php
/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 7/26/2017
 * Time: 1:05 AM
 */

require_once "../common/config.php";

$mailer = new Mailer($db);

echo $mailer->generateHTML("verify.html.twig", ["key" => "87733r72rkjkjehkjhfksdfy7isyfs7fy87syf78sfihkjajk"]);
