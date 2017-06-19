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
     */
    public function __construct($dbPDO)
    {
        parent::__construct($dbPDO);
        $this->sidType = "hacker-sid";
        $this->setup();
    }

    /**
     * Sets up a logged in user by storing user details in an associative array
     */
    public function setup()
    {
        if(!$this->isLoggedIn())
            return;

        $sql = "SELECT * FROM users WHERE sid = :sid";

        $query = $this->db->prepare($sql);
        $sid = $this->getSID();
        $query->bindParam(":sid", $sid);
        $query->execute();

        $this->userInfo = $query->fetch(PDO::FETCH_ASSOC);

        //We have to do some cleanup here so we do accidentally expose any unwanted columns
        unset($this->userInfo['pass']);
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

    /**
     * Login method
     * @param $email string User's email
     * @param $pass string User's password
     * @return bool If the action succeeded or not
     */
    //TODO: Rework this function so it can be implemented better with a login endpoint
    //NOTE: Probably better that a 'User' doesn't handle login, since it doesn't allow
    //for error handling very well.
    public function login($email, $pass)
    {
        $sql = "SELECT * FROM users WHERE email=:email LIMIT 1";

        //Every attempt at login should effectively regenerate the ID, since it's an attempt at privilege elevation
        session_regenerate_id(true);

        try {
            $query = $this->db->prepare($sql);
            $query->bindParam(":email", $email);
            $query->execute();

            $result = $query->fetch();

            //Result is only false on no match
            if (!$result) {
                return false;
            }
            //Login failed due to incorrect
            if (!password_verify($pass, $result['pass'])) {
                return false;
            }

            $sql = "UPDATE users SET sid = :session WHERE id=:user";
            $userSID = generateSID();
            $query = $this->db->prepare($sql);
            $query->bindParam(":user", $result['id']);
            $query->bindParam(":session", $userSID);
            $query->execute();

            $this->setSID($userSID);
            $this->extendSession();
            return true;

        } catch (PDOException $e) {
            return false;
        }


    }

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
        $sql = "UPDATE users SET sid = NULL WHERE sid=:usersid";
        $query = $this->db->prepare($sql);
        $query->bindParam(":usersid", $sid);
        $query->execute();
        $this->destroySession();
        session_start();
        return true;
    }

    public function hasSessionExpired()
    {
        return (time() > $_SESSION['discard_after']);
    }

    public function extendSession()
    {
        $_SESSION['discard_after'] = SESSION_EXPIRATION_SECONDS + time();
    }
}