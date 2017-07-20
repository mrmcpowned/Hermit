<?php

/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 7/20/2017
 * Time: 2:08 AM
 */
class UnknownUserException extends Exception
{
    /**
     * UnknownUserException constructor.
     * @param string $message
     * @param null $code
     */
    public function __construct($message, $code = null)
    {
        $this->message = $message;
        $this->code = $code;
    }
}