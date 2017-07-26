<?php

/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 7/24/2017
 * Time: 12:59 AM
 */
class MailerException extends Exception
{

    /**
     * MailerException constructor.
     * @param string $message
     * @param null $code
     */
    public function __construct($message, $code = null)
    {
        $this->message = $message;
        $this->code = $code;
    }
}