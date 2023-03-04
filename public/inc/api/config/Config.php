<?php

$domain = $_SERVER['SERVER_NAME'];
//INSERIRE PATH ASSOLUTA DEL SERVER 
$rootDir = isset($_SERVER["DOCUMENT_ROOT"]) && $_SERVER["DOCUMENT_ROOT"] != '' ? realpath($_SERVER["DOCUMENT_ROOT"]) : "/home/u949902263/domains/crurated.com/public_html/members";
$request_uri = str_replace("/", "-", $_SERVER['REQUEST_URI']);
$request_uri = str_replace(".php", "", $request_uri);

error_reporting(E_ALL);
setlocale(LC_TIME, "it_IT");

if (strpos($domain, "localhost") !== false) {
    $id_local = 1;
    $path = "http://localhost/dokyhr/public/";
    $path_media = "http://localhost/dokyhr/public";
    ini_set('display_errors', 1);
} else {
    $id_local = 0;
    $path = "http://" . $_SERVER['SERVER_NAME'];
    $path_media = "http://" . $_SERVER['SERVER_NAME'] . "";
    ini_set('display_errors', 0);
}

if (strpos($domain, "localhost") !== false) {
    $id_local = 1;
    $path = "";
    $path_media = "";
    ini_set('display_errors', 1);
} else {
    $id_local = 0;
    $path = "http://" . $_SERVER['SERVER_NAME'];
    $path_media = "http://" . $_SERVER['SERVER_NAME'] . "";
    ini_set('display_errors', 0);
}

ini_set('log_errors', 1);
ini_set('error_log', '../EventLog/GeneralError/error_on$request_uri.txt');

define("KEY_API", hash('sha256', "gnakGnak!290193_luca"));
define("STRIPE_SK", 'sk_test_51JAa8uAsbtfzWtLLbLFNiuTnWEJTgWIx4vqyvHFUNHRK922lRsLC6gxlaU2k4kvyxJECam6cgpDKWXw1MD5oKZPO00W90Jvn0S');
define('IS_LOCAL', $id_local);
define('DOMAIN', "http://" . $domain);
define("ROOT_DIR", $rootDir);
/* define('PATH_URL', $path);
define('PATH_URL_MEDIA', $path_media); */