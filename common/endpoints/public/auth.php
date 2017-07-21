<?php

/**
 * FULLY REFACTORED AS STUB
 */

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

//First we prep using the predefined sanitization config
$allowedFields = [];

$requiredFields = [
    'email',
    'pass'
];

//We only need these two fields out of the entire config
$allowedFields['email'] = Site::$registrationFields['email'];
$allowedFields['pass'] = Site::$registrationFields['pass'];

//Go through the captcha check
$response = $_POST['g-recaptcha-response'];

if(!recaptcha_verify($response, RECAPTCHA_SECRET)){
    throw new CaptchaException("Captcha failed to verify");
}

//now we sanitize input
sanitize_array($_POST,$allowedFields);

if(!isset($_POST['email'])){
    throw new MissingFieldException("Email is missing");
}

if(!isset($_POST['pass'])){
    throw new MissingFieldException("Password is missing");
}
validate_array($_POST, $allowedFields, $errors);
json_response($errors);

//Validate

//Finally initiate login
$email = $_POST['email'];
$pass = $_POST['pass'];
$sql = "SELECT * FROM hackers WHERE email=:email";

//Every attempt at login should effectively regenerate the php session ID, since it's an attempt at privilege elevation
session_regenerate_id(true);

$query = $db->prepare($sql);
$query->bindParam(":email", $email);
$query->execute();

$result = $query->fetch();

//Result is only false on no match
if (!$result) {
    //TODO: Log incorrect attempt
    throw new Exception("Email is incorrect");
}
//Login failed due to incorrect password
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