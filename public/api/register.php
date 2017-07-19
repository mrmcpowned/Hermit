<?php

require_once "../../common/config.php";
require_once "../../common/functions.php";
require_once "../../common/Hacker.php";

$user = new Hacker($db);

/**
 * This file handles user registration
 */

/*
 * Things to get done:
 * TODO: Finish this file
 * DONE: Commit to git (I'm always commiting to git)
 */


//DONE: Moved this to site config
//This defines what we will consider valid data taken from a POST for Hacker profile creation or update
$acceptableFields = $site->getRegistrationFields();

$requiredFields = $site->getRequiredRegistrationFields();


//DONE: Handle registration logic (Registration logic has been handled completely)
//DONE: Handle validation logic (The validation logic has been automated using a config array)
//TODO: [FUTURE] Possibly split validation in a way that it can be reused for backend user creation

/*
 * Register pre-flight check, FAIL if:
 *
 * - Registration is closed OR Walk-Ins are Disabled - DONE
 * - Required fields are empty - DONE
 * - Required fields to not meet minimum/maximum requirements - DONE
 * - Fields do not pass validation - DONE
 * - File is over limit OR file is not of allowed types - DONE
 *   - File shouldn't be a required field for walk-ins
 *
 * Only the allowed fields will be processed, all others will be ignored
 *
 * Information should be sanitized before entering the database
 *
 * After pre-flight check, query should be built programatically, since there could be omitted fields
 *
 */

//Check if the request was sent via post
if(!isset($_POST))
    die;

//Set our header
header("Content-Type: application/json");


//Create array to push error messages to
$errors = [];

//Check if we're NOT accepting registrations OR walk-ins


if($user->isLoggedIn())
    $errors['Registration'][] = "You're already registered";

if(!($site->isAcceptingRegistrations() OR $site->isAcceptingWalkIns()))
    $errors['Registration'] = "Sorry, registrations are currently closed";

json_response($errors);

//Check captcha
$response = $_POST['g-recaptcha-response'];

if(!recaptcha_verify($response, RECAPTCHA_SECRET)){
    $errors['Captcha'][] = "Captcha failed to verify";
    json_encode($errors);
}

unset($_POST['g-recaptcha-response']);



//Sanitize all inputs

sanitize_array($_POST, $acceptableFields);

//Perform specific checks

$missing = missing_fields($requiredFields, $_POST, $acceptableFields);

//If our set of missing fields is greater than zero, then we're missing fields...
if(count($missing) > 0){

    //Check which specific required fields are missing
    foreach($missing as $field){
        $errors['Missing Fields'][] = "Field '" . $field['name'] . "' is missing.";
    }

    //Output the response
    json_response($errors);
}

//Make sure users accept the MLH terms of service
if(!$_POST['mlh_accept'])
    $errors['MLH Code of Conduct'][] = "Please accept the MLH Code of Conduct";

//We only check if the user has accepted, saving this response is of no real value
unset($_POST['mlh_accept']);

//By now we have all our required fields, now we need to make sure all fields are following length and value checks
validate_array($_POST, $acceptableFields, $errors);
json_response($errors);

//If the email does not end with one of the whitelisted domains, throw an error
$found = false;
foreach($site->getValidEmails() as $address){
    if(endswith($_POST['email'], $address)) {
        $found = true;
        break;
    }
}
if(!$found)
    $errors['Email'][] = "The email address you entered is not in the list of whitelisted domains";

//No need to run a query if it's not an acceptable email
json_response($errors);

//Check if there are any errors so far, and if so, execute a response

/*
 * DONE: There shouldn't already be an email in the system for the user supplied
 * DONE: User shouldn't be able to register with an email address whose domain is not whitelisted
 */

try {
    $userQuery = $db->prepare("SELECT COUNT(*) FROM hackers WHERE email = :email");
    $userQuery->bindValue(":email", $_POST['email']);
    if(!$userQuery->execute())
        $errors['Database Error'][] = $userQuery->errorInfo();

    $queryResult = $userQuery->fetchColumn(0);
    if($queryResult > 0)
        $errors['Email'][] = "That email already exists in our system";
} catch (Exception $e) {
    $errors['Database Error'][] = $e->getMessage();
}

json_response($errors);

/*
 * By now, all text data is sanitized and validated
 * Next, we have to validate the resume upload and check if the email is in the database
 */



//Resume file check
/*
 * Resume shouldn't be:
 * - More than 2MB
 * - Any format other than doc, docx, or pdf
 * - Required if it's a walk-in
 */
//We don't require resumes from walk-ins

if(!isset($_FILES['resume']) AND !$site->isAcceptingWalkIns()){
    $errors['Resume'][] = "A resume is required.";
    json_response($errors);
}

if(isset($_FILES['resume'])){

    $resume = $_FILES['resume'];

    //We shouldn't be having more than 1 file uploaded
    if(count($resume['name']) > 1){
        $errors['Resume'] = "Please only upload 1 file";
    }

    //If PHP has an upload error, respect it
    if($resume['error'] !== UPLOAD_ERR_OK){
        $errors['Resume'][] = codeToMessage($resume['error']);
    }

    //If file is not of the acceptable type, throw an error
    if(!is_acceptable_file_type($resume['type'])){
        $errors['Resume'][] = "Resume is not in one of the acceptable file formats";
    }

    //If size is greater than 2MB, error
    if($resume['size'] > 2000000){
        $errors['Resume'][] = "Resume is larger than 2MB. Please upload a smaller file";
    }

    json_response($errors);

    //Now prep the file for move
    $fileInfo = pathinfo($resume['name']);

    $newName = $_POST['f_name'] . "-" . $_POST['l_name'] . "-" . generateAlphaCode();
    $newName = filter_var($newName, FILTER_SANITIZE_URL);
    $newName .= "." . $fileInfo['extension'];

    $success = move_uploaded_file($resume["tmp_name"],
        "../../common/resumes/" . $newName);
    if (!$success){
        $errors['Resume'][] = "Error saving file to directory";
    }

    $_POST['resume'] = $newName;
}

//DONE: Generate Check-in code (at size 4) and check for collision. If collision, generate at size 5
$checkInCode = generateAlphaCode(4);

/*
 * This bit of code checks if the Check-In Code already exists, and if so, generates at size 5
 *
 * Then you might as "But Chris, what happens if there's a collision there?"
 * To which I'd respond "The script will produce a database error since that column is unique, thus requiring
 * our applicant to simply press 'submit' once again."
 */
try {
    $codeCheckQuery = $db->prepare("SELECT COUNT(*) FROM hackers WHERE check_in_code = :code");
    $codeCheckQuery->bindParam(":code", $checkInCode);
    if (!$codeCheckQuery->execute())
        $errors['Database Error'][] = $codeCheckQuery->errorInfo();

    $queryResult = $codeCheckQuery->fetchColumn(0);
    if ($queryResult > 0)
        $checkInCode = generateAlphaCode(5);
} catch (Exception $e) {
    $errors['Database Error'][] = $e->getMessage();
}

$_POST['check_in_code'] = $checkInCode;

json_response($errors);

//Now we need to create our email verification id, which we can do the same way as SIDs
$_POST['email_vid'] = generateSID();

//Can't forget to hash the password. Last thing we need is to be storing this in plaintext
$_POST['pass'] = password_hash($_POST['pass'], PASSWORD_DEFAULT);

//DONE: Work on SQL query with all items as placeholders and fill in those that are optional using an array of binds

//First we have to prepare the values
$preparedPairs = [];

foreach ($_POST as $key => $value){
    $preparedKey = ":$key";
    $preparedPairs[$preparedKey] = $value;
}

$columnNames = implode(", ", array_keys($_POST));
$columnValues = implode(", ", array_keys($preparedPairs));

//TODO: [IN PROGRESS] Implement the rest of the columns for new users (refer to notebook) and the email verification, timestamp etc
//TODO: Get to work on the validation controller
//TODO: Get to work on the email queue

$columnNames = "($columnNames, date_created)";
$columnValues = "($columnValues, UNIX_TIMESTAMP())";

$newUserSQL = "INSERT INTO hackers $columnNames VALUES $columnValues";

//DONE: Do what's below with the other query
try {
    $newUserQuery = $db->prepare($newUserSQL);
    if(!$newUserQuery->execute($preparedPairs))
        throw new Exception($newUserQuery->errorInfo());

} catch (Exception $e) {
    $errors['Database Error'][] = $e->getMessage();
    json_response($errors);
}
http_response_code(201);
json_response($errors, false);