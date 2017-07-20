<?php

/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 7/20/2017
 * Time: 2:23 AM
 */
class UnknownKeyException extends Exception
{
    /**
     * UnknownKeyException constructor.
     * @param string $message
     * @param null $code
     */
    public function __construct($message, $code = null)
    {
        $this->message = $message;
        $this->code = $code;
    }
}