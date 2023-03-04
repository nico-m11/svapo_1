<?php
require_once '../config/Config.php';
/* require 'EventLog.php'; */
require 'Users.php';
require_once "../resources/PHPMailer-master/src/Exception.php";
require_once "../resources/PHPMailer-master/src/PHPMailer.php";
require_once "../resources/PHPMailer-master/src/SMTP.php";

require_once 'EmailSistem.php';

class Calendar
{

    // var connessione al db e tabella

    private $conn;
    private $user;
    private $email;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->user = new Users($db);
        $this->email = new EmailSistem();
    }

    public function Checkin($idUser)
    {
        $idUser = $idUser['idUser'];
        //query per inserire check-in
        $sql = "SELECT MAX(startDate) AS startDate FROM presenze WHERE idUser = $idUser AND description='Presenza'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $lastCheckout = $stmt->fetch(PDO::FETCH_ASSOC);
        $lastCheckout = isset($lastCheckout['startDate']) ? date("Y-m-d", strtotime($lastCheckout['startDate'])) : date("Y-m-d", strtotime("-1 days"));
        $today = date("Y-m-d");
        $now = date("H:i");

        if ($today > $lastCheckout) {
            $sql = "INSERT INTO `presenze`(`idUser`, `idOrganization`, `allDay`, `title`, `startDate`, `startTime`,
                    `endDate`, `endTime`, `description`, `confirmed`) 
                    VALUES ($idUser,1,0,'Presenza','$today','$now',NULL,NULL,'Presenza',0)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $result = ["risultato" => true];
        } else {
            $result = ["risultato" => false];
        }


        return $result;
    }

    public function Checkout($idUser)
    {
        $idUser = $idUser['idUser'];
        //quey per aggiornare ultimo check-in
        $today = date("Y-m-d");
        $now = date("H:i");
        $sql = "UPDATE presenze SET endDate='$today', endTime='$now', confirmed = 1
                WHERE endDate IS NULL AND endTime IS NULL AND description = 'presenza' 
                AND title = 'presenza' AND idUser = $idUser";
        try {

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            $result = ["Risultato" => "Effettuato"];

            return $result;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    public function GetAllPresenze($idUser)
    {
        $sql = "SELECT event.start_date, event.start_time, 
        event.end_date, event.end_time, event.i, event.display, event.backgroundColor
        FROM event_handling INNER JOIN event ON event.id = event_handling.id_evento
        WHERE event_handling.id_user = $idUser AND event_handling.event_type LIKE 'checkin'";
        //recupero tutti gli eventi
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $events = $stmt->fetch(PDO::FETCH_ASSOC);

        foreach ($events as $event) {
            $evento = [
                "groupId" => "presenze",
                "start" => $event['start_date'] + "T" + $event['start_time'],
                "end" => $event['end_date'] + "T" + $event['end_time'],
                "i" => $event['i'],
                "display" => $event['display'],
                "backgroundColor" => $event['backgroundColor']
            ];
        }

        return $evento;
    }

    public function GetPresenzeForTab($input)
    {
        $idUser = $input['idUser'];
        $idRole = $input['idRole'];

        $sql = "SELECT event.start_date, event.start_time, 
        event.end_date, event.end_time, event.i, event.display, event.backgroundColor
        FROM event_handling INNER JOIN event ON event.id = event_handling.id_evento
        WHERE event_handling.id_user = $idUser AND event_handling.event_type LIKE 'checkin' AND event.end_time IS NOT NULL";
        //recupero tutti gli eventi
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //recupero features ruolo
        $sql = "SELECT * FROM features_by_roles WHERE id_roles = $idRole";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $features = $stmt->fetch(PDO::FETCH_ASSOC);

        $checkin = new DateTime($features['oraCheckin']);
        $checkout = new DateTime($features['oraCheckout']);
        $pausa = (int)$features['minutiPausa'] * 60;
        $turnoMinuti = floor(($checkout->getTimestamp() - $checkin->getTimestamp() - $pausa) / 60);

        $result = [];
        foreach ($events as $event) {
            $startDate = $event['start_date'];
            $startTime = $event['start_time'];
            $endDate = $event['end_date'];
            $endTime = $event['end_time'];
            $inizio = strtotime($startTime);
            $fine = strtotime($endTime);
            $dataInizio = new DateTime($startDate . 'T' . $startTime);
            $dataFine = new DateTime($endDate . 'T' . $endTime);
            $totale = floor(($dataFine->getTimeStamp() - $dataInizio->getTimestamp() - $pausa) / 60);
            $ore = floor($totale / (60));
            $minuti = floor($totale) % 60;
            $perc = floor(($totale * 100) / $turnoMinuti);

            $inizio = new DateTime($startTime, new DateTimeZone('UTC'));
            $fine = new DateTime($endTime, new DateTimeZone('UTC'));

            $inizio = $inizio->setTimezone(new DateTimeZone('Europe/Madrid'))->format('H:i');
            $fine = $fine->setTimezone(new DateTimeZone('Europe/Madrid'))->format('H:i');

            if ($ore < 0) {
                $ore = 0;
            }
            if ($minuti < 0) {
                $minuti = 0;
            }
            if ($perc < 0) {
                $perc = 0;
            }

            $row = [
                "checkin"  => date("d-m-Y", strtotime($startDate)) . " Ore: " . date("H:i", $inizio),
                "checkout" => date("d-m-Y", strtotime($endDate)) . " Ore: " . date("H:i", $fine),
                "totaleOre"   => "Ore: " . $ore . " Minuti: " . $minuti,
                "target"      => $perc . "%"
            ];
            array_push($result, $row);
        }
        return $result;
    }

    public function LastCheck($idUser)
    {
        $idUser = $idUser['idUser'];
        $sql = "SELECT * FROM presenze
                WHERE endDate IS NULL AND endTime IS NULL 
                AND description = 'presenza' AND title = 'presenza'
                AND idUser = $idUser";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $control = [
                "setCheck" => "Check-Out",
                "setColor" => "danger"
            ];
        } else {
            $control = [
                "setCheck" => "Check-In",
                "setColor" => "success"
            ];
        }
        return $control;
    }

    public function RichiestaStraordinario($input)
    {
        $idUser = $input['idUtente'];
        $idDepartment = $input['idDepartment'];
        $dataStraordinario = $input['dataStraordinario'];
        $descrizione = $input['descrizione'];
        $oreStraordinario = $input['oreStraordinario'];
        $oreStraordinario = $oreStraordinario * 60;

        $sql = "INSERT INTO `requests`(`id`, `idUtente`, `idDepartment`, `tipoRichiesta`, allDay, 
        `startDate`, `startTime`, `endDate`, `endTime`, `hours`, `status`, `seen`, `extra`, 
        `dateRequest`) 
        VALUES (NULL,$idUser,$idDepartment,'straordinario', false, '$dataStraordinario',NULL,
        NULL,NULL,'$oreStraordinario','pending',0,'$descrizione', CURRENT_TIMESTAMP)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
    }

    public function GetHours($idUser)
    {
        $sql = "SELECT SUM(hours) AS ore FROM requests 
        WHERE idUtente=$idUser AND (tipoRichiesta LIKE 'rol'
        OR tipoRichiesta LIKE 'straordinario')";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $hours = $stmt->fetch(PDO::FETCH_ASSOC);
        $hours = round($hours['ore'] / 60, 1);

        return $hours;
    }

    public function RichiestaPermesso($file, $input)
    {
        $idUser = $input['idUser'];
        $idDepartment = $input['idDepartment'];
        $tipoPermesso = $input['tipoPermesso'];
        $allDay = isset($input['allDay']) && $input['allDay'] ? 1 : 0;
        $start = $input['start'];
        $startTime = $input['startTime'];
        $end = $input['end'];
        $endTime = $input['endTime'];
        $descrizione = $input['numeroProtocollo'];



        switch ($tipoPermesso) {
            case "malattia":
                $start = $input['start'];
                $allDay = "true";
                $startTime = "NULL";
                $end = $input['end'];
                $endTime = "NULL";
                $hours = 0;
                // no break
            case "centoQuattro":
                $start = $input['start'];
                $allDay = "true";
                $startTime = "NULL";
                $end = "NULL";
                $endTime = "NULL";
                $hours = 0;
                // no break
            case "rol":
                $start = $input['start'];
                $startTime = $input['startTime'];
                $end = $input['end'];
                $endTime = $input['endTime'];
                $hours = round((strtotime($start . 'T' . $startTime) - strtotime($end . 'T' . $endTime)) / 60, 1);
                // no break
            case "rol":
                $start = $input['start'];
                $startTime = $input['startTime'];
                $end = $input['end'];
                $endTime = $input['endTime'];
                $hours = round((strtotime($start . 'T' . $startTime) - strtotime($end . 'T' . $endTime)) / 60, 1);
                // no break
            case "retribuito":
                $start = $input['start'];
                $startTime = $input['startTime'];
                $end = $input['end'];
                $endTime = $input['endTime'];
                $hours = round((strtotime($start . 'T' . $startTime) - strtotime($end . 'T' . $endTime)) / 60, 1);
                break;
        }


        //upload del file

        $file_name = $file['name'] . '.pdf';

        if (strpos($_SERVER['SERVER_NAME'], "localhost")) {
            $path = "public/media/files/";
        } else {
            $path = "C:/xampp/htdocs/HR/dokyhr/public/media/files/";
        }

        move_uploaded_file($file['tmp_name'], $path . $file['name']);

        $file = '/media/files/' . $file['name'];

        if ($file != '/media/files/') {

            $sql = "INSERT INTO `requests`(`id`, `idUtente`, `idDepartment`, `tipoRichiesta`,
             `allDay`, `startDate`, `startTime`, `endDate`, `endTime`, `hours`, `status`, `seen`, `extra`,
              `dateRequest`) 
            VALUES (NULL,$idUser,$idDepartment,'$tipoPermesso', $allDay,$start,$startTime,
              $end,$endTime,(-1*$hours),'pending',0,'$file',
              CURRENT_TIMESTAMP)";
        } else {

            $sql = "INSERT INTO `requests`(`id`, `idUtente`, `idDepartment`, `tipoRichiesta`,
            `allDay`, `startDate`, `startTime`, `endDate`, `endTime`, `hours`, `status`, `seen`, `extra`,
             `dateRequest`) 
           VALUES (NULL,$idUser,$idDepartment,'$tipoPermesso', $allDay,$start,$startTime,
             $end,$endTime,(-1*$hours),'pending',0,$descrizione,
             CURRENT_TIMESTAMP)";
        }
        $stmt = $this->conn->prepare($sql);
        //execute query
        $stmt->execute();

        $sql = "SELECT user.email, user_data.firstname, user_data.lastname FROM user LEFT JOIN user_data ON user.id = user_data.id_user WHERE user.idDepartment = $idDepartment AND user.id_role = 3";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $info_user = $stmt->fetch(PDO::FETCH_ASSOC);

        $sqlUtente = "SELECT firstname, lastname FROM user_data WHERE id_utente = $idUser";
        $stmt = $this->conn->prepare($sqlUtente);
        $stmt->execute();

        $utentePerm = $stmt->fetch(PDO::FETCH_ASSOC);
        $nomeUtente = $utentePerm['fistname'] . " " . $utentePerm['lastname'];

        $email = $info_user["email"];
        $firstName = $info_user["firstname"];
        $lastname = $info_user['lastname'];
        $tipoPermesso = ucfirst($tipoPermesso);

        $emailCall = new EmailSistem();

        $oggetto = "Richiesta permesso - $tipoPermesso";
        $messaggio = "<html>
        <head>
        
        </head>
        <body>
        <p>Salve, $firstName $lastname</p><br/>
        <p>L'utente $nomeUtente ha effettuato una richiesta di permesso ($tipoPermesso)</p><br/>
        <p>accedi nell'area di <a href='https://test.dokyhr.it/user-profile/profile-overview'>messaggi ricevuti</a> per visualizzarla </p>
        <p>Saluti, il team di DokyHR</p>
        </body>
        </html>";

        $emailCall->SendMail($email, $oggetto, $messaggio, $pathAllegato = "");
    }

    public function GetCountry()
    {
        $sql = "SELECT country_name, id FROM countries";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $countries = $stmt->fetchAll(PDO::FETCH_ASSOC);


        $result = [];
        foreach ($countries as $country) {
            $arr = [
                "id" => $country['id'],
                "country" => $country['country_name'],
            ];
            array_push($result, $arr);
        }

        return $result;
    }
    public function GetRegioni()
    {
        $sql = "SELECT id, nome FROM regioni";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $regioni = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($regioni as $regione) {
            $arr = [
                "id" => $regione['id'],
                "regione" => $regione['nome'],
            ];
            array_push($result, $arr);
        }
        return $result;
    }
    public function GetProvince($input)
    {
        $idRegione = $input['idRegione'];

        $sql = "SELECT id, nome, sigla_automobilistica FROM province WHERE id_regione = (SELECT id FROM regioni WHERE nome LIKE '$idRegione')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $province = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($province as $provincia) {
            $arr = [
                "id" => $provincia['id'],
                "provincia" => $provincia['nome'],
                "sigla" => $provincia['sigla_automobilistica'],
            ];
            array_push($result, $arr);
        }
        return $result;
    }
    public function GetCity($input)
    {
        $idProvincia = $input['idProvincia'];

        $sql = "SELECT id, nome FROM comuni WHERE id_provincia =(SELECT id FROM province WHERE sigla_automobilistica LIKE '$idProvincia')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $cities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($cities as $city) {
            $arr = [
                "id" => $city['id'],
                "city" => $city['nome']
            ];
            array_push($result, $arr);
        }
        return $result;
    }
    public function GetDepartments($input)
    {

        if ($input['idOrganization'] == 0) {
            $idOrganization = $input['idUser'];
        } else {
            $idOrganization = $input['idOrganization'];
        }
        $idRole = $input['idRole'];
        $idDepartment = $input['idDepartment'];
        $ownDepartment = $input['ownDepartment'];

        if ((int)$idRole == _ADMIN_) {
            $sql = "SELECT * FROM department WHERE idOrganization = $idOrganization AND id != $ownDepartment";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result = [];
            foreach ($departments as $department) {
                $arr = [
                    "nome" => ucwords($department['name']),
                    "id" => $department['id']
                ];
                array_push($result, $arr);
            }
            return $result;
        } else return "Non autorizzato";
    }

    public function GetUsers($input)
    {
        if ($input['idOrganization'] == 0) {
            $idOrganization = $input['idUser'];
        } else {
            $idOrganization = $input['idOrganization'];
        }
        $idRole = $input['idRole'];
        $idDepartment = $input['idDepartment'];
        $idUser = $input['idUser'];

        if ((int)$idRole == _ADMIN_) {
            $sql = "SELECT user_data.firstname, user_data.lastname, user.id
            FROM user JOIN user_data ON user.id = user_data.id_user 
            WHERE user.idDepartment = $idDepartment AND user.id != $idUser";
        } else if ((int)$idRole == _HR_) {
            $sql = "SELECT user_data.firstname, user_data.lastname, user.id
            FROM user_data JOIN user ON user.id = user_data.id_user
            JOIN department ON user.idDepartment = department.id 
            WHERE department.id = $idDepartment OR department.idPadre = $idDepartment AND user.id != $idUser";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($users as $user) {
            $arr = [
                "fullname" => ucwords($user['firstname']) . " " . ucwords($user['lastname']),
                "id" => $user['id']
            ];
            array_push($result, $arr);
        }

        return $result;
    }

    public function GetPresenzeTab($input)
    {
        $dipendente = $input['dipendente'];

        $sql = "SELECT * FROM presenze WHERE idUser = $dipendente AND confirmed = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $presenze = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $months = ["Gennaio", "Febbraio", "Marzo", "Aprile", "Maggio", "Giugno", "Luglio", "Agosto", "Settembre", "Ottobre", "Novembre", "Dicembre"];

        $result = [];
        foreach ($presenze as $presenza) {
            if ($presenza['allDay'] == 0) {
                $startDay = date("d", strtotime($presenza['startDate']));
                $startMonth = $months[(int)date("m", strtotime($presenza['startDate'])) - 1];
                $startYear = date("Y", strtotime($presenza['startDate']));
                $startDay = $startDay . " " . $startMonth . " " . $startYear;
                $endDay = date("d", strtotime($presenza['endDate']));
                $endMonth = $months[(int)date("m", strtotime($presenza['endDate'])) - 1];
                $endYear = date("Y", strtotime($presenza['endDate']));
                $endDay = $endDay . " " . $endMonth . " " . $endYear;

                $inizio = new DateTime($presenza['startTime'], new DateTimeZone('UTC'));
                $fine = new DateTime($presenza['endTime'], new DateTimeZone('UTC'));

                $inizio = $inizio->setTimezone(new DateTimeZone('Europe/Madrid'))->format('H:i');
                $fine = $fine->setTimezone(new DateTimeZone('Europe/Madrid'))->format('H:i');


                $arr = [
                    "id" => (int)$presenza['id'],
                    "type" => $presenza['title'],
                    "startDate" => $startDay,
                    "startTime" => isset($presenza['startTime']) || $presenza['startTime'] != "" ? $inizio : "-",
                    "endDate" => $endDay,
                    "endTime" => isset($presenza['endTime']) || $presenza['endTime'] != "" ? $fine : "-",
                    "button" => [
                        "id" => $presenza['id'],
                        "startDate" => date("Y-m-d", strtotime($presenza['startDate'])),
                        "startTime" => isset($presenza['startTime']) || $presenza['startTime'] != "" ? $inizio : "-",
                        "endDate" => date("Y-m-d", strtotime($presenza['endDate'])),
                        "endTime" => isset($presenza['endTime']) || $presenza['endTime'] != "" ? $fine : "-",
                        "allDay" => false,
                        "type" => $presenza['title'],
                        "date" => $startDay,
                    ],
                ];
            } else {
                $startDay = date("d", strtotime($presenza['startDate']));
                $startMonth = $months[(int)date("m", strtotime($presenza['startDate'])) - 1];
                $startYear = date("Y", strtotime($presenza['startDate']));
                $startDay = $startDay . " " . $startMonth . " " . $startYear;
                $arr = [
                    "id" => (int)$presenza['id'],
                    "type" => $presenza['title'],
                    "startDate" => $startDay,
                    "startTime" => "Intera giornata",
                    "endDate" => "-",
                    "endTime" => "-",
                    "button" => [
                        "id" => $presenza['id'],
                        "startDate" => date("Y-m-d", strtotime($presenza['startDate'])),
                        "startTime" => "",
                        "endDate" => "",
                        "endTime" => "",
                        "allDay" => true,
                        "type" => $presenza['title'],
                        "date" => $startDay,
                    ],
                ];
            }
            array_push($result, $arr);
        }
        return $result;
    }

    public function editGestione($input)
    {
        $id = $input['idPresenza'];
        $type = $input['type'];
        $startDate = $input['startDate'];
        $startTime = $input['startTime'];
        $endDate = $input['endDate'];
        $endTime = $input['endTime'];

        $sql = "UPDATE `presenze` SET 
        `title`='$type',`startDate`='$startDate',`startTime`='$startTime',
        `endDate`='$endDate',`endTime`='$endTime' WHERE id = $id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
    }

    public function DeleteGestione($input)
    {
        $id = $input['idPresenza'];

        $sql = "DELETE FROM presenze WHERE id = $id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
    }

    public function CreateGestione($input)
    {
        if ($input['idOrganization'] == 0) {
            $idOrganization = $input['idSupervisor'];
        } else {
            $idOrganization = $input['idOrganization'];
        }

        $idDipendente = $input['idUser'];
        $type = $input['type'];
        $startDate = $input['startDate'];
        $startTime = $input['startTime'];
        $endDate = $input['endDate'];
        $endTime = $input['endTime'];
        $allDay = isset($input['allDay']) && $input['allDay'] == 1 ? true : false;

        if ($allDay) {
            $sql = "INSERT INTO presenze (idUser, idOrganization, allDay, title, startDate)
                    VALUES($idDipendente, $idOrganization, 1, '$type', '$startDate')";
        } else {

            $sql = "INSERT INTO `presenze`(`idUser`, `idOrganization`, `allDay`, `title`, `startDate`, `startTime`, `endDate`, `endTime`) 
                    VALUES ($idDipendente,$idOrganization,0,'$type','$startDate','$startTime','$endDate','$endTime')";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
    }

    public function AddNewEvent($input)
    {
        if ($input['idOrganization'] == 0) {
            $idOrganization = $input['idUser'];
        } else {
            $idOrganization = $input['idOrganization'];
        }

        $idUser = $input['idUser'];
        $allDay = $input['allDay'];
        $department = $input['department'];
        $selectedDepartment = isset($input['selectedDepartment']) && $department ? $input['selectedDepartment'] : -1;
        $startDate = $input['startDate'];
        $startTime = isset($input['startTime']) ? $input['startTime'] : "";
        $endDate = isset($input['endDate']) ? $input['endDate'] : "";
        $endTime = isset($input['endTime']) ? $input['endTime'] : "";
        $title = $input['title'];
        $description = isset($input['description']) ? $input['description'] : "";
        $backgroundColor = $input['backgroundColor'];

        if ($allDay) {

            $sql = "INSERT INTO `events`(`idUser`, `idOrganization`, `idDepartment`, `title`, 
                    `allday`, `startDate`, `description`, `backgroundColor`) 
                    VALUES ($idUser,$idOrganization,$selectedDepartment,'$title',1,'$startDate',
                    '$description','$backgroundColor')";
        } else {
            $sql = "INSERT INTO `events`(`idUser`, `idOrganization`, `idDepartment`, `title`, `allday`,
                `startDate`, `startTime`, `endDate`, `endTime`, `description`, `backgroundColor`)
                VALUES ($idUser,$idOrganization,$selectedDepartment,'$title',0,'$startDate',
                '$startTime','$endDate','$endTime','$description','$backgroundColor')";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
    }

    public function GetEvents($input)
    {
        if ($input['idOrganization'] == 0) {
            $idOrganization = $input['idUser'];
        } else {
            $idOrganization = $input['idOrganization'];
        }
        $idUser = $input['idUser'];
        $idDepartment = $input['idDepartment'];

        $sql = "SELECT * FROM `events` WHERE idUser = $idUser OR idDepartment = $idDepartment OR idDepartment = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($events as $event) {
            if ($event['allday'] == 1) {
                $arr = [
                    "title" => $event['title'],
                    "start" => $event['startDate'],
                    "end" => $event['startDate'],
                    "extendedProps" => [
                        "allDay" => true,
                        "id" => $event['id'],
                    ],
                    "description" => isset($event['description']) ? $event['description'] : "",
                    "backgroundColor" => $event['backgroundColor'],
                    "borderColor" => $event['backgroundColor'],
                ];
                if ($event['idDepartment'] != -1) {
                    $arr['extendedProps']["department"] = $event['idDepartment'] == 0 ? "Aziendale" : "Tutto il dipartimento";
                }
            } else {
                $arr = [
                    "title" => $event['title'],
                    "start" => date("Y-m-d", strtotime($event['startDate'])) . "T" . date("H:i:s", strtotime($event['startTime'])),
                    "end" => date("Y-m-d", strtotime($event['endDate'])) . "T" . date("H:i:s", strtotime($event['endTime'])),
                    "extendedProps" => [
                        "allDay" => false,
                        "id" => $event['id'],
                    ],
                    "description" => isset($event['description']) ? $event['description'] : "",
                    "backgroundColor" => $event['backgroundColor'],
                    "borderColor" => $event['backgroundColor'],
                ];
                if ($event['idDepartment'] != -1) {
                    $arr['extendedProps']["department"] = $event['idDepartment'] == 0 ? "Aziendale" : "Tutto il dipartimento";
                }
            }
            array_push($result, $arr);
        }
        return $result;
    }

    public function DeleteEvento($input)
    {
        $idEvento = $input['idEvento'];

        $sql = "DELETE FROM events WHERE id = $idEvento";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
    }

    public function RichiediPermesso($file, $input)
    {
        if ($input['idOrganization'] == 0) {
            $idOrganization = $input['idUser'];
        } else {
            $idOrganization = $input['idOrganization'];
        }

        $isFile = $file;
        $type = $input['type'];
        $startDate = $input['startDate'];
        $startTime = $input['startTime'];
        $endDate = $input['endDate'];
        $endTime = $input['endTime'];
        $idUser = $input['idUser'];
        $description = $input['description'];
        $protocollo = isset($input['protocollo']) ? $input['protocollo'] : "0";
        $idDepartment = $input['idDepartment'];
        $idDepartment = $input['idDepartment'];
        $nomeDipendente = ucwords($input['firstname']) . " " . ucwords($input['lastname']);

        switch ($type) {
            case "centoquattro":
                $type = "permesso per 104";
                break;
            case "malattia":
                $type = "permesso di malattia";
                break;
            case "rol":
                $type = "permesso di riduzione orario lavorativo";
                break;
            case "retribuito":
                $type = "permesso retribuito";
                break;
        }
        if ($isFile) {
            $path = "public/media/files/";
            move_uploaded_file($file['tmp_name'], $path . "id-" . $idUser . $file['name']);
            $file = '/media/files/' . "id-" . $idUser . $file['name'];

            if ($input['allDay'] == 1) {
                $sql = "INSERT INTO `presenze`(`idUser`, `idOrganization`, `allDay`, `title`, `startDate`, `description`, `confirmed`) VALUES ($idUser,$idOrganization,1,
                'Permesso','$startDate','$type',0);
                INSERT INTO `richieste`(`idUser`, `idPadre`, `idPresenza`, `idDepartment`, `idOrganization`, `description`, `status`, `extra`) 
                VALUES ($idUser,0,(SELECT LAST_INSERT_ID()), $idDepartment,$idOrganization, '$description', 0, '$file')";
            } else {
                $sql = "INSERT INTO `presenze`(`idUser`, `idOrganization`, `allDay`, `title`, `startDate`, `startTime`, 
                `endDate`, `endTime`, `description`, `confirmed`) VALUES ($idUser,$idOrganization,0,
                'Permesso','$startDate', '$startTime', '$endDate', '$endTime', '$type',0);
                INSERT INTO `richieste`(`idUser`, `idPadre`, `idPresenza`, `idDepartment`,`idOrganization`, `description`, `status`, `extra`) 
                VALUES ($idUser,0,(SELECT LAST_INSERT_ID()), $idDepartment, $idOrganization, '$description', 0, '$file')";
            }
        } else {
            if ($input['allDay'] == 1) {
                $sql = "INSERT INTO `presenze`(`idUser`, `idOrganization`, `allDay`, `title`, `startDate`, `description`, `confirmed`) VALUES ($idUser,$idOrganization,1,
                'Permesso','$startDate','$type',0);
                INSERT INTO `richieste`(`idUser`, `idPadre`, `idPresenza`, `idDepartment`, `idOrganization`, `description`, `status`, `extra`) 
                VALUES ($idUser,0,(SELECT LAST_INSERT_ID()),$idDepartment,$idOrganization,0, '$description', '$protocollo')";
            } else {
                $sql = "INSERT INTO `presenze`(`idUser`, `idOrganization`, `allDay`, `title`, `startDate`, `startTime`, 
                `endDate`, `endTime`, `description`, `confirmed`) VALUES ($idUser,$idOrganization,0,
                'Permesso','$startDate', '$startTime', '$endDate', '$endTime', '$type',0);
                INSERT INTO `richieste`(`idUser`, `idPadre`, `idPresenza`, `idDepartment`, `idOrganization`, `description`, `status`, `extra`) 
                VALUES ($idUser, 0,(SELECT LAST_INSERT_ID()), $idDepartment,$idOrganization, '$description', 0, '$protocollo')";
            }
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $sql = "SELECT user.email, user.emailAziendale, user_data.firstname, user_data.lastname FROM department 
        JOIN user ON user.idDepartment = department.id 
        JOIN user_data ON user.id = user_data.id_user WHERE (department.id = $idDepartment AND user.id_role =" . _HR_ . ") 
        OR (department.id = (SELECT idPadre FROM department WHERE id = $idDepartment AND user.id_role = " . _HR_ . ")) 
        OR user.id_role = " . _ADMIN_;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $supervisors = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $mail = new EmailSistem();
        foreach ($supervisors as $supervisor) {
            $firstname = $supervisor['firstname'];
            $lastname = $supervisor['lastname'];
            $email = isset($supervisor['emailAziendale']) && $supervisor['emailAziendale'] != "undefined" ? $supervisor['emailAziendale'] : $supervisor['email'];
            $oggetto = "Richiesta $type";
            $desMessage = strlen($description) > 0 ? "<br/><p>Con seguente motivazione: $description</p>" : "";
            $messaggio = "<p>Ciao $firstname $lastname!</p>
                <p>$nomeDipendente ha appena fatto richiesta di un $type</p>
                    $desMessage
                 <p>Per poter accettare o rifiutare accedi subito alla tua area personale.</p>
                 <p>Distinti saluti,<br>
                 Il team di DokyHR.</p>";
            $mail->SendMail($email, $oggetto, $messaggio);
        }
    }

    public function GetRichieste($input)
    {
        $auth = $this->user->GetUserByToken(["accessToken" => $input['accessToken']]);
        $idOrganization = $auth['id_organization'];
        $idRole = $input['idRole'];
        $idUser = $input['idUser'];

        if ($idRole == _ADMIN_) {
            $sql = "SELECT richieste.*, user_data.firstname, user_data.lastname, user.email, presenze.title, presenze.description AS presDescription,
                    presenze.allDay, presenze.startDate, presenze.startTime, presenze.endDate, presenze.endTime
                    FROM richieste JOIN user_data ON richieste.idUser = user_data.id_user
                    JOIN presenze ON richieste.idPresenza = presenze.id
                    JOIN user ON richieste.idUser = user.id
                    WHERE richieste.idOrganization = $idOrganization";
        } else if ($idRole == _HR_) {
            $idDepartment = $input['idDepartment'];
            $sql = "SELECT richieste.*, user_data.firstname, user_data.lastname, user.email, presenze.title, presenze.description AS presDescription,
                    presenze.allDay, presenze.startDate, presenze.startTime, presenze.endDate, presenze.endTime
                    FROM richieste JOIN user_data ON user_data.id_user = richieste.idUser
                    JOIN department ON richieste.idDepartment = department.id
                    JOIN presenze ON richieste.idPresenza = presenze.id
                    JOIN user ON richieste.idUser = user.id
                    WHERE department.id = $idDepartment OR department.idPadre = $idDepartment";
        } else {
            $sql = "SELECT richieste.*, presenze.title, presenze.description AS presDescription,
                    presenze.allDay, presenze.startDate, presenze.startTime, presenze.endDate, presenze.endTime
                    FROM richieste JOIN presenze ON richieste.idPresenza = presenze.id WHERE richieste.idUser = $idUser";
        }

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $richieste = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [
                "presenze" => [],
                "ferie" => [],
                "straordinari" => [],
                "permessi" => [],
            ];

            function handleType($type)
            {
                $placeholder = "";
                switch (trim(strtolower($type))) {
                    case "permesso di riduzione orario lavorativo":
                        $placeholder = "ROL";
                        break;
                    case "ferie":
                        $placeholder = "Ferie";
                        break;
                    case "straordinario":
                        $placeholder = "Straordinario";
                        break;
                    case "permesso di malattia":
                        $placeholder = "Malattia";
                        break;
                    case "permesso retribuito":
                        $placeholder = "Retribuito";
                        break;
                }

                return $placeholder;
            }

            foreach ($richieste as $richiesta) {
                $arr = [
                    "idRichiesta" => $richiesta['id'],
                    "nomeDipendente" => $idRole == _DIPENDENTE_ ? "" : $richiesta['firstname'] . " " . $richiesta['lastname'],
                    "idPresenza" => $richiesta['idPresenza'],
                    "dipendenteEmail" => isset($richiesta['email']) ? $richiesta['email'] : "",
                    "type" => handleType($richiesta['presDescription']),
                    "status" => $richiesta['status'], //0: in attesa, 1: confermata, -1: rifiutata
                    "description" => $richiesta['description'],
                    "allDay" => (int)$richiesta['allDay'],
                    "startDate" => isset($richiesta['startDate']) ? date("Y-m-d", strtotime($richiesta['startDate'])) : "",
                    "startTime" => isset($richiesta['startTime']) ? date("H:i", strtotime($richiesta['startTime'])) : "",
                    "endDate" => isset($richiesta['endDate']) ? date("Y-m-d", strtotime($richiesta['endDate'])) : "",
                    "endTime" => isset($richiesta['endTime']) ? date("H:i", strtotime($richiesta['endTime'])) : "",
                    "dateUpdated" => date($richiesta['dateUpdated']),
                    "dataRichiesta" => date($richiesta['dataRichiesta']),
                ];
                if (trim(handleType($richiesta['presDescription'])) == "Ferie") {
                    array_push($result['ferie'], $arr);
                } else if (trim(handleType($richiesta['presDescription'])) == "Straordinario") {
                    array_push($result['straordinari'], $arr);
                } else if (trim($richiesta['presDescription']) == "Presenza") {
                    array_push($result['presenze'], $arr);
                } else {
                    array_push($result['permessi'], $arr);
                }
            }
            return ['code' => 200, 'message' => 'riuscito', 'result' => $result];
        } catch (PDOException $e) {
            return ['code' => $e->getCode(), 'message' => $e->getMessage()];
        }
    }

    public function RichiediStraordinario($input)
    {
        if ($input['idOrganization'] == 0) {
            $idOrganization = $input['idUser'];
        } else {
            $idOrganization = $input['idOrganization'];
        }

        $idUser = $input['idUser'];
        $nomeDipendente = ucwords($input['firstname']) . " " . ucwords($input['lastname']);
        $idDepartment = $input['idDepartment'];
        $startDate = $input['startDate'];
        $startTime = $input['startTime'];
        $endTime = $input['endTime'];
        $hours = round(abs(strtotime($endTime) - strtotime($startTime)) / 60, 2);
        $description = $input['descrizione'];

        $sql = "INSERT INTO `presenze`(`idUser`, `idOrganization`, `allDay`, `title`, `startDate`, `startTime`, 
        `endDate`, `endTime`, `description`, `confirmed`) VALUES ($idUser,$idOrganization,0,
        'Permesso','$startDate', '$startTime', '$startDate', '$endTime', 'straordinario',0);
        INSERT INTO `richieste`(`idUser`, `idPadre`, `idPresenza`, `idDepartment`, `idOrganization`, `description`, `status`, `extra`) 
        VALUES ($idUser, 0,(SELECT LAST_INSERT_ID()), $idDepartment,$idOrganization,'$description', 0, 'straordinario - $hours')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $sql = "SELECT user.email, user.emailAziendale, user_data.firstname, user_data.lastname FROM department 
        JOIN user ON user.idDepartment = department.id 
        JOIN user_data ON user.id = user_data.id_user WHERE (department.id = $idDepartment AND user.id_role =" . _HR_ . ") 
        OR (department.id = (SELECT idPadre FROM department WHERE id = $idDepartment AND user.id_role = " . _HR_ . ")) 
        OR user.id_role = " . _ADMIN_;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $supervisors = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $mail = new EmailSistem();
        foreach ($supervisors as $supervisor) {
            $firstname = $supervisor['firstname'];
            $lastname = $supervisor['lastname'];
            $email = isset($supervisor['emailAziendale']) && $supervisor['emailAziendale'] != "undefined" ? $supervisor['emailAziendale'] : $supervisor['email'];
            $oggetto = "Richiesta straordinario";
            $desMessage = strlen($description) > 0 ? "<br/><p>Con seguente motivazione: $description</p>" : "";
            $messaggio = "<p>Ciao $firstname $lastname!</p>
                <p>$nomeDipendente ha appena fatto richiesta di un straordinario di $hours minuti</p>
                $desMessage
                 <p>Per poter accettare o rifiutare accedi subito alla tua area personale, nell'apposita area richieste.</p>
                 <p>Distinti saluti,<br>
                 Il team di DokyHR.</p>";
            $mail->SendMail($email, $oggetto, $messaggio);
        }
    }

    public function RichiediPresenza($input)
    {
        if ($input['idOrganization'] == 0) {
            $idOrganization = $input['idUser'];
        } else {
            $idOrganization = $input['idOrganization'];
        }

        $idUser = $input['idUser'];
        $nomeDipendente = ucwords($input['firstname']) . " " . ucwords($input['lastname']);
        $idDepartment = $input['idDepartment'];
        $startDate = $input['startDate'];
        $startTime = $input['startTime'];
        $endTime = $input['endTime'];
        $description = $input['descrizione'];

        $sql = "INSERT INTO `presenze`(`idUser`, `idOrganization`, `allDay`, `title`, `startDate`, `startTime`, 
        `endDate`, `endTime`, `description`, `confirmed`) VALUES ($idUser,$idOrganization,0,
        'Presenza','$startDate', '$startTime', '$startDate', '$endTime', 'presenze',0);
        INSERT INTO `richieste`(`idUser`, `idPadre`, `idPresenza`, `idDepartment`, `idOrganization`, `description`, `status`, `extra`) 
        VALUES ($idUser, 0,(SELECT LAST_INSERT_ID()), $idDepartment,$idOrganization,'$description', 0, 'Presenza')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $sql = "SELECT user.email, user.emailAziendale, user_data.firstname, user_data.lastname FROM department 
        JOIN user ON user.idDepartment = department.id 
        JOIN user_data ON user.id = user_data.id_user WHERE (department.id = $idDepartment AND user.id_role =" . _HR_ . ") 
        OR (department.id = (SELECT idPadre FROM department WHERE id = $idDepartment AND user.id_role = " . _HR_ . ")) 
        OR user.id_role = " . _ADMIN_;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $supervisors = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $mail = new EmailSistem();
        foreach ($supervisors as $supervisor) {
            $firstname = $supervisor['firstname'];
            $lastname = $supervisor['lastname'];
            $email = isset($supervisor['emailAziendale']) && $supervisor['emailAziendale'] != "undefined" ? $supervisor['emailAziendale'] : $supervisor['email'];
            $oggetto = "Richiesta presenza";
            $desMessage = strlen($description) > 0 ? "<br/><p>Con seguente motivazione: $description</p>" : "";
            $messaggio = "<p>Ciao $firstname $lastname!</p>
                <p>$nomeDipendente ha appena fatto richiesta di presenza </p>
                $desMessage
                 <p>Per poter accettare o rifiutare accedi subito alla tua area personale, nell'apposita area richieste.</p>
                 <p>Distinti saluti,<br>
                 Il team di DokyHR.</p>";
            $mail->SendMail($email, $oggetto, $messaggio);
        }
    }

    public function RichiediFerie($input)
    {
        if ($input['idOrganization'] == 0) {
            $idOrganization = $input['idUser'];
        } else {
            $idOrganization = $input['idOrganization'];
        }

        $idUser = $input['idUser'];
        $nomeDipendente = ucwords($input['firstname']) . " " . ucwords($input['lastname']);
        $idDepartment = $input['idDepartment'];
        $description = $input['descrizione'];
        $startDate = $input['startDate'];
        $endDate = $input['endDate'];
        $descrizione = $input['descrizione'] != "" ? "Ulteriori informazioni: " . $input['descrizione'] : "";

        $sql = "INSERT INTO `presenze`(`idUser`, `idOrganization`, `allDay`, `title`, `startDate`, 
        `endDate`,  `description`, `confirmed`) VALUES ($idUser,$idOrganization,0,
        'Permesso','$startDate', '$endDate', 'ferie',0);
        INSERT INTO `richieste`(`idUser`, `idPadre`, `idPresenza`, `idDepartment`, `idOrganization`, `description`, `status`, `extra`) 
        VALUES ($idUser, 0,(SELECT LAST_INSERT_ID()), $idDepartment,$idOrganization, '$description',0, 'ferie')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $sql = "SELECT user.email, user.emailAziendale, user_data.firstname, user_data.lastname FROM department 
        JOIN user ON user.idDepartment = department.id 
        JOIN user_data ON user.id = user_data.id_user WHERE (department.id = $idDepartment AND user.id_role =" . _HR_ . ") 
        OR (department.id = (SELECT idPadre FROM department WHERE id = $idDepartment AND user.id_role = " . _HR_ . ")) 
        OR user.id_role = " . _ADMIN_;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $supervisors = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $mail = new EmailSistem();
        foreach ($supervisors as $supervisor) {
            $firstname = $supervisor['firstname'];
            $lastname = $supervisor['lastname'];
            $email = isset($supervisor['emailAziendale']) && $supervisor['emailAziendale'] != "undefined" ? $supervisor['emailAziendale'] : $supervisor['email'];
            $oggetto = "Richiesta ferie";
            $desMessage = strlen($description) > 0 ? "<br/><p>Con seguente motivazione: $description</p>" : "";
            $messaggio = "<p>Ciao $firstname $lastname!</p>
                <p>$nomeDipendente ha appena fatto richiesta di ferie</p>
                    $desMessage
                 <p>Per poter accettare o rifiutare accedi subito alla tua area personale, nell'apposita area richieste.</p>
                 <p>$descrizione</p>
                 <p>Distinti saluti,<br>
                 Il team di DokyHR.</p>";
            $mail->SendMail($email, $oggetto, $messaggio);
        }
    }

    public function GetPresenze($input)
    {
        $idUser = $input['idUser'];

        $sql = "SELECT * FROM presenze WHERE idUser = $idUser AND confirmed = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $presenze = $stmt->fetchAll(PDO::FETCH_ASSOC);

        function handleColor($title)
        {
            if ($title == "Presenza") {
                $color = "#2a9d8f";
            } else if ($title == "Permesso") {
                $color = "#ca031a";
            } else if ($title == "Ferie") {
                $color = "#8950FC";
            } else if ($title == "Straordinario") {
                $color = "#ffc300";
            } else if ($title == "Assenza") {
                $color = "#c1121f";
            }
            return $color;
        }

        $result = [];
        foreach ($presenze as $presenza) {

            $inizio = isset($presenza['startTime']) ? new DateTime($presenza['startTime'], new DateTimeZone('UTC')) : "";
            $fine = isset($presenza['endTime']) ? new DateTime($presenza['endTime'], new DateTimeZone('UTC')) : "";

            $inizio = isset($presenza['startTime']) ? $inizio->setTimezone(new DateTimeZone('Europe/Madrid'))->format('H:i') : "";
            $fine = isset($presenza['endTime']) ? $fine->setTimezone(new DateTimeZone('Europe/Madrid'))->format('H:i') : "";

            if ($presenza['allDay'] == 0) {

                $arr = [
                    "id" => $presenza['id'],
                    "title" => $presenza['title'],
                    "start" => isset($presenza['startTime']) ? date("Y-m-d", strtotime($presenza['startDate'])) . "T" . $inizio : date("Y-m-d", strtotime($presenza['startDate'])),
                    "end" => isset($presenza['endTime']) ?  date("Y-m-d", strtotime($presenza['endDate'])) . "T" . $fine : date("Y-m-d", strtotime($presenza['endDate'])),
                    "backgroundColor" => handleColor($presenza['title']),
                    "color" => handleColor($presenza['title'])
                ];
            } else {
                $arr = [
                    "id" => $presenza['id'],
                    "title" => $presenza['title'],
                    "start" => isset($presenza['startTime']) ? date("Y-m-d", strtotime($presenza['startDate'])) . "T" . date("H:i", strtotime($presenza['startTime'])) : date("Y-m-d", strtotime($presenza['startDate'])),
                    "allDay" => true,
                    "backgroundColor" => handleColor($presenza['title']),
                    "color" => handleColor($presenza['title'])
                ];
            }
            array_push($result, $arr);
        }
        return $result;
    }

    public function GetProssimiEventi($input)
    {
        if ($input['idOrganization'] == 0) {
            $idOrganization = $input['idUser'];
        } else {
            $idOrganization = $input['idOrganization'];
        }
        $idUser = $input['idUser'];
        $idDepartment = $input['idDepartment'];

        $sql = "SELECT * FROM events WHERE startDate >= CURRENT_DATE AND 
                (idUser = $idUser OR idDepartment = $idDepartment OR (idDepartment = 0 AND idOrganization = $idOrganization)) 
                ORDER BY startDate asc LIMIT 5";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($events as $event) {
            $arr = [
                "title" => $event['title'],
                "startDate" => date("d/m/Y", strtotime($event['startDate'])),
                "startTime" => $event['allday'] == 0  ? date("H:i", strtotime($event['startTime'])) : "-",
                "endTime" => $event['allday'] == 0 ? date("H:i", strtotime($event['endTime'])) : "-",
                "description" => isset($event['description']) ? $event['description'] : "",
                "allDay" => $event['allday']
            ];
            array_push($result, $arr);
        }
        return $result;
    }

    public function UpdateRequest($input)
    {
        $auth = $this->user->GetUserByToken(["accessToken" => $input['accessToken']]);

        if (!isset($auth['code'])) {
            $idPresenza = $input['idPresenza'];
            $idRichiesta = $input['idRichiesta'];
            $type = $input['type'];
            $dipendenteMail = $input['dipendenteMail'];
            $dipendente = $input['dipendente'];
            $value = $input['value'];

            $sql = "UPDATE `richieste` SET `status` = $value, `dateUpdated` = CURRENT_TIMESTAMP WHERE `id` = $idRichiesta;
                    UPDATE `presenze` SET `confirmed` = $value WHERE `id` = $idPresenza";

            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();

                $oggetto = "Aggiornamento richiesta - $type";
                $messaggio = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                <html xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office" style="width:100%;font-family:arial, "helvetica neue", helvetica, sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;padding:0;Margin:0">
                <head>
                <meta charset="UTF-8">
                <meta content="width=device-width, initial-scale=1" name="viewport">
                <meta name="x-apple-disable-message-reformatting">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta content="telephone=no" name="format-detection">
                <title>Nuovo modello</title><!--[if (mso 16)]>
                <style type="text/css">
                a {text-decoration: none;}
                </style>
                <![endif]--><!--[if gte mso 9]><style>sup { font-size: 100% !important; }</style><![endif]--><!--[if gte mso 9]>
                <xml>
                <o:OfficeDocumentSettings>
                <o:AllowPNG></o:AllowPNG>
                <o:PixelsPerInch>96</o:PixelsPerInch>
                </o:OfficeDocumentSettings>
                </xml>
                <![endif]-->
                <style type="text/css">
                #outlook a {
                padding:0;
                }
                .ExternalClass {
                width:100%;
                }
                .ExternalClass,
                .ExternalClass p,
                .ExternalClass span,
                .ExternalClass font,
                .ExternalClass td,
                .ExternalClass div {
                line-height:100%;
                }
                .es-button {
                mso-style-priority:100!important;
                text-decoration:none!important;
                }
                a[x-apple-data-detectors] {
                color:inherit!important;
                text-decoration:none!important;
                font-size:inherit!important;
                font-family:inherit!important;
                font-weight:inherit!important;
                line-height:inherit!important;
                }
                .es-desk-hidden {
                display:none;
                float:left;
                overflow:hidden;
                width:0;
                max-height:0;
                line-height:0;
                mso-hide:all;
                }
                [data-ogsb] .es-button {
                border-width:0!important;
                padding:10px 20px 10px 20px!important;
                }
                @media only screen and (max-width:600px) {p, ul li, ol li, a { line-height:150%!important } h1, h2, h3, h1 a, h2 a, h3 a { line-height:120%!important } h1 { font-size:30px!important; text-align:left } h2 { font-size:26px!important; text-align:left } h3 { font-size:20px!important; text-align:left } h1 a { text-align:left } .es-header-body h1 a, .es-content-body h1 a, .es-footer-body h1 a { font-size:30px!important } h2 a { text-align:left } .es-header-body h2 a, .es-content-body h2 a, .es-footer-body h2 a { font-size:26px!important } h3 a { text-align:left } .es-header-body h3 a, .es-content-body h3 a, .es-footer-body h3 a { font-size:20px!important } .es-menu td a { font-size:14px!important } .es-header-body p, .es-header-body ul li, .es-header-body ol li, .es-header-body a { font-size:14px!important } .es-content-body p, .es-content-body ul li, .es-content-body ol li, .es-content-body a { font-size:16px!important } .es-footer-body p, .es-footer-body ul li, .es-footer-body ol li, .es-footer-body a { font-size:14px!important } .es-infoblock p, .es-infoblock ul li, .es-infoblock ol li, .es-infoblock a { font-size:12px!important } *[class="gmail-fix"] { display:none!important } .es-m-txt-c, .es-m-txt-c h1, .es-m-txt-c h2, .es-m-txt-c h3 { text-align:center!important } .es-m-txt-r, .es-m-txt-r h1, .es-m-txt-r h2, .es-m-txt-r h3 { text-align:right!important } .es-m-txt-l, .es-m-txt-l h1, .es-m-txt-l h2, .es-m-txt-l h3 { text-align:left!important } .es-m-txt-r img, .es-m-txt-c img, .es-m-txt-l img { display:inline!important } .es-button-border { display:block!important } a.es-button, button.es-button { font-size:20px!important; display:block!important; border-left-width:0px!important; border-right-width:0px!important } .es-btn-fw { border-width:10px 0px!important; text-align:center!important } .es-adaptive table, .es-btn-fw, .es-btn-fw-brdr, .es-left, .es-right { width:100%!important } .es-content table, .es-header table, .es-footer table, .es-content, .es-footer, .es-header { width:100%!important; max-width:600px!important } .es-adapt-td { display:block!important; width:100%!important } .adapt-img { width:100%!important; height:auto!important } .es-m-p0 { padding:0px!important } .es-m-p0r { padding-right:0px!important } .es-m-p0l { padding-left:0px!important } .es-m-p0t { padding-top:0px!important } .es-m-p0b { padding-bottom:0!important } .es-m-p20b { padding-bottom:20px!important } .es-mobile-hidden, .es-hidden { display:none!important } tr.es-desk-hidden, td.es-desk-hidden, table.es-desk-hidden { width:auto!important; overflow:visible!important; float:none!important; max-height:inherit!important; line-height:inherit!important } tr.es-desk-hidden { display:table-row!important } table.es-desk-hidden { display:table!important } td.es-desk-menu-hidden { display:table-cell!important } .es-menu td { width:1%!important } table.es-table-not-adapt, .esd-block-html table { width:auto!important } table.es-social { display:inline-block!important } table.es-social td { display:inline-block!important } }
                </style>
                </head>
                <body style="width:100%;font-family:arial, "helvetica neue", helvetica, sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;padding:0;Margin:0">
                <div class="es-wrapper-color" style="background-color:#EFEFEF"><!--[if gte mso 9]>
                <v:background xmlns:v="urn:schemas-microsoft-com:vml" fill="t">
                <v:fill type="tile" color="#efefef"></v:fill>
                </v:background>
                <![endif]-->
                <table class="es-wrapper" width="100%" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;padding:0;Margin:0;width:100%;height:100%;background-repeat:repeat;background-position:center top">
                <tr style="border-collapse:collapse">
                <td valign="top" style="padding:0;Margin:0">
                <table class="es-header" cellspacing="0" cellpadding="0" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;background-color:transparent;background-repeat:repeat;background-position:center top">
                <tr style="border-collapse:collapse">
                <td align="center" style="padding:0;Margin:0">
                <table class="es-header-body" cellspacing="0" cellpadding="0" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#E6EBEF;width:600px">
                <tr style="border-collapse:collapse">
                <td style="padding:20px;Margin:0;background-color:#1e1e2d" bgcolor="#1e1e2d" align="left">
                <table width="100%" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                <tr style="border-collapse:collapse">
                <td valign="top" align="center" style="padding:0;Margin:0;width:560px">
                <table width="100%" cellspacing="0" cellpadding="0" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                <tr style="border-collapse:collapse">
                <td style="padding:0;Margin:0;font-size:0px" align="center"><a href="https://viewstripo.email/" target="_blank" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;text-decoration:none;color:#677D9E;font-size:14px"><img src="https://dokyhr.it/media/logos/DOKYHRLogoBianco.png" alt="Financial logo" title="Financial logo" style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic" width="134" height="81"></a></td>
                </tr>
                </table></td>
                </tr>
                </table></td>
                </tr>
                </table></td>
                </tr>
                </table>
                <table class="es-content" cellspacing="0" cellpadding="0" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%">
                <tr style="border-collapse:collapse">
                <td align="center" style="padding:0;Margin:0">
                <table class="es-content-body" cellspacing="0" cellpadding="0" bgcolor="#ffffff" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#FFFFFF;width:600px">
                <tr style="border-collapse:collapse">
                <td align="left" style="Margin:0;padding-left:30px;padding-right:30px;padding-top:40px;padding-bottom:40px">
                <table width="100%" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                <tr style="border-collapse:collapse">
                <td valign="top" align="center" style="padding:0;Margin:0;width:540px">
                <table width="100%" cellspacing="0" cellpadding="0" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                <tr style="border-collapse:collapse">
                <td align="left" style="padding:0;Margin:0"><h3 style="Margin:0;line-height:24px;mso-line-height-rule:exactly;font-family:arial, "helvetica neue", helvetica, sans-serif;font-size:20px;font-style:normal;font-weight:normal;color:#666666">Ciao ' . $dipendente . ',<br></h3></td>
                </tr>
                <tr style="border-collapse:collapse">
                <td align="left" style="padding:0;Margin:0;padding-top:15px"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, "helvetica neue", helvetica, sans-serif;line-height:21px;color:#999999;font-size:14px">La tua richiesta di ' . ($type == "ROL" ? $type : strtolower($type)) . ' ha subito un aggiornamento<br></p></td>
                </tr>
                <tr style="border-collapse:collapse">
                <td align="left" style="padding:0;Margin:0;padding-top:15px"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, "helvetica neue", helvetica, sans-serif;line-height:21px;color:#999999;font-size:14px">Il tuo supervisor ' . $auth['firstname'] . " " . $auth['lastname'] . ' ha appena ' . ($value == 1 ? "accettato" : "rifiutato") . ' la tua richiesta<br></p></td>
                </tr>
                <tr style="border-collapse:collapse">
                <td align="left" style="padding:0;Margin:0;padding-top:15px"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, "helvetica neue", helvetica, sans-serif;line-height:21px;color:#999999;font-size:14px">Puoi accedere alla tua area personale per verificarne i dettagli<br></p></td>
                </tr>
                <tr style="border-collapse:collapse">
                <td align="left" style="padding:0;Margin:0;padding-top:25px"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, "helvetica neue", helvetica, sans-serif;line-height:21px;color:#999999;font-size:14px">Saluti,<br>il team di DokyHR<br></p></td>
                </tr>
                <tr style="border-collapse:collapse">
                <td align="left" style="padding:0;Margin:0;padding-top:15px"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, "helvetica neue", helvetica, sans-serif;line-height:21px;color:#333333;font-size:14px"><br></p></td>
                </tr>
                </table></td>
                </tr>
                </table></td>
                </tr>
                </table></td>
                </tr>
                </table></td>
                </tr>
                </table>
                </div>
                </body>
                </html>';

                $this->email->SendMail($dipendenteMail, $oggetto, $messaggio);

                return ['code' => 200, 'message' => 'Success'];
            } catch (PDOException $e) {
                return ['code' => 500, 'message' => "Error code: " . $e->getCode() . "\n" . $e->getMessage()];
            }
        } else return ['code' => 403, 'message' => 'Forbidden'];
    }
}
