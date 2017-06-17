<?php

function generateSID()
{
    return hash('sha256', time() . bin2hex(random_bytes(8)));
}

function extendSession($time = 10)
{
    if(isset($_SESSION)){
        $now = time();
        $_SESSION['discard_after'] = $now + $time;
    }
}