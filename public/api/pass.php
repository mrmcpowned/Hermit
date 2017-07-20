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
 *
 * THIS FILE HAS BEEN TESTED AND WORKS
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
 *      - FIRST check if a verification ID was supplied
 *      - THEN check if verification ID is valid, else deny the request
 *      - IF valid, then check if the request is still within the allowed window (30 min), ELSE deny the request
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
validate_array($_POST, $passManager->getManagementFields(), $errors);
json_response($errors);


//It's time to ... SWITCH! *Nintendo Switch click noise*
switch ($type) {

    //TODO: [SEMI-DONE] Complete code for a forgotten password reset
    case VerificationType::FORGOT:
        $email = null;
        if($user->isLoggedIn()){
            $email = $user->getUserInfo()['email'];
        } else {
            if(!empty($_POST['email'])){
                $email = $_POST['email'];
            } else {
                $errors['Missing Field'][] = "Email is missing";
                break;
            }
        }
        //We now have a email we need to create a key for, but we need to check if we're spamming or not
        $checkCooldownSQL = "SELECT pass_reset_time from hackers where email = :email";

        try {
            $query = $db->prepare($checkCooldownSQL);
            $query->bindParam(":email", $email);

            if (!$query->execute())
                throw new Exception($query->errorInfo());
            //If the email is incorrect then we get back no results
            if(!$result = $query->fetchColumn()){
                $errors['Incorrect Email'][] = "There is no user with that email address";
                break;
            }
            if (!$passManager->isPastRequestCooldown($result)){
                $errors['Reset Cooldown'][] = "Please wait a while before initiating another reset";
                break;
            }
        } catch (Exception $e) {
            $errors['Database Error'][] = $e->getMessage();
            break;
        }

        //Fun fact, in the event the email doesn't exist, the query errors out
        $result = $passManager->setResetKeyByEmail($email);
        if(is_array($result)){
            $result = $result[0];
        } else {
            $errors['Database Error'][] = $result;
            break;
        }
        //Lets assume for now we have a working mailer
        //TODO: Send to mail queue table

        $errors['Success'][] = $result;

        break;
    //DONE: Complete code for changing password with current password
    case VerificationType::CHANGE:
        if(!$user->isLoggedIn()){
            $errors['Unauthenticated'][] = "You are currently not logged in";
            break;
        }

        if(empty($_POST['curr_pass'])){
            $errors['Missing Field'][] = "Current password is missing";
            break;
        }

        if(empty($_POST['new_pass'])){
            $errors['Missing Field'][] = "New password is missing";
            break;
        }

        $currPassword = $_POST['curr_pass'];
        $newPassword = $_POST['new_pass'];

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
    //DONE: Complete code for initiating a reset after user has submitted a valid key
    case VerificationType::RESET:

        if(empty($_POST['key'])){
            $errors['Missing Field'][] = "No verification key was supplied";
            break;
        }

        if(empty($_POST['new_pass'])){
            $errors['Missing Field'][] = "No new password supplied";
            $errors['Missing Field'][] = $_POST['new_pass'];
            break;
        }

        $newPassword = $_POST['new_pass'];
        $resetKey = $_POST['key'];

        //Check if key is valid

        $result = $passManager->isValidKey($resetKey);

        if(!is_array($result)){
            $errors['Database Error'][] = $result;
            break;
        }

        //Check if we're within the window to allow a reset
        if(!$passManager->isWithinWindow($result[0])) {
            $errors['Key Expired'][] = "The validation key has expired";
        }

        $result = $passManager->changePasswordByKey($resetKey, $newPassword);

        //Finally, change the password
        if($result !== true){
            $errors['Database Error'][] = $result;
        }

        break;

    default:
        $errors['Missing Type'][] = "Type is not a valid option";
}

json_response($errors, false);