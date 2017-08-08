<?php
/**
 * While this may seem like a trivial file, it's pretty important to clear the database so we're not needlessly stocking
 * up on already sent emails.
 *
 */
require_once "../config.php";

$mailer = new Mailer($db);

$mailer->cleanQueue();