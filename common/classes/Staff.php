<?php


/*
 * - Staff will have permissions
 * - Permissions will be bitwise, but also with named types
 * - Named representation of whether the use has a certain permission will
 * also be created in an array to allow for easy management from a view context
 *
 *
 * Permissions are as follows:
 *
 *  - view stats
 *  - view hackers (Name, GitHub, LinkedIn, School, Age) [Meant more for sponsors]
 *  - download resumes [Meant more for sponsors]
 *  - add hackers (Full View)
 *  - delete hackers
 *  - edit hackers
 *  - check-in hackers (has its own check-in page/view)
 *  - view staff
 *  - add staff
 *  - delete staff
 *  - edit staff
 *  - edit site config
 *
 */

/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 6/18/2017
 * Time: 6:27 PM
 */
class Staff extends User
{
    protected $whereClauseSQL = "WHERE hackers.sid = :id";
    protected $userSelectSQL = "SELECT * FROM staff ";

    public function __construct($dbPDO)
    {
        $this->db = $dbPDO;
        $this->sidType = "staff-sid";
        if ($this->isLoggedIn())
            $this->userSetup();
        $this->sessionCheck();
        $this->extendSession();
    }

    protected function userSetup()
    {
        $query = $this->db->prepare($this->userSelectSQL . $this->whereClauseSQL);
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

    public function logout()
    {
        $sid = $this->getSID();
        $sql = "UPDATE staff SET sid = NULL, current_ip = NULL WHERE sid=:usersid";
        $query = $this->db->prepare($sql);
        $query->bindParam(":usersid", $sid);
        $query->execute();
        $this->destroySession();
        session_start();
    }

}