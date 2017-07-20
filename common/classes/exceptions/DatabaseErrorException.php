<?php

/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 7/20/2017
 * Time: 2:34 AM
 */
class DatabaseErrorException extends PDOException
{


    /**
     * DatabaseErrorException constructor.
     * @param mixed $message
     * @param null $code
     */
    public function __construct($message, $code = null)
    {
        if(is_array($message))
            $message = implode(" - ", $message);

        $this->message = $message;
        $this->code = $code;
    }
}