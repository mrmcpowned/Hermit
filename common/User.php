<?php

/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 6/13/2017
 * Time: 9:01 PM
 */
//TODO: Get IP verification working
abstract class User
{

    /**
     * @var PDO Database object
     */
    protected $db;
    protected $sidType;
    /**
     * @var array Associative array containing user info
     */
    protected $userInfo;

    /**
     * @var string Hash of the user's password
     */
    protected $passHash;

    /**
     * @return array Associative array containing userInfo
     */
    public function getUserInfo()
    {
        return $this->userInfo;
    }

    /**
     * Checks if user is logged in.
     * @return bool
     */
    public function isLoggedIn()
    {
        return isset($_SESSION[$this->sidType]);
    }

    /**
     * Gets the SID of the current User, returns null if not logged in.
     * @return mixed null or string
     */
    public function getSID()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }
        return $_SESSION[$this->sidType];
    }

    public function setSID($newSID)
    {
        $_SESSION[$this->sidType] = $newSID;
    }

    public function destroySID()
    {
        unset($_SESSION[$this->sidType]);
    }

    public function destroySession()
    {
        session_unset();
        session_destroy();
    }

    public function sessionCheck()
    {
        //Only logout a user if they were logged in and their session has expired
        if ($this->isLoggedIn()) {

            if($this->hasSessionExpired()) {
                // session has taken too long between requests
                $this->logout();
            }

            //TODO: Actually implement this when testing in a remote environment
            //TODO: Include an insert for the IP on login
            //Destroy a session of a user's IP doesn't match the one they logged in with
            /*if($this->getUserInfo()['current_ip'] != $_SERVER['REMOTE_ADDR'])
                $this->logout();*/
        }

    }

    public function hasSessionExpired()
    {
        return (time() > $_SESSION['discard-' . $this->sidType]);
    }

    public function extendSession()
    {
        $_SESSION['discard-' . $this->sidType] = SESSION_EXPIRATION_SECONDS + time();
    }

    public abstract function logout();

    /**
     * This method lets us verify a User's password without having to expose the hash anywhere.
     * @param $givenPassword string Password given to verify.
     * @return bool If the password was valid or not.
     */
    public function isPasswordCorrect($givenPassword){
        return password_verify($givenPassword, $this->passHash);
    }

}