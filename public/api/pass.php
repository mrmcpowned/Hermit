<?php

require_once "../../common/config.php";
require_once "../../common/functions.php";
require_once "../../common/Hacker.php";
require_once "../../common/PasswordManager.php";

$user = new Hacker($db);
$passManager = new PasswordManager($db, $user);

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

if(!isset($_POST['change_type'])){
    $errors['Missing Type'][] = "Type of action is missing";
    json_response($errors);
}

//It's time to ... SWITCH! *Nintendo Switch click noise*
switch ($_POST[]) {

    case 0:

    default:
        $errors['Missing Type'][] = "Type is not a valid option";

}