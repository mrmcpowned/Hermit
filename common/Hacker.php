<?php
require_once "User.php";

/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 6/18/2017
 * Time: 6:27 PM
 */
class Hacker extends User
{

    /**
     * Hacker constructor.
     * @param $dbPDO PDO Database connection
     */
    public function __construct($dbPDO)
    {
        $this->db = $dbPDO;
        $this->sidType = "hacker-sid";
        $this->userSetup();
        $this->sessionCheck();
        $this->extendSession();
    }

    /**
     * Sets up a logged in user by storing user details in an associative array
     */
    public function userSetup()
    {
        if (!$this->isLoggedIn())
            return;

        $sql = "SELECT * FROM hackers WHERE sid = :sid";

        $query = $this->db->prepare($sql);
        $sid = $this->getSID();
        $query->bindParam(":sid", $sid);
        $query->execute();

        $this->userInfo = $query->fetch(PDO::FETCH_ASSOC);

        //We have to do some cleanup here so we don't accidentally expose any unwanted columns
        unset($this->userInfo['pass']);
        $this->passHash = $this->userInfo['id'];
        unset($this->userInfo['id']);
        unset($this->userInfo['sid']);

    }

    public function getFirstName()
    {
        return $this->userInfo['f_name'];
    }

    public function getLastName()
    {
        return $this->userInfo['l_name'];
    }

    //DONE: Rework this function so it can be implemented better with a login endpoint

    /**
     * Logs out the currently logged in user
     *
     * @return bool
     */
    public function logout()
    {
        if (!$this->isLoggedIn())
            return false;

        $sid = $this->getSID();
        $sql = "UPDATE hackers SET sid = NULL, current_ip = NULL WHERE sid=:usersid";
        $query = $this->db->prepare($sql);
        $query->bindParam(":usersid", $sid);
        $query->execute();
        $this->destroySession();
        session_start();
        return true;
    }
}