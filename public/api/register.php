<?php

//require_once "../../common/config.php";
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
        "filter" => FILTER_SANITIZE_STRING,
        "name" => "First Name"
    ],
    "l_name" => [
        "filter" => FILTER_SANITIZE_STRING,
        "name" => "Last Name"
    ],
    "email" => [
        "filter" => FILTER_SANITIZE_EMAIL,
        "name" => "E-Mail"
    ],
    "pass" => [
        "filter" => FILTER_DEFAULT, //Pass gets hashed, so no real issue of injection here
        "name" => "Password"
    ],
    "age" => [
        "filter" => FILTER_SANITIZE_NUMBER_INT,
        "name" => "Age"
    ],
    "gender" => [
        "filter" => FILTER_SANITIZE_NUMBER_INT,
        "name" => "Gender"
    ], //Normalize - DONE
    "class_year" => [
        "filter" => FILTER_SANITIZE_NUMBER_INT,
        "name" => "Class Year"
    ], //Normalize - DONE
    "school" => [
        "filter" => FILTER_SANITIZE_NUMBER_INT,
        "name" => "School"
    ], //Normalize - DONE
    "race" => [
        "filter" => FILTER_SANITIZE_NUMBER_INT,
        "name" => "Race"
    ], //normalize - DONE
    "is_hispanic" => [
        "filter" => FILTER_VALIDATE_BOOLEAN,
        "name" => "Are you of Hispanic/Latino origins?"
    ], //Boolean is hispanic
    "zip_code" => [
        "filter" => FILTER_SANITIZE_NUMBER_INT,
        "name" => "City"
    ],
    "state" => [
        "filter" => FILTER_SANITIZE_NUMBER_INT,
        "name" => "State"
    ], //Normalize - DONE
    "shirt_size" => [
        "filter" => FILTER_SANITIZE_NUMBER_INT,
        "name" => "Shirt Size"
    ], //Normalize - DONE
    "diet_restrictions" => [
        "filter" => FILTER_SANITIZE_NUMBER_INT,
        "name" => "Dietary Restrictions"
    ], //Normalize - IN PROGRESS
    "diet_other" => [
        "filter" => FILTER_SANITIZE_STRING,
        "name" => "Diet Other"
    ],
    "github" => [
        "filter" => FILTER_SANITIZE_STRING,
        "name" => "GitHub Profile"
    ], //URL Escape and only the username
    "linkedin" => [
        "filter" => FILTER_SANITIZE_STRING,
        "name" => "LinkedIn Profile"
    ], //Ditto
    "is_first_hackathon" => [
        "filter" => FILTER_VALIDATE_BOOLEAN,
        "name" => "Is this your first hackathon?"
    ],
    "resume" => [
        "name" => "Resume"
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
    "diet_restrictions",
    "resume"
];


//TODO: Handle registration logic
//TODO: Handle validation logic
//TODO: Possibly spit validation in a way that it can be reused for backend user creation

/*
 * Register pre-flight check, FAIL if:
 *
 * - Registration is closed OR Walk-Ins are Disabled
 * - Required fields are empty
 * - Required fields to not meet minimum/maximum requirements
 * - Fields do not pass validation
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

//First check captcha
//$response = $_POST['g-recaptcha-response'];

/*if(!recaptcha_verify($response)){
    $return['errors'][] = "Captcha failed to verify";
    echo json_encode($return);
    die;
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
        $_POST[$entry] = filter_input(INPUT_POST, $entry, $acceptableFields[$entry]['filter']);
    }
}

//Perform specific checks

$missing = missing_fields($requiredFields, $_POST, $acceptableFields);

//If our set of missing fields is greater than zero, then we're missing fields...
if(count($missing) > 0){

    //Check which specific required fields are missing
    foreach($missing as $field){
        $errors[] = "Field '" . $field['name'] . "' is missing.";
    }

    json_response($errors);

}