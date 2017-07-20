<?php

/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 7/20/2017
 * Time: 3:15 AM
 */
class UnauthenticatedException extends Exception
{

    /**
     * UnauthenticatedException constructor.
     * @param $message
     * @param null $code
     * @internal param string $string
     */
    public function __construct($message, $code = null)
    {
        $this->message = $message;
        $this->code = $code;
    }
}