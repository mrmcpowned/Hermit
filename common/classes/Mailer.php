<?php

/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 7/23/2017
 * Time: 8:51 PM
 */
class Mailer
{

    private $db;
    private $mail;

    /**
     * Mailer constructor.
     * @param PDO $db
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;

        $this->mail = new PHPMailer();
        $this->mail->setFrom("no-reply@shellhacks.net", "ShellHacks");

        $this->mail->isSMTP();                                      // Set mailer to use SMTP
        $this->mail->SMTPAuth = true;                               // Enable SMTP authentication
        $this->mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted
        $this->mail->Host = 'smtp.mandrillapp.com';                 // Specify main and backup server
        $this->mail->Port = 587;                                    // Set the SMTP port
        $this->mail->Username = MANDRILL_USERNAME;                  // SMTP username
        $this->mail->Password = MANDRILL_APIKEY;                    // SMTP password
        $this->mail->SMTPDebug = 2;
        $this->mail->Debugoutput = 'error_log';

        $this->mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );


        $this->mail->isHTML(true);                                  // Set email format to HTML

    }


    /**
     * Gets an array of emails to be processed
     */
    public function getEmails(){
        $sql = "SELECT * FROM mail_queue WHERE sending_status = 1 LIMIT 10";
        $query = $this->db->prepare($sql);

        if(!$query->execute()){
            throw new DatabaseErrorException($query->errorInfo());
        }

        return $query->fetchAll(PDO::FETCH_ASSOC);

    }

    public function setMessageStatus($messageID, $status, $errorMessage = null){
        $sql = "UPDATE mail_queue SET sending_status = :status";

        if($errorMessage != null)
            $sql .= ", error_message = :error_message";

        $sql .= " WHERE id = :id";

        $query = $this->db->prepare($sql);
        $query->bindParam(":id", $messageID);
        $query->bindParam(":status", $status);

        if($errorMessage != null)
            $query->bindParam(":error_message", $errorMessage);

        if(!$query->execute()){
            throw new DatabaseErrorException($query->errorInfo());
        }

        if($query->rowCount() < 1){
            throw new DatabaseErrorException("Message ID '$messageID' was not updated");
        }

    }

    public function queueMail($address, $subject, $message){
        $sql = "INSERT INTO mail_queue (email, subject, message, sending_status) VALUES (:email, :subject, :message, 1)";
        $query = $this->db->prepare($sql);
        $query->bindParam(":email", $address);
        $query->bindParam(":subject", $subject);
        $query->bindParam(":message", $message);

        if(!$query->execute()){
            throw new DatabaseErrorException($query->errorInfo());
        }
    }

    public function sendMail($address, $subject, $message){

        $this->mail->ClearAddresses();
        $this->mail->addAddress($address);
        $this->mail->Subject = $subject;
        $this->mail->msgHTML($message);

        if (!$this->mail->send()) {
            throw new MailerException($this->mail->ErrorInfo);
        }
    }

    
    public function generateHTML($template, $context)
    {
        $loader = new Twig_Loader_Filesystem(EMAIL_TEMPLATES_PATH);
        $twig = new Twig_Environment($loader);

        return $twig->render($template, $context);
    }

    public function cleanQueue(){
        $sql = "DELETE FROM mail_queue WHERE sending_status = 3";
        $query = $this->db->prepare($sql);

        if(!$query->execute()){
            throw new DatabaseErrorException($query->errorInfo());
        }
    }

}
