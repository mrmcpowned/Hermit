<?php

/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 7/20/2017
 * Time: 10:33 PM
 */
class EmailVerify
{

    private $db;

    const REQUEST_COOLDOWN = (3 * 60);
    const EXPIRE_TIME = (60 * 60);

    /**
     * EmailVerify constructor.
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getVerifyTime($key)
    {
        $sql = "SELECT email_verify_time FROM hackers WHERE is_email_confirmed = 0 AND ";

        if(filter_var($key, FILTER_VALIDATE_EMAIL)){
            $sql .= "email = :key";
        } else {
            $sql .= "email_vid = :key";
        }

        $query = $this->db->prepare($sql);
        $query->bindParam(":key", $key);

        if (!$query->execute()) {
            throw new DatabaseErrorException($query->errorInfo());
        }
        $verifyTime = $query->fetchColumn();
        //If no users were returned, then the $verifyTime is false
        if (!$verifyTime) {
            throw new UnknownKeyException("Verification key is invalid or user is already verified");
        }
        return $verifyTime;
    }

    public function isPastCooldown($verifyTime)
    {
        return (time() - $verifyTime) >= self::REQUEST_COOLDOWN;
    }

    public function isWithinWindow($verifyTime)
    {
        return (time() - $verifyTime) <= self::EXPIRE_TIME;
    }

    public function verifyEmail($key)
    {
        $sql = "UPDATE hackers SET is_email_confirmed = 1, email_vid = NULL WHERE email_vid = :email_vid";
        $query = $this->db->prepare($sql);
        $query->bindParam(":email_vid", $key);

        if (!$query->execute()) {
            throw new DatabaseErrorException($query->errorInfo());
        }
    }

    public function resetVID($email)
    {
        $newEmailID = generateSID();
        $sql = "UPDATE hackers SET email_vid = :email_vid, email_verify_time = UNIX_TIMESTAMP() WHERE email = :email AND is_email_confirmed = 0";
        $query = $this->db->prepare($sql);
        $query->bindParam(":email_vid", $newEmailID);
        $query->bindParam(":email", $email);

        if (!$query->execute()) {
            throw new DatabaseErrorException($query->errorInfo());
        }

        if ($query->rowCount() < 1) {
            throw new EmailException("The email '$email' does not exist or is already verified.");
        }

        return $newEmailID;

    }
}