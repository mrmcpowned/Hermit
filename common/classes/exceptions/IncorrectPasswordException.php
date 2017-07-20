<?php

/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 7/20/2017
 * Time: 3:26 AM
 */
class IncorrectPasswordException extends Exception
{

    /**
     * IncorrectPasswordException constructor.
     * @param string $message
     * @param null $code
     * @internal param string $string
     */
    public function __construct($message, $code = null)
    {
        $this->message = $message;
        $this->code = $code;
    }
}