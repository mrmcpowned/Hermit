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

function codeToMessage($code)
{

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
function json_response($errors = []){
    $response = [
        "success" => false,
        "errors" => $errors
    ];

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