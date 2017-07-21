<?php

/**
 * FULLY REFACTORED AS STUB
 */

//Update current user's information

/*
 * Most fields are not meant to be updated, so as to preserve the integrity of the data. Users could ask to have these
 * fields changed on an individual basis.
 *
 * For now, the following fields can be updated
 * - Shirt Size
 * - Diet Restrictions
 */

//There's no password changing here because password changing and resetting will be done from the same controller
/*$acceptableFields = [
    "shirt_size" => [
        "filter" => [FILTER_SANITIZE_NUMBER_INT],
        "name" => "Shirt Size",
        "length" => [
            "min" => 1,
            "max" => 1
        ]
    ], //Normalize - DONE
    "diet_restrictions" => [
        "filter" => [FILTER_SANITIZE_NUMBER_INT],
        "name" => "Dietary Restrictions",
        "length" => [
            "min" => 1,
            "max" => 1
        ]
    ], //Normalize - IN PROGRESS
    "diet_other" => [
        "filter" => [FILTER_CALLBACK],
        "filterOptions" => [
            "options" => "strip_tags"
        ],
        "name" => "Diet Other",
        "length" => [
            "min" => 3,
            "max" => 500
        ]
    ]
];*/

//Here we build our validation array from fields already defined for the site
$acceptableFields = [];
$acceptableFields['shirt_size'] = Site::$registrationFields['shirt_size'];
$acceptableFields['diet_restrictions'] = Site::$registrationFields['diet_restrictions'];
$acceptableFields['diet_other'] = Site::$registrationFields['diet_other'];
$acceptableFields['pass'] = Site::$registrationFields['pass'];

if(!$user->isLoggedIn()){
    throw new UnauthenticatedException("You are currently not logged in");
}

//All update requests REQUIRE a current password to be sent
if (empty($_POST['curr_pass'])) {
    throw new MissingFieldException("Current password is required");
}
//Store current pass

//Go through the cleansing routine
sanitize_array($_POST, $acceptableFields);
validate_array($_POST, $acceptableFields, $errors);
json_response($errors);

$currentPass = $_POST['curr_pass'];
unset($_POST['curr_pass']);

//after filtering, if the POST is empty, error our
if (empty($_POST)) {
    throw new MissingFieldException("Please submit at least 1 field to update");
}

//Check if passwords match
if ($user->isPasswordCorrect($currentPass)) {
    throw new IncorrectPasswordException("The password entered does not match the current password");
}

$userSID = $user->getSID();

//now update the given fields in the database

//Generate query
//First we have to prepare the values
$preparedPairs = [];
$sqlSets = [];

foreach ($_POST as $key => $value) {
    $preparedKey = ":$key";
    $preparedPairs[$preparedKey] = $value;
    //While we're here we can prepare a set of "set" statements
    $sqlSets[] = "$key = :$key";
}

$preparedPairs[':sid'] = $userSID;

$setStatement = implode(", ", $sqlSets);
$sql = "UPDATE hackers SET $setStatement WHERE sid = :sid";

//Prepare the SQL statement
$updateQuery = $db->prepare($sql);
if (!$updateQuery->execute($preparedPairs)) {
    throw new DatabaseErrorException($updateQuery->errorInfo());
}
if ($updateQuery->rowCount() < 1){
    throw new UnknownUserException("No user found");
}