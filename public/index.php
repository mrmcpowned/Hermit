<?php
require_once '../common/config.php';

/**
 *
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 6/14/2017
 * Time: 11:09 PM
 */
$isLogged = $user->isLoggedIn();
$fname = $user->getFirstName();
var_dump($isLogged);
var_dump($fname);

if(!$isLogged){
    $user->login("me@dank.memes", "password");
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <?php if( $isLogged ): ?>
        <h1>Hi there <?= $fname ?></h1>
    <?php else: ?>
        <form action="" method="post">

            <input type="text" name="user">
            <input type="password" name="pass">
            <button type="submit">Send</button>
        </form>
    <?php endif; ?>

    <?php
    if($isLogged)
        $user->logout();
    var_dump($user->isLoggedIn());
    ?>
</body>
</html>
