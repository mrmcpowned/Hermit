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
        if ($this->isLoggedIn())
            $this->userSetup();
        $this->sessionCheck();
        $this->extendSession();
    }

    /**
     * Sets up a logged in user by storing user details in an associative array
     */
    protected function userSetup()
    {
        $sql = "SELECT * FROM hackers WHERE sid = :sid";

        $query = $this->db->prepare($sql);
        $sid = $this->getSID();
        $query->bindParam(":sid", $sid);
        $query->execute();

        $this->userInfo = $query->fetch(PDO::FETCH_ASSOC);

        //If the SID On the server changed, our fetch came up false
        if($this->userInfo === false){
            $this->logout();
            return;
        }

        $this->passHash = $this->userInfo['pass'];
        //We have to do some cleanup here so we don't accidentally expose any unwanted columns
        unset($this->userInfo['pass']);
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



    /**
     * Logs out the currently logged in user
     *
     * TODO: Possibly add a message parameter so a messaage could be left in the new session explaining what kind of
     * logout occurred.
     *
     * @return bool
     */
    public function logout()
    {
        $sid = $this->getSID();
        $sql = "UPDATE hackers SET sid = NULL, current_ip = NULL WHERE sid=:usersid";
        $query = $this->db->prepare($sql);
        $query->bindParam(":usersid", $sid);
        $query->execute();
        $this->destroySession();
        session_start();
    }

    public function getEmail()
    {
        return $this->userInfo['email'];
    }

    public function isVerified(){
        return $this->userInfo['is_email_confrmed'] == 1;
    }

}