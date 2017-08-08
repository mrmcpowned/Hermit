<?php

require_once "../config.php";

interface EmailState{
    const QUEUED = 1;
    const SENDING = 2;
    const SENT = 3;
    const ERROR = 4;
}

/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 7/17/2017
 * Time: 12:35 AM
 */

$mailer = new Mailer($db);

$emails = $mailer->getEmails();

//No need to continue if there's no emails queued
if(empty($emails))
    die;

foreach ($emails as $email){
    try{
        //Set to SENDING
        $mailer->setMessageStatus($email['id'], EmailState::SENDING);
    } catch (Exception $e){
        //Set to FAILED
        $mailer->setMessageStatus($email['id'], EmailState::ERROR, $e->getMessage());
    }
}
//We have to split the assignment of state from the mail sending, else it'll be possible to send dupe emails in
// the event a cron reruns the script while a previous instance is still executing.
foreach ($emails as $email){
    try{
        //Send
        $mailer->sendMail($email['email'], $email['subject'], $email['message']);
        //Set to SENT
        $mailer->setMessageStatus($email['id'], EmailState::SENT);
    } catch (Exception $e){
        //Set to FAILED
        $mailer->setMessageStatus($email['id'], EmailState::ERROR, $e->getMessage());
    }
}