<?php

/*******************************
 * Setting HTTP response headers
 *******************************/
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");

if (isset($_SERVER) && count($_SERVER) > 0) {

    $have_server = 1;
    //INSERIRE PATH ASSOLUTA SERVER al posto di : "/home/u949902263/domains/crurated.com/public_html/members"
    $rootDir = isset($_SERVER["DOCUMENT_ROOT"]) && $_SERVER["DOCUMENT_ROOT"] != '' ? realpath($_SERVER["DOCUMENT_ROOT"]) : "/home/u949902263/domains/crurated.com/public_html/members";
    $domain = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : ""; //INSERIRE URL SITO

    if (strpos($domain, "localhost") !== false) {
        $rootDir = $rootDir . "/dokyhr/public";
    }
} else {
    $have_server = 0;
    $rootDir = ""; //INSERIRE PATH ASSOLUTA
}

$request_metod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : "GET";
require $rootDir . '/inc/api/config/Config.php';

//if (isset($_GET['key']) && $_GET['key'] == PSW_CRON) {

/*******************************
 * Global API Required Resources
 *******************************/
require ROOT_DIR . '/inc/api/config/Database.php';

/************************
 * Specific API Resources
 ************************/
require ROOT_DIR . '/inc/api/objects/Crontab.php';

/*************************
 * Setting returning array
 *************************/
$result = array();

/**********************
 * Check request method
 **********************/
if ($request_metod == "GET") {

    // get database connection
    $database = new Database();
    $db = $database->getConnection();

    $MPSistem = new Crontab($db);

    $result = $MPSistem->Autocheckin();
    //$result = "disabled";
} else {
    /*******************************
     * Error handling
     * in case of bad request method
     *******************************/
    $result['code'] = 400;
    $result['message'] = "Bad request";
}
// } else {

//     /*******************************
//      * Error handling
//      * in case of bad request method
//      *******************************/
//     $result['code'] = 400;
//     $result['message'] = "Bad request";
// }


/***************
 * Print result
 ***************/
echo $result;
