<?php

class Database
{
    //locale
    // public $localKey = [
    //     "host" => "localhost",
    //     "dbName" => "svapo",
    //     "username" => "root",
    //     "password" => "root"
    // ];
    // //test
    // private $testKey = [
    //     "host" => "localhost",
    //     "dbName" => "u527579377_dokyhrtest",
    //     "username" => "u527579377_dokyhrtest",
    //     "password" => "H?m?*D^5w"
    // ];
    // //master
    // private $masterKey = [
    //     "host" => "localhost",
    //     "dbName" => "u527579377_dokyhr",
    //     "username" => "u527579377_dokyhr",
    //     "password" => "s]67og]?A=4"
    // ];

    // public $conn;


    // public function getConnection()
    // {

    //     $stringfromfile = file('../../../../.git/HEAD', FILE_USE_INCLUDE_PATH);
    //     $firstLine = $stringfromfile[0]; //get the string from the array
    //     $explodedstring = explode("/", $firstLine, 3); //seperate out by the "/" in the string
    //     $branchname = $explodedstring[2]; //get the one that is always the branch name
    //     $domain = $_SERVER['SERVER_NAME'];

    //     if (strpos($domain, "localhost") !==false) {
    //         $dbName = $this->localKey['dbName'];
    //         $host = $this->localKey['host'];
    //         $username = $this->localKey['username'];
    //         $password = $this->localKey['password'];
    //     } else if (strpos($domain, "test.dokyhr.it") !== false || $branchname == "test") {
    //         $dbName = $this->testKey['dbName'];
    //         $host = $this->testKey['host'];
    //         $username = $this->testKey['username'];
    //         $password = $this->testKey['password'];
    //     }else{
    //         $dbName = $this->masterKey['dbName'];
    //         $host = $this->masterKey['host'];
    //         $username = $this->masterKey['username'];
    //         $password = $this->masterKey['password'];
    //     }

    //     $this->conn = null;

    //     try {
    //         $this->conn = new PDO("mysql:host=" . $host . ";dbname=" . $dbName, $username, $password);
    //         $this->conn->exec("set names utf8");
    //     } catch (PDOException $exception) {
    //         echo "Connection error: " . $exception->getMessage();
    //     }

    //     return $this->conn;
    //}


    public $localKey = [
        "host" => "localhost",
        "dbName" => "svapo",
        "username" => "root",
        "password" => "root"
    ];

    public $conn;

    public function getConnection()
    {

        $dbName = $this->localKey['dbName'];
        $host = $this->localKey['host'];
        $username = $this->localKey['username'];
        $password = $this->localKey['password'];

        try {
            $this->conn = new PDO("mysql:host=" . $host . ";dbname=" . $dbName, $username, $password);
            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
