<?php
/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 7/25/2017
 * Time: 7:07 PM
 */
define("DB_HOST", "");
define("DB_USER", "");
define("DB_PASS", "");
define("DB_NAME", "");

define("RECAPTCHA_SECRET", "");

define('SESSION_EXPIRATION_SECONDS', 3600); //1 Hour Expiration
define('RESUME_PATH', __DIR__ . "/resumes/"); //Relative to the constant file, but absolute when used
define('EMAIL_TEMPLATES_PATH', __DIR__ . "/../templates/email");
define('PUBLIC_TEMPLATES_PATH', __DIR__ . "/../templates/public");