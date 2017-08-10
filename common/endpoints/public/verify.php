<?php
/**
 * This file handles email verification
 *
 * To verify an email we need:
 *  - A key to check against
 *  - The ability to resend verification should the previous request expire
 *  - Ability to resend verification email at will, but only when logged in and not already verified
 *
 */

$emailVerifier = new EmailVerify($db);
$mailer = new Mailer($db);

$allowedFields = [
    "action",
    "key",
];

if (!isset($_POST['type'])) {
    throw new MissingFieldException("Action field is missing");
}

if(!$site->isAcceptingRegistrations()){
    throw new RegistrationException("Registration is closed");
}

$type = $_POST['type'];

switch ($type) {
    case VerificationType::VERIFY:
        if (empty($_POST['key']))
            throw new MissingFieldException("There is no verification key provided");

        $key = $_POST['key'];

        $verifyTime = $emailVerifier->getVerifyTime($key);

        //If the key expired
        if(!$emailVerifier->isWithinWindow($verifyTime)){
            throw new ExpiredKeyException("Please request a new key as the one supplied has expired");
        }

        $emailVerifier->verifyEmail($key);

        $response['message']['Email Verified'] = "Your email has been successfully verified";

        break;

    case VerificationType::RESET:

        if(!$user->isLoggedIn()){
            throw new UnauthenticatedException("Please login before resetting verification ID");
        }

        if($user->isVerified()){
            throw new EmailException("Your email is already verified");
        }

        $verifyTime = $emailVerifier->getVerifyTime($user->getEmail());

        //If the key is requested too quickly
        if(!$emailVerifier->isPastCooldown($verifyTime)){
            throw new ExpiredKeyException("Please wait a few minutes to request a new key");
        }

        $newKey = $emailVerifier->resetVID($user->getEmail());

//        $errors['Success'][] = $newKey;

        //DONE: Email new key

        $mailer->queueMail($user->getEmail(), "Please verify your email address for ShellHacks",
            $mailer->generateHTML("verify.html.twig", ["user" => $user, "key" => $newKey])
        );

        break;

    default:
        throw new UnknownTypeException("Action of type '$type' is invalid.");
}