<?php

/**
 * Used to create a haceker without a hacker having to log in. Useful for sending personalized emails
 */
class VirtualHacker extends Hacker
{

    private $userID;

    public function __construct(PDO $dbPDO, $userID)
    {
        $this->userID = $userID;
        $this->userSetup();
    }

    protected function userSetup()
    {
        $sql = "SELECT * FROM hackers WHERE id = :id";
        $query = $this->db->prepare($sql);
        $query->bindParam(":id", $this->userID);

        $query->execute();

        $this->userInfo = $query->fetch(PDO::FETCH_ASSOC);

        unset($this->userInfo['pass']);
        unset($this->userInfo['sid']);
    }

}