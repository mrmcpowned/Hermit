<?php

/**
 * Used to create a haceker without a hacker having to log in. Useful for sending personalized emails
 */
class VirtualHacker extends Hacker
{

    private $uniqueID;

    protected $userSelectSQL = "SELECT * FROM hackers WHERE id = :id";

    public function __construct(PDO $dbPDO, $uniqueID)
    {
        $this->uniqueID = $uniqueID;
        $this->userSetup();
    }

    protected function userSetup()
    {
        $query = $this->db->prepare($this->userSelectSQL);
        $query->bindParam(":id", $this->uniqueID);

        $query->execute();

        $this->userInfo = $query->fetch(PDO::FETCH_ASSOC);

        unset($this->userInfo['pass']);
        unset($this->userInfo['sid']);

    }

}