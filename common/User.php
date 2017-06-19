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
     * @var PDO Database object
     */
    protected $db;
    protected $sidType;
    /**
     * @var array Associative array containing user info
     */
    protected $userInfo;

    /**
     * User constructor.
     * @param $dbPDO PDO PDO Object to interface with DB
     */
    public function __construct($dbPDO)
    {
        $this->db = $dbPDO;
        $this->sidType = "user-sid";
    }

    /**
     * @return array Associative array containing userInfo
     */
    public function getUserInfo()
    {
        return $this->userInfo;
    }

    /*
    These lines aren't really necessary right now, since ideally I'd
    fetch all fields and fields and omit the ones that don't need to be
    accessed/become publicly facing.

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

    Registration is handled differently by the Staff members
    public function register($email, $pass)
    {
        //TODO: Register with only email or registration with all the necessary details?

        $sql = "INSERT INTO users (email, pass) VALUES (:email, :pass) ";

        $passHash = password_hash($pass, PASSWORD_DEFAULT);

        $query = $this->db->prepare($sql);
        $query->bindParam(":email", $email);
        $query->bindParam(":pass", $passHash);

        $query->execute();

    }*/


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


}