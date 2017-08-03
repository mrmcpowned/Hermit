<?php

/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 7/24/2017
 * Time: 10:05 PM
 */

interface RegistrationState
{
    const DENIED = -1;
    const UNVERIFIED = 0;
    const REGISTERED = 1;
    const REJECTED = 2;
    const ACCEPTED = 3;

    const STATE_NAMES[
        self::DENIED => "Denied",
        self::UNVERIFIED => "Unverified",
        self::REGISTERED => "Registered",
        self::REJECTED => "Rejected",
        self::ACCEPTED => "Accepted"
    ];

}