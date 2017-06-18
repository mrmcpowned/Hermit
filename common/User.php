<?php

/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 6/13/2017
 * Time: 9:01 PM
 */
class User
{

    /**
     * @var PDO Type of user, be it hacker or backend
     */
    protected $db;
    protected $sidType;

    /**
     * User constructor.
     * @param $dbPDO PDO PDO Object to interface with DB
     */
    public function __construct($dbPDO)
    {
        $this->db = $dbPDO;
        $this->sidType = "user-sid";
    }

    public function getFirstName()
    {
        if (!$this->isLoggedIn())
            return;


        $sid = $this->getSID();
        $sql = "SELECT f_name FROM users WHERE sid=:usersid";
        $query = $this->db->prepare($sql);
        $query->bindParam(":usersid", $sid);
        $query->execute();

        $result = $query->fetch();

        return $result[0];


    }

    public function getLastName()
    {
        if (!$this->isLoggedIn())
            return;

        $sid = $this->getSID();
        $sql = "SELECT l_name FROM users WHERE sid=:usersid";
        $query = $this->db->prepare($sql);
        $query->bindParam(":usersid", $sid);
        $query->execute();

        $result = $query->fetch();

        return $result[0];

    }

    public function register($email, $pass)
    {
        //TODO: Register with only email or registration with all the necessary details?

        $sql = "INSERT INTO users (email, pass) VALUES (:email, :pass) ";

        $passHash = password_hash($pass, PASSWORD_DEFAULT);

        $query = $this->db->prepare($sql);
        $query->bindParam(":email", $email);
        $query->bindParam(":pass", $passHash);

        $query->execute();

    }


    /**
     * Login method
     * @param $email string User's email
     * @param $pass string User's password
     * @return bool If the action succeeded or not
     */
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
            extendSession();
            return true;

        } catch (PDOException $e) {
            return false;
        }


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
     * @return null or string
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

    public function destroySession(){
        session_unset();
        session_destroy();
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
}