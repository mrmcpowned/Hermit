<?php

/**
 * This should manage the updating of passwords
 */
class PasswordManager
{

    private $db;
    /**
     * @var User
     */
    private $currentUser;
    private $userInfo;
    const COOLDOWN_SECONDS = (5 * 60);
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
    }

    public function isWithinWindow()
    {
        return (time() - $this->userInfo['pass_reset_time']) <= PasswordManager::RESET_WINDOW;
    }

    /**
     * Change password for currently logged in user
     * @param $newPassword string New password to assign
     */
    public function updatePassword($newPassword)
    {
        $hashedPassword = password_hash($newPassword, $newPassword);
        $sql = "UPDATE hackers SET pass = :pass";
    }

    public function isPastRequestCooldown()
    {
        return (time() - $this->userInfo['pass_reset_time']) >= PasswordManager::COOLDOWN_SECONDS;
    }

    public function changePasswordByKey($key, $newPassword)
    {

    }
    
}