<?php

/**
 * Class Site
 * The Site class handles site operations. Things like creating files or updating settings.
 */
class Site
{

    private $db;
    /**
     * @var array Associative array holding the site's settings
     */
    private $settings;

    /**
     * Site constructor.
     */
    public function __construct($db)
    {
        $this->db = $db;
    }




}