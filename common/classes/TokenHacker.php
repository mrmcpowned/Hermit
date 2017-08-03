<?php

/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 7/25/2017
 * Time: 12:20 AM
 */
class TokenHacker extends VirtualHacker
{
    protected $whereClauseSQL= "WHERE hackers.check_in_code = :id";
}