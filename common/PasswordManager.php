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
            "name" => "Reset Key"
        ],
        "curr_password" => [
            "filter" => [FILTER_DEFAULT],
            "name" => "Current Password"
        ],
        "new_password" => [
            "filter" => [FILTER_DEFAULT],
            "name" => "New Password"
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
    const COOLDOWN_SECONDS = (3 * 60);
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
        $this->managementFields['email'] = Site::getRegistrationFields()['email'];
    }

    public function isWithinWindow()
    {
        return (time() - $this->userInfo['pass_reset_time']) <= PasswordManager::RESET_WINDOW;
    }

    /**
     * Change password for currently logged in user
     * @param $newPassword string New password to assign
     * @return bool|string
     */
    public function updatePassword($newPassword)
    {

        $hashedPassword = password_hash($newPassword, $newPassword);
        $sql = "UPDATE hackers SET pass = :pass WHERE sid = :sid";
        $email = $this->userInfo['email'];
        $newPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        try {
            $sqlQuery = $this->db->prepare($sql);
            $sqlQuery->bindParam(":pass", $newPassword);
            $sqlQuery->bindParam(":email", $email);

            if (!$sqlQuery->execute()) {
                return "Error executing SQL: " . $sqlQuery->errorInfo();
            }
        } catch (Exception $e) {
            return "Error executing SQL: " . $e->getMessage();
        }

        return true;

    }

    public function isPastRequestCooldown()
    {
        return (time() - $this->userInfo['pass_reset_time']) >= PasswordManager::COOLDOWN_SECONDS;
    }

    public function changePasswordByKey($key, $newPassword)
    {
        $sql = "UPDATE hackers SET pass = :pass WHERE pass_reset_vid = :id";
    }
    
}