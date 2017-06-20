<?php

function generateSID()
{
    return hash('sha256', time() . bin2hex(random_bytes(8)));
}

function trimLow($input){
    return trim(strtolower($input));
}