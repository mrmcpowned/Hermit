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


    public static $requiredRegistrationFields = [
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
        "state",
        "shirt_size",
        "is_first_hackathon",
        "diet_restriction",
        "mlh_accept"
    ];

    public static $registrationFields = [
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
            "filter" => [FILTER_SANITIZE_EMAIL, FILTER_CALLBACK],
            "validate" => FILTER_VALIDATE_EMAIL,
            "filterOptions" => [
                "options" => "strtolower"
            ],
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
        "diet_restriction" => [
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
                FILTER_SANITIZE_URL
            ],
            "name" => "LinkedIn Profile",
            "length" => [
                "min" => 0,
                "max" => 255
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
    private $schools;
    private $validEmails;
    private $genders;
    private $states;
    private $shirtSizes;
    private $classYears;

    /**
     * Site constructor.
     * @param PDO $db
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->setUp();
    }

    private function setUp(){

        $this->settings = [];

        $sql = "SELECT * FROM site_settings";

        $query = $this->db->prepare($sql);
        $query->execute();

        while($row = $query->fetch()){
            $this->settings[$row['name']] = $row['value'];
        }

        //Create an array out of the comma separated list and trim the values after
        $this->validEmails = array_map("trim", explode(',', $this->settings['valid_emails']));

    }

    public function getValidEmails()
    {
        return $this->validEmails;
    }

    public function getSchools(){
        if(!is_null($this->schools))
            return $this->schools;

        $sql = "SELECT * FROM schools";
        $query = $this->db->query($sql);
        //We do this to create an array whose indices are the IDs of the schools
        while($row = $query->fetch(PDO::FETCH_ASSOC)){
            $this->schools[$row['id']] = $row['name'];
        }
        return $this->schools;
    }

    public function getGenders()
    {
        if(!is_null($this->genders))
            return $this->genders;

        //Note: Could possibly move this action to its own method, but would require accounting for more than just a KV pair
        $sql = "SELECT * FROM genders";
        $query = $this->db->query($sql);
        while($row = $query->fetch(PDO::FETCH_ASSOC)){
            $this->genders[$row['id']] = $row['name'];
        }
        return $this->genders;

    }

    public function getClassYears()
    {
        if(!is_null($this->classYears))
            return $this->classYears;

        //Note: Could possibly move this action to its own method, but would require accounting for more than just a KV pair
        $sql = "SELECT * FROM class_years";
        $query = $this->db->query($sql);
        while($row = $query->fetch(PDO::FETCH_ASSOC)){
            $this->classYears[$row['id']] = $row['year'];
        }
        return $this->classYears;

    }

    public function getStates()
    {
        if(!is_null($this->states))
            return $this->states;

        $sql = "SELECT * FROM states";
        $query = $this->db->query($sql);
        while($row = $query->fetch(PDO::FETCH_ASSOC)){
            $this->states[$row['id']] = [
                "state" => $row['state'],
                "name" => $row['name']
            ];
        }
        return $this->states;
    }

    public function getShirtSizes()
    {
        if(!is_null($this->shirtSizes))
            return $this->shirtSizes;

        $sql = "SELECT * FROM shirt_sizes";
        $query = $this->db->query($sql);
        while($row = $query->fetch(PDO::FETCH_ASSOC)){
            $this->shirtSizes[$row['id']] = $row['shirt_size'];
        }
        return $this->shirtSizes;
    }


    //DONE: Get from DB
    public function getRegistrationOpenTime()
    {
        return filter_var($this->settings['reg_open'], FILTER_VALIDATE_INT);
    }

    //DONE: Get from DB
    public function getRegistrationCloseTime()
    {
        return filter_var($this->settings['reg_close'], FILTER_VALIDATE_INT);
    }

    public function isAcceptingRegistrations()
    {
        $now = time();
        return ($this->isWithinRegistrationWindow($now));
    }

    public function isWithinRegistrationWindow($time)
    {
        return ($time < $this->getRegistrationCloseTime() && $time > $this->getRegistrationOpenTime());
    }

    //DONE: Get from DB
    public function isAcceptingWalkIns()
    {
        return filter_var($this->settings['walk_ins'], FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return array
     */
    public function getRequiredRegistrationFields()
    {
        return self::$requiredRegistrationFields;
    }

    /**
     * @return array
     */
    public function getRegistrationFields()
    {
        return self::$registrationFields;
    }



}