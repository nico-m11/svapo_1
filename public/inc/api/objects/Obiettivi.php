<?php

require_once '../config/Config.php';
require_once '../config/Costanti.php';

class Obiettivi
{

    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /********************************************************************************************************************************/

    //Inizio funzioni per popolare tabella

    public function GetAllTicketEmployee($input)
    {
        $idUtente = $input['idUser'];
        $authToken = $input['authToken'];

        $sql = "SELECT COUNT(*) AS auth FROM user WHERE authToken LIKE '$authToken'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $auth = $stmt->fetch(PDO::FETCH_ASSOC);
        $auth = $auth['auth'];

        if ($auth > 0) {

            $sql = "SELECT * FROM customercare WHERE exId = $idUtente";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $dati = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sql = "SELECT firstname, lastname FROM user_data WHERE id_user = $idUtente";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            $nomeOperatore = isset($userData['firstname']) && isset($userData['lastname']) ? ucwords($userData['firstname']) . " " . ucwords($userData['lastname']) : "";

            $result = [];
            foreach ($dati as $dato) {
                $chiamata = [
                    'id' => $dato['id'],
                    'nomeOperatore'   => $nomeOperatore,
                    'tipoChiamata'    => isset($dato['tipoChiamata']) ? $dato['tipoChiamata'] : "",
                    'dataRicevuta'    => date("d-m-Y", strtotime($dato['dataRicevuta'])),
                    'oraRicevuta'     => date("H:m", strtotime($dato['oraRicevuta'])),
                    'durata'          => $dato['durata'],
                    'nrDialed'        => $dato['nrDialed'],
                    'ringTime'        => $dato['ringTime'],
                    'esitoChiamata'   => isset($dato['esitoChiamata']) ? $dato['esitoChiamata'] : "",
                    'numeroChiamante' => $dato['numeroChiamante'],
                    'codiceSoggetto'  => isset($dato['codiceSoggetto']) ? $dato['codiceSoggetto'] : "",
                    'nomeSoggetto'    => $dato['nomeSoggetto'],
                    'note'            => isset($dato['note']) ? $dato['note'] : ""
                ];

                array_push($result, $chiamata);
            }

            return $result;
        }
    }

    public function GetAllTicketSupervisor($input)
    {
        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUser'];
        }

        $idDipendente = $input['idDipendente'];
        $idRole = $input['idRuolo'];

        if ((int)$idRole == _ADMIN_ || (int)$idRole == _HR_) {
            if ($idDipendente != "media") {
                $sql = "SELECT * FROM customercare LEFT JOIN user_data ON customercare.exId = user_data.id_user WHERE customercare.exId = $idDipendente";
            } else {
                $sql = "SELECT * FROM customercare LEFT JOIN user_data ON customercare.exId = user_data.id_user WHERE customercare.idOrganization = $idOrganization";
            }
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [];
            foreach ($tickets as $ticket) {
                $arr = [
                    "id" => isset($ticket['id']) ? $ticket['id'] : 0,
                    "nomeOperatore" => isset($ticket['firstname']) && isset($ticket['lastname']) ? $ticket['firstname'] . " " . $ticket['lastname'] : "",
                    "tipoChiamata" => isset($ticket['tipoChiamata']) ?  $ticket['tipoChiamata'] : "",
                    "dataRicevuta" => isset($ticket['dataRicevuta']) ?  $ticket['dataRicevuta'] : "",
                    "oraRicevuta" => isset($ticket['oraRicevuta']) ?  $ticket['oraRicevuta'] : "",
                    "durata" => isset($ticket['durata']) ?  $ticket['durata'] : "",
                    "nrDialed" => isset($ticket['nrDialed']) ?  $ticket['nrDialed'] : "",
                    "ringTime" => isset($ticket['ringtìTime']) ?  $ticket['ringtìTime'] : "",
                    "numeroChiamante" => isset($ticket['numeroChiamante']) ?  $ticket['numeroChiamante'] : "",
                    "nomeSoggetto" => isset($ticket['nomeSoggetto']) ?  $ticket['nomeSoggetto'] : "",
                ];
                array_push($result, $arr);
            }
            return $result;
        } else return "Non autorizzato";
    }

    public function GetAllWorksSupervisor($input)
    {


        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUser'];
        }

        $idDipendente = $input['idDipendente'];
        $idRole = $input['idRuolo'];

        if ((int)$idRole == _ADMIN_ || (int)$idRole == _HR_) {
            if ($idDipendente != "media") {
                $sql = "SELECT * FROM officina LEFT JOIN user_data ON officina.exId = user_data.id_user WHERE officina.exId = $idDipendente";
            } else {
                $sql = "SELECT * FROM officina LEFT JOIN user_data ON officina.exId = user_data.id_user WHERE officina.idOrganization = $idOrganization";
            }
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [];
            foreach ($tickets as $ticket) {
                $arr = [
                    "id" => isset($ticket['id']) ? $ticket['id'] : 0,
                    "nomeMeccanico" => isset($ticket['firstname']) && isset($ticket['lastname']) ? $ticket['firstname'] . " " . $ticket['lastname'] : "",
                    "orTot" => isset($ticket['oraInizio']) && isset($ticket['oraFine']) ?  abs((strtotime($ticket['oraFine']) - strtotime($ticket['oraInizio'])) / (60)) : "",
                    "totAttivita" => isset($ticket['oraInizio']) && isset($ticket['oraFine']) ?  date("H:i", strtotime($ticket['oraInizio'])) . "-" . date("H:i", strtotime($ticket['oraFine']))  : "",
                    "numCommessa" => isset($ticket['numCommessa']) ?  $ticket['numCommessa'] : "",
                    "addebito" => isset($ticket['addebito']) ?  $ticket['addebito'] : "",
                    "desLavoro" => isset($ticket['desLavoro']) ?  $ticket['desLavoro'] : "",
                    "unLavoro" => isset($ticket['unLavoro']) ?  $ticket['unLavoro'] : "",
                    "desMarca" => isset($ticket['desMarca']) ?  $ticket['desMarca'] : "",
                    "desSede" => isset($ticket['desSede']) ?  $ticket['desSede'] : "",
                ];
                array_push($result, $arr);
            }
            return $result;
        } else return "Non autorizzato";
    }

    public function GetAllWorksEmployee($input)
    {

        $exId = $input['idUser'];
        $authToken = $input['authToken'];

        $sql = "SELECT COUNT(*) AS auth FROM user WHERE authToken LIKE '$authToken'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $auth = $stmt->fetch(PDO::FETCH_ASSOC);
        $auth = $auth['auth'];

        if ($auth > 0) {

            $sql = "SELECT * FROM officina WHERE exId = $exId";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $dati = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sql = "SELECT firstname, lastname FROM user_data WHERE id_user = $exId";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            $nomeOperatore = isset($userData['firstname']) && isset($userData['lastname']) ? ucwords($userData['firstname']) . " " . ucwords($userData['lastname']) : "";

            $result = [];
            foreach ($dati as $dato) {
                $time1 = isset($dato['oraInizio']) ?  new DateTime($dato['oraInizio']) : "";
                $time2 = isset($dato['oraFine']) ? new DateTime($dato['oraFine']) : "";
                $interval = isset($dato['oraInizio']) && isset($dato['oraFine']) ?  $time1->diff($time2) : "";
                $chiamata = [
                    'id' => $dato['id'],
                    'nomeMeccanico' => $nomeOperatore,
                    'totAttivita'    => isset($dato['oraInizio']) && isset($dato['oraFine']) ? date("H:i", strtotime($dato['oraInizio'])) . " - " . date("H:i", strtotime($dato['oraFine'])) : "",
                    'orTot'    => isset($interval) ? $interval->format('%i minuti') : "",
                    'numCommessa'     => isset($dato['numCommessa']) ? $dato['numCommessa'] : "",
                    'addebito'        => isset($dato['addebito']) ? $dato['addebito'] : "",
                    'desLavoro'   => isset($dato['desLavoro']) ? $dato['desLavoro'] : "",
                    'unLavoro' => isset($dato['unLavoro']) ? $dato['unLavoro'] : "",
                    'desMarca'  => isset($dato['desMarca']) ? $dato['desMarca'] : "",
                    'desSede'    => isset($dato['desSede']) ? $dato['desSede'] : ""
                ];

                array_push($result, $chiamata);
            }

            return $result;
        }
    }

    public function GetAllContratti($input)
    {
        $idVenditore = $input['idUser'];

        $sql = "SELECT * FROM venditori WHERE idVenditore = $idVenditore";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $vendite = $stmt->fetchAll(PDO::FETCH_ASSOC);


        function handleStato($stato)
        {
            $status = "";
            if ($stato == "offerta aperta" || $stato == "") {
                $status = "PREVENTIVO";
            } else if ($stato == "vendita iniziata" || $stato == "fattura emessa" || $stato == "vendita approvata") {
                $status = "CONTRATTO";
            } else if ($stato == "offerta annullata" || $stato = "annullata") {
                $status = "MANCATA VENDITA";
            }

            return $status;
        }

        function handleCount($vendite)
        {
            $count = 0;
            foreach ($vendite as $vendita) {
                if ($vendita['stato'] == "vendita iniziata" || $vendita['stato'] == "fattura emessa" || $vendita['stato'] == "vendita approvata") {
                    $count += 1;
                }
            }

            return $count;
        }

        $result = [];
        foreach ($vendite as $vendita) {
            $arr = [
                "idTrattativa" => $vendita['idTrattativa'],
                "codiceOfferta" => $vendita['codiceOfferta'],
                "idVenditore" => $vendita['idVenditore'],
                "venditore" => ucwords($vendita['venditore']),
                "stato" => handleStato(strtolower($vendita['stato']))
            ];
            array_push($result, $arr);
        }
        return [
            "tabella" => $result,
            "riuscite" => handleCount($vendite) . "/" . count($vendite)
        ];
    }

    //Fine funzioni per popolare tabella

    /**************************************************************************************************************************** */

    /* Inizio funzioni per progress bar e torta */

    public function CalculateTargetSupervisor($input)
    {
        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUser'];
        }

        $idDipendente = $input['idDipendente'];
        $idRole = $input['idRuolo'];

        if ((int)$idRole == _ADMIN_ || (int)$idRole == _HR_) {
            if ($idDipendente != "media") {

                $begin = date("Y-m-d", strtotime('-3 month'));
                $end = date("Y-m-d", strtotime('-1 days'));
                $sql = "SELECT * FROM customercare WHERE exId = $idDipendente AND (dataRicevuta BETWEEN '$begin' AND '$end')";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $dati = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $sum = 0;
                foreach ($dati as $durataChiamata) {
                    $chiamataMinuti = abs(strtotime("00:00:00") - strtotime($durataChiamata['durata']));
                    $sum += $chiamataMinuti;
                }

                $mediaLavoro = ($sum / count($dati)) / 60;

                //Raccolgo i dati che occorrono per calcolare obiettivi e ore lavorative
                $sqlFeatures = "SELECT department.* FROM department LEFT JOIN modules ON department.idModule = modules.id WHERE modules.id = " . _CUSTOMERCARE_;
                $stmtFeature = $this->conn->prepare($sqlFeatures);
                $stmtFeature->execute();
                $features = $stmtFeature->fetch(PDO::FETCH_ASSOC);

                $timestampCheckin = strtotime($features['oraCheckin']);
                $timestampCheckout = strtotime($features['oraCheckout']);

                $minutiGiornalieri = (abs($timestampCheckout - $timestampCheckin) / 60) - $features['minutiPausa'];
                $minutiK = abs(strtotime("00:00:00") - strtotime($features['kMinuti'])) / 60;
                $tempoMedioAttivita = round($minutiK + $mediaLavoro);
                $targetGiornaliero = round(($minutiGiornalieri * ($features['deltaObiettivi'] / 100)) / ($tempoMedioAttivita));

                $sqlCountGiornaliere = "SELECT COUNT(*) AS yesterdayCalls FROM customercare WHERE exId = $idDipendente AND dataRicevuta = '$end'";
                $stmtCountGiornaliere = $this->conn->prepare($sqlCountGiornaliere);
                $stmtCountGiornaliere->execute();

                $today = date("Y-m-d");
                if (date('w', strtotime($today)) === '1') {
                    $monday = $today;
                } else {
                    $monday = date("Y-m-d", strtotime("previous monday"));
                }
                $sunday = date("Y-m-d", strtotime("next sunday"));

                $sqlCountSettimanale = "SELECT COUNT(*) AS weeklyCalls FROM customercare WHERE (dataRicevuta BETWEEN '$monday' AND '$sunday') AND exId = $idDipendente";
                $stmtCountSettimanale = $this->conn->prepare($sqlCountSettimanale);
                $stmtCountSettimanale->execute();

                $sqlMinutiRaggiunti = "SELECT * FROM customercare WHERE exId = $idDipendente AND dataRicevuta = '$end'";
                $stmtMinutiRaggiunti = $this->conn->prepare($sqlMinutiRaggiunti);
                $stmtMinutiRaggiunti->execute();
                $yesterdayCalls = $stmtMinutiRaggiunti->fetchAll(PDO::FETCH_ASSOC);

                $minutiRaggiunti = 0;
                foreach ($yesterdayCalls as $yesterdayCall) {
                    $durata = abs(strtotime("00:00:00") - strtotime($yesterdayCall['durata'])) / 60;
                    $durata = $durata + $minutiK;
                    $minutiRaggiunti += $durata;
                }

                $sqlCountMinutiSettimanale = "SELECT * FROM customercare WHERE (dataRicevuta BETWEEN '$monday' AND '$sunday') AND exId = $idDipendente";
                $stmtCountMinutiSettimanle = $this->conn->prepare($sqlCountMinutiSettimanale);
                $stmtCountMinutiSettimanle->execute();
                $weeklyCallsMinutes = $stmtCountMinutiSettimanle->fetchAll(PDO::FETCH_ASSOC);

                $minutiSettimanaliRaggiunti = 0;
                foreach ($weeklyCallsMinutes as $chiamataSettimana) {
                    $durata = abs(strtotime("00:00:00") - strtotime($chiamataSettimana['durata'])) / 60;
                    $durata = $durata + $minutiK;
                    $minutiSettimanaliRaggiunti += $durata;
                }

                $countToday = $stmtCountGiornaliere->fetch(PDO::FETCH_ASSOC);
                $countToday = $countToday['yesterdayCalls'];
                $countWeekly = $stmtCountSettimanale->fetch(PDO::FETCH_ASSOC);
                $countWeekly = $countWeekly['weeklyCalls'];

                $result = [
                    'minutiProduttivita'            => $minutiGiornalieri,
                    'minutiSettimanaliProduttivita' => $minutiSettimanaliRaggiunti,
                    'targetGiornaliero'             => $targetGiornaliero,
                    'targetSettimanale'             => $targetGiornaliero * 5,
                    'todayCalls'                    => (int)$countToday,
                    'weeklyCalls'                   => (int)$countWeekly,
                    'minutiRaggiunti'               => $minutiRaggiunti
                ];

                return $result;
            } else {
                $begin = date("Y-m-d", strtotime('-3 month'));
                $end = date("Y-m-d", strtotime('-1 days'));
                $sql = "SELECT * FROM customercare WHERE idOrganization = $idOrganization AND (dataRicevuta BETWEEN '$begin' AND '$end')";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $dati = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $sum = 0;
                foreach ($dati as $durataChiamata) {
                    $chiamataMinuti = abs(strtotime("00:00:00") - strtotime($durataChiamata['durata']));
                    $sum += $chiamataMinuti;
                }

                $mediaLavoro = ($sum / count($dati)) / 60;

                //Raccolgo i dati che occorrono per calcolare obiettivi e ore lavorative
                $sqlFeatures = "SELECT * FROM department WHERE id = 1";
                $stmtFeature = $this->conn->prepare($sqlFeatures);
                $stmtFeature->execute();
                $features = $stmtFeature->fetch(PDO::FETCH_ASSOC);

                $timestampCheckin = strtotime($features['oraCheckin']);
                $timestampCheckout = strtotime($features['oraCheckout']);

                $minutiGiornalieri = (abs($timestampCheckout - $timestampCheckin) / 60) - $features['minutiPausa'];
                $minutiK = abs(strtotime("00:00:00") - strtotime($features['kMinuti'])) / 60;
                $tempoMedioAttivita = round($minutiK + $mediaLavoro);
                $targetGiornaliero = round(($minutiGiornalieri * ($features['deltaObiettivi'] / 100)) / ($tempoMedioAttivita));

                $sqlCountGiornaliere = "SELECT COUNT(*) AS yesterdayCalls FROM customercare WHERE idOrganization = $idOrganization AND dataRicevuta = '$end'";
                $stmtCountGiornaliere = $this->conn->prepare($sqlCountGiornaliere);
                $stmtCountGiornaliere->execute();

                $today = date("Y-m-d");
                if (date('w', strtotime($today)) === '1') {
                    $monday = $today;
                } else {
                    $monday = date("Y-m-d", strtotime("previous monday"));
                }
                $sunday = date("Y-m-d", strtotime("next sunday"));

                $sqlCountSettimanale = "SELECT COUNT(*) AS weeklyCalls FROM customercare WHERE (dataRicevuta BETWEEN '$monday' AND '$sunday') AND idOrganization = $idOrganization";
                $stmtCountSettimanale = $this->conn->prepare($sqlCountSettimanale);
                $stmtCountSettimanale->execute();

                $sqlMinutiRaggiunti = "SELECT * FROM customercare WHERE idOrganization = $idOrganization AND dataRicevuta = '$end'";
                $stmtMinutiRaggiunti = $this->conn->prepare($sqlMinutiRaggiunti);
                $stmtMinutiRaggiunti->execute();
                $yesterdayCalls = $stmtMinutiRaggiunti->fetchAll(PDO::FETCH_ASSOC);

                $minutiRaggiunti = 0;
                foreach ($yesterdayCalls as $yesterdayCall) {
                    $durata = abs(strtotime("00:00:00") - strtotime($yesterdayCall['durata'])) / 60;
                    $durata = $durata + $minutiK;
                    $minutiRaggiunti += $durata;
                }

                $sqlCountMinutiSettimanale = "SELECT * FROM customercare WHERE (dataRicevuta BETWEEN '$monday' AND '$sunday') AND idOrganization=$idOrganization";
                $stmtCountMinutiSettimanle = $this->conn->prepare($sqlCountMinutiSettimanale);
                $stmtCountMinutiSettimanle->execute();
                $weeklyCallsMinutes = $stmtCountMinutiSettimanle->fetchAll(PDO::FETCH_ASSOC);

                $minutiSettimanaliRaggiunti = 0;
                foreach ($weeklyCallsMinutes as $chiamataSettimana) {
                    $durata = abs(strtotime("00:00:00") - strtotime($chiamataSettimana['durata'])) / 60;
                    $durata = $durata + $minutiK;
                    $minutiSettimanaliRaggiunti += $durata;
                }

                $countToday = $stmtCountGiornaliere->fetch(PDO::FETCH_ASSOC);
                $countToday = $countToday['yesterdayCalls'];
                $countWeekly = $stmtCountSettimanale->fetch(PDO::FETCH_ASSOC);
                $countWeekly = $countWeekly['weeklyCalls'];

                $result = [
                    'minutiProduttivita'            => $minutiGiornalieri,
                    'minutiSettimanaliProduttivita' => $minutiSettimanaliRaggiunti,
                    'targetGiornaliero'             => $targetGiornaliero,
                    'targetSettimanale'             => $targetGiornaliero * 5,
                    'todayCalls'                    => (int)$countToday,
                    'weeklyCalls'                   => (int)$countWeekly,
                    'minutiRaggiunti'               => $minutiRaggiunti
                ];

                return $result;
            }
        }
    }

    public function CalculateTargetEmployee($input)
    {
        $idDipendente = $input['idUser'];
        $authToken = $input['authToken'];

        $sql = "SELECT COUNT(*) AS auth FROM user WHERE authToken LIKE '$authToken'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $auth = $stmt->fetch(PDO::FETCH_ASSOC);
        $auth = $auth['auth'];

        if ($auth > 0) {

            $begin = date("Y-m-d", strtotime('-3 month'));
            $end = date("Y-m-d", strtotime('-1 days'));
            $sql = "SELECT * FROM customercare WHERE exId = $idDipendente AND (dataRicevuta BETWEEN '$begin' AND '$end')";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $dati = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sum = 0;
            foreach ($dati as $durataChiamata) {
                $chiamataMinuti = abs(strtotime("00:00:00") - strtotime($durataChiamata['durata']));
                $sum += $chiamataMinuti;
            }

            $mediaLavoro = ($sum / count($dati)) / 60;

            //Raccolgo i dati che occorrono per calcolare obiettivi e ore lavorative
            $sqlFeatures = "SELECT * FROM department WHERE id = 1";
            $stmtFeature = $this->conn->prepare($sqlFeatures);
            $stmtFeature->execute();
            $features = $stmtFeature->fetch(PDO::FETCH_ASSOC);

            $timestampCheckin = strtotime($features['oraCheckin']);
            $timestampCheckout = strtotime($features['oraCheckout']);

            $minutiGiornalieri = (abs($timestampCheckout - $timestampCheckin) / 60) - $features['minutiPausa'];
            $minutiK = abs(strtotime("00:00:00") - strtotime($features['kMinuti'])) / 60;
            $tempoMedioAttivita = round($minutiK + $mediaLavoro);
            $targetGiornaliero = round(($minutiGiornalieri * ($features['deltaObiettivi'] / 100)) / ($tempoMedioAttivita));

            $sqlCountGiornaliere = "SELECT COUNT(*) AS yesterdayCalls FROM customercare WHERE exId = $idDipendente AND dataRicevuta = '$end'";
            $stmtCountGiornaliere = $this->conn->prepare($sqlCountGiornaliere);
            $stmtCountGiornaliere->execute();

            $today = date("Y-m-d");
            if (date('w', strtotime($today)) === '1') {
                $monday = $today;
            } else {
                $monday = date("Y-m-d", strtotime("previous monday"));
            }
            $sunday = date("Y-m-d", strtotime("next sunday"));

            $sqlCountSettimanale = "SELECT COUNT(*) AS weeklyCalls FROM customercare WHERE (dataRicevuta BETWEEN '$monday' AND '$sunday') AND exId = $idDipendente";
            $stmtCountSettimanale = $this->conn->prepare($sqlCountSettimanale);
            $stmtCountSettimanale->execute();

            $sqlMinutiRaggiunti = "SELECT * FROM customercare WHERE exId = $idDipendente AND dataRicevuta = '$end'";
            $stmtMinutiRaggiunti = $this->conn->prepare($sqlMinutiRaggiunti);
            $stmtMinutiRaggiunti->execute();
            $yesterdayCalls = $stmtMinutiRaggiunti->fetchAll(PDO::FETCH_ASSOC);

            $minutiRaggiunti = 0;
            foreach ($yesterdayCalls as $yesterdayCall) {
                $durata = abs(strtotime("00:00:00") - strtotime($yesterdayCall['durata'])) / 60;
                $durata = $durata + $minutiK;
                $minutiRaggiunti += $durata;
            }

            $sqlCountMinutiSettimanale = "SELECT * FROM customercare WHERE (dataRicevuta BETWEEN '$monday' AND '$sunday') AND exId = $idDipendente";
            $stmtCountMinutiSettimanle = $this->conn->prepare($sqlCountMinutiSettimanale);
            $stmtCountMinutiSettimanle->execute();
            $weeklyCallsMinutes = $stmtCountMinutiSettimanle->fetchAll(PDO::FETCH_ASSOC);

            $minutiSettimanaliRaggiunti = 0;
            foreach ($weeklyCallsMinutes as $chiamataSettimana) {
                $durata = abs(strtotime("00:00:00") - strtotime($chiamataSettimana['durata'])) / 60;
                $durata = $durata + $minutiK;
                $minutiSettimanaliRaggiunti += $durata;
            }

            $countToday = $stmtCountGiornaliere->fetch(PDO::FETCH_ASSOC);
            $countToday = $countToday['yesterdayCalls'];
            $countWeekly = $stmtCountSettimanale->fetch(PDO::FETCH_ASSOC);
            $countWeekly = $countWeekly['weeklyCalls'];

            $result = [
                'minutiProduttivita'            => $minutiGiornalieri,
                'minutiSettimanaliProduttivita' => $minutiSettimanaliRaggiunti,
                'targetGiornaliero'             => $targetGiornaliero,
                'targetSettimanale'             => $targetGiornaliero * 5,
                'todayCalls'                    => (int)$countToday,
                'weeklyCalls'                   => (int)$countWeekly,
                'minutiRaggiunti'               => $minutiRaggiunti
            ];

            return $result;
        }
    }

    public function CalculateOfficinaTargetSupervisor($input)
    {
        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUser'];
        }

        $idDipendente = $input['idDipendente'];
        $idRole = $input['idRuolo'];

        if ((int)$idRole == _ADMIN_ || (int)$idRole == _HR_) {
            if ($idDipendente != "media") {
                $begin = date("Y-m-d", strtotime('-3 month'));
                $end = date("Y-m-d", strtotime('-1 days'));

                $sql = "SELECT * FROM officina WHERE exId = $idDipendente AND (dataLavoro BETWEEN '$begin' AND '$end')";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $dati = $stmt->fetchAll(PDO::FETCH_ASSOC);


                $sum = 0;
                foreach ($dati as $durataLavoro) {
                    $minutiLavoro = abs(strtotime($durataLavoro['oraFine']) - strtotime($durataLavoro['oraInizio']));
                    $sum += $minutiLavoro;
                }

                $mediaLavoro = ($sum / count($dati)) / 60;

                //Raccolgo i dati che occorrono per calcolare obiettivi e ore lavorative
                $sqlFeatures = "SELECT * FROM department WHERE id = 2";
                $stmtFeature = $this->conn->prepare($sqlFeatures);
                $stmtFeature->execute();
                $features = $stmtFeature->fetch(PDO::FETCH_ASSOC);

                $timestampCheckin = strtotime($features['oraCheckin']);
                $timestampCheckout = strtotime($features['oraCheckout']);

                $minutiGiornalieri = (abs($timestampCheckout - $timestampCheckin) / 60) - $features['minutiPausa'];
                $minutiK = abs(strtotime("00:00:00") - strtotime($features['kMinuti'])) / 60;
                $tempoMedioAttivita = round($minutiK + $mediaLavoro);
                $targetGiornaliero = round(($minutiGiornalieri * ($features['deltaObiettivi'] / 100)) / ($tempoMedioAttivita));


                $sqlCountGiornaliere = "SELECT COUNT(*) AS yesterdayWorks FROM officina WHERE exId = $idDipendente AND dataLavoro = '$end'";
                $stmtCountGiornaliere = $this->conn->prepare($sqlCountGiornaliere);
                $stmtCountGiornaliere->execute();

                $today = date("Y-m-d");
                if (date('w', strtotime($today)) === '1') {
                    $monday = $today;
                } else {
                    $monday = date("Y-m-d", strtotime("-2 weeks monday"));
                }
                $sunday = date("Y-m-d", strtotime("previous sunday"));

                $sqlCountSettimanale = "SELECT COUNT(*) AS weeklyWorks FROM officina WHERE (dataLavoro BETWEEN '$monday' AND '$sunday') AND exId = $idDipendente";
                $stmtCountSettimanale = $this->conn->prepare($sqlCountSettimanale);
                $stmtCountSettimanale->execute();

                $sqlMinutiRaggiunti = "SELECT * FROM officina WHERE exId = $idDipendente AND dataLavoro = '$end'";
                $stmtMinutiRaggiunti = $this->conn->prepare($sqlMinutiRaggiunti);
                $stmtMinutiRaggiunti->execute();
                $yesterdayWorks = $stmtMinutiRaggiunti->fetchAll(PDO::FETCH_ASSOC);

                $minutiRaggiunti = 0;
                foreach ($yesterdayWorks as $yesterdayWork) {
                    $durata = abs(strtotime($yesterdayWork['oraFine']) - strtotime($yesterdayWork['oraInizio'])) / 60;
                    $durata = $durata + $minutiK;
                    $minutiRaggiunti += $durata;
                }

                $sqlCountMinutiSettimanale = "SELECT * FROM officina WHERE (dataLavoro BETWEEN '$monday' AND '$sunday') AND exId = $idDipendente";
                $stmtCountMinutiSettimanle = $this->conn->prepare($sqlCountMinutiSettimanale);
                $stmtCountMinutiSettimanle->execute();
                $weeklyWorksMinutes = $stmtCountMinutiSettimanle->fetchAll(PDO::FETCH_ASSOC);

                $minutiSettimanaliRaggiunti = 0;
                foreach ($weeklyWorksMinutes as $lavoroSettimana) {
                    $durata = abs(strtotime($lavoroSettimana['oraFine']) - strtotime($lavoroSettimana['oraInizio'])) / 60;
                    $durata = $durata + $minutiK;
                    $minutiSettimanaliRaggiunti += $durata;
                }

                $countToday = $stmtCountGiornaliere->fetch(PDO::FETCH_ASSOC);
                $countToday = $countToday['yesterdayWorks'];
                $countWeekly = $stmtCountSettimanale->fetch(PDO::FETCH_ASSOC);
                $countWeekly = $countWeekly['weeklyWorks'];

                $result = [
                    'minutiProduttivita'            => $minutiGiornalieri,
                    'minutiSettimanaliProduttivita' => $minutiSettimanaliRaggiunti,
                    'targetGiornaliero'             => $targetGiornaliero,
                    'targetSettimanale'             => $targetGiornaliero * 5,
                    'todayCalls'                    => (int)$countToday,
                    'weeklyCalls'                   => (int)$countWeekly,
                    'minutiRaggiunti'               => $minutiRaggiunti
                ];

                return $result;
            } else {

                $begin = date("Y-m-d", strtotime('-3 month'));
                $end = date("Y-m-d", strtotime('-1 days'));

                $sql = "SELECT * FROM officina WHERE idOrganization = $idOrganization AND (dataLavoro BETWEEN '$begin' AND '$end')";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $dati = $stmt->fetchAll(PDO::FETCH_ASSOC);


                $sum = 0;
                foreach ($dati as $durataLavoro) {
                    $minutiLavoro = abs(strtotime($durataLavoro['oraFine']) - strtotime($durataLavoro['oraInizio']));
                    $sum += $minutiLavoro;
                }

                $mediaLavoro = ($sum / count($dati)) / 60;

                //Raccolgo i dati che occorrono per calcolare obiettivi e ore lavorative
                $sqlFeatures = "SELECT * FROM department WHERE id = 2";
                $stmtFeature = $this->conn->prepare($sqlFeatures);
                $stmtFeature->execute();
                $features = $stmtFeature->fetch(PDO::FETCH_ASSOC);

                $timestampCheckin = strtotime($features['oraCheckin']);
                $timestampCheckout = strtotime($features['oraCheckout']);

                $minutiGiornalieri = (abs($timestampCheckout - $timestampCheckin) / 60) - $features['minutiPausa'];
                $minutiK = abs(strtotime("00:00:00") - strtotime($features['kMinuti'])) / 60;
                $tempoMedioAttivita = round($minutiK + $mediaLavoro);
                $targetGiornaliero = round(($minutiGiornalieri * ($features['deltaObiettivi'] / 100)) / ($tempoMedioAttivita));


                $sqlCountGiornaliere = "SELECT COUNT(*) AS yesterdayWorks FROM officina WHERE idOrganization = $idOrganization AND dataLavoro = '$end'";
                $stmtCountGiornaliere = $this->conn->prepare($sqlCountGiornaliere);
                $stmtCountGiornaliere->execute();

                $today = date("Y-m-d");
                if (date('w', strtotime($today)) === '1') {
                    $monday = $today;
                } else {
                    $monday = date("Y-m-d", strtotime("-2 weeks monday"));
                }
                $sunday = date("Y-m-d", strtotime("previous sunday"));

                $sqlCountSettimanale = "SELECT COUNT(*) AS weeklyWorks FROM officina WHERE (dataLavoro BETWEEN '$monday' AND '$sunday') AND idOrganization = $idOrganization";
                $stmtCountSettimanale = $this->conn->prepare($sqlCountSettimanale);
                $stmtCountSettimanale->execute();

                $sqlMinutiRaggiunti = "SELECT * FROM officina WHERE idOrganization = $idOrganization AND dataLavoro = '$end'";
                $stmtMinutiRaggiunti = $this->conn->prepare($sqlMinutiRaggiunti);
                $stmtMinutiRaggiunti->execute();
                $yesterdayWorks = $stmtMinutiRaggiunti->fetchAll(PDO::FETCH_ASSOC);

                $minutiRaggiunti = 0;
                foreach ($yesterdayWorks as $yesterdayWork) {
                    $durata = abs(strtotime($yesterdayWork['oraFine']) - strtotime($yesterdayWork['oraInizio'])) / 60;
                    $durata = $durata + $minutiK;
                    $minutiRaggiunti += $durata;
                }

                $sqlCountMinutiSettimanale = "SELECT * FROM officina WHERE (dataLavoro BETWEEN '$monday' AND '$sunday') AND idOrganization = $idOrganization";
                $stmtCountMinutiSettimanle = $this->conn->prepare($sqlCountMinutiSettimanale);
                $stmtCountMinutiSettimanle->execute();
                $weeklyWorksMinutes = $stmtCountMinutiSettimanle->fetchAll(PDO::FETCH_ASSOC);

                $minutiSettimanaliRaggiunti = 0;
                foreach ($weeklyWorksMinutes as $lavoroSettimana) {
                    $durata = abs(strtotime($lavoroSettimana['oraFine']) - strtotime($lavoroSettimana['oraInizio'])) / 60;
                    $durata = $durata + $minutiK;
                    $minutiSettimanaliRaggiunti += $durata;
                }

                $countToday = $stmtCountGiornaliere->fetch(PDO::FETCH_ASSOC);
                $countToday = $countToday['yesterdayWorks'];
                $countWeekly = $stmtCountSettimanale->fetch(PDO::FETCH_ASSOC);
                $countWeekly = $countWeekly['weeklyWorks'];

                $result = [
                    'minutiProduttivita'            => $minutiGiornalieri,
                    'minutiSettimanaliProduttivita' => $minutiSettimanaliRaggiunti,
                    'targetGiornaliero'             => $targetGiornaliero,
                    'targetSettimanale'             => $targetGiornaliero * 5,
                    'todayCalls'                    => (int)$countToday,
                    'weeklyCalls'                   => (int)$countWeekly,
                    'minutiRaggiunti'               => $minutiRaggiunti
                ];

                return $result;
            }
        }
    }

    public function CalculateOfficinaTargetEmployee($input)
    {
        $idUser = $input['idUser'];


        $begin = date("Y-m-d", strtotime('-3 month'));
        $end = date("Y-m-d", strtotime('-1 days'));

        $sql = "SELECT * FROM officina WHERE exId = $idUser AND (dataLavoro BETWEEN '$begin' AND '$end')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $dati = $stmt->fetchAll(PDO::FETCH_ASSOC);


        $sum = 0;
        foreach ($dati as $durataLavoro) {
            $minutiLavoro = abs(strtotime($durataLavoro['oraFine']) - strtotime($durataLavoro['oraInizio']));
            $sum += $minutiLavoro;
        }

        $mediaLavoro = ($sum / count($dati)) / 60;

        //Raccolgo i dati che occorrono per calcolare obiettivi e ore lavorative
        $sqlFeatures = "SELECT * FROM department WHERE id = 2";
        $stmtFeature = $this->conn->prepare($sqlFeatures);
        $stmtFeature->execute();
        $features = $stmtFeature->fetch(PDO::FETCH_ASSOC);

        $timestampCheckin = strtotime($features['oraCheckin']);
        $timestampCheckout = strtotime($features['oraCheckout']);

        $minutiGiornalieri = (abs($timestampCheckout - $timestampCheckin) / 60) - $features['minutiPausa'];
        $minutiK = abs(strtotime("00:00:00") - strtotime($features['kMinuti'])) / 60;
        $tempoMedioAttivita = round($minutiK + $mediaLavoro);
        $targetGiornaliero = round(($minutiGiornalieri * ($features['deltaObiettivi'] / 100)) / ($tempoMedioAttivita));


        $sqlCountGiornaliere = "SELECT COUNT(*) AS yesterdayWorks FROM officina WHERE exId = $idUser AND dataLavoro = '$end'";
        $stmtCountGiornaliere = $this->conn->prepare($sqlCountGiornaliere);
        $stmtCountGiornaliere->execute();

        $today = date("Y-m-d");
        if (date('w', strtotime($today)) === '1') {
            $monday = $today;
        } else {
            $monday = date("Y-m-d", strtotime("-2 weeks monday"));
        }
        $sunday = date("Y-m-d", strtotime("previous sunday"));

        $sqlCountSettimanale = "SELECT COUNT(*) AS weeklyWorks FROM officina WHERE (dataLavoro BETWEEN '$monday' AND '$sunday') AND exId = $idUser";
        $stmtCountSettimanale = $this->conn->prepare($sqlCountSettimanale);
        $stmtCountSettimanale->execute();

        $sqlMinutiRaggiunti = "SELECT * FROM officina WHERE exId = $idUser AND dataLavoro = '$end'";
        $stmtMinutiRaggiunti = $this->conn->prepare($sqlMinutiRaggiunti);
        $stmtMinutiRaggiunti->execute();
        $yesterdayWorks = $stmtMinutiRaggiunti->fetchAll(PDO::FETCH_ASSOC);

        $minutiRaggiunti = 0;
        foreach ($yesterdayWorks as $yesterdayWork) {
            $durata = abs(strtotime($yesterdayWork['oraFine']) - strtotime($yesterdayWork['oraInizio'])) / 60;
            $durata = $durata + $minutiK;
            $minutiRaggiunti += $durata;
        }

        $sqlCountMinutiSettimanale = "SELECT * FROM officina WHERE (dataLavoro BETWEEN '$monday' AND '$sunday') AND exId = $idUser";
        $stmtCountMinutiSettimanle = $this->conn->prepare($sqlCountMinutiSettimanale);
        $stmtCountMinutiSettimanle->execute();
        $weeklyWorksMinutes = $stmtCountMinutiSettimanle->fetchAll(PDO::FETCH_ASSOC);

        $minutiSettimanaliRaggiunti = 0;
        foreach ($weeklyWorksMinutes as $lavoroSettimana) {
            $durata = abs(strtotime($lavoroSettimana['oraFine']) - strtotime($lavoroSettimana['oraInizio'])) / 60;
            $durata = $durata + $minutiK;
            $minutiSettimanaliRaggiunti += $durata;
        }

        $countToday = $stmtCountGiornaliere->fetch(PDO::FETCH_ASSOC);
        $countToday = $countToday['yesterdayWorks'];
        $countWeekly = $stmtCountSettimanale->fetch(PDO::FETCH_ASSOC);
        $countWeekly = $countWeekly['weeklyWorks'];

        $result = [
            'minutiProduttivita'            => $minutiGiornalieri,
            'minutiSettimanaliProduttivita' => $minutiSettimanaliRaggiunti,
            'targetGiornaliero'             => $targetGiornaliero,
            'targetSettimanale'             => $targetGiornaliero * 5,
            'todayCalls'                    => (int)$countToday,
            'weeklyCalls'                   => (int)$countWeekly,
            'minutiRaggiunti'               => $minutiRaggiunti
        ];

        return $result;
    }

    public function CalculateGraficoVenditoriEmployee($input)
    {
        $idUser = $input['idUser'];
        $idOrganization = $input['idOrganization'];
        $startDate = $input['startDate'];
        $endDate = $input['endDate'];

        $sql = "SELECT COUNT(DISTINCT idTrattativa) AS riuscite, dateCreated FROM venditori WHERE idVenditore = $idUser AND 
        (stato LIKE 'VENDITA INIZIATA' OR stato LIKE 'FATTURA EMESSA' OR stato LIKE 'VENDITA APPROVATA')
        AND (dateCreated BETWEEN '$startDate' AND '$endDate') 
        GROUP BY CAST(MONTH(dateCreated) AS VARCHAR(2)) + '-' + CAST(YEAR(dateCreated) AS VARCHAR(4))";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $confirmed = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $riuscite = [];
        foreach ($confirmed as $t) {
            $riuscite[date("M Y", strtotime($t['dateCreated']))] = $t['riuscite'];
        }

        $sql = "SELECT COUNT(DISTINCT idTrattativa) AS fallite, dateCreated FROM venditori WHERE idVenditore = $idUser AND 
        (stato LIKE 'OFFERTA ANNULLATA' OR stato LIKE 'ANNULLATA') AND (dateCreated BETWEEN '$startDate' AND '$endDate') 
        GROUP BY CAST(MONTH(dateCreated) AS VARCHAR(2)) + '-' + CAST(YEAR(dateCreated) AS VARCHAR(4))";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $failed = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $fallite = [];
        foreach ($failed as $t) {
            $fallite[date("M Y", strtotime($t['dateCreated']))] = $t['fallite'];
        }

        $sql = "SELECT COUNT(DISTINCT idTrattativa) AS attesa, dateCreated FROM venditori WHERE idVenditore = $idUser AND 
        stato LIKE 'OFFERTA APERTA' AND (dateCreated BETWEEN '$startDate' AND '$endDate') 
        GROUP BY CAST(MONTH(dateCreated) AS VARCHAR(2)) + '-' + CAST(YEAR(dateCreated) AS VARCHAR(4))";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $sospese = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $attesa = [];
        foreach ($sospese as $t) {
            $attesa[date("M Y", strtotime($t['dateCreated']))] = $t['attesa'];
        }

        $sql = "SELECT venditeMensili FROM obiettivivenditori WHERE idOrganization = $idOrganization";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $obiettivo = $stmt->fetch(PDO::FETCH_ASSOC);
        $obiettivo = $obiettivo['venditeMensili'];

        $result = [];
        $start = strtotime(date('M Y', strtotime($startDate)));
        $end = strtotime(date('M Y', strtotime($endDate)));
        while ($start <= $end) {
            $key = date('M Y', $start);
            $arr = [
                "month" => $key,
                "riuscite" => isset($riuscite[$key]) ? (int)$riuscite[$key] : 0,
                "fallite" => isset($fallite[$key]) ? (int)$fallite[$key] : 0,
                "attesa" => isset($attesa[$key]) ? (int)$attesa[$key] : 0,
                "obiettivo" => $obiettivo
            ];
            array_push($result, $arr);
            $start = strtotime("+1 month", $start);
        }

        return $result;
    }

    public function FillProgressTestDriveVenditori($input)
    {
        $idUser = (int)$input['idRuolo'] === _ADMIN_ || (int)$input['idRuolo'] === _HR_ ? $input['idDipendente'] : $input['idUser'];
        $idOrganization = $input['idOrganization'] == 0 ? $input['idUser'] : $input['idOrganization'];

        $begin = date("Y-m-d", strtotime('-3 month'));
        $end = date("Y-m-d", strtotime('-1 days'));
        $sql = "SELECT * FROM testdrive WHERE idDipendente = $idUser AND (dateTest BETWEEN '$begin' AND '$end')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $dati = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sum = 0;
        foreach ($dati as $durataChiamata) {
            $chiamataMinuti = abs(strtotime($durataChiamata["endTime"]) - strtotime($durataChiamata['startTime']));
            $sum += $chiamataMinuti;
        }

        $mediaLavoro = $sum > 0 ? ($sum / count($dati)) / 60 : 0;

        //Raccolgo i dati che occorrono per calcolare obiettivi e ore lavorative
        $sqlFeatures = "SELECT * FROM department WHERE id = (SELECT idDepartment FROM user WHERE id = $idUser)";
        $stmtFeature = $this->conn->prepare($sqlFeatures);
        $stmtFeature->execute();
        $features = $stmtFeature->fetch(PDO::FETCH_ASSOC);

        $timestampCheckin = strtotime($features['oraCheckin']);
        $timestampCheckout = strtotime($features['oraCheckout']);

        $minutiGiornalieri = (abs($timestampCheckout - $timestampCheckin) / 60) - $features['minutiPausa'];
        $minutiK = abs(strtotime("00:00:00") - strtotime($features['kMinuti'])) / 60;
        $tempoMedioAttivita = round($minutiK + $mediaLavoro);
        $targetGiornaliero = round(($minutiGiornalieri * ($features['deltaObiettivi'] / 100)) / ($tempoMedioAttivita));

        $start = date("Y-m-d", strtotime("-1 week"));
        $end = date("Y-m-d", strtotime("today"));
        $sql = "SELECT * FROM testdrive WHERE idDipendente = $idUser AND dateTest BETWEEN $start AND $end";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $weekTests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $today = date("Y-m-d");
        $sql = "SELECT * FROM testdrive WHERE idDipendente = $idUser AND dateTest LIKE '$today'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $todayTests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $minutiRaggiunti = 0;
        foreach ($todayTests as $yesterdayCall) {
            $durata = abs(strtotime($yesterdayCall["endDate"]) - strtotime($yesterdayCall['startDate'])) / 60;
            $durata = $durata + $minutiK;
            $minutiRaggiunti += $durata;
        }



        $minutiSettimanaliRaggiunti = 0;
        foreach ($weekTests as $chiamataSettimana) {
            $durata = abs(strtotime($chiamataSettimana['endDate']) - strtotime($chiamataSettimana['startDate'])) / 60;
            $durata = $durata + $minutiK;
            $minutiSettimanaliRaggiunti += $durata;
        }

        $countToday = count($todayTests);
        $countWeekly = count($weekTests);

        $result = [
            'minutiProduttivita'            => $minutiGiornalieri,
            'minutiSettimanaliProduttivita' => $minutiSettimanaliRaggiunti,
            'targetGiornaliero'             => $targetGiornaliero,
            'targetSettimanale'             => $targetGiornaliero * 5,
            'todayCalls'                    => (int)$countToday,
            'weeklyCalls'                   => (int)$countWeekly,
            'minutiRaggiunti'               => $minutiRaggiunti
        ];

        return $result;
    }

    /* Fine funzioni per progress bar e torta */

    public function AddTicket($input)
    {
        $idUtente        = $input['idUtente'];
        $tipoChiamata    = $input['tipoChiamata'];
        $dataRicevuta    = $input['dataRicevuta'];
        $oraRicevuta     = $input['oraRicevuta'];
        $durata          = $input['durata'];
        $nrDialed        = $input['nrDialed'];
        $ringTime        = $input['ringTime'];
        $esitoChiamata   = $input['esitoChiamata'];
        $numeroChiamente = $input['numeroChiamante'];
        $codiceSoggetto  = $input['codiceSoggetto'];
        $nomeSoggetto    = $input['nomeSoggetto'];
        $note            = $input['note'];

        $sql = "INSERT INTO `customercare`
        (`id`, `idUtente`, `tipoChiamata`, `dataRicevuta`, `oraRicevuta`, `durata`, `nrDialed`, `ringTime`, `esitoChiamata`, `numeroChiamante`, `codiceSoggetto`, `nomeSoggetto`, `note`, `dataCreazione`)
         VALUES (NULL,$idUtente,'$tipoChiamata','$dataRicevuta','$oraRicevuta','$durata','$nrDialed','$ringTime','$esitoChiamata','$numeroChiamente','$codiceSoggetto','$nomeSoggetto','$note',CURDATE())";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return true;
    }

    public function exportCsv($input)
    {
        $idUtente = $input['idUtente'];


        $sqlUserInfo = "SELECT * FROM user_data WHERE id_user = $idUtente";
        $stmtUserInfo = $this->conn->prepare($sqlUserInfo);
        $stmtUserInfo->execute();
        $userInfo = $stmtUserInfo->fetch(PDO::FETCH_ASSOC);
        $userName = $userInfo['firstname'];
        $userLastname = $userInfo['lastname'];



        $sql = "SELECT * FROM customercare WHERE $idUtente";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);



        $result = [];
        foreach ($data as $ticket) {
            $newTicket = [

                "Id_chiamata"  => $ticket['id'],
                "Tipo_chiamata"  => $ticket['tipoChiamata'],
                "data_ricevuta"  => $ticket['dataRicevuta'],
                "ora_ricevuta"  => $ticket['oraRicevuta'],
                "durata"  => $ticket['durata'],
                "nr_dialed"  => $ticket['nrDialed'],
                "ring_time"  => $ticket['ringTime'],
                "esito_chiamata"  => $ticket['esitoChiamata'],
                "numero_chiamante"  => $ticket['numeroChiamante'],
                "codice_soggetto"  => $ticket['codiceSoggetto'],
                "nome_soggetto" => $ticket['nomeSoggetto'],
                "note" => $ticket['note']

            ];
            array_push($result, $newTicket);
        }

        return $result;
    }

    public function GetNumberCallsSupervisor($input)
    {

        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUser'];
        }

        $idDipendente = $input['idDipendente'];
        $idRole = $input['idRuolo'];
        $start = date("Y-m-d", strtotime($input['start']));
        $end = date("Y-m-d", strtotime($input['end']));

        if ((int)$idRole == _ADMIN_ || (int)$idRole ==   _HR_) {
            if ($idDipendente != "media") {
                $sql = "SELECT dataRicevuta, COUNT(*) AS calls FROM customercare 
                WHERE exId = $idDipendente AND (dataRicevuta BETWEEN '$start' AND '$end') 
                GROUP BY dataRicevuta 
                ORDER BY dataRicevuta ASC";
            } else {
                $sql = "SELECT dataRicevuta, COUNT(*) AS calls FROM customercare 
                WHERE idOrganization = $idOrganization AND (dataRicevuta BETWEEN '$start' AND '$end') 
                GROUP BY dataRicevuta 
                ORDER BY dataRicevuta ASC";
            }

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $count = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($count as $key => $conto) {
                $conto['calls'] = (int)$conto['calls'];
                $count[$key] = $conto;
            }

            $count = array_map(function ($conto) {
                return array(
                    'argument' => date('d M', strtotime($conto['dataRicevuta'])),
                    'value' => $conto['calls']
                );
            }, $count);

            return $count;
        }
    }

    public function GetNumberCallsEmployee($input)
    {


        $idDipendente = isset($input['idUser']) ? $input['idUser'] : 0;
        $authToken = isset($input['authToken']) ? $input['authToken'] : "";

        $sql = "SELECT COUNT(*) AS auth FROM user WHERE authToken LIKE '$authToken'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $auth = $stmt->fetch(PDO::FETCH_ASSOC);
        $auth = $auth['auth'];

        if ($auth > 0) {

            $start = date("Y-m-d", strtotime($input['start']));
            $end = date("Y-m-d", strtotime($input['end']));


            $sql = "SELECT dataRicevuta, COUNT(*) AS calls FROM customercare 
                WHERE exId = $idDipendente AND (dataRicevuta BETWEEN '$start' AND '$end') 
                GROUP BY dataRicevuta 
                ORDER BY dataRicevuta ASC";


            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $count = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($count as $key => $conto) {
                $conto['calls'] = (int)$conto['calls'];
                $count[$key] = $conto;
            }

            $count = array_map(function ($conto) {
                return array(
                    'argument' => date('d M', strtotime($conto['dataRicevuta'])),
                    'value' => $conto['calls']
                );
            }, $count);

            return $count;
        }
    }

    public function GetCustomerCareChart($input)
    {

        $idDipendente = isset($input['idUser']) ? $input['idUser'] : 0;



        $start = date("Y-m-d", strtotime($input['startDate']));
        $end = date("Y-m-d", strtotime($input['endDate']));

        $sql = "SELECT dataRicevuta, COUNT(*) AS calls FROM customercare 
                WHERE exId = $idDipendente AND (dataRicevuta BETWEEN '$start' AND '$end') 
                GROUP BY dataRicevuta 
                ORDER BY dataRicevuta ASC";


        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $count = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($count as $key => $conto) {
            $conto['calls'] = (int)$conto['calls'];
            $count[$key] = $conto;
        }

        $count = array_map(function ($conto) {
            return array(
                'argument' => date('d M', strtotime($conto['dataRicevuta'])),
                'value' => $conto['calls']
            );
        }, $count);

        $sqlFeatures = "SELECT * FROM department WHERE id = (SELECT idDepartment FROM user WHERE id = $idDipendente)";
        $stmtFeature = $this->conn->prepare($sqlFeatures);
        $stmtFeature->execute();
        $features = $stmtFeature->fetch(PDO::FETCH_ASSOC);

        $timestampCheckin = strtotime($features['oraCheckin']);
        $timestampCheckout = strtotime($features['oraCheckout']);

        $minutiGiornalieri = (abs($timestampCheckout - $timestampCheckin) / 60) - $features['minutiPausa'];
        $minutiK = abs(strtotime("00:00:00") - strtotime($features['kMinuti'])) / 60;

        $sql = "SELECT * FROM customercare 
            WHERE exId = $idDipendente AND( dataRicevuta BETWEEN '$start' AND '$end') 
            ORDER BY dataRicevuta ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $minutiRaggiunti = [];
        $sum = 0;
        foreach ($data as $newTicket) {
            $durata = abs(strtotime("00:00:00") - strtotime($newTicket['durata'])) / 6000;
            $durata = $durata + $minutiK;
            $sum += $durata - $minutiK;
            if (isset($minutiRaggiunti[$newTicket['dataRicevuta']])) {
                $minutiRaggiunti[$newTicket['dataRicevuta']] += round($durata);
            } else {
                $minutiRaggiunti[$newTicket['dataRicevuta']] = round($durata);
            }
        }

        $mediaLavoro = $sum > 0 && count($data) > 0 ?  ($sum / count($data)) / 60 : 0;

        $tempoMedioAttivita = round($minutiK + $mediaLavoro);
        $targetGiornaliero = round(($minutiGiornalieri * ($features['deltaObiettivi'] / 100)) / ($tempoMedioAttivita));
        $deltaObiettivi = $features['deltaObiettivi'];
        $minutiLavoro = round((strtotime($features['oraCheckout']) - strtotime($features['oraCheckin'])) / 60);
        $minutiLavoro = $minutiLavoro - $features['minutiPausa'];
        $minutiGiornalieri = round(($minutiLavoro * $deltaObiettivi) / 100);

        $keyArray = array_keys($minutiRaggiunti);

        $result = [];
        foreach ($keyArray as $key) {
            $output = [
                "month" => date('d M', strtotime($key)),
                "minuti"    => $minutiRaggiunti[$key],
            ];
            array_push($result, $output);
        }

        $chart = [];
        for ($i = 0; $i < count($result); $i++) {
            if ($result[$i]['month'] == $count[$i]['argument']) {
                $arr = [
                    "month" => $result[$i]["month"],
                    "minuti" => $result[$i]["minuti"],
                    "nrCalls" => $count[$i]['value'],
                    "obiettivo" => $minutiGiornalieri,
                    "target" => $targetGiornaliero
                ];
                array_push($chart, $arr);
            }
        }

        return $chart;
    }

    public function GetMinuteOfficinaSupervisor($input)
    {
        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUser'];
        }

        $idDipendente = $input['idDipendente'];
        $idRole = $input['idRuolo'];
        $start = date("Y-m-d", strtotime($input['start']));
        $end = date("Y-m-d", strtotime($input['end']));

        $sqlFeatures = "SELECT * FROM department WHERE id = 2";
        $stmtFeature = $this->conn->prepare($sqlFeatures);
        $stmtFeature->execute();
        $features = $stmtFeature->fetch(PDO::FETCH_ASSOC);

        $timestampCheckin = strtotime($features['oraCheckin']);
        $timestampCheckout = strtotime($features['oraCheckout']);

        $minutiGiornalieri = (abs($timestampCheckout - $timestampCheckin) / 60) - $features['minutiPausa'];
        $minutiK = abs(strtotime("00:00:00") - strtotime($features['kMinuti'])) / 60;

        if ($idRole == _ADMIN_ || $idRole == _HR_) {

            if ($idDipendente != "media") {
                $sql = "SELECT * FROM officina 
                WHERE exId = $idDipendente 
                AND (dataLavoro BETWEEN '$start' AND '$end')
                ORDER BY dataLavoro ASC";
            } else {
                $sql = "SELECT * FROM officina 
                WHERE idOrganization = $idOrganization 
                AND (dataLavoro BETWEEN '$start' AND '$end')
                ORDER BY dataLavoro ASC";
            }

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $minutiRaggiunti = [];
            foreach ($data as $newTicket) {
                $durata = abs(strtotime($newTicket['oraInizio']) - strtotime($newTicket['oraFine'])) / 60;
                $durata = $durata + $minutiK;
                $chiave = $newTicket['dataLavoro'];
                if (isset($minutiRaggiunti[$chiave])) {
                    $minutiRaggiunti[$chiave] += round($durata);
                } else {
                    $minutiRaggiunti[$chiave] = round($durata);
                }
            }

            $keyArray = array_keys($minutiRaggiunti);

            $result = [];
            foreach ($keyArray as $key) {
                $output = [
                    "month" => date('d M', strtotime($key)),
                    "minuti"    => $minutiRaggiunti[$key]
                ];
                array_push($result, $output);
            }

            return $result;
        }
    }

    public function GetMinuteOfficinaEmployee($input)
    {

        $idDipendente = isset($input['idUser']) ? $input['idUser'] : 0;
        $authToken = isset($input['authToken']) ? $input['authToken'] : "";
        $idDepartment = isset($input['idDepartment']) ? $input['idDepartment'] : 0;


        $sql = "SELECT COUNT(*) AS auth FROM user WHERE authToken LIKE '$authToken'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $auth = $stmt->fetch(PDO::FETCH_ASSOC);
        $auth = $auth['auth'];

        if ($auth > 0) {

            $start = date("Y-m-d", strtotime($input['start']));
            $end = date("Y-m-d", strtotime($input['end']));

            $sqlFeatures = "SELECT * FROM department WHERE id = $idDepartment";
            $stmtFeature = $this->conn->prepare($sqlFeatures);
            $stmtFeature->execute();
            $features = $stmtFeature->fetch(PDO::FETCH_ASSOC);

            $timestampCheckin = strtotime($features['oraCheckin']);
            $timestampCheckout = strtotime($features['oraCheckout']);

            $minutiGiornalieri = (abs($timestampCheckout - $timestampCheckin) / 60) - $features['minutiPausa'];
            $minutiK = abs(strtotime("00:00:00") - strtotime($features['kMinuti'])) / 60;



            $sql = "SELECT * FROM officina 
                WHERE exId = $idDipendente 
                AND (dataLavoro BETWEEN '$start' AND '$end')
                ORDER BY dataLavoro ASC";


            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $minutiRaggiunti = [];
            foreach ($data as $newTicket) {
                $durata = abs(strtotime($newTicket['oraInizio']) - strtotime($newTicket['oraFine'])) / 60;
                $durata = $durata + $minutiK;
                $chiave = $newTicket['dataLavoro'];
                if (isset($minutiRaggiunti[$chiave])) {
                    $minutiRaggiunti[$chiave] += round($durata);
                } else {
                    $minutiRaggiunti[$chiave] = round($durata);
                }
            }

            $keyArray = array_keys($minutiRaggiunti);

            $result = [];
            foreach ($keyArray as $key) {
                $output = [
                    "month" => date('d M', strtotime($key)),
                    "minuti"    => $minutiRaggiunti[$key]
                ];
                array_push($result, $output);
            }

            return $result;
        }
    }

    public function GetNumberWorksSupervisor($input)
    {
        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUser'];
        }

        $idDipendente = $input['idDipendente'];
        $idRole = $input['idRuolo'];
        $start = date("Y-m-d", strtotime($input['start']));
        $end = date("Y-m-d", strtotime($input['end']));



        if ((int)$idRole == _ADMIN_ || (int)$idRole) {
            if ($idDipendente != "media") {
                $sql = "SELECT dataLavoro, COUNT(*) AS calls FROM officina 
                WHERE exId = $idDipendente AND (dataLavoro BETWEEN '$start' AND '$end') 
                GROUP BY dataLavoro 
                ORDER BY dataLavoro ASC;";
            } else {
                $sql = "SELECT dataLavoro, COUNT(*) AS calls FROM officina 
                WHERE idOrganization = $idOrganization AND (dataLavoro BETWEEN '$start' AND '$end') 
                GROUP BY dataLavoro 
                ORDER BY dataLavoro ASC;";
            }
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $count = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($count as $key => $conto) {
                $conto['calls'] = (int)$conto['calls'];
                $conto['dataLavoro'] = date("d M", strtotime($conto['dataLavoro']));
                $count[$key] = $conto;
            }

            $count = array_map(function ($conto) {
                return array(
                    'argument' => $conto['dataLavoro'],
                    'value' => $conto['calls']
                );
            }, $count);

            return $count;
        }
    }

    public function GetNumberWorksChartEmployee($input)
    {


        $idDipendente = $input['idUser'];

        $sqlFeatures = "SELECT * FROM department WHERE id = (SELECT idDepartment FROM user WHERE id = $idDipendente)";
        $stmtFeature = $this->conn->prepare($sqlFeatures);
        $stmtFeature->execute();
        $features = $stmtFeature->fetch(PDO::FETCH_ASSOC);

        $timestampCheckin = strtotime($features['oraCheckin']);
        $timestampCheckout = strtotime($features['oraCheckout']);

        $minutiGiornalieri = (abs($timestampCheckout - $timestampCheckin) / 60) - $features['minutiPausa'];
        $minutiK = abs(strtotime("00:00:00") - strtotime($features['kMinuti'])) / 60;

        $start = date("Y-m-d", strtotime($input['startDate']));
        $end = date("Y-m-d", strtotime($input['endDate']));

        $sql = "SELECT * FROM officina 
                WHERE exId = $idDipendente 
                AND (dataLavoro BETWEEN '$start' AND '$end')
                ORDER BY dataLavoro ASC";


        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $minutiRaggiunti = [];
        $sum = 0;
        foreach ($data as $newTicket) {
            $durata = abs(strtotime($newTicket['oraInizio']) - strtotime($newTicket['oraFine'])) / 60;
            $durata = $durata + $minutiK;
            $chiave = $newTicket['dataLavoro'];
            $sum += $durata - $minutiK;
            if (isset($minutiRaggiunti[$chiave])) {
                $minutiRaggiunti[$chiave] += round($durata);
            } else {
                $minutiRaggiunti[$chiave] = round($durata);
            }
        }

        $mediaLavoro = $sum > 0 && count($data) > 0 ? ($sum / count($data)) / 60 : 0;

        $tempoMedioAttivita = round($minutiK + $mediaLavoro);
        $targetGiornaliero = round(($minutiGiornalieri * ($features['deltaObiettivi'] / 100)) / ($tempoMedioAttivita));

        $keyArray = array_keys($minutiRaggiunti);

        $result = [];
        foreach ($keyArray as $key) {
            $output = [
                "month" => date('d M', strtotime($key)),
                "minuti"    => $minutiRaggiunti[$key]
            ];
            array_push($result, $output);
        }


        $sql = "SELECT dataLavoro, COUNT(*) AS calls FROM officina 
                WHERE exId = $idDipendente AND (dataLavoro BETWEEN '$start' AND '$end') 
                GROUP BY dataLavoro 
                ORDER BY dataLavoro ASC;";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $count = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($count as $key => $conto) {
            $conto['calls'] = (int)$conto['calls'];
            $conto['dataLavoro'] = date("d M", strtotime($conto['dataLavoro']));
            $count[$key] = $conto;
        }

        $count = array_map(function ($conto) {
            return array(
                'argument' => $conto['dataLavoro'],
                'value' => $conto['calls']
            );
        }, $count);

        $targetGiornaliero = round(($minutiGiornalieri * ($features['deltaObiettivi'] / 100)) / ($tempoMedioAttivita));
        $deltaObiettivi = $features['deltaObiettivi'];
        $minutiLavoro = round((strtotime($features['oraCheckout']) - strtotime($features['oraCheckin'])) / 60);
        $minutiLavoro = $minutiLavoro - $features['minutiPausa'];
        $minutiGiornalieri = round(($minutiLavoro * $deltaObiettivi) / 100);

        $chart = [];
        for ($i = 0; $i < count($result); $i++) {
            if ($result[$i]['month'] == $count[$i]['argument']) {
                $arr = [
                    "month" => $result[$i]["month"],
                    "minuti" => $result[$i]["minuti"],
                    "nrWorks" => $count[$i]['value'],
                    "obiettivo" => $minutiGiornalieri,
                    "target" => $targetGiornaliero
                ];
                array_push($chart, $arr);
            }
        }
        return $chart;
    }

    public function GetNumberVenditeChart($input)
    {
    }

    /**************************************************************************************************
     * Funzioni per importare csv di lavoro                                                           *
     **************************************************************************************************/

    public function ImportCsvVenditori($input)
    {
        if ($input['idOrganization'] == 0) {
            $idOrganization = $input['idUser'];
        } else {
            $idOrganization = $input['idOrganization'];
        }

        $sql = "SELECT user_data.id_user, customField
        FROM user LEFT JOIN user_data ON user.id = user_data.id_user 
        WHERE user.id_organization = $idOrganization";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $fieldDictionary = [];

        foreach ($fields as $field) {


            $customsField = json_decode($field['customField'], true);
            if (is_array($customsField)) {

                foreach ($customsField as $slug => $exField) {

                    if ($exField != "") {

                        $fieldDictionary[$exField] = $field['id_user'];
                    }
                }
            }
        }

        $dati = $input['dati'];
        $colonne = $input['colonne'];

        $lastSettings = json_encode($colonne);

        $sql = "SELECT id FROM `lastsettings` WHERE idOrganization = $idOrganization AND idModule =" . _VENDITORI_;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            $sql = "UPDATE `lastsettings` SET `relation` = '$lastSettings' WHERE id = " . $count['id'];
        } else {
            $sql = "INSERT INTO `lastsettings`(`idOrganization`, `idModule`, `relation`)
            VALUES ($idOrganization," . _VENDITORI_ . ",'$lastSettings')";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        function remove_empty_($array)
        {
            return array_filter($array, '_remove_empty_internal_');
        }

        function _remove_empty_internal_($value)
        {
            return !empty($value) || $value === 0;
        }

        foreach ($dati as $key => $dato) {
            if (isset($dato[$colonne['exId']])) {

                $dato[$colonne['exId']] = $fieldDictionary[$dato[$colonne['exId']]];
                $dati[$key] = $dato;
            } else {
                unset($dati[$key]);
            }
        }

        $key = [];

        foreach ($colonne as $colonna) {
            array_push($key, $colonna);
        }

        $values = [];

        foreach ($dati as $dato) {
            array_push($values, $dato);
        }

        $newArray = [];

        for ($i = 0; $i < count($values); ++$i) {
            $arr = [];

            foreach ($key as $k) {
                isset($values[$i][$k]) ? $arr[$k] = $values[$i][$k] : $arr[$k] = "";
                $value = isset($values[$i][$k]) ? $values[$i][$k] : "";
            }
            array_push($newArray, $arr);
        }

        function RemoveSpecialChar_($str)
        {

            // Using str_replace() function 
            // to replace the word 
            $res = str_replace(array(
                '\'', '"',
                ',', ';', '<', '>'
            ), ' ', $str);

            // Returning the result 
            return $res;
        }

        function validateDate_($date, $format = 'Y-m-d H:i:s')
        {
            $d = DateTime::createFromFormat($format, $date);
            return $d && $d->format($format) == $date;
        }

        $stringa = "";
        for ($i = 0; $i < count($values) - 1; ++$i) {
            $stringa .= "(";
            foreach ($newArray[$i] as $key => $value) {
                $value = RemoveSpecialChar_(($value));

                if (strpos($value, "/")) {

                    $value = str_replace('/', '-', $value);
                    $value = date('Y-m-d', strtotime($value));
                }

                $value = trim($value);
                $stringa .= "'$value', ";
            }


            if ($i == count($values) - 2) {
                $stringa .= "CURRENT_TIMESTAMP)";
            } else {
                $stringa .= "CURRENT_TIMESTAMP), ";
            }
        }

        $sql = "INSERT INTO `venditori`(`idVenditore`, `idTrattativa`, `codiceOfferta`, `venditore`, `stato`, dateCreated) 
                VALUES $stringa ON DUPLICATE KEY UPDATE stato=values(stato)";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return false;
        } catch (PDOException $e) {
            echo "Connessione fallita " . $e->getMessage();
            return true;
            exit;
        }
    }

    public function importCsvCustomerCare($input)
    {
        if ($input['idOrganization'] == 0) {
            $idOrganization = $input['idUser'];
        } else {
            $idOrganization = $input['idOrganization'];
        }
        $useCustomField = isset($input['useCustomField']) && ($input['useCustomField'] == 1 || $input['useCustomField'] == true) ? true : false;

        if ($useCustomField == true) {
            $sql = "SELECT user_data.id_user, user_data.customField
            FROM user LEFT JOIN user_data ON user.id = user_data.id_user 
            WHERE user.id_organization = $idOrganization AND user_data.customField != '[]'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $fieldDictionary = [];

            foreach ($fields as $field) {


                $customsField = json_decode($field['customField'], true);
                if (is_array($customsField)) {

                    foreach ($customsField as $slug => $exField) {

                        if ($exField != "") {

                            $fieldDictionary[$exField] = $field['id_user'];
                        }
                    }
                }
            }
        }

        $dati = $input['dati'];
        $colonne = $input['colonne'];

        $lastSettings = json_encode($colonne);

        $sql = "SELECT id FROM `lastsettings` WHERE idOrganization = $idOrganization AND idModule =" . _CUSTOMERCARE_;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            $sql = "UPDATE `lastsettings` SET `relation` = '$lastSettings' WHERE id = " . $count['id'];
        } else {
            $sql = "INSERT INTO `lastsettings`(`idOrganization`, `idModule`, `relation`)
            VALUES ($idOrganization," .  _CUSTOMERCARE_ . ",'$lastSettings')";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        function remove_empty($array)
        {
            return array_filter($array, '_remove_empty_internal');
        }

        function _remove_empty_internal($value)
        {
            return !empty($value) || $value === 0;
        }

        foreach ($dati as $key => $dato) {
            if (isset($dato[$colonne['exId']])) {

                $dato[$colonne['exId']] = isset($fieldDictionary[$dato[$colonne['exId']]]) ? $fieldDictionary[$dato[$colonne['exId']]] : 0;
                $dati[$key] = $dato;
            } else {
                unset($dati[$key]);
            }
        }

        $key = [];

        foreach ($colonne as $colonna) {
            array_push($key, $colonna);
        }

        $values = [];

        foreach ($dati as $dato) {
            array_push($values, $dato);
        }

        $newArray = [];

        for ($i = 0; $i < count($values); ++$i) {
            $arr = [];
            foreach ($key as $k) {
                /* isset($values[$i][$k]) ? $arr[$k] = $values[$i][$k] : $arr[$k] = ""; */
                $arr[$k] = $values[$i][$k];
                $value = isset($values[$i][$k]) ? $values[$i][$k] : "";
            }
            array_push($newArray, $arr);
        }



        function RemoveSpecialChar($str)
        {

            // Using str_replace() function 
            // to replace the word 
            $res = str_replace(array(
                '\'', '"',
                ',', ';', '<', '>'
            ), ' ', $str);

            // Returning the result 
            return $res;
        }

        function validateDate($date, $format = 'Y-m-d H:i:s')
        {
            $d = DateTime::createFromFormat($format, $date);
            return $d && $d->format($format) == $date;
        }

        $stringa = "";
        for ($i = 0; $i < count($values) - 1; ++$i) {
            $stringa .= "(";
            foreach ($newArray[$i] as $key => $value) {
                $value = RemoveSpecialChar(($value));

                if (strpos($value, "/")) {

                    $value = str_replace('/', '-', $value);
                    $value = date('Y-m-d', strtotime($value));
                }

                $stringa .= "'$value', ";
            }

            if ($i == count($values) - 2) {
                $stringa .= "CURRENT_TIMESTAMP())";
            } else {
                $stringa .= " CURRENT_TIMESTAMP()), ";
            }
        }

        $sql = "INSERT INTO `customercare`(`exId`, `tipoChiamata`, `dataRicevuta`, `oraRicevuta`, `durata`,
         `nrDialed`, `ringTime`, `numeroChiamante`, `nomeSoggetto`, `dataCreazione`) VALUES $stringa";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return false;
        } catch (PDOException $e) {
            echo "Connessione fallita " . $e->getMessage();
            return true;
            exit;
        }
    }

    public function ImportCsvOfficina($input)
    {
        if ($input['idOrganization'] == 0) {
            $idOrganization = $input['idUser'];
        } else {
            $idOrganization = $input['idOrganization'];
        }

        $sql = "SELECT user_data.id_user, user_data.customField
            FROM user LEFT JOIN user_data ON user.id = user_data.id_user 
            WHERE user.id_organization = $idOrganization";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $fieldDictionary = [];

        foreach ($fields as $field) {


            $customsField = json_decode($field['customField'], true);
            if (is_array($customsField)) {

                foreach ($customsField as $slug => $exField) {

                    if ($exField != "") {

                        $fieldDictionary[$exField] = isset($field['id_user']) ? $field['id_user'] : null;
                    }
                }
            }
        }

        $dati = $input['dati'];
        $colonne = $input['colonne'];

        $lastSettings = json_encode($colonne);

        $sql = "SELECT id FROM `lastsettings` WHERE idOrganization = $idOrganization AND idModule =" . _OFFICINA_;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            $sql = "UPDATE `lastsettings` SET `relation` = '$lastSettings' WHERE id = " . $count['id'];
        } else {
            $sql = "INSERT INTO `lastsettings`(`idOrganization`, `idModule`, `relation`)
            VALUES ($idOrganization," . _OFFICINA_ . ",'$lastSettings')";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        function removeEmpty($array)
        {
            return array_filter($array, 'removeEmptyInternal');
        }

        function removeEmptyInternal($value)
        {
            return !empty($value) || $value === 0;
        }

        foreach ($dati as $key => $dato) {
            if (isset($dato[$colonne['exId']])) {
                $dato[$colonne['exId']] = isset($fieldDictionary[$dato[$colonne['exId']]]) ? $fieldDictionary[$dato[$colonne['exId']]] : 0;
                $dati[$key] = $dato;
            } else {
                unset($dati[$key]);
            }
        }

        $key = [];

        foreach ($colonne as $colonna) {
            array_push($key, $colonna);
        }

        $values = [];

        foreach ($dati as $dato) {
            array_push($values, $dato);
        }


        $newArray = [];

        for ($i = 0; $i < count($values); ++$i) {
            $arr = [];

            foreach ($key as $k) {
                isset($values[$i][$k]) ? $arr[$k] = $values[$i][$k] : $arr[$k] = "";
                $value = isset($values[$i][$k]) ? $values[$i][$k] : "";
            }
            array_push($newArray, $arr);
        }

        function RemoveSpecialCharOff($str)
        {

            // Using str_replace() function 
            // to replace the word 
            $res = str_replace(array(
                '\'', '"',
                ',', ';', '<', '>'
            ), ' ', $str);

            // Returning the result 
            return $res;
        }

        function validateDateOff($date, $format = 'Y-m-d H:i:s')
        {
            $d = DateTime::createFromFormat($format, $date);
            return $d && $d->format($format) == $date;
        }


        $stringa = "";
        for ($i = 0; $i < count($values) - 1; ++$i) {
            $stringa .= "(";
            foreach ($newArray[$i] as $value) {
                $value = RemoveSpecialCharOff(($value));

                if (strpos($value, "/")) {
                    $value = str_replace('/', '-', $value);
                    $value = date('Y-m-d', strtotime($value));
                    $stringa .= "'$value', ";
                } else if (strpos($value, ":") && (strpos($value, " - ") && ((strpos($value, "0")) || (strpos($value, "1")) || (strpos($value, "2")) || (strpos($value, "3")) || (strpos($value, "4")) || (strpos($value, "5")) || (strpos($value, "6")) || (strpos($value, "7")) || (strpos($value, "8")) || (strpos($value, "9"))))) {

                    $value = explode(" - ", $value);
                    $oraInizio = $value[0];
                    $oraFine = $value[1];
                    $stringa .= "'$oraInizio', '$oraFine', ";
                } else {

                    $stringa .= "'$value', ";
                }
            }

            if ($i == count($values) - 2) {
                $stringa .= "CURRENT_TIMESTAMP())";
            } else {
                $stringa .= " CURRENT_TIMESTAMP()), ";
            }
        }

        $sql = "INSERT INTO `officina`(`exId`, `oraInizio`, `oraFine`, `dataLavoro`, `numCommessa`, 
        `addebito`, `desLavoro`, `unLavoro`, `desSede`, `createDate`) VALUES $stringa";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return true;
    }

    /**************************************************************************************************
     * Valutazione utente                                                                             *
     **************************************************************************************************/

    public function CreateValutationField($input)
    {

        if ($input['idOrganization'] == 0) {
            $idOrganization = $input['idUser'];
        } else {
            $idOrganization = $input['idOrganization'];
        }

        $values = $input['values'];
        $nomeCampo = strtolower($values['nomeCampo']);
        $idModule = $values['module'];
        $descrizione = isset($values['descrizione']) ? $values['descrizione'] : "";
        $slug = strtolower(str_replace(' ', '_', $nomeCampo));

        $sql = "SELECT COUNT(*) AS checkName FROM campivalutazione WHERE campo LIKE '$nomeCampo' AND idOrganization = $idOrganization AND idModule = $idModule";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $checkName = $stmt->fetch(PDO::FETCH_ASSOC);
        $checkName = $checkName['checkName'];

        $sql = "SELECT COUNT(*) AS maxFields FROM campivalutazione WHERE idOrganization = $idOrganization AND idModule = $idModule";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $maxFields = $stmt->fetch(PDO::FETCH_ASSOC);
        $maxFields = $maxFields['maxFields'];

        if ($checkName == 0 && $maxFields <= 20) {

            $sql = "INSERT INTO `campivalutazione`(`idOrganization`, `idModule`, `campo`, `descrizione`, `slug`) 
            VALUES ($idOrganization,$idModule,'$nomeCampo','$descrizione','$slug')";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            return "";
        } else if ($checkName > 0) {
            return "Nome campo già esistente";
        } else if ($maxFields > 20) {
            return "Raggiunto numero massimo di campi";
        }
    }

    public function EditValutationField($input)
    {

        if ($input['idOrganization'] == 0) {
            $idOrganization = $input['idUser'];
        } else {
            $idOrganization = $input['idOrganization'];
        }

        $idField = $input['idField'];
        $input = $input['values'];
        $idModule = $input['module'];
        $nameField = trim($input['name']);
        $description = $input['description'];
        $slug = strtolower(str_replace(' ', '_', $nameField));

        $sql = "SELECT COUNT(*) AS checkName FROM campivalutazione WHERE campo LIKE '$nameField' AND idOrganization = $idOrganization AND idModule = $idModule AND id!=$idField";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $checkName = $stmt->fetch(PDO::FETCH_ASSOC);
        $checkName = $checkName['checkName'];

        if ($checkName == 0) {
            $sql = "UPDATE `campivalutazione` SET `idModule`=$idModule,`campo`='$nameField',
                    `descrizione`='$description',`slug`='$slug' WHERE id=$idField";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            return [
                "message" => "Campo aggiornato con successo",
                "color" => "green"
            ];
        } else return [
            "message" => "Nome campo già esistente",
            "color" => "red"
        ];
    }

    public function DeleteValutationField($input)
    {
        $idField = $input['idField'];

        $sql = "DELETE FROM `campivalutazione` WHERE id=$idField";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return true;
    }

    public function GetValutationFields($input)
    {

        if ($input['idOrganization'] == 0) {
            $idOrganization = $input['idUser'];
        } else {
            $idOrganization = $input['idOrganization'];
        }

        $sql = "SELECT campivalutazione.*,modules.name FROM campivalutazione
                JOIN modules ON modules.id = campivalutazione.idModule
                WHERE idOrganization = $idOrganization ORDER BY modules.name ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($fields as $field) {
            $arr = [
                "module" => isset($field['name']) ? ucwords($field['name']) : "",
                "nomeCampo" => isset($field['campo']) ? ucwords($field['campo']) : "",
                "descrizioneCampo" => isset($field['descrizione']) ? $field['descrizione'] : "",
                "id" => isset($field['id']) ? $field["id"] : "",
                "idModule" => isset($field['idModule']) ? $field['idModule'] : "",
            ];
            array_push($result, $arr);
        }
        return $result;
    }

    public function GetColumnsSupervisor($input)
    {
        if ($input['idOrganization'] == 0) {
            $idOrganization = $input['idUser'];
        } else {
            $idOrganization = $input['idOrganization'];
        }

        $idRole = $input['idRole'];
        $selectedUser = $input['selectedUser'];
        $page = isset($input['page']) ? $input['page'] : "resoconto";

        if ((int)$idRole == _ADMIN_ || (int)$idRole == _HR_) {
            $sql = "SELECT campo, slug, descrizione FROM campivalutazione 
            WHERE idOrganization = $idOrganization 
            AND idModule = (SELECT idModule FROM department LEFT JOIN user ON user.idDepartment = department.id WHERE user.id = $selectedUser)";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($page === "valutazione") {
                $result = [];
            } else if ($page === "resoconto") {

                $result = [
                    array(
                        "name" => "data_valutazione",
                        "title" => "Data Valutazione"
                    ),
                    array(
                        "name" => "tipo_valutazione",
                        "title" => "Tipo valutazione",
                    ),
                ];
            }
            foreach ($fields as $field) {
                if ($page === "valutazione") {
                    $arr = [
                        "name" => $field['slug'],
                        "title" => ucwords($field['campo']),
                        "description" => $field['descrizione'] === "" ? false :  ucfirst($field['descrizione'])
                    ];
                } else if ($page === "resoconto") {
                    $arr = [
                        "name" => $field['slug'],
                        "title" => ucwords($field['campo']),
                    ];
                }
                array_push($result, $arr);
            }
            return $result;
        }
    }

    public function GetRowsSupervisor($input)
    {
        if ($input['idOrganization'] == 0) {
            $idOrganization = $input['idUser'];
        } else {
            $idOrganization = $input['idOrganization'];
        }

        $idRole = $input['idRole'];
        $idDipendente = $input['selectedUser'];


        if ((int)$idRole === _ADMIN_ || (int)$idRole === _HR_) {

            $sql = "SELECT * FROM valutazioni WHERE idDipendente = $idDipendente";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $valutazioni = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [];
            foreach ($valutazioni as $valutazione) {
                $arr = [
                    "tipo_valutazione" => ucwords($valutazione['type']),
                    "data_valutazione" => date("d-m-Y", strtotime($valutazione['dataValutazione'])),
                ];
                $valutazione = json_decode($valutazione['valutazione'], true);
                foreach ($valutazione as $key => $field) {
                    $arr[$key] = $field . "/5";
                }
                array_push($result, $arr);
            }

            return $result;
        } else return "Non autorizzato";
    }

    public function GetColumnsEmployee($input)
    {

        $idModule = $input['idModule'];
        $idOrganization = $input['idOrganization'];

        $sql = "SELECT campo,slug,descrizione FROM campivalutazione WHERE idModule = $idModule AND idOrganization = $idOrganization";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!isset($input['page'])) {
            $result = [
                array(
                    "name" => "data_valutazione",
                    "title" => "Data Valutazione"
                ),
                array(
                    "name" => "tipo_valutazione",
                    "title" => "Tipo valutazione",
                ),
            ];

            foreach ($fields as $field) {
                $arr = [
                    "name" => isset($field['slug']) ? $field['slug'] : "",
                    "title" => isset($field['campo']) ? ucwords($field['campo']) : ""
                ];
                array_push($result, $arr);
            }
        } else if (isset($input['page']) && $input['page'] === "valutazione") {
            $result = [];

            foreach ($fields as $field) {
                $arr = [
                    "name" => isset($field['slug']) ? $field['slug'] : "",
                    "title" => isset($field['campo']) ? ucwords($field['campo']) : "",
                    "description" => !isset($field['descrizione']) || $field['descrizione'] === ""  ? false : ucfirst($field['descrizione'])
                ];
                array_push($result, $arr);
            }
        }

        return $result;
    }

    public function GetRowsDipendente($input)
    {
        $idDipendente = $input['id'];

        $sql = "SELECT * FROM valutazioni WHERE idDipendente = $idDipendente";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $valutations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($valutations as $valutazione) {
            $arr = [
                "tipo_valutazione" => ucwords($valutazione['type']),
                "data_valutazione" => date("d-m-Y", strtotime($valutazione['dataValutazione'])),
            ];
            $valutazione = json_decode($valutazione['valutazione'], true);
            foreach ($valutazione as $key => $field) {
                $arr[$key] = $field . "/5";
            }
            array_push($result, $arr);
        }

        return $result;
    }

    public function ValutaDipendente($input)
    {
        if ($input['idOrganization'] == 0) {
            $idOrganization = $input['idUser'];
        } else {
            $idOrganization = $input['idOrganization'];
        }

        $idDipendente = $input['selectedUser'];
        $type = isset($input['type']) ? $input['type'] : "";
        $values = $input['values'];

        $valutation = [];
        foreach ($values as $key => $value) {
            $valutation[$key] = $value;
        }
        $valutation = json_encode($valutation);

        $sql = "SELECT COUNT(*) AS lastValutation, id FROM valutazioni 
        WHERE MONTH(dataValutazione) = MONTH(CURRENT_DATE) 
        AND idDipendente = $idDipendente AND type LIKE 'valutazione'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $val = $stmt->fetch(PDO::FETCH_ASSOC);
        $lastValutation = $val['lastValutation'];

        if ($lastValutation > 0) {
            $sql = "UPDATE `valutazioni` SET `valutazione`='$valutation',`dataValutazione` = CURRENT_TIMESTAMP WHERE id = " . $val['id'];
        } else {
            $sql = "UPDATE valutazioni SET last = 0 WHERE last = 1 AND idDipendente = $idDipendente AND type LIKE '$type';
            INSERT INTO `valutazioni`(`idOrganization`, `idDipendente`, `type`, `valutazione`, last, `dataValutazione`) 
            VALUES ($idOrganization,$idDipendente,'valutazione','$valutation',1,CURRENT_TIMESTAMP)";
        }

        try {

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            return "Valutazione effettuata con successo";
        } catch (PDOException $e) {

            return $e;
        }
    }

    public function ValutazioneEmployee($input)
    {
        if ($input['idOrganization'] == 0) {
            $idOrganization = $input['idUser'];
        } else {
            $idOrganization = $input['idOrganization'];
        }

        $idDipendente = $input['selectedUser'];
        $type = isset($input['type']) ? $input['type'] : "";
        $values = $input['values'];

        $valutation = [];
        foreach ($values as $key => $value) {
            $valutation[$key] = $value;
        }
        $valutation = json_encode($valutation);

        $sql = "SELECT COUNT(*) AS lastValutation, id FROM valutazioni 
        WHERE MONTH(dataValutazione) = MONTH(CURRENT_DATE) 
        AND idDipendente = $idDipendente AND type LIKE '$type'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $val = $stmt->fetch(PDO::FETCH_ASSOC);
        $lastValutation = $val['lastValutation'];

        if ($lastValutation > 0) {
            $sql = "UPDATE `valutazioni` SET `valutazione`='$valutation',`dataValutazione` = CURRENT_TIMESTAMP WHERE id = " . $val['id'];
        } else {
            $sql = "UPDATE valutazioni SET last = 0 WHERE last = 1 AND idDipendente = $idDipendente AND type LIKE '$type';
            INSERT INTO `valutazioni`(`idOrganization`, `idDipendente`, `type`, `valutazione`, last, `dataValutazione`) 
            VALUES ($idOrganization,$idDipendente,'$type','$valutation',1,CURRENT_TIMESTAMP)";
        }

        try {

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            return "Valutazione effettuata con successo";
        } catch (PDOException $e) {

            return $e;
        }
    }

    public function GetValutationsUser($input)
    {
        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idDipendente'];
        }

        $idRole = $input['idRole'];
        $idDepartment = $input['idDepartment'];

        if ($idRole == _ADMIN_) {
            $sql = "SELECT department.idModule, department.name AS nameDepartment, modules.name AS nameModule, user_data.firstname, user_data.lastname, valutazioni.* FROM user_data 
            JOIN user ON user_data.id_user = user.id
            JOIN department ON user.idDepartment = department.id
            JOIN modules ON department.idModule = modules.id
            JOIN valutazioni ON user.id = valutazioni.idDipendente
            WHERE valutazioni.idOrganization = $idOrganization";
        } else if ($idRole == _HR_) {
            $sql = "SELECT department.idModule, department.name AS nameDepartment, modules.name AS nameModule, user_data.firstname, user_data.lastname, valutazioni.* FROM user_data 
            JOIN user ON user_data.id_user = user.id
            JOIN department ON user.idDepartment = department.id
            JOIN modules ON department.idModule = modules.id
            JOIN valutazioni ON user.id = valutazioni.idDipendente
            WHERE department.id = $idDepartment OR department.idPadre = $idDepartment";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $valutations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sommaAzienda = 0;
        $mediaValutazione = [];
        $count = 0;
        for ($i = 0; $i < count($valutations); $i++) {
            $valutazione = json_decode($valutations[$i]['valutazione'], true);
            $somma = 0;
            $count = $count + count($valutazione);
            if (isset($mediaValutazione[$valutations[$i]['idDipendente']]["count"])) {

                $mediaValutazione[$valutations[$i]['idDipendente']]["count"] += count($valutazione);
            } else {
                $mediaValutazione[$valutations[$i]['idDipendente']]["count"] = count($valutazione);
            }
            foreach ($valutazione as $val) {
                $somma += $val;
                if (isset($mediaValutazione[$valutations[$i]['idDipendente']]['somma'])) {
                    $mediaValutazione[$valutations[$i]['idDipendente']]["somma"] += $val;
                } else {
                    $mediaValutazione[$valutations[$i]['idDipendente']]["somma"] = $val;
                }
                if (isset($mediaValutazione[$valutations[$i]['idDipendente']]['lastValutation'])) {

                    $mediaValutazione[$valutations[$i]['idDipendente']]['lastValutation'] = strtotime($valutations[$i]['dataValutazione']) > $mediaValutazione[$valutations[$i]['idDipendente']]['lastValutation'] ? date("d-m-Y", strtotime($valutations[$i]['dataValutazione'])) : date("d-m-Y", strtotime($mediaValutazione[$valutations[$i]['idDipendente']]['lastValutation']));
                } else {
                    $mediaValutazione[$valutations[$i]['idDipendente']]['lastValutation'] = date("d-m-Y", strtotime($valutations[$i]['dataValutazione']));
                }
                $mediaValutazione[$valutations[$i]['idDipendente']]['nomeDipendente'] = ucwords($valutations[$i]['firstname'] . " " . $valutations[$i]['lastname']);
                $mediaValutazione[$valutations[$i]['idDipendente']]['nameModule'] = ucwords($valutations[$i]['nameModule']);
                $mediaValutazione[$valutations[$i]['idDipendente']]['nameDepartment'] = ucwords($valutations[$i]['nameDepartment']);
                $mediaValutazione[$valutations[$i]['idDipendente']]['id'] = (int)$valutations[$i]['idDipendente'];
            }
            $sommaAzienda += $somma;
        }
        $mediaAzienda = $sommaAzienda > 0 && $count > 0 ? $sommaAzienda / $count : 0;
        $mediaAzienda = $mediaAzienda > 0 ? round($mediaAzienda * 2 / 2) : 0;

        $result = [];
        foreach ($mediaValutazione as $key => $valutazione) {
            $media = $valutazione['somma'] / $valutazione['count'];
            $media = round($media * 2) / 2;
            $mediaValutazione[$key]['media'] = $media . "/5";
            if ($media > $mediaAzienda) {
                $mediaValutazione[$key]['stato'] = "Sopra la media";
            } else if ($media = $mediaAzienda) {
                $mediaValutazione[$key]['stato'] = "Nella media";
            } else {
                $mediaValutazione[$key]['stato'] = "Sotto la media";
            }
            array_push($result, $mediaValutazione[$key]);
        }

        return $result;
    }

    public function GetValutazioni($input)
    {
        $idDipendente = isset($input['selectedUser']) ? $input['selectedUser'] : $input['id'];

        //RECUPERO LE AUTOVALUTAZIONI
        $sql = "SELECT * FROM valutazioni WHERE idDipendente = $idDipendente AND type = 'autovalutazione' AND last = 1";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $autovalutazioni = $stmt->fetch(PDO::FETCH_ASSOC);
                $autovalutazioni = json_decode($autovalutazioni['valutazione'], true);
            }
        } catch (PDOException $e) {
            echo $e;
        }

        //RECUPERO LE VALUTAZIONI
        $sql = "SELECT * FROM valutazioni WHERE idDipendente = $idDipendente AND type = 'valutazione' AND last = 1";
        try {

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $valutazioni = $stmt->fetch(PDO::FETCH_ASSOC);
                $valutazioni = json_decode($valutazioni['valutazione'], true);
            }
        } catch (PDOException $e) {
            echo $e;
        }

        //MAPPO I CAMPIVALUTAZIONI PER ESEGUIRE OPERAZIONI DI MEDIA E RACCOLTA DATI
        $sql = "SELECT campivalutazione.slug FROM 
        user JOIN department ON user.idDepartment = department.id
        JOIN campivalutazione ON department.idModule = campivalutazione.idModule
        WHERE user.id = $idDipendente";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $slug = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //CREO L'ARRAY RESULT PER FAR TORNARE ALLA FUNZIONE I CAMPI NECESSARI ALLA COMPILAZIONE GRAFICO
        $result = [];
        for ($i = 0; $i < count($slug); $i++) {
            $arr = [
                "field" => ucwords(str_replace("_", " ", $slug[$i]['slug'])),
                "valutation" => isset($valutazioni[$slug[$i]['slug']]) ? $valutazioni[$slug[$i]['slug']] / 5 * 100 : 0,
                "autovalutation" => isset($autovalutazioni[$slug[$i]['slug']]) ? $autovalutazioni[$slug[$i]['slug']] / 5 * 100 : 0,
                "mediaValutazione" => isset($valutazioni[$slug[$i]['slug']]) && isset($autovalutazioni[$slug[$i]['slug']]) ? (($valutazioni[$slug[$i]['slug']] + $autovalutazioni[$slug[$i]['slug']]) / 2) / 5 * 100 : 0
            ];
            array_push($result, $arr);
        }

        return $result;
    }

    public function GetRiepilogoValutazioni($input)
    {
        $idDipendente = isset($input['idDipendente']) ? $input['idDipendente'] : $input['id'];
        $start = date("Y-m-d", strtotime($input['start'] . ' -1 day'));
        $end = date("Y-m-d", strtotime($input['end'] . ' +1 day'));


        //RECUPER VALUTAZIONI PER INTEVALLO DI DATA
        $sql = "SELECT valutazione, dataValutazione FROM valutazioni WHERE type LIKE 'valutazione' AND idDipendente = $idDipendente AND dataValutazione BETWEEN '$start' AND '$end' ORDER BY dataValutazione DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $valutazioni = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //RECUPERO AUTOVALUTAZIONI PER INTEVALLO DI DATA
        $sql = "SELECT valutazione, dataValutazione FROM valutazioni WHERE type LIKE 'autovalutazione' AND idDipendente = $idDipendente AND dataValutazione BETWEEN '$start' AND '$end' ORDER BY dataValutazione DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $autovalutazioni = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //RECUPERO CAMPI VALUTAZIONE PER MAPPARE QUELLI ANCORA ESISTENTI NELLE VALUTAZIONI PASSATE
        $sql = "SELECT campivalutazione.slug FROM 
            user JOIN department ON user.idDepartment = department.id
            JOIN campivalutazione ON department.idModule = campivalutazione.idModule
            WHERE user.id = $idDipendente";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $slug = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $valutazioniArr = [];
        $autovalutazioniArr = [];
        $counter = 0;
        foreach ($valutazioni as $key => $valutazione) {
            $valutazioniEnd = json_decode($valutazione['valutazione'], true);
            $dataValutazione = date("M", strtotime($valutazione['dataValutazione']));
            for ($i = 0; $i < count($slug); $i++) {
                if (isset($valutazioniEnd[$slug[$i]['slug']])) {
                    if (isset($valutazioniArr[$dataValutazione])) {
                        $valutazioniArr[$dataValutazione] += $valutazioniEnd[$slug[$i]['slug']];
                    } else {
                        $valutazioniArr[$dataValutazione] = $valutazioniEnd[$slug[$i]['slug']];
                    }
                    $counter = $counter + 1;
                } else continue;
            }
            if (isset($valutazioniArr[$dataValutazione])) {

                $valutazioniArr[$dataValutazione] = round($valutazioniArr[$dataValutazione] / $counter * 2) / 2;
            }
            $counter = $counter - $counter;
        }
        $counter = 0;
        foreach ($autovalutazioni as $key => $autovalutazione) {
            $autovalutazioniEnd = json_decode($autovalutazione['valutazione'], true);
            $dataValutazione = date("M", strtotime($autovalutazione['dataValutazione']));
            for ($i = 0; $i < count($slug); $i++) {
                if (isset($autovalutazioniEnd[$slug[$i]['slug']])) {
                    if (isset($autovalutazioniArr[$dataValutazione])) {
                        $autovalutazioniArr[$dataValutazione] += $autovalutazioniEnd[$slug[$i]['slug']];
                    } else {
                        $autovalutazioniArr[$dataValutazione] = $autovalutazioniEnd[$slug[$i]['slug']];
                    }
                    $counter = $counter + 1;
                } else continue;
            }
            if (isset($autovalutazioniArr[$dataValutazione])) {

                $autovalutazioniArr[$dataValutazione] = round($autovalutazioniArr[$dataValutazione] / $counter * 2) / 2;
            }
            $counter = $counter - $counter;
        }
        $start = new DateTime($start);
        $end = new DateTime($end);

        $result = [];
        for ($i = $start; $i <= $end; $i->modify("+1 month")) {
            $arr = [
                "field" => $i->format("M"),
                "valutation" => isset($valutazioniArr[$i->format("M")]) ? $valutazioniArr[$i->format("M")] / 5 * 100 : 0,
                "autovalutation" => isset($autovalutazioniArr[$i->format("M")]) ? $autovalutazioniArr[$i->format("M")] / 5 * 100 : 0,
                "mediaValutazione" => isset($autovalutazioniArr[$i->format("M")]) && isset($valutazioniArr[$i->format("M")]) ? (($autovalutazioniArr[$i->format("M")] + $valutazioniArr[$i->format("M")]) / 2) / 5 * 100 : 0,
            ];
            array_push($result, $arr);
        }
        return $result;
    }

    public function GetTestDrive($input)
    {
        $idUser = $input['idUser'];
        $idOrganization = $input['idOrganization'] == 0 ? $input['idUser'] : $input['idOrganization'];

        $sql = "SELECT * FROM testdrive WHERE idDipendente = $idUser";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($tests as $test) {
            $arr = [
                "id" => $test['id'],
                "firstname" => $test['firstname'],
                "lastname" => $test['lastname'],
                "phone" => $test['phone'],
                "veicolo" => $test['veicolo'],
                "idTrattativa" => $test['idTrattativa'],
                "dateTest" => date("d-m-Y", strtotime($test['dateTest'])),
                "startTime" => date("H:i", strtotime($test['startTime'])),
                "endTime" => date("H:i", strtotime($test['endTime'])),
                "button" => [
                    "id" => $test['id'],
                    "firstname" => $test['firstname'],
                    "lastname" => $test['lastname'],
                    "phone" => $test['phone'],
                    "veicolo" => $test['veicolo'],
                    "idTrattativa" => $test['idTrattativa'],
                    "dateTest" => $test['dateTest'],
                    "startTime" => $test['startTime'],
                    "endTime" => $test['endTime'],
                ]
            ];
            array_push($result, $arr);
        }
        return $result;
    }

    public function AddTestDrive($input)
    {

        $idUser = $input['idUser'];
        $idOrganization = $input['idOrganization'] == 0 ? $input['idUser'] : $input['idOrganization'];
        $idTest = $input['idTest'] != 0 || isset($input['idTest']) ? $input['idTest'] : false;
        $firstname = $input['firstname'];
        $lastname = $input['lastname'];
        $phone = $input['phone'];
        $veicolo = $input['veicolo'];
        $idTrattativa = $input['idTrattativa'];
        $dateTest = $input['dateTest'];
        $startTime = $input['startTime'];
        $endTime = $input['endTime'];

        if ($idTest == false) {
            $sql = "INSERT INTO `testdrive`(`firstname`, `lastname`, `phone`, `veicolo`, `idTrattativa`, `dateTest`, 
                    `startTime`, `endTime`, `idDipendente`, `idOrganization`) 
                    VALUES ('$firstname','$lastname','$phone','$veicolo', '$idTrattativa',
                    '$dateTest','$startTime','$endTime',$idUser,$idOrganization)";
        } else {
            $sql = "UPDATE `testdrive` SET `firstname`='$firstname',`lastname`='$lastname',
                    `phone`='$phone',`veicolo`='$veicolo', `idTrattativa`='$idTrattativa', `dateTest`='$dateTest',`startTime`='$startTime',
                    `endTime`='$endTime',`idDipendente`=$idUser,`idOrganization`=$idOrganization WHERE id = $idTest";
        }

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    public function AddObiettiviVenditori($input)
    {
        $idUser = $input['idUser'];
        $idOrganization = $input['idOrganization'] == 0 ? $input['idUser'] : $input['idOrganization'];
        $vendite = $input['vendite'];

        $sql = "SELECT COUNT(*) AS obiettivi FROM obiettivivenditori WHERE idOrganization = $idOrganization";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $count['obiettivi'];

        if ($count == 0) {

            $sql = "INSERT INTO `obiettivivenditori`(`idOrganization`, `venditeMensili`) 
                VALUES ($idOrganization,$vendite)";
        } else {
            $sql = "UPDATE `obiettivivenditori` SET 
            `venditeMensili` = $vendite,`dateCreated`=CURRENT_TIMESTAMP() WHERE idOrganization = $idOrganization";
        }
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return "";
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    public function GetObiettiviVenditori($input)
    {
        $idUser = $input['idUser'];
        $idOrganization = $input['idOrganization'] == 0 ? $input['idUser'] : $input['idOrganization'];

        $sql = "SELECT venditeMensili FROM obiettivivenditori WHERE idOrganization = $idOrganization AND dateCreated=(SELECT MAX(dateCreated))";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $obiettivi = $stmt->fetch(PDO::FETCH_ASSOC);
        $obiettivi = $obiettivi['venditeMensili'];

        return $obiettivi;
    }

    public function DeleteTestDrive($input)
    {
        $idTest = $input['idTest'];

        $sql = "DELETE FROM `testdrive` WHERE id = $idTest";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
    }

    public function GetValutazioniDipartimento($input)
    {
        $idUser = $input['idUtente'];
        $idOrganization = $input['idOrganization'] == 0 ? $input['idUtente'] : $input['idOrganization'];
        $idDepartment = $input['idDepartment'];
        $idRole = $input['idRole'];


        $sql = "SELECT * FROM modules JOIN department ON department.idModule = modules.id WHERE department.id = $idDepartment";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $module = $stmt->fetch(PDO::FETCH_ASSOC);
        $idModule = $module['idModule'];
        $kMinuti = $module['kMinuti'];
        $minutiPausa = $module['minutiPausa'];
        $deltaObiettivi = $module['deltaObiettivi'];
        $oraCheckin = $module['oraCheckin'];
        $oraCheckout = $module['oraCheckout'];

        $sql = "SELECT id FROM user WHERE idDepartment = $idDepartment";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $string = "";
        for ($i = 0; $i < count($users); $i++) {
            if ($i < count($users) - 1) {
                if ($idModule == _CUSTOMERCARE_ || $idModule == _OFFICINA_) {

                    $string .= "exId = " . $users[$i]['id'] . " OR ";
                } else {

                    $string .= "id = " . $users[$i]['id'] . " OR ";
                }
            } else {
                if ($idModule == _CUSTOMERCARE_ || $idModule == _OFFICINA_) {


                    $string .= "exId = " . $users[$i]['id'];
                } else {

                    $string .= "id = " . $users[$i]['id'];
                }
            }
        }

        $start = date("Y-m-d", strtotime("-3 months"));
        $end = date("Y-m-d");

        if ($idModule == _VENDITORI_) {
            if ($idRole == _ADMIN_ || $idRole == _HR_) {

                $sql = "SELECT * FROM testdrive WHERE (dateTest BETWEEN '$start' AND '$end') AND ($string)";
            } else {
                $sql = "SELECT * FROM testdrive WHERE (dateTest BETWEEN '$start' AND '$end') AND idDipendente = $idUser";
            }
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $drives = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sum = 0;
            foreach ($drives as $durataChiamata) {
                $chiamataMinuti = abs(strtotime($durataChiamata["endTime"]) - strtotime($durataChiamata['startTime']));
                $sum += $chiamataMinuti;
            }

            $mediaLavoro = $sum > 0 ? ($sum / count($drives)) / 60 : 0;

            //Raccolgo i dati che occorrono per calcolare obiettivi e ore lavorative
            $timestampCheckin = strtotime($module['oraCheckin']);
            $timestampCheckout = strtotime($module['oraCheckout']);

            $minutiGiornalieri = (abs($timestampCheckout - $timestampCheckin) / 60) - $minutiPausa;
            $minutiK = abs(strtotime("00:00:00") - strtotime($kMinuti)) / 60;
            $tempoMedioAttivita = round($minutiK + $mediaLavoro);
            $targetGiornaliero = round(($minutiGiornalieri * ($deltaObiettivi / 100)) / ($tempoMedioAttivita));

            $start = date("Y-m-d", strtotime("-2 weeks monday"));
            $end = date("Y-m-d", strtotime("-1 weeks sunday"));
            if ($idRole == _ADMIN_ || $idRole == _HR_) {

                $sql = "SELECT COUNT(*) AS testdrives, dateTest FROM testdrive 
                        WHERE (dateTest BETWEEN '$start' AND '$end') AND ($string) 
                        GROUP BY day(dateTest)";
            } else {
                $sql = "SELECT COUNT(*) AS testdrives, dateTest FROM testdrive 
                        WHERE (dateTest BETWEEN '$start' AND '$end') AND idDipendente = $idUser 
                        GROUP BY day(dateTest)";
            }
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $drives = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [0, 0, 0, 0, 0, 0, 0];
            foreach ($drives as $call) {
                $target = round($call['calls'] / $targetGiornaliero * 100);
                $result[date('N', strtotime($call['dataRicevuta'])) - 1] = $target;
            }
        } else if ($idModule == _OFFICINA_) {
            if ($idRole == _ADMIN_ || $idRole == _HR_) {

                $sql = "SELECT * FROM officina WHERE (dataLavoro BETWEEN '$start' AND '$end') AND ($string)";
            } else {
                $sql = "SELECT * FROM officina WHERE (dataLavoro BETWEEN '$start' AND '$end') AND exId = $idUser";
            }
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $works = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sum = 0;
            foreach ($works as $durataChiamata) {
                $chiamataMinuti = abs(strtotime($durataChiamata["oraFine"]) - strtotime($durataChiamata['oraInizio']));
                $sum += $chiamataMinuti;
            }

            $mediaLavoro = $sum > 0 ? ($sum / count($works)) / 60 : 0;

            //Raccolgo i dati che occorrono per calcolare obiettivi e ore lavorative
            $timestampCheckin = strtotime($module['oraCheckin']);
            $timestampCheckout = strtotime($module['oraCheckout']);

            $minutiGiornalieri = (abs($timestampCheckout - $timestampCheckin) / 60) - $minutiPausa;
            $minutiK = abs(strtotime("00:00:00") - strtotime($kMinuti)) / 60;
            $tempoMedioAttivita = round($minutiK + $mediaLavoro);
            $targetGiornaliero = round(($minutiGiornalieri * ($deltaObiettivi / 100)) / ($tempoMedioAttivita));

            $start = date("Y-m-d", strtotime("-2 weeks monday"));
            $end = date("Y-m-d", strtotime("-1 weeks sunday"));
            if ($idRole == _ADMIN_ || $idRole == _HR_) {

                $sql = "SELECT COUNT(*) AS works, dataLavoro FROM officina 
                        WHERE (dataLavoro BETWEEN '$start' AND '$end') AND ($string) 
                        GROUP BY day(dataLavoro)";
            } else {

                $sql = "SELECT COUNT(*) AS works, dataLavoro FROM officina 
                        WHERE (dataLavoro BETWEEN '$start' AND '$end') AND exId = $idUser 
                        GROUP BY day(dataLavoro)";
            }

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $works = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [0, 0, 0, 0, 0, 0, 0];
            foreach ($works as $call) {
                $target = round($call['calls'] / $targetGiornaliero * 100);
                $result[date('N', strtotime($call['dataLavoro'])) - 1] = $target;
            }
        } else if ($idModule == _CUSTOMERCARE_) {
            if ($idRole == _ADMIN_ || $idRole == _HR_) {

                $sql = "SELECT * FROM customercare WHERE (dataRicevuta BETWEEN '$start' AND '$end') AND ($string)";
            } else {
                $sql = "SELECT * FROM customercare WHERE (dataRicevuta BETWEEN '$start' AND '$end') AND exId = $idUser";
            }
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $calls = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sum = 0;
            foreach ($calls as $durataChiamata) {
                $chiamataMinuti = abs(strtotime("00:00:00") - strtotime($durataChiamata['durata']));

                $sum += $chiamataMinuti;
            }

            $mediaLavoro = $sum > 0 ? ($sum / count($calls)) / 60 : 0;

            //Raccolgo i dati che occorrono per calcolare obiettivi e ore lavorative
            $timestampCheckin = strtotime($module['oraCheckin']);
            $timestampCheckout = strtotime($module['oraCheckout']);

            $minutiGiornalieri = (abs($timestampCheckout - $timestampCheckin) / 60) - $minutiPausa;
            $minutiK = abs(strtotime("00:00:00") - strtotime($kMinuti)) / 60;
            $tempoMedioAttivita = round($minutiK + $mediaLavoro);
            $targetGiornaliero = round(($minutiGiornalieri * ($deltaObiettivi / 100)) / ($tempoMedioAttivita));


            $start = date("Y-m-d", strtotime("-2 weeks monday"));
            $end = date("Y-m-d", strtotime("-1 weeks sunday"));
            if ($idRole == _ADMIN_ || $idRole == _HR_) {

                $sql = "SELECT COUNT(*) AS calls, dataRicevuta FROM customercare 
                        WHERE (dataRicevuta BETWEEN '$start' AND '$end') AND ($string) 
                        GROUP BY day(dataRicevuta)";
            } else {

                $sql = "SELECT COUNT(*) AS calls, dataRicevuta FROM customercare 
                        WHERE (dataRicevuta BETWEEN '$start' AND '$end') AND exId = $idUser 
                        GROUP BY day(dataRicevuta)";
            }

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $calls = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [0, 0, 0, 0, 0, 0, 0];
            foreach ($calls as $call) {
                $target = round($call['calls'] / $targetGiornaliero * 100);
                $result[date('N', strtotime($call['dataRicevuta'])) - 1] = $target;
            }
        }




        return $result;
    }

    public function GetLastSettings($input)
    {
        $idOrganization = $input['idOrganization'];
        $idModule = $input['idModule'];

        $sql = "SELECT relation FROM lastsettings WHERE idModule = $idModule AND idOrganization = $idOrganization";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $lastSettings = $stmt->fetch(PDO::FETCH_ASSOC);
            $lastSettings = json_decode($lastSettings['relation'], true);
            return $lastSettings;
        } else {
            return [];
        }
    }
}
