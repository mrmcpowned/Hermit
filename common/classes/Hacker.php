<?php

/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 6/18/2017
 * Time: 6:27 PM
 */
class Hacker extends User
{

    protected $userSelectSQL = "SELECT * FROM hackers WHERE sid = :id";

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
        $query = $this->db->prepare($this->userSelectSQL);
        $sid = $this->getSID();
        $query->bindParam(":id", $sid);
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

    public function getFirstName()
    {
        return $this->userInfo['f_name'];
    }

    public function getLastName()
    {
        return $this->userInfo['l_name'];
    }

    public function getEmail()
    {
        return $this->userInfo['email'];
    }

    public function isVerified(){
        return filter_var($this->userInfo['is_email_confirmed'], FILTER_VALIDATE_BOOLEAN);
    }

    public function getTimeVerified(){
        return filter_var($this->userInfo['email_verify_time']);
    }

    public function isEntranceDenied()
    {
        return filter_var($this->userInfo['entrance_denied'], FILTER_VALIDATE_BOOLEAN);
    }

    public function isWalkIn()
    {
        return filter_var($this->userInfo['is_walk_in'], FILTER_VALIDATE_BOOLEAN);
    }

    public function canAttend(){
        return filter_var($this->userInfo['can_attend'], FILTER_VALIDATE_BOOLEAN);
    }

    public function getState()
    {
        //I can cheat like this in PHP and it makes me feel dirty.
        global $site;

        //This only happens if the flag has been set on the admin backend
        if($this->isEntranceDenied())
            return RegistrationState::DENIED;

        //canAttend is a boolean we can flip on the backend
        if($this->isWalkIn() || $this->canAttend())
            return RegistrationState::ACCEPTED;

        //If we are verified and verified within the registration window (or a walk-in), then we're registered
        if(($this->isVerified() && ($site->isWithinRegistrationWindow($this->getTimeVerified()))))
            return RegistrationState::REGISTERED;

        //EMail not verified and site isn't accepting registrations means you've been rejected (for tardiness)
        if(!$this->isVerified() && !$site->isAcceptingRegistrations())
            return RegistrationState::REJECTED;

        return RegistrationState::UNVERIFIED;

    }

    public function isAccepted()
    {
        switch ($this->getState()){
            case RegistrationState::ACCEPTED:
                return true;

            default:
                return false;
        }
    }

}