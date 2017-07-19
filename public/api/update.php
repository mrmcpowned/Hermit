<?php

require_once "../../common/config.php";
require_once "../../common/functions.php";
require_once "../../common/Hacker.php";

$user = new Hacker($db);

/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 6/20/2017
 * Time: 1:18 AM
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
$acceptableFields = [
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
];


if (!isset($_POST))
    die;

header("Content-Type: application/json");

$errors = [];


if(!$user->isLoggedIn()){
    $errors['Unauthorized'][] = "Only logged in users can access this endpoint";
}

//All update requests REQUIRE a current password to be sent
if (!isset($_POST['curr_pass'])) {
    $errors['Missing Fields'][] = "Current password is missing";
    json_response($errors);
}
//Store current pass
$currentPass = $_POST['curr_pass'];

//Go through the cleansing routine
foreach ($_POST as $entry => $value) {
    //First check if the option is whitelisted, and if not unset it from the post
    if (!array_key_exists($entry, $acceptableFields)) {
        unset($_POST[$entry]);
        continue;
    }
    //Then filter the input as defined by the config array
    if (isset($acceptableFields[$entry]['filter'])) {
        foreach ($acceptableFields[$entry]['filter'] as $filter) {
            if ($filter === FILTER_CALLBACK) {
                $_POST[$entry] = filter_input(INPUT_POST, $entry, FILTER_CALLBACK, $acceptableFields[$entry]['filterOptions']);
            } else {
                $_POST[$entry] = filter_input(INPUT_POST, $entry, $filter);
            }
        }
    }
}

//after filtering, if the POST is empty, error our
if (empty($_POST)) {
    $errors['Missing Fields'][] = "Please submit at least 1 field to update";
}

try {
    $getPassQuery = $db->prepare("SELECT pass FROM hackers WHERE sid = :sid");
    $userSID = $user->getSID();
    $getPassQuery->bindParam(":sid", $userSID);
    if (!$getPassQuery->execute()) {
        $errors['Database Error'][] = $getPassQuery->errorInfo();
    }
    $dbPass = $getPassQuery->fetchColumn(0);
} catch (Exception $e) {
    $errors['Database Error'][] = $e->getMessage();
}

json_response($errors);

//If the result is 0 rows, error out
if ($getPassQuery->rowCount() < 1) {
    $errors['Session Error'][] = "Session expired, please login again";
    json_response($errors);
}

//Check if passwords match
if (!password_verify($currentPass, $dbPass)) {
    $errors['Password Error'][] = "The password entered does not match the current password";
    json_response($errors);
}

//now update the given fields in the database
//first generate query

//First we have to prepare the values
$preparedPairs = [];
$sqlSets = [];

foreach ($_POST as $key => $value) {
    $preparedKey = ":$key";
    $preparedPairs[$preparedKey] = $value;
    //While we're here we can prepare a set of "set" statements
    $sqlSets[] = "$key = :$key";
}

$preparedPairs[':pass'] = $dbPass;
$preparedPairs[':sid'] = $userSID;

$setStatement = implode(", ", $sqlSets);
$sql = "UPDATE hackers SET $setStatement WHERE sid = :sid AND pass = :pass";

//Prepare the SQL statement
try {
    $updateQuery = $db->prepare($sql);
    if (!$updateQuery->execute($preparedPairs)) {
        throw new Exception($updateQuery->errorInfo());
    }
    if ($updateQuery->rowCount() < 1){
        throw new Exception("No user found");
    }
} catch (Exception $e) {
    $errors['Database Error'][] = $e->getMessage();
}

//Finally send a response
json_response($errors, false);