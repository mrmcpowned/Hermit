<?php
/**
 * This file handles user registration
 */


//This defines what we will consider valid data taken from a POST for Hacker profile creation or update
$userFieldFilters = [
    "f_name" => "",
    "l_name" => "",
    "email" => "",
    "pass" => "",
    "age" => "",
    "gender" => "",
    "class_year" => "",
    "school" => "",
    "race" => "",
    "is_hispanic" => "",
//    "city" => "",
//    "state" => "",
    "shirt_size" => "", //Mix of male and female sizes, only allowed values so no need to filter
    "diet_restrictions" => "", //This one should be an integer, relative to the restriction type in the DB
    "github" => "", //URL Escape and only the username
    "linkedin" => "", //Ditto
    "is_first_hackathon" => ""
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
    "is_hispanic",
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
 *
 *
 */