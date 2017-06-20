<?php

function generateSID()
{
    return hash('sha256', time() . bin2hex(random_bytes(8)));
}

function trimLow($input){
    return trim(strtolower($input));
}

function generateAlphaCode($size = 4){

    $output = "";

    for($i = 0; $i < $size; $i++){
        $offset = random_int(0, 255);

        if(rand(0,1))
            //Concat a capital letter
            $output .= chr( 65 + ($offset%26));
        else
            //Concat a number
            $output .= chr( 48 + ($offset%10));
    }
    return $output;
}