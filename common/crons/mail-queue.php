<?php

require_once "../config.php";

/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 7/17/2017
 * Time: 12:35 AM
 */

$mailer = new Mailer($db);

foreach ($mailer->getEmails() as $email){
    try{
        //Set to SENDING
        $mailer->setMessageStatus($email['id'], 2);
        //Send
        $mailer->sendMail($email['email'], $email['subject'], $email['message']);
        //Set to SENT
        $mailer->setMessageStatus($email['id'], 3);
    } catch (MailerException $e){
        //Set to FAILED
        $mailer->setMessageStatus($email['id'], 4, $e->getMessage());
    }
}


echo "Done at " . time();