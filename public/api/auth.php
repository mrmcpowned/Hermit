<?php
/**
 * Created by PhpStorm.
 * User: mrmcp
 * Date: 6/18/2017
 * Time: 10:46 PM
 */

$errors = [];

//TODO: Handle auth with proper logic
/*
 * Proper auth logic flow:
 *
 * Email should be sanitized
 * Email should not be empty
 * Email should not be less than minimum character limit
 * Password should not be less than minimum character limit
 *
 */
//TODO: Maybe handle QR check-ins here too?
/*
 * QR Code auth could be generated at the confirmation stage, with an endpoint guarding against collisions
 */
