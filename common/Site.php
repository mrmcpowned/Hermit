<?php

/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 6/17/2017
 * Time: 2:50 PM
 */
class Site
{

    private $db;

    /**
     * Site constructor.
     */
    public function __construct($db)
    {
        $this->db = $db;
    }


}