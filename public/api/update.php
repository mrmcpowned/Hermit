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
 * - Password
 * - Shirt Size
 * - Diet Restrictions
 */

if(!isset($_POST) AND )
    die;

header("Content-Type: application/json");

$errors = [];

