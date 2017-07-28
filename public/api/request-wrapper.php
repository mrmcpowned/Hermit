<?php

require_once '../../common/config.php';

/**
 * This file wraps all the other endpoint stubs to allow for a simpler method of generating a json response
 *
 * This way the stubs themselves can exist in a non-public directory
 *
 * Accessing this page would look like the following:
 *  http://shellhacks.net/api/request-wrapper.php?type=registration
 *
 */

//Only allow post requests
if (!isset($_POST))
    die;

$errors = [];
$response = [
    "errors" => &$errors,
    "message" => []
];

header("Content-Type: application/json");

$fileName = $_GET['type'];

//Start Try
try {

    $user = new Hacker($db);

    if (! @include_once "../../common/endpoints/public/$fileName.php")
        throw new Exception("Requested endpoint does not exist");

} catch (PDOException $e) {
    $errors['Database Error'][] = $e->getMessage();
} catch (ExpiredKeyException $e){
    $errors['Expired Key'][] = $e->getMessage();
} catch (UnknownKeyException $e){
    $errors['Unknown Key'][] = $e->getMessage();
} catch (UnknownTypeException $e){
    $errors['Unknown Type'][] = $e->getMessage();
} catch (UnknownUserException $e){
    $errors['Unknown User'][] = $e->getMessage();
} catch (MissingFieldException $e){
    $errors['Missing Field'][] = $e->getMessage();
} catch (IncorrectPasswordException $e){
    $errors['Incorrect Password'][] = $e->getMessage();
} catch (UnauthenticatedException $e){
    $errors['Unauthenticated'][] = $e->getMessage();
} catch (CooldownException $e){
    $errors['Request Cooldown'][] = $e->getMessage();
} catch (CaptchaException $e) {
    $errors['Captcha Error'][] = $e->getMessage();
} catch (EmailException $e) {
    $errors['Email Error'][] = $e->getMessage();
} catch (ResumeException $e) {
    $errors['Resume Error'][] = $e->getMessage();
} catch (RegistrationException $e){
    $errors['Registration Error'][] = $e->getMessage();
} catch (Exception $e){
    $errors['General Error'][] = $e->getMessage();
}

json_response($response, false);