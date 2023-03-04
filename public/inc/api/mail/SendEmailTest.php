<?php

/*******************************
 * Setting HTTP response headers
 *******************************/
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");

require '../config/Config.php';

if ($_SERVER['HTTP_AUTHORIZATION'] == PSW_CRON) {
    /*******************************
     * Global API Required Resources
     *******************************/
    require '../config/Database.php';

    /************************
     * Specific API Resources
     ************************/
    require '../objects/EmailSistem.php';

    /*************************
     * Setting returning array
     *************************/
    $result = array();

    /**********************
     * Check request method
     **********************/
    if ($_SERVER['REQUEST_METHOD'] == "GET") {

        // get database connection
        $database = new Database();
        $db = $database->getConnection();

        $mail = new EmailSistem($db);

        //$_POST = json_decode(file_get_contents("php://input"), true);

        $result = $mail->SendEmailTest();
    } else {
        /*******************************
         * Error handling
         * in case of bad request method
         *******************************/
        $result['code'] = 400;
        $result['message'] = "Bad request";
    }
} else {

    /*******************************
     * Error handling
     * in case of bad request method
     *******************************/
    $result['code'] = 400;
    $result['message'] = "Bad request";
}

/***************
 * Print result
 ***************/
echo $result;
