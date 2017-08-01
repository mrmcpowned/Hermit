<?php

$passManager = new PasswordManager($db, $user);
$mailer = new Mailer($db);

/**
 * This page handles PASSWORD RESETS
 * Password Resets can happen 2 ways:
 * - The user is logged in and wishes to change their password
 * - The user has forgotten their password and wishes to reset it
 *
 * THIS FILE HAS BEEN TESTED AND WORKS
 *
 * FULLY REFACTORED AS STUB
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



if (!isset($_POST['change_type'])) {
    throw new MissingFieldException("Type of action is missing");
}

$type = $_POST['change_type'];

//Batch processes like these shouldn't throw exceptions so users have greater detail into what's wrong with their input
sanitize_array($_POST, $passManager->getManagementFields());
validate_array($_POST, $passManager->getManagementFields(), $errors);
json_response($response);


//It's time to ... SWITCH! *Nintendo Switch click noise*
switch ($type) {

    //DONE: Complete code for a forgotten password reset
    case VerificationType::FORGOT:
        $email = null;
        if ($user->isLoggedIn()) {
            $email = $user->getUserInfo()['email'];
        } else {
            if (!empty($_POST['email'])) {
                $email = $_POST['email'];
            } else {
                throw new MissingFieldException("Email is missing");
            }
        }
        //We now have a email we need to create a key for, but we need to check if we're spamming or not
        //DONE: Exception
        if (!$passManager->isPastRequestCooldown($email))
            throw new CooldownException("Please wait a while before initiating another reset");


        //Fun fact, in the event the email doesn't exist, the query errors out and throws an exception
        $resetKey = $passManager->setResetKeyByEmail($email);

        //Lets assume for now we have a working mailer
        //DONE: Send to mail queue table

        $mailer->queueMail($email, "Reset Forgotten Password",
            $mailer->generateHTML("forgot.html.twig", ["key" => $resetKey]
        ));

        break;
    //DONE: Complete code for changing password with current password
    case VerificationType::CHANGE:
        if (!$user->isLoggedIn()) {
            throw new UnauthenticatedException("You are currently not logged in");
        }

        if (empty($_POST['curr_pass'])) {
            throw new MissingFieldException("Current password is missing");
        }

        if (empty($_POST['new_pass'])) {
            throw new MissingFieldException("New password is missing");
        }

        $currPassword = $_POST['curr_pass'];
        $newPassword = $_POST['new_pass'];

        if (!$user->isPasswordCorrect($currPassword)) {
            throw new IncorrectPasswordException("The current password supplied was incorrect");
        }

        //By here we should have a correct password and be authenticated
        $passManager->updatePassword($user->getEmail(), $newPassword);

        break;
    //DONE: Complete code for initiating a reset after user has submitted a valid key
    case VerificationType::RESET:

        if (empty($_POST['key'])) {
            throw new MissingFieldException("No verification key was supplied");
        }

        if (empty($_POST['new_pass'])) {
            throw new MissingFieldException("No new password supplied");
        }

        $newPassword = $_POST['new_pass'];
        $resetKey = $_POST['key'];

        //Check if we're within the window to allow a reset
        if (!$passManager->isWithinWindow($resetKey)) {
            throw new ExpiredKeyException("The validation key has expired");
        }

        //Finally, change the password
        $passManager->changePasswordByKey($resetKey, $newPassword);

        break;

    default:
        throw new UnknownTypeException("$type is not a valid option");
}