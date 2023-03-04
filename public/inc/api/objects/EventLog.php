<?php
require_once '../config/Config.php';


class EventLog
{

    // var connessione al db e tabella

    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // CALL THE FUNCTION
    // $event_log = new EventLog($this->conn);
    // $input_logSimple = ["parameters" => "test", "type" => "error"];
    // $event_log->logSimple($input_logSimple);

    public function logSimple($output)
    {

        $parameters = $output["parameters"];
        $type = isset($output["type"]) && $output["type"] == "error" ? "Error" : "Event";
        $user = isset($output["user"]) ? "User: " . $output["user"] : '';
        $event = isset($output["event"]) ? "Event: " . $output["event"] : '';

        $time = date("d-m-Y h:i:sa");
        $data = $time . " " . $user . " " . $event . " Data log: " . $parameters . "\n";

        $mese = date("m");
        $anno = date("Y");
        $path = "../eventLog/" . $type . "/" . $anno . "/";

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $file = $path . $mese . ".txt";

        $fd = @fopen($file, "a+");
        fwrite($fd, $data);
        fclose($fd);
    }
}
