<?php

/*******************************
 * Setting HTTP response headers
 *******************************/
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Credentials: true");

require '../config/Config.php';

// $key = "66148357b2fda762a650ba6465b9db6b6f4d0facab276b0550240a792e717180";

if ($_SERVER['HTTP_AUTHORIZATION'] == KEY_API) {

    /*******************************
     * Global API Required Resources
     *******************************/
    require '../config/Database.php';

    /************************
     * Specific API Resources
     ************************/
    require '../objects/Users.php';

    /*************************
     * Setting returning array
     *************************/
    $result = array();

    /**********************
     * Check request method
     **********************/
    if ($_SERVER['REQUEST_METHOD'] == "POST") {

        // get database connection
        $database = new Database();
        $db = $database->getConnection();

        $user = new Users($db);
        $_POST = json_decode(file_get_contents("php://input"), true);

        $input = $_POST;
        
        //print_r($_POST);
        $result = $user->UpdatePassword($input);
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
echo json_encode($result);
