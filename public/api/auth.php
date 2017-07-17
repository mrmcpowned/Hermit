<?php

require_once '../../common/config.php';
require_once '../../common/Hacker.php';
/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 6/18/2017
 * Time: 10:46 PM
 */

$user = new Hacker($db);

$errors = [];

//TODO: Handle auth with proper logic
/*
 * Proper auth logic flow:
 *
 * Email should be sanitized
 * Email should not be empty
 * Email should not be less than minimum character limit
 * Password should not be less than minimum character limit
 *
 */
//TODO: Maybe handle QR check-ins here too? (Nah, prolly best to be its own file)
/*
 * QR Code auth could be generated at the confirmation stage, with an endpoint guarding against collisions
 */

if(!isset($_POST))
    die;

header("Content-Type: application/json");

//First we prep using the predefined sanitization config
$allFields = $site::getRegistrationFields();

$allowedFields = [];

$requiredFields = [
    'email',
    'pass'
];

$allowedFields['email'] = $allFields['email'];
$allowedFields['pass'] = $allFields['pass'];

//now we sanitize input
sanitize_array($_POST,$allowedFields);

if(!isset($_POST['email'])){
    $errors['Missing Field'][] = "Email is missing";
}

if(!isset($_POST['pass'])){
    $errors['Missing Field'][] = "Password is missing";
}

json_response($errors);

//Validate

validate_array($_POST, $allowedFields, $errors);
json_response($errors);



//Finally initiate login
$email = $_POST['email'];
$pass = $_POST['pass'];
$sql = "SELECT * FROM hackers WHERE email=:email LIMIT 1";

//Every attempt at login should effectively regenerate the php session ID, since it's an attempt at privilege elevation
session_regenerate_id(true);

try {
    $query = $db->prepare($sql);
    $query->bindParam(":email", $email);
    $query->execute();

    $result = $query->fetch();

    //Result is only false on no match
    if (!$result) {
        //TODO: Log incorrect attempt
        throw new Exception("Email is incorrect");
    }
    //Login failed due to incorrect
    if (!password_verify($pass, $result['pass'])) {
        //TODO: Log incorrect attempt
        throw new Exception("Password is incorrect");
    }

    $sql = "UPDATE hackers SET sid = :session, current_ip = :current_ip WHERE id=:user";
    $userSID = generateSID();
    $query = $db->prepare($sql);
    $query->bindParam(":user", $result['id']);
    $query->bindParam(":session", $userSID);
    $query->bindParam(":current_ip", $_SERVER['REMOTE_ADDR']);
    $query->execute();

    $user->setSID($userSID);
    $user->extendSession();

} catch (PDOException $e) {
    //If we've reached this stage, chances are I won't be able to log this unless it's in a flat file
    $errors['Database Error'][] = $e->getMessage();
} catch (Exception $e){
    $errors['Login Error'][] = $e->getMessage();
}

json_response($errors, false);