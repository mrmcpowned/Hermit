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
        "name" => "First Name",
        "length" => [
            "min" => 2,
            "max" => 50
        ]
    ],
    "l_name" => [
        "filter" => FILTER_SANITIZE_STRING,
        "name" => "Last Name",
        "length" => [
            "min" => 2,
            "max" => 50
        ]
    ],
    "email" => [
        "filter" => FILTER_SANITIZE_EMAIL,
        "name" => "E-Mail",
        "length" => [
            "min" => 7,
            "max" => 255
        ]
    ],
    "pass" => [
        "filter" => FILTER_DEFAULT, //Pass gets hashed, so no real issue of injection here
        "name" => "Password",
        "length" => [
            "min" => 8,
            "max" => 255
        ]
    ],
    "age" => [
        "filter" => FILTER_SANITIZE_NUMBER_INT,
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
        "filter" => FILTER_SANITIZE_NUMBER_INT,
        "name" => "Gender",
        "length" => [
            "min" => 1,
            "max" => 1
        ]
    ], //Normalize - DONE
    "class_year" => [
        "filter" => FILTER_SANITIZE_NUMBER_INT,
        "name" => "Class Year",
        "length" => [
            "min" => 1,
            "max" => 1
        ]
    ], //Normalize - DONE
    "school" => [
        "filter" => FILTER_SANITIZE_NUMBER_INT,
        "name" => "School",
        "length" => [
            "min" => 1,
            "max" => 3
        ]
    ], //Normalize - DONE
    "race" => [
        "filter" => FILTER_SANITIZE_NUMBER_INT,
        "name" => "Race",
        "length" => [
            "min" => 1,
            "max" => 1
        ]
    ], //normalize - DONE
    "is_hispanic" => [
        "filter" => FILTER_VALIDATE_BOOLEAN,
        "name" => "Are you of Hispanic/Latino origins?",
        "length" => [
            "min" => 1,
            "max" => 1
        ]
    ], //Boolean is hispanic
    "zip_code" => [
        "filter" => FILTER_SANITIZE_NUMBER_INT,
        "name" => "City",
        "length" => [
            "min" => 5,
            "max" => 5
        ]
    ],
    "state" => [
        "filter" => FILTER_SANITIZE_NUMBER_INT,
        "name" => "State",
        "length" => [
            "min" => 1,
            "max" => 2
        ]
    ], //Normalize - DONE
    "shirt_size" => [
        "filter" => FILTER_SANITIZE_NUMBER_INT,
        "name" => "Shirt Size",
        "length" => [
            "min" => 1,
            "max" => 1
        ]
    ], //Normalize - DONE
    "diet_restrictions" => [
        "filter" => FILTER_SANITIZE_NUMBER_INT,
        "name" => "Dietary Restrictions",
        "length" => [
            "min" => 1,
            "max" => 1
        ]
    ], //Normalize - IN PROGRESS
    "diet_other" => [
        "filter" => FILTER_SANITIZE_STRING,
        "name" => "Diet Other",
        "length" => [
            "min" => 1,
            "max" => 1
        ]
    ],
    "github" => [
        "filter" => FILTER_SANITIZE_STRING,
        "name" => "GitHub Profile",
        "length" => [
            "min" => 1,
            "max" => 1
        ]
    ], //URL Escape and only the username
    "linkedin" => [
        "filter" => FILTER_SANITIZE_STRING,
        "name" => "LinkedIn Profile",
        "length" => [
            "min" => 1,
            "max" => 1
        ]
    ], //Ditto
    "is_first_hackathon" => [
        "filter" => FILTER_VALIDATE_BOOLEAN,
        "name" => "Is this your first hackathon?",
        "length" => [
            "min" => 1,
            "max" => 1
        ]
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

//Check if we're NOT accepting registrations OR walk-ins
/*
if(!($site->isAcceptingRegistrations() OR $site->isAcceptingWalkinIns())){
    http_response_code(400);
    $errors[] = "Sorry, registrations are currently closed.";
}

//We don't accept resumes from walk-ins
if($site->isAcceptingWalkIns()){
    if(($key = array_search("resume", $requiredFields)) !== false) {
        unset($requiredFields[$key]);
    }
}
*/

//Check captcha
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
        $errors['Missing Fields'][] = "Field '" . $field['name'] . "' is missing.";
    }

    //Output the response
    json_response($errors);
}

//By now we have all our required fields, now we need to make sure all fields are following length and value checks
foreach ($_POST as $key => $value){
    $fieldName = $acceptableFields[$key]['name'];
    if(isset($acceptableFields[$key]['length'])){
        //We have to check the length of applicable values, even numbers
        $valueLength = strlen($value);
        $min = $acceptableFields[$key]['length']['min'];
        $max = $acceptableFields[$key]['length']['min'];
        if($valueLength < $min)
            $errors['Value Length'] = "Length of field '$fieldName' is less than the minimum of '$min'";
        if($valueLength > $max)
            $errors['Value Length'] = "Length of field '$fieldName' is greater than the maximum of '$max'";
    }

    //Value is used for checking if the actual value of a field is within a specified range
    if(isset($acceptableFields[$key]['value'])){
        $min = $acceptableFields[$key]['value']['min'];
        $max = $acceptableFields[$key]['value']['min'];
        if($value < $min)
            $errors['Value Size'] = "Value of field '$fieldName' is less than the minimum of '$min'";
        if($value > $max)
            $errors['Value Size'] = "Value of field '$fieldName' is greater than the maximum of '$max'";
    }
}
//Check if there are any errors so far
if(!empty($errors))
    json_response($errors);


