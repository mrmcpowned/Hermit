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
    private $db;

    /**
     * User constructor.
     * @param $dbPDO PDO PDO Object to interface with DB
     */
    public function __construct($dbPDO)
    {
        $this->db = $dbPDO;
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

        if ($result) {
            return $result[0];
        }
    }

    public function getLastName()
    {
        if (!$this->isLoggedIn())
            return;


    }

    public function login($email, $pass)
    {
        $sql = "SELECT * FROM users WHERE email=:email LIMIT 1";


        try {
            $query = $this->db->prepare($sql);
            $query->bindParam(":email", $email);
            $query->execute();

            $result = $query->fetch();

            //Result is only false on no match
            if (!$result) {
                return false;
            }
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
        return isset($_SESSION['sid']);
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
        return $_SESSION['sid'];
    }

    public function setSID($newSID)
    {
        $_SESSION['sid'] = $newSID;
    }

    public function destroySID()
    {
        unset($_SESSION['sid']);
    }

    public function logout()
    {
        if (!$this->isLoggedIn())
            return false;

        $sid = $this->getSID();
        $sql = "UPDATE users SET sid = NULL WHERE sid=:user-sid";
        $query = $this->db->prepare($sql);
        $query->bindParam(":user-sid", $sid);
        $this->destroySID();
        session_destroy();
        return true;
    }
}