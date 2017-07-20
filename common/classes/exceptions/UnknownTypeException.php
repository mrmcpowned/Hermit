<?php

/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 7/20/2017
 * Time: 4:00 AM
 */
class UnknownTypeException extends Exception
{

    /**
     * UnknownTypeException constructor.
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