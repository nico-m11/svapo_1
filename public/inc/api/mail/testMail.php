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


    /*******************************
     * Global API Required Resources
     *******************************/
    require '../config/Database.php';

    /************************
     * Specific API Resources
     ************************/
    require "../objects/EmailSistem.php";

    

        // get database connection
        $database = new Database();
        $db = $database->getConnection();

        $obiettivi = new EmailSistem($db);
        
        $result = $obiettivi->SendEmailTest();
    
        
    
