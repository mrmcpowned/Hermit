<?php

/**
 * Class Site
 * The Site class handles site operations. Things like creating files or updating settings.
 */
class Site
{

    /*
     *
     */

    /*
     * EMAILS
     *
     * - Whitelist a list of emails
     * - Literally just only get domain and check against DB once
     * - Email whitelist will be its own table
     *
     */


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

    public function getValidEmails()
    {
        return ['.edu', 'mymdc.net'];
    }

    public function isAcceptingRegistrations()
    {
        return true;
    }

    public function isAcceptingWalkIns()
    {
        return false;
    }


}