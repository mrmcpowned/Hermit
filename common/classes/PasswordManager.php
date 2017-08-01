<?php

require_once 'Site.php';

/**
 * This should manage the updating of passwords
 */
class PasswordManager
{

    private $requiredManagementFields = [
        "change_type"
    ];

    private $managementFields = [
        "change_type" => [
            "filter" => [FILTER_SANITIZE_NUMBER_INT],
            "name" => "Action Type"
        ],
        "key" => [
            "filter" => [FILTER_DEFAULT], //Since we're only checking for this hash, there's no need to sanitize
            "name" => "Reset Key",
            "length" => [
                "min" => 8,
                "max" => 255
            ]
        ],
        "curr_pass" => [
            "filter" => [FILTER_DEFAULT],
            "name" => "Current Password",
            "length" => [
                "min" => 8,
                "max" => 255
            ]

        ],
        "new_pass" => [
            "filter" => [FILTER_DEFAULT],
            "name" => "New Password",
            "length" => [
                "min" => 8,
                "max" => 255
            ]
        ]
    ];

    /**
     * @return array
     */
    public function getRequiredManagementFields(): array
    {
        return $this->requiredManagementFields;
    }

    /**
     * @return array
     */
    public function getManagementFields(): array
    {
        return $this->managementFields;
    }

    private $db;
    /**
     * @var User
     */
    private $currentUser;
    private $userInfo;
    const COOLDOWN_SECONDS = (3 * 60); //This is to make sure people can't spam a password reset email every 5 seconds
    const RESET_WINDOW = (30 * 60);

    /**
     * PasswordManager constructor.
     * @param PDO $db
     * @param User $currentUser
     */
    public function __construct(PDO $db, User $currentUser)
    {
        $this->db = $db;
        $this->currentUser = $currentUser;
        $this->userInfo = $currentUser->getUserInfo();
        $this->managementFields['email'] = Site::$registrationFields['email'];
    }

    public function isWithinWindow($key)
    {
        $resetTime = $this->getResetTime($key);
        return time() - $resetTime <= PasswordManager::RESET_WINDOW;
    }

    public function getResetTime($key){
        $sql = "SELECT pass_reset_time FROM hackers where ";

        $isEmail = filter_var($key, FILTER_VALIDATE_EMAIL);

        if($isEmail){
            $sql .= "email = :key";
        } else {
            $sql .= "pass_reset_vid = :key";
        }

        $query = $this->db->prepare($sql);
        $query->bindParam(":key", $key);

        if (!$query->execute()) {
            throw new DatabaseErrorException($query->errorInfo());
        }

        $resetTime = $query->fetchColumn();
        if (!$resetTime) {
            throw new UnknownKeyException(($isEmail ? "Unable to find $key in the system" : "Provided key is invalid"));
        }

        return $resetTime;
    }

    /**
     * Change password for currently logged in user
     * @param $email
     * @param $newPassword string New password to assign
     * @throws UnknownUserException
     */
    public function updatePassword($email, $newPassword)
    {

        $sql = "UPDATE hackers SET pass = :pass WHERE email = :email";
        $newPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $sqlQuery = $this->db->prepare($sql);
        $sqlQuery->bindParam(":pass", $newPassword);
        $sqlQuery->bindParam(":email", $email);

        if (!$sqlQuery->execute()) {
            throw new DatabaseErrorException($sqlQuery->errorInfo());
        }
        if($sqlQuery->rowCount() < 1){
            throw new UnknownUserException("No user found with email $email");
        }

    }

    public function isPastRequestCooldown($email)
    {
        $resetTime = $this->getResetTime($email);
        return (time() - $resetTime) >= PasswordManager::COOLDOWN_SECONDS;
    }

    public function changePasswordByKey($key, $newPassword)
    {
        $sql = "UPDATE hackers SET pass = :pass, pass_reset_vid = NULL WHERE pass_reset_vid = :vid";
        $newPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $sqlQuery = $this->db->prepare($sql);
        $sqlQuery->bindParam(":pass", $newPassword);
        $sqlQuery->bindParam(":vid", $key);

        if (!$sqlQuery->execute()) {
            throw new DatabaseErrorException($sqlQuery->errorInfo());
        }
        if ($sqlQuery->rowCount() < 1){
            throw new UnknownUserException("No user with verification ID $key");
        }

    }

    /**
     * Sets a password reset verification ID
     * @param $email string Email to lookup by
     * @return array|string If no errors arise, the nan array containing the key is returned, else a
     * string with the error
     * @throws UnknownUserException
     */
    public function setResetKeyByEmail($email){
        $sql = "UPDATE hackers SET pass_reset_vid = :pass_reset_vid, pass_reset_time = UNIX_TIMESTAMP() WHERE email = :email";
        $resetKey = generateSID();

        $sqlQuery = $this->db->prepare($sql);
        $sqlQuery->bindParam(":pass_reset_vid", $resetKey);
        $sqlQuery->bindParam(":email", $email);

        if (!$sqlQuery->execute()) {
            throw new DatabaseErrorException($sqlQuery->errorInfo());
        }
        if ($sqlQuery->rowCount() < 1){
            throw new UnknownUserException("No user with email $email");
        }

        return $resetKey;
    }
    
}