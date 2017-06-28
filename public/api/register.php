<?php

require_once "../../common/config.php";
require_once "../../common/functions.php";

/**
 * This file handles user registration
 */

/*
 * Things to get done:
 * TODO: Finish this file
 * TODO: Commit to git
 */



//This defines what we will consider valid data taken from a POST for Hacker profile creation or update
$acceptableFields = [
    "f_name" => [
        "default" => "",
        "filter" => [FILTER_SANITIZE_STRING],
        "name" => "First Name",
        "length" => [
            "min" => 2,
            "max" => 50
        ]
    ],
    "l_name" => [
        "default" => "",
        "filter" => [FILTER_SANITIZE_STRING],
        "name" => "Last Name",
        "length" => [
            "min" => 2,
            "max" => 50
        ]
    ],
    "email" => [
        "default" => "",
        "filter" => [FILTER_SANITIZE_EMAIL],
        "validate" => FILTER_VALIDATE_EMAIL,
        "name" => "E-Mail",
        "length" => [
            "min" => 7,
            "max" => 255
        ]
    ],
    "pass" => [
        "default" => "",
        "filter" => [FILTER_DEFAULT], //Pass gets hashed, so no real issue of injection here
        "name" => "Password",
        "length" => [
            "min" => 8,
            "max" => 255
        ]
    ],
    "age" => [
        "default" => "",
        "filter" => [FILTER_SANITIZE_NUMBER_INT],
        "name" => "Age",
        "length" => [
            "min" => 2,
            "max" => 2
        ],
        "value" => [
            "min" => 15,
            "max" => 99
        ]
    ],
    "gender" => [
        "default" => "",
        "filter" => [FILTER_SANITIZE_NUMBER_INT],
        "name" => "Gender",
        "length" => [
            "min" => 1,
            "max" => 1
        ]
    ], //Normalize - DONE
    "class_year" => [
        "default" => "",
        "filter" => [FILTER_SANITIZE_NUMBER_INT],
        "name" => "Class Year",
        "length" => [
            "min" => 1,
            "max" => 1
        ]
    ], //Normalize - DONE
    "school" => [
        "default" => "",
        "filter" => [FILTER_SANITIZE_NUMBER_INT],
        "name" => "School",
        "length" => [
            "min" => 1,
            "max" => 3
        ]
    ], //Normalize - DONE
    "race" => [
        "default" => "",
        "filter" => [FILTER_SANITIZE_NUMBER_INT],
        "name" => "Race",
        "length" => [
            "min" => 1,
            "max" => 1
        ]
    ], //normalize - DONE
    "is_hispanic" => [
        "default" => "",
        "filter" => [FILTER_VALIDATE_BOOLEAN],
        "name" => "Are you of Hispanic/Latino origins?"
    ], //Boolean is hispanic
    "zip_code" => [
        "default" => "",
        "filter" => [FILTER_SANITIZE_NUMBER_INT],
        "name" => "City",
        "length" => [
            "min" => 5,
            "max" => 5
        ]
    ],
    "state" => [
        "default" => "",
        "filter" => [FILTER_SANITIZE_NUMBER_INT],
        "name" => "State",
        "length" => [
            "min" => 1,
            "max" => 2
        ]
    ], //Normalize - DONE
    "shirt_size" => [
        "default" => "",
        "filter" => [FILTER_SANITIZE_NUMBER_INT],
        "name" => "Shirt Size",
        "length" => [
            "min" => 1,
            "max" => 1
        ]
    ], //Normalize - DONE
    "diet_restrictions" => [
        "default" => "",
        "filter" => [FILTER_SANITIZE_NUMBER_INT],
        "name" => "Dietary Restrictions",
        "length" => [
            "min" => 1,
            "max" => 1
        ]
    ], //Normalize - IN PROGRESS
    "diet_other" => [
        "default" => "",
        "filter" => [FILTER_SANITIZE_STRING],
        "name" => "Diet Other",
        "length" => [
            "min" => 1,
            "max" => 1
        ]
    ],
    "github" => [
        "default" => "",
        "filter" => [
            FILTER_SANITIZE_STRING,
            FILTER_SANITIZE_URL
        ],
        "name" => "GitHub Profile",
        "length" => [
            "min" => 0,
            "max" => 20
        ]
    ], //URL Escape and only the username
    "linkedin" => [
        "default" => "",
        "filter" => [
            FILTER_SANITIZE_STRING,
            FILTER_SANITIZE_URL
        ],
        "name" => "LinkedIn Profile",
        "length" => [
            "min" => 0,
            "max" => 30
        ]
    ], //Ditto
    "is_first_hackathon" => [
        "default" => "",
        "filter" => [FILTER_VALIDATE_BOOLEAN],
        "name" => "Is this your first hackathon?"
    ],
    "resume" => [
        "default" => "",
        "name" => "Resume",
    ]
];

$requiredFields = [
    "f_name",
    "l_name",
    "email",
    "pass",
    "age",
    "gender",
    "class_year",
    "school",
    "race",
    "is_hispanic",
    "zip_code",
    "state",
    "shirt_size",
    "is_first_hackathon",
    "diet_restrictions"
];


//TODO: Handle registration logic
//TODO: Handle validation logic
//TODO: Possibly spit validation in a way that it can be reused for backend user creation

/*
 * Register pre-flight check, FAIL if:
 *
 * - Registration is closed OR Walk-Ins are Disabled - DONE
 * - Required fields are empty - DONE
 * - Required fields to not meet minimum/maximum requirements - DONE
 * - Fields do not pass validation - DONE
 * - File is over limit OR file is not of allowed types
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

if(!($site->isAcceptingRegistrations() OR $site->isAcceptingWalkIns())){
    $errors['Registration'] = "Sorry, registrations are currently closed.";
    json_response($errors);
}

//Check captcha
//$response = $_POST['g-recaptcha-response'];

/*if(!recaptcha_verify($response)){
    $errors['Captcha'][] = "Captcha failed to verify";
    echo json_encode($return);
}

unset($_POST['g-recaptcha-response']);

*/

//Sanitize all inputs

foreach($_POST as $entry => $value){
    //First check if the option is whitelisted, and if not unset it from the post
    if(!array_key_exists($entry, $acceptableFields)){
        unset($_POST[$entry]);
        continue;
    }
    //Then filter the input as defined by the config array
    if (isset($acceptableFields[$entry]['filter'])) {
        foreach ($acceptableFields[$entry]['filter'] as $filter) {
            $_POST[$entry] = filter_input(INPUT_POST, $entry, $filter);
        }
    }


}

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

//By now we have all our required fields, now we need to make sure all fields are following length and value checks
foreach ($_POST as $key => $value){
    $fieldName = $acceptableFields[$key]['name'];

    //Length is to check if string length is within the configured range, including numbers
    if(isset($acceptableFields[$key]['length'])){
        //We have to check the length of applicable values, even numbers
        $valueLength = strlen($value);
        $min = $acceptableFields[$key]['length']['min'];
        $max = $acceptableFields[$key]['length']['max'];
        if($valueLength < $min)
            $errors['Value Length'][] = "Length of field '$fieldName' is less than the minimum of '$min' at a size of '$valueLength'";
        if($valueLength > $max)
            $errors['Value Length'][] = "Length of field '$fieldName' is greater than the maximum of '$max' at a size of '$valueLength'";
    }

    //Value is used for checking if the actual value of a field is within a specified range
    if(isset($acceptableFields[$key]['value'])){
        $min = $acceptableFields[$key]['value']['min'];
        $max = $acceptableFields[$key]['value']['max'];
        if($value < $min)
            $errors['Value Size'][] = "Value of field '$fieldName' is less than the minimum of '$min'";
        if($value > $max)
            $errors['Value Size'][] = "Value of field '$fieldName' is greater than the maximum of '$max'";
    }

    //Validate via filter for any items that are set to validate
    if(isset($acceptableFields[$key]['validate'])){
        if(!filter_var($_POST[$key], $acceptableFields[$key]['validate']))
            $errors['Validation'][] = "Field '$fieldName' is invalidly formatted.";
    }
}

//If the email does not end with one of the whitelisted domains, throw an error
$found = false;
foreach($site->getValidEmails() as $address){
    if(endswith($_POST['email'], $address)) {
        $found = true;
        break;
    }
}
if(!$found)
    $errors{'Email'}[] = "The email address you entered is not in the list of whitelisted domains.";

//No need to run a query if it's not an acceptable email
if(!empty($errors))
    json_response($errors);

//Check if there are any errors so far, and if so, execute a response

/*
 * TODO: There shouldn't already be an email in the system for the user supplied
 * TODO: User shouldn't be able to register with an email address whose domain is not whitelisted
 */

$query = $db->prepare("SELECT COUNT(*) as users FROM hackers WHERE email = :email");
$query->bindValue(":email", $_POST['email']);
$query->execute();

$queryResult = $query->fetch();

if($queryResult['users'] > 0)
    $errors['Email'][] = "That email already exists in our system.";

if(!empty($errors))
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
//We don't accept resumes from walk-ins

if(!isset($_FILES['resume']) AND !$site->isAcceptingWalkIns()){
    $errors['Resume'][] = "A resume is required.";
    json_response($errors);
}

if(isset($_FILES['resume'])){

    $resume = $_FILES['resume'];

    //We shouldn't be having more than 1 file uploaded
    if(count($resume['name']) > 1){
        $errors['Resume'] = "Please only upload 1 file.";
    }

    //If PHP has an upload error, respect it
    if($resume['error'] !== UPLOAD_ERR_OK){
        $errors['Resume'][] = codeToMessage($resume['error']);
    }

    //If file is not of the acceptable type, through an error
    if(!is_acceptable_file_type($resume['type'])){
        $errors['Resume'][] = "Resume is not one of the acceptable file formats";
    }

    //If size is greater than 2MB, error
    if($resume['size'] > 2000000){
        $errors['Resume'][] = "Resume is larger than 2MB. Please upload a smaller file.";
    }

    if(!empty($errors))
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

//TODO: Work on SQL query with all items as placeholders and fill in those that are optional using an array of binds
