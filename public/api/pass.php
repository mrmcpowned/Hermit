<?php

require_once "../../common/config.php";
require_once "../../common/functions.php";
require_once "../../common/Hacker.php";
require_once "../../common/PasswordManager.php";

$user = new Hacker($db);
$passManager = new PasswordManager($db, $user);

//This interface acts as a pseudo enum
interface VerificationType{
    const FORGOT = 0;
    const CHANGE = 1;
    const RESET = 2;
}

/**
 * This page handles PASSWORD RESETS
 * Password Resets can happen 2 ways:
 * - The user is logged in and wishes to change their password
 * - The user has forgotten their password and wishes to reset it
 */


/*
 * Possible flow of events
 * - Is this a forgotten password, or is it a request to change an existing password? [change_type]
 *  - IF this is a forgotten password [change_type=0], and the user is NOT signed in, REQUIRE an E-Mail address [email]
 *      - Else they're signed in and we can get the email automagically
 *      - email a session ID for the email to verify
 *      - store the unix timestamp of when the request was requested (only a 30 min window should be allowed)
 *  - IF this is not a forgotten password [change_type=1], then the user has requested to change their own password
 *      - IF user is NOT logged in, deny the request (Only logged in users should be able to change their own passwords)
 *      - IF current password does NOT match, do not allow the action to happen
 *  - IF the user is resetting their password via email confirmation [change_type=2]
 *      - FIRST check if a verification ID and password were supplied
 *      - THEN check if verification ID is valid, else deny the request
 *      - IF valid, then check if the request is still within the allowed window (30 min), ELSE deny the request
 *      -
 *
 */

//Only allow post requests
if (!isset($_POST))
    die;

$errors = [];

header("Content-Type: application/json");

if(!isset($_POST['change_type'])){
    $errors['Missing Type'][] = "Type of action is missing";
    json_response($errors);
}

$type = $_POST['change_type'];

sanitize_array($_POST, $passManager->getManagementFields());


//It's time to ... SWITCH! *Nintendo Switch click noise*
switch ($type) {

    //TODO: Complete code for a forgotten password reset
    case VerificationType::FORGOT:
        $email = null;
        if($user->isLoggedIn()){
            $email = $user->getUserInfo()['email'];
        } else {
            if(isset($_POST['email'])){
                $email = $_POST['email'];
            } else {
                $errors['Missing Field'][] = "Email is missing";
                break;
            }
        }
        //We now have a email we need to create a key for

        break;
    //DONE: Complete code for changing password with current password
    case VerificationType::CHANGE:
        if(!$user->isLoggedIn()){
            $errors['Unauthenticated'][] = "You are currently not logged in";
            break;
        }

        if(!isset($_POST['curr_password'])){
            $errors['Missing Field'][] = "Current password is missing";
            break;
        }

        if(!isset($_POST['new_password'])){
            $errors['Missing Field'][] = "New password is missing";
            break;
        }

        $currPassword = $_POST['curr_password'];
        $newPassword = $_POST['new_password'];

        if(!$user->isPasswordCorrect($currPassword)){
            $errors['Incorrect Password'][] = "The current password supplied was incorrect";
            break;
        }

        //By here we should have a correct password and be authenticated
        $result = $passManager->updatePassword($newPassword);
        if($result !== true){
            $errors['Database Error'][] = $result;
        }

        break;
    //TODO: Complete code for initiating a reset after user has submitted
    case VerificationType::RESET:
        break;

    default:
        $errors['Missing Type'][] = "Type is not a valid option";
}

json_response($errors, false);