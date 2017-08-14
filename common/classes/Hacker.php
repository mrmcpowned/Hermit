<?php

/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 6/18/2017
 * Time: 6:27 PM
 */
class Hacker extends User
{

    protected $whereClauseSQL = "WHERE hackers.sid = :id";
    protected $userSelectSQL = "SELECT * FROM hackers ";
    protected $labelSelectSQL = "SELECT genders.name AS gender , diets.name AS diet, class_years.year AS class_year,
     races.name AS race, shirt_sizes.shirt_size AS shirt_size, schools.name AS school, states.name AS state,
     majors.major AS major
     FROM hackers 
     INNER JOIN genders
        ON hackers.gender = genders.id
     INNER JOIN diets
        ON hackers.diet_restriction = diets.id
     INNER JOIN class_years
        ON hackers.class_year = class_years.id
     INNER JOIN races
        ON hackers.race = races.id
     INNER JOIN shirt_sizes
        ON hackers.shirt_size = shirt_sizes.id
     INNER JOIN schools
        ON hackers.school = schools.id
     INNER JOIN states
        ON hackers.state = states.id 
    INNER JOIN majors
        ON hackers.major = majors.id";

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
        $query = $this->db->prepare($this->userSelectSQL . $this->whereClauseSQL);
        $sid = $this->getSID();
        $query->bindParam(":id", $sid);
        $query->execute();

        $this->userInfo = $query->fetch(PDO::FETCH_ASSOC);

        $query = $this->db->prepare($this->labelSelectSQL . $this->whereClauseSQL);
        $query->bindParam(":id", $sid);
        $query->execute();

        $this->userInfoLabel = $query->fetch(PDO::FETCH_ASSOC);

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

    public function getAge()
    {
        return $this->userInfo['age'];
    }

    public function getGender()
    {
        return $this->userInfo['gender'];
    }

    public function getClassYear()
    {
        return $this->userInfo['class_year'];
    }

    public function getSchool()
    {
        return $this->userInfo['school'];
    }

    public function getRace()
    {
        return $this->userInfo['race'];
    }

    public function getMajor()
    {
        return $this->userInfo['major'];
    }

    public function getState()
    {
        return $this->userInfo['state'];
    }

    public function getShirtSize()
    {
        return $this->userInfo['shirt_size'];
    }

    public function getDietRestriction()
    {
        return $this->userInfo['diet_restriction'];
    }

    public function getGenderLabel()
    {
        return $this->userInfoLabel['gender'];
    }

    public function getClassYearLabel()
    {
        return $this->userInfoLabel['class_year'];
    }

    public function getSchoolLabel()
    {
        return $this->userInfoLabel['school'];
    }

    public function getRaceLabel()
    {
        return $this->userInfoLabel['race'];
    }

    public function getMajorLabel()
    {
        return $this->userInfoLabel['major'];
    }

    public function getStateLabel()
    {
        return $this->userInfoLabel['state'];
    }

    public function getShirtSizeLabel()
    {
        return $this->userInfoLabel['shirt_size'];
    }

    public function getDietRestrictionLabel()
    {
        return $this->userInfoLabel['diet_restriction'];
    }

    public function getDietOther()
    {
        return $this->userInfo['diet_other'];
    }

    public function getGithub()
    {
        return $this->userInfo['github'];
    }

    public function getLinkedin()
    {
        return $this->userInfo['linkedin'];
    }

    public function getCheckInCode()
    {
        return $this->userInfo['check_in_code'];
    }

    public function isFirstHackathon()
    {
        return filter_var($this->userInfo['is_first_hackathon'], FILTER_VALIDATE_BOOLEAN);
    }

    public function isHispanic()
    {
        return filter_var($this->userInfo['is_hispanic'], FILTER_VALIDATE_BOOLEAN);
    }

    public function isVerified(){
        return filter_var($this->userInfo['is_email_confirmed'], FILTER_VALIDATE_BOOLEAN);
    }

    public function getTimeVerified(){
        return filter_var($this->userInfo['email_verify_time'], FILTER_VALIDATE_INT);
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

    public function getRegistrationState()
    {
        //I can cheat like this in PHP and it makes me feel dirty.
        global $site;

        //This only happens if the flag has been set on the admin backend
        if($this->isEntranceDenied())
            return RegistrationState::DENIED;

        //canAttend is a boolean we can flip on the backend
        if($this->isWalkIn() || $this->canAttend())
            return RegistrationState::ACCEPTED;

        //If we are verified and verified within the registration window, then we're registered
        if(($this->isVerified() && ($site->isWithinRegistrationWindow($this->getTimeVerified()))))
            return RegistrationState::REGISTERED;

        //EMail not verified and site isn't accepting registrations means you've been rejected (for tardiness)
        if(!$this->isVerified() && !$site->isAcceptingRegistrations())
            return RegistrationState::REJECTED;

        return RegistrationState::UNVERIFIED;

    }

    public function getRegistrationStateLabel()
    {
        return RegistrationState::STATE_NAMES[$this->getRegistrationState()];
    }

    public function isAccepted()
    {
        switch ($this->getRegistrationState()){
            case RegistrationState::ACCEPTED:
                return true;

            default:
                return false;
        }
    }

}