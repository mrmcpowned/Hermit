<?php

function generateSID()
{
    return hash('sha256', time() . bin2hex(random_bytes(8)));
}

function generateAlphaCode($size = 6)
{

    $output = "";

    for ($i = 0; $i < $size; $i++) {
        $offset = random_int(0, 255);

        if (rand(0, 1))
            //Concat a capital letter
            $output .= chr(65 + ($offset % 26));
        else
            //Concat a number
            $output .= chr(48 + ($offset % 10));
    }
    return $output;
}

function hasPermissions($bitPermissions, $permissionsRequired)
{

}

function recaptcha_verify($captchaResponse)
{
    $ch = curl_init("https://www.google.com/recaptcha/api/siteverify");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ["secret" => "", "response" => ""]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    $response = json_decode($response, true);
    curl_close($ch);
    return $response["success"] === true;
}

function missing_fields($required, $input, $total){
    $fieldsWeWant = array_intersect_key($total, array_flip($required));
    $fieldsWeHave = array_intersect_key($input, array_flip($required));
    //We do this to get the keys with named values instead of the keys with input values
    $fieldsWeHave = array_intersect_key($total, $fieldsWeHave);

    return array_diff_key($fieldsWeWant, $fieldsWeHave);
}

/**
 * Creates proper JSON response and ends the script
 * @param array $errors

 */
function json_response($errors = []){
    $response = [
        "success" => false,
        "errors" => $errors
    ];

    if(!empty($errors)) {
        echo json_encode($response);
        die;
    }

    $response['success'] = true;
    echo json_encode($response);
    die;
}