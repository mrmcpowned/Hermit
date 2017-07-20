<?php

/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 7/20/2017
 * Time: 6:05 AM
 */
class CaptchaException extends Exception
{

    /**
     * CaptchaException constructor.
     * @param string $string
     */
    public function __construct($message, $code = null)
    {
        $this->message = $message;
        $this->code = $code;
    }
}