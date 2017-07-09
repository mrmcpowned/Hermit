<?php

require_once "../../common/config.php";
require_once "../../common/functions.php";
require_once "../../common/Hacker.php";

$user = new Hacker($db);

/**
 * This page handles PASSWORD RESETS
 * Password Resets can happen 2 ways:
 * - The user is logged in and wishes to change their password
 * - The user has forgotten their password and wishes to reset it
 */


/*
 * Possible flow of events
 * - Is this a forgotten password, or is it a request to change an existing password? [change_type=0]
 *  - IF this is a forgotten password, and the user is NOT signed in, REQUIRE an E-Mail address [email]
 *      - Else they're signed in and we can get the email automagically
 *      - email a session ID for the email to verify
 *      - store the unix timestamp of when the request was requested (only a 30 min window should be allowed)
 *  - IF this is not a forgotten password, then the user has requested to change their own password [change_type = 1]
 *      - IF user is NOT logged in, deny the request (Only logged in users should be able to change their own passwords)
 *      - IF current password does NOT match, do not allow the action to happen
 *
 */

//Only allow post requests
if(!isset($_POST))
    die;

//
if()