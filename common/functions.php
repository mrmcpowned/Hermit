<?php

/**
 * Generates an SHA-256 hash made up of the time and a random set of bytes
 * @return string The hash
 */
function generateSID()
{
    return hash('sha256', time() . bin2hex(random_bytes(8)));
}

/**
 * Generates an alpha-numeric code and the length specified
 * @param int $size Specifies the length of the code
 * @return string The generated alpha-numeric code
 */
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

/**
 * Verifies a Google Recaptcha response
 * @param $captchaResponse string The response to verify
 * @return bool
 */
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

function codeToMessage($code) {
    $messages = [
        UPLOAD_ERR_INI_SIZE => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
        UPLOAD_ERR_FORM_SIZE => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
        UPLOAD_ERR_PARTIAL => "The uploaded file was only partially uploaded",
        UPLOAD_ERR_NO_FILE => "No file was uploaded",
        UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder",
        UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk",
        UPLOAD_ERR_EXTENSION => "File upload stopped by extension"
    ];

    return $messages[$code];
}

/**
 * Creates proper JSON response and ends the script
 * @param array $errors

 */
function json_response($errors = [], $checkIfEmpty = true){
    $response = [
        "success" => false,
        "errors" => $errors
    ];

    /**
     * We use this to cut down on our outer logic to check if there's any errors
     * If there happens to be an error the function wont return prematurely and go about it's regular execution
     * For a final response check, we set this to false to respect the possibility of an empty errors array, which
     * signifies a successful response.
     */
    if($checkIfEmpty){
        if(empty($errors)){
            return;
        }
    }

    if(!empty($errors)) {
        http_response_code(400);
        echo json_encode($response);
        die;
    }

    $response['success'] = true;
    echo json_encode($response);
    die;
}

function is_acceptable_file_type($type){
    $acceptableTypes = [
        "application/msword",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "application/pdf"
    ];
    return array_key_exists($type, array_flip($acceptableTypes));
}

//Thank you based StackOverflow
function endswith($string, $test) {
    $strlen = strlen($string);
    $testlen = strlen($test);
    if ($testlen > $strlen) return false;
    return substr_compare($string, $test, $strlen - $testlen, $testlen) === 0;
}

function sanitize_array(&$inputArray, $configArray){
    foreach($inputArray as $entry => $value){
        //All values should be trimmed
        $_POST[$entry] = trim($value);
        //First check if the option is whitelisted, and if not unset it from the post
        if(!array_key_exists($entry, $configArray)){
            unset($inputArray[$entry]);
            continue;
        }
        //Then filter the input as defined by the config array
        if (isset($configArray[$entry]['filter'])) {
            foreach ($configArray[$entry]['filter'] as $filter) {
                if ($filter === FILTER_CALLBACK) {
                    $inputArray[$entry] = filter_var($value, FILTER_CALLBACK, $configArray[$entry]['filterOptions']);
                } else {
                    $inputArray[$entry] = filter_var($value, $filter);
                }
            }
        }
    }
}

function validate_array($inputArray, $configArray, &$errorsArray){
    foreach ($inputArray as $key => $value){
        $fieldName = $configArray[$key]['name'];

        //Length is to check if string length is within the configured range, including numbers
        if(isset($configArray[$key]['length'])){
            //We have to check the length of applicable values, even numbers
            $valueLength = strlen($value);
            $min = $configArray[$key]['length']['min'];
            $max = $configArray[$key]['length']['max'];
            if($valueLength < $min)
                $errorsArray['Value Length'][] = "Length of field '$fieldName' is less than the minimum of '$min' at a size of '$valueLength'";
            if($valueLength > $max)
                $errorsArray['Value Length'][] = "Length of field '$fieldName' is greater than the maximum of '$max' at a size of '$valueLength'";
        }

        //Value is used for checking if the actual value of a field is within a specified range
        if(isset($configArray[$key]['value'])){
            $min = $configArray[$key]['value']['min'];
            $max = $configArray[$key]['value']['max'];
            if($value < $min)
                $errorsArray['Value Size'][] = "Value of field '$fieldName' is less than the minimum of '$min'";
            if($value > $max)
                $errorsArray['Value Size'][] = "Value of field '$fieldName' is greater than the maximum of '$max'";
        }

        //Validate via filter for any items that are set to validate
        if(isset($configArray[$key]['validate'])){
            if(!filter_var($inputArray[$key], $configArray[$key]['validate']))
                $errorsArray['Validation'][] = "Field '$fieldName' is invalidly formatted.";
        }
    }
}