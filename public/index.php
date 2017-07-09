<?php
require_once '../common/config.php';
require_once '../common/Hacker.php';
require_once '../vendor/autoload.php';
$user = new Hacker($db);
$loader = new Twig_Loader_Filesystem('../templates/public');

$twig = new Twig_Environment($loader);


/**
 *
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 6/14/2017
 * Time: 11:09 PM
 */
$isLogged = $user->isLoggedIn();
$fname = $user->getFirstName();

//$context = [
//    "isLoggedIn" => $isLogged,
//    "fname" => $fname
//];

$context = [
    "user" => $user
];

var_dump($isLogged);
var_dump($_POST);
var_dump($_SESSION);

if(!$isLogged && isset($_POST['type'])) {
    var_dump($_POST);
//    $_POST['user'] = filter_var($_POST['user'], FILTER_SANITIZE_STRING);
    $_POST = array_map('trim', $_POST);
    var_dump($_POST);
    if($_POST['type'] == "login"){
        var_dump($user->login($_POST['user'], $_POST['pass']));
    } else {
//        $user->register($_POST['user'], $_POST['pass']);
    }
    header("Location: /hermit/public");
}

echo $twig->render('index.twig', $context);

/*<!doctype html>
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
        <select name="type" id="">
            <option value="login">Login</option>
            <option value="register">Register</option>
        </select>
        <button type="submit">Send</button>
    </form>
<?php endif; ?>

<?php
//    if($isLogged)
//        $user->logout();
var_dump($user->isLoggedIn());
var_dump($_SESSION);
?>
</body>
</html>
?>
*/

