<?php


/*
 * - Staff will have permissions
 * - Permissions will be bitwise, but also with named types
 * - Named representation of whether the use has a certain permission will
 * also be created in an array to allow for easy management from a view context
 *
 *
 * Permissions are as follows:
 *
 *  - view stats
 *  - view hackers (Name, GitHub, LinkedIn, School, Age) [Meant more for sponsors]
 *  - download resumes [Meant more for sponsors]
 *  - add hackers (Full View)
 *  - delete hackers
 *  - edit hackers
 *  - check-in hackers (has its own check-in page/view)
 *  - view staff
 *  - add staff
 *  - delete staff
 *  - edit staff
 *  - edit site config
 *
 */

/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 6/18/2017
 * Time: 6:27 PM
 */
class Staff extends User
{

}