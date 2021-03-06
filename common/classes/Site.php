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
        "major",
        "is_hispanic",
        "state",
        "shirt_size",
        "is_first_hackathon",
        "activity_info",
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
        ],
        "major" => [
            "filter" => [FILTER_SANITIZE_NUMBER_INT],
            "name" => "Race",
            "length" => [
                "min" => 1,
                "max" => 3
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
                FILTER_SANITIZE_URL
            ],
            "name" => "GitHub Profile",
            "length" => [
                "min" => 0,
                "max" => 255
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
        ],
        "activity_info" => [
            "filter" => [FILTER_CALLBACK],
            "filterOptions" => [
                "options" => "strip_tags"
            ],
            "name" => "Activity Info",
            "length" => [
                "min" => 7,
                "max" => 500
            ]
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
    private $dietRestrictions;
    private $races;
    private $majors;
    private $events;
    private $time;
    private $commitID;

    /**
     * Site constructor.
     * @param PDO $db
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->setUp();
    }

    private function setUp()
    {

        $this->settings = [];

        $sql = "SELECT * FROM site_settings";

        $query = $this->db->prepare($sql);
        $query->execute();

        while ($row = $query->fetch()) {
            $this->settings[$row['name']] = $row['value'];
        }

        //Create an array out of the comma separated list and trim the values after
        $this->validEmails = array_map("trim", explode(',', $this->settings['valid_emails']));

        $this->time = time();

        $this->commitID = trim(exec('git log --pretty="%h" -n1 HEAD'));

    }

    public function getValidEmails()
    {
        return $this->validEmails;
    }

    public function getSchools()
    {
        if (!is_null($this->schools))
            return $this->schools;

        $sql = "SELECT * FROM schools";
        $query = $this->db->query($sql);
        //We do this to create an array whose indices are the IDs of the schools
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $this->schools[$row['id']]['name'] = $row['name'];
            $this->schools[$row['id']]['hasBus'] = $row['has_bus'];
            $this->schools[$row['id']]['busCaptain'] = $row['bus_captain'];
        }
        return $this->schools;
    }

    public function getGenders()
    {
        if (!is_null($this->genders))
            return $this->genders;

        //Note: Could possibly move this action to its own method, but would require accounting for more than just a KV pair
        $sql = "SELECT * FROM genders";
        $query = $this->db->query($sql);
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $this->genders[$row['id']] = $row['name'];
        }
        return $this->genders;

    }

    public function getClassYears()
    {
        if (!is_null($this->classYears))
            return $this->classYears;

        //Note: Could possibly move this action to its own method, but would require accounting for more than just a KV pair
        $sql = "SELECT * FROM class_years";
        $query = $this->db->query($sql);
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $this->classYears[$row['id']] = $row['year'];
        }
        return $this->classYears;

    }

    public function getDietRestrictions()
    {
        if (!is_null($this->dietRestrictions))
            return $this->dietRestrictions;

        //Note: Could possibly move this action to its own method, but would require accounting for more than just a KV pair
        $sql = "SELECT * FROM diets";
        $query = $this->db->query($sql);
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $this->dietRestrictions[$row['id']] = $row['name'];
        }
        return $this->dietRestrictions;

    }

    public function getRaces()
    {
        if (!is_null($this->races))
            return $this->races;

        //Note: Could possibly move this action to its own method, but would require accounting for more than just a KV pair
        $sql = "SELECT * FROM races";
        $query = $this->db->query($sql);
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $this->races[$row['id']] = $row['name'];
        }
        return $this->races;

    }

    public function getMajors()
    {
        if (!is_null($this->majors))
            return $this->majors;

        //Note: Could possibly move this action to its own method, but would require accounting for more than just a KV pair
        $sql = "SELECT * FROM majors ORDER BY major";
        $query = $this->db->query($sql);
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $this->majors[$row['id']] = $row['major'];
        }
        return $this->majors;

    }

    public function getStates()
    {
        if (!is_null($this->states))
            return $this->states;

        $sql = "SELECT * FROM states";
        $query = $this->db->query($sql);
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $this->states[$row['id']] = [
                "state" => $row['state'],
                "name" => $row['name']
            ];
        }
        return $this->states;
    }

    public function getShirtSizes()
    {
        if (!is_null($this->shirtSizes))
            return $this->shirtSizes;

        $sql = "SELECT * FROM shirt_sizes";
        $query = $this->db->query($sql);
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $this->shirtSizes[$row['id']] = $row['shirt_size'];
        }
        return $this->shirtSizes;
    }

    public function getEvents()
    {
        if (!is_null($this->events))
            return $this->events;

        $sql = "SELECT events_schedule.id, events_schedule.start, events_schedule.end, events_schedule.title, 
                  events_schedule.description, event_locations.room 
                FROM events_schedule 
                JOIN event_locations 
                  ON event_locations.id = events_schedule.location 
                ORDER BY start ASC ";
        $query = $this->db->query($sql);
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $this->events[] = $row;
        }
        return $this->events;
    }

    /**
     * Method to return current time
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
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

    public function getWaitlistCloseTime()
    {
        return filter_var($this->settings['wait_close'], FILTER_VALIDATE_INT);
    }

    public function isAcceptingRegistrations()
    {
        return ($this->isWithinRegistrationWindow(time()));
    }

    public function isWaitilistActive()
    {
        return (time() >= $this->getRegistrationCloseTime() && time() <= $this->getWaitlistCloseTime());
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

    public function getAddress()
    {
        return SITE_ADDRESS;
    }

    /**
     * Returns the current git commit ID for the site
     * @return string
     */
    public function getCommitID()
    {
        return $this->commitID;
    }

}