<?php

/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 7/25/2017
 * Time: 12:20 AM
 */
class EmailHacker extends VirtualHacker
{
    protected $whereClauseSQL = "WHERE hackers.email = :id";
}