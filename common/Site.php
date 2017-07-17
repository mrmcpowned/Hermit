<?php

/**
 * Class Site
 * The Site class handles site operations. Things like creating files or updating settings.
 */
class Site
{

    /*
     *
     */

    /*
     * EMAILS
     *
     * - Whitelist a list of emails
     * - Literally just only get domain and check against DB once
     * - Email whitelist will be its own table
     *
     */


    private static $requiredRegistrationFields = [
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
        "mlh_accept"
    ];

    private static $registrationFields = [
        "f_name" => [
            "filter" => [FILTER_CALLBACK],
            "filterOptions" => [
                "options" => "strip_tags"
            ],
            "name" => "First Name",
            "length" => [
                "min" => 2,
                "max" => 50
            ]
        ],
        "l_name" => [
            "filter" => [FILTER_CALLBACK],
            "filterOptions" => [
                "options" => "strip_tags"
            ],
            "name" => "Last Name",
            "length" => [
                "min" => 2,
                "max" => 50
            ]
        ],
        "email" => [
            "filter" => [FILTER_SANITIZE_EMAIL],
            "validate" => FILTER_VALIDATE_EMAIL,
            "name" => "E-Mail",
            "length" => [
                "min" => 7,
                "max" => 255
            ]
        ],
        "pass" => [
            "filter" => [FILTER_DEFAULT], //Pass gets hashed, so no real issue of injection here
            "name" => "Password",
            "length" => [
                "min" => 8,
                "max" => 255
            ]
        ],
        "age" => [
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
            "filter" => [FILTER_SANITIZE_NUMBER_INT],
            "name" => "Gender",
            "length" => [
                "min" => 1,
                "max" => 1
            ]
        ], //Normalize - DONE
        "class_year" => [
            "filter" => [FILTER_SANITIZE_NUMBER_INT],
            "name" => "Class Year",
            "length" => [
                "min" => 1,
                "max" => 1
            ]
        ], //Normalize - DONE
        "school" => [
            "filter" => [FILTER_SANITIZE_NUMBER_INT],
            "name" => "School",
            "length" => [
                "min" => 1,
                "max" => 4
            ]
        ], //Normalize - DONE
        "race" => [
            "filter" => [FILTER_SANITIZE_NUMBER_INT],
            "name" => "Race",
            "length" => [
                "min" => 1,
                "max" => 1
            ]
        ], //normalize - DONE
        "is_hispanic" => [
            "filter" => [FILTER_VALIDATE_BOOLEAN],
            "name" => "Are you of Hispanic/Latino origins?"
        ], //Boolean is hispanic
        "zip_code" => [
            "filter" => [FILTER_SANITIZE_NUMBER_INT],
            "name" => "City",
            "length" => [
                "min" => 5,
                "max" => 5
            ]
        ],
        "state" => [
            "filter" => [FILTER_SANITIZE_NUMBER_INT],
            "name" => "State",
            "length" => [
                "min" => 1,
                "max" => 2
            ]
        ], //Normalize - DONE
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
        ],
        "github" => [
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
            "filter" => [FILTER_VALIDATE_BOOLEAN],
            "name" => "Is this your first hackathon?"
        ],
        "mlh_accept" => [
            "name" => "MLH Code of Conduct",
            "filter" => [FILTER_VALIDATE_BOOLEAN]
        ],
        "resume" => [
            "name" => "Resume",
        ]
    ];

    private $db;
    /**
     * @var array Associative array holding the site's settings
     */
    private $settings;

    /**
     * Site constructor.
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getValidEmails()
    {
        return ['.edu', 'mymdc.net'];
    }

    public function isAcceptingRegistrations()
    {
        return true;
    }

    public function isAcceptingWalkIns()
    {
        return false;
    }

    /**
     * @return array
     */
    public static function getRequiredRegistrationFields(): array
    {
        return Site::$requiredRegistrationFields;
    }

    /**
     * @return array
     */
    public static function getRegistrationFields(): array
    {
        return Site::$registrationFields;
    }



}