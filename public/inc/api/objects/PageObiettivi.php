<?php

require_once '../config/Config.php';
require_once '../config/Costanti.php';

class PageObiettivi
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function minutes($time)
    {
        $time = explode(':', $time);
        return ($time[0] * 60) + ($time[1]) + ($time[2] / 60);
    }

    function weeks_in_month($month, $year)
    {
        // Start of month
        $start = mktime(0, 0, 0, $month, 1, $year);
        // End of month
        $end = mktime(0, 0, 0, $month, date('t', $start), $year);
        // Start week
        $start_week = date('W', $start);
        // End week
        $end_week = date('W', $end);

        if ($end_week < $start_week) { // Month wraps
            return ((52 + $end_week) - $start_week) + 1;
        }

        return ($end_week - $start_week) + 1;
    }

    function days_between($datefrom, $dateto)
    {
        $fromday_start = mktime(0, 0, 0, date("m", $datefrom), date("d", $datefrom), date("Y", $datefrom));
        $diff = $dateto - $datefrom;
        $days = intval($diff / 86400); // 86400  / day

        if (($datefrom - $fromday_start) + ($diff % 86400) > 86400)
            $days++;

        return  $days;
    }

    function weeks_between($datefrom, $dateto)
    {
        $day_of_week = date("w", $datefrom);
        $fromweek_start = $datefrom - ($day_of_week * 86400) - ($datefrom % 86400);
        $diff_days = $this->days_between($datefrom, $dateto);
        $diff_weeks = intval($diff_days / 7);
        $seconds_left = ($diff_days % 7) * 86400;

        if (($datefrom - $fromweek_start) + $seconds_left > 604800)
            $diff_weeks++;

        return $diff_weeks;
    }

    public function GetDepartments($input)
    {
        $idModule = $input['idModule'];
        $idOrganization = $input['idOrganization'];

        $sql = "SELECT * FROM department WHERE idModule = $idModule AND idOrganization = $idOrganization";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [];
            foreach ($departments as $department) {
                $arr = [
                    "label" => strtoupper($department['name']),
                    "value" => $department['id'],
                ];
                array_push($result, $arr);
            }

            return $result;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    public function GetUsers($input)
    {
        $idDepartment = $input['idDepartment'];

        $sql = "SELECT user_data.firstname, user_data.lastname, user_data.id_user FROM user_data 
        JOIN user ON user.id = user_data.id_user 
        WHERE user.idDepartment = $idDepartment";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            $result = [];
            if ($stmt->rowCount() > 0) {
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($users as $user) {
                    $arr = [
                        "label" => ucwords($user['firstname']) . " " . ucwords($user['lastname']),
                        "value" => $user['id_user'],
                    ];
                    array_push($result, $arr);
                }
            }
            return $result;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    public function GetRangeDate($input)
    {
        $idUser = $input['idUser'];

        $sql = "SELECT MIN(dateTest) AS minDate, MAX(dateTest) AS maxDate FROM testdrive WHERE idDipendente = $idUser;";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $ranges = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [];
            foreach ($ranges as $range) {
                $arr = [
                    "minDate" => isset($range['minDate']) ?  date("Y-m-d", strtotime($range['minDate'])) : date("Y-m-d"),
                    "maxDate" => isset($range['maxDate']) ? date("Y-m-d", strtotime($range['maxDate'])) : date("Y-m-d"),
                ];
                array_push($result, $arr);
            }

            return $result;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    public function GetRangeDateModules($input)
    {
        $idUser = $input['idUser'];
        $idModule = $input['idModule'];

        if ($idModule == _OFFICINA_) {
            $sql = "SELECT MIN(dataLavoro) AS minDate, MAX(dataLavoro) AS maxDate FROM officina WHERE exId = $idUser;";
        } else if ($idModule == _VENDITORI_) {
            $sql = "SELECT MIN(dateCreated) AS minDate, MAX(dateCreated) AS maxDate FROM venditori WHERE idVenditore = $idUser";
        } else if ($idModule == _CUSTOMERCARE_) {
            $sql = "SELECT MIN(dataRicevuta) AS minDate, MAX(dataRicevuta) AS maxDate FROM customercare WHERE exId = $idUser;";
        }

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $ranges = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [];

            foreach ($ranges as $range) {
                $arr = [
                    "minDate" => isset($range['minDate']) ?  date("Y-m-d", strtotime($range['minDate'])) : date("Y-m-d"),
                    "maxDate" => isset($range['maxDate']) ? date("Y-m-d", strtotime($range['maxDate'])) : date("Y-m-d"),
                ];
                array_push($result, $arr);
            }

            return $result;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    public function GetCallsProgressBar($input)
    {
        $idUser = $input['idUser'];

        $start = date("Y-m-d", strtotime("-3 months monday"));
        $end = date("Y-m-d");

        $sql = "SELECT customercare.*, department.oraCheckin, department.oraCheckout, 
                department.minutiPausa, department.deltaObiettivi, department.kMinuti 
                FROM customercare JOIN user ON customercare.exId = user.id
                JOIN department ON user.idDepartment = department.id
                WHERE customercare.exId = $idUser AND customercare.dataRicevuta BETWEEN '$start' AND '$end'";

        $oneMonth = [
            "start" => date("Y-m-d", strtotime("-1 month monday")),
            "end" => date("Y-m-d"),
        ];
        $oneWeek = [
            "start" => date('N') == 1 ? date('Y-m-d') : date('Y-m-d', strtotime('last monday')),
            "end" => date('N') == 7 ? date('Y-m-d') : date('Y-m-d', strtotime('next sunday'))

        ];

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $calls = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($stmt->rowCount() > 0) {
                $kMinuti = (strtotime($calls[0]["kMinuti"]) - strtotime("00:00:00"));
                $deltaObiettivi = $calls[0]['deltaObiettivi'];
                $minutiLavoro = round((strtotime($calls[0]['oraCheckout']) - strtotime($calls[0]['oraCheckin'])) / 60);
                $minutiLavoro = $minutiLavoro - $calls[0]['minutiPausa'];
                $effettiviLavoro = round(($minutiLavoro * $deltaObiettivi) / 100);
                $effettiviSettimana = $effettiviLavoro * 5;
                $effettiviMese = $this->weeks_in_month(date("m"), date("y")) * $effettiviSettimana;

                $numberCalls = [
                    "weekCalls" => 0,
                    "monthCalls" => 0,
                    "percentageDurationMonth" => 0,
                    "percentageDurationWeek" => 0,
                    "percentageWeek" => 0,
                    "percentageMonth" => 0,
                    "obiettivoSettimanale" => 0,
                    "obiettivoMensile" => 0,
                    "percentageWeekHours" => 0,
                    "percentageMonthHours" => 0,
                ];

                $sommaDurata = 0;
                $durataMese = 0;
                $durataSettimana = 0;

                foreach ($calls as $call) {

                    $durata = strtotime($call['durata']) - strtotime("00:00:00");

                    $sommaDurata += $durata + $kMinuti;

                    if ($call['dataRicevuta'] <= $oneWeek['end'] && $call['dataRicevuta'] >= $oneWeek['start']) {
                        $numberCalls['weekCalls'] += 1;
                        $durataSettimana += $durata + $kMinuti;
                    }

                    if ($call['dataRicevuta'] <= $oneMonth['end'] && $call['dataRicevuta'] >= $oneMonth['start']) {
                        $numberCalls['monthCalls'] += 1;
                        $durataMese += $durata + $kMinuti;
                    }
                }
                $sommaDurata = round($sommaDurata / 60);
                $mediaDurata = round($sommaDurata / $stmt->rowCount());
                $numberCalls['obiettivoSettimanale'] = round($minutiLavoro / $mediaDurata) * 5;
                $numberCalls['obiettivoMensile'] = $numberCalls['obiettivoSettimanale'] * $this->weeks_in_month(date("m"), date("y"));
                $numberCalls['percentageWeek'] = $numberCalls['weekCalls'] > 0 && $numberCalls['obiettivoSettimanale'] ? round(100 * $numberCalls['weekCalls'] / $numberCalls['obiettivoSettimanale'], 1) : 0;
                $numberCalls['percentageMonth'] = $numberCalls['monthCalls'] > 0 && $numberCalls['obiettivoMensile'] ? round(100 * $numberCalls['monthCalls'] / $numberCalls['obiettivoMensile'], 1) : 0;

                $numberCalls['percentageDurationWeek'] = round(100 * ($durataSettimana / 60) / $effettiviSettimana, 1);
                $numberCalls['percentageDurationMonth'] = round(100 * ($durataMese / 60) / $effettiviMese, 1);

                return $numberCalls;
            } else return [];
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    public function GetWorksProgressBar($input)
    {
        $idUser = $input['idUser'];

        $start = date("Y-m-d", strtotime("-3 months monday"));
        $end = date("Y-m-d");

        $sql = "SELECT officina.*, department.oraCheckin, department.oraCheckout, 
                department.minutiPausa, department.deltaObiettivi, department.kMinuti 
                FROM officina JOIN user ON officina.exId = user.id
                JOIN department ON user.idDepartment = department.id
                WHERE officina.exId = $idUser AND officina.dataLavoro BETWEEN '$start' AND '$end'";

        $oneMonth = [
            "start" => date("Y-m-d", strtotime("-1 month monday")),
            "end" => date("Y-m-d"),
        ];
        $oneWeek = [
            "start" => date('N') == 1 ? date('Y-m-d') : date('Y-m-d', strtotime('last monday')),
            "end" => date('N') == 7 ? date('Y-m-d') : date('Y-m-d', strtotime('next sunday'))

        ];

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $calls = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($stmt->rowCount() > 0) {
                $kMinuti = (strtotime($calls[0]["kMinuti"]) - strtotime("00:00:00"));
                $deltaObiettivi = $calls[0]['deltaObiettivi'];
                $minutiLavoro = round((strtotime($calls[0]['oraCheckout']) - strtotime($calls[0]['oraCheckin'])) / 60);
                $minutiLavoro = $minutiLavoro - $calls[0]['minutiPausa'];
                $effettiviLavoro = round(($minutiLavoro * $deltaObiettivi) / 100);
                $effettiviSettimana = $effettiviLavoro * 5;
                $effettiviMese = $this->weeks_in_month(date("m"), date("y")) * $effettiviSettimana;

                $numberCalls = [
                    "weekWorks" => 0,
                    "monthWorks" => 0,
                    "percentageDurationMonth" => 0,
                    "percentageDurationWeek" => 0,
                    "percentageWeek" => 0,
                    "percentageMonth" => 0,
                    "obiettivoSettimanale" => 0,
                    "obiettivoMensile" => 0,
                    "percentageWeekHours" => 0,
                    "percentageMonthHours" => 0,
                ];

                $sommaDurata = 0;
                $durataMese = 0;
                $durataSettimana = 0;

                foreach ($calls as $call) {

                    $durata = strtotime($call['oraFine']) - strtotime($call['oraInizio']);

                    $sommaDurata += $durata + $kMinuti;

                    if ($call['dataLavoro'] <= $oneWeek['end'] && $call['dataLavoro'] >= $oneWeek['start']) {
                        $numberCalls['weekWorks'] += 1;
                        $durataSettimana += $durata + $kMinuti;
                    }

                    if ($call['dataLavoro'] <= $oneMonth['end'] && $call['dataLavoro'] >= $oneMonth['start']) {
                        $numberCalls['monthWorks'] += 1;
                        $durataMese += $durata + $kMinuti;
                    }
                }
                $sommaDurata = round($sommaDurata / 60);
                $mediaDurata = round($sommaDurata / $stmt->rowCount());
                $numberCalls['obiettivoSettimanale'] = round($minutiLavoro / $mediaDurata) * 5;
                $numberCalls['obiettivoMensile'] = $numberCalls['obiettivoSettimanale'] * $this->weeks_in_month(date("m"), date("y"));
                $numberCalls['percentageWeek'] = $numberCalls['weekWorks'] > 0 && $numberCalls['obiettivoSettimanale'] ? round(100 * $numberCalls['weekWorks'] / $numberCalls['obiettivoSettimanale'], 1) : 0;
                $numberCalls['percentageMonth'] = $numberCalls['monthWorks'] > 0 && $numberCalls['obiettivoMensile'] ? round(100 * $numberCalls['monthWorks'] / $numberCalls['obiettivoMensile'], 1) : 0;

                $numberCalls['percentageDurationWeek'] = round(100 * ($durataSettimana / 60) / $effettiviSettimana, 1);
                $numberCalls['percentageDurationMonth'] = round(100 * ($durataMese / 60) / $effettiviMese, 1);

                return $numberCalls;
            } else return [];
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    public function GetContractsProgressBar($input)
    {
        $idUser = $input['idUser'];
        $start = date("Y-m-d", strtotime("-3 months monday"));
        $end = date("Y-m-d");

        $sql = "SELECT testdrive.*, department.oraCheckin, department.oraCheckout, 
                department.minutiPausa, department.deltaObiettivi, department.kMinuti 
                FROM testdrive JOIN user ON testdrive.idDipendente = user.id
                JOIN department ON user.idDepartment = department.id
                WHERE testdrive.idDipendente = $idUser AND testdrive.dateTest BETWEEN '$start' AND '$end'";

        $oneMonth = [
            "start" => date("Y-m-d", strtotime("-1 month monday")),
            "end" => date("Y-m-d"),
        ];
        $oneWeek = [
            "start" => date('N') == 1 ? date('Y-m-d') : date('Y-m-d', strtotime('last monday')),
            "end" => date('N') == 7 ? date('Y-m-d') : date('Y-m-d', strtotime('next sunday'))

        ];

        $period = [
            "start" => date("Y-m-d", strtotime($input['startDate'])),
            "end" => date("Y-m-d", strtotime($input['endDate'])),
        ];

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $calls = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($stmt->rowCount() > 0) {
                $kMinuti = (strtotime($calls[0]["kMinuti"]) - strtotime("00:00:00"));
                $deltaObiettivi = $calls[0]['deltaObiettivi'];
                $minutiLavoro = round((strtotime($calls[0]['oraCheckout']) - strtotime($calls[0]['oraCheckin'])) / 60);
                $minutiLavoro = $minutiLavoro - $calls[0]['minutiPausa'];
                $effettiviLavoro = round(($minutiLavoro * $deltaObiettivi) / 100);
                $effettiviSettimana = $effettiviLavoro * 5;
                $effettiviMese = $this->weeks_in_month(date("m"), date("y")) * $effettiviSettimana;

                $numberCalls = [
                    "weekWorks" => 0,
                    "monthWorks" => 0,
                    "periodWorks" => 0,
                    "percentageDurationMonth" => 0,
                    "percentageDurationWeek" => 0,
                    "percentageWeek" => 0,
                    "percentageMonth" => 0,
                    "percentagePeriod" => 0,
                    "obiettivoSettimanale" => 0,
                    "obiettivoMensile" => 0,
                    "obiettivoPeriod" => 0,
                ];

                $sommaDurata = 0;

                foreach ($calls as $call) {

                    $durata = strtotime($call['endTime']) - strtotime($call['startTime']);

                    $sommaDurata += $durata + $kMinuti;

                    if ($call['dateTest'] <= $oneWeek['end'] && $call['dateTest'] >= $oneWeek['start']) {
                        $numberCalls['weekWorks'] += 1;
                    }

                    if ($call['dateTest'] <= $oneMonth['end'] && $call['dateTest'] >= $oneMonth['start']) {
                        $numberCalls['monthWorks'] += 1;
                    }

                    if ($call['dateTest'] <= $period['end'] && $call['dateTest'] >= $period['start']) {
                        $numberCalls['periodWorks'] += 1;
                    }
                }
                $sommaDurata = round($sommaDurata / 60);
                $mediaDurata = round($sommaDurata / $stmt->rowCount());
                $obiettivoGiornaliero = round($minutiLavoro / $mediaDurata);
                $numberCalls['obiettivoSettimanale'] = round($minutiLavoro / $mediaDurata) * 5;
                $numberCalls['obiettivoMensile'] = $numberCalls['obiettivoSettimanale'] * $this->weeks_in_month(date("m"), date("y"));
                $numberCalls['obiettivoPeriod'] = $this->days_between(strtotime($period['start']), strtotime($period['end'])) > 0 ? $obiettivoGiornaliero * $this->days_between(strtotime($period['start']), strtotime($period['end'])) : $obiettivoGiornaliero;
                $numberCalls['percentageWeek'] = $numberCalls['weekWorks'] > 0 && $numberCalls['obiettivoSettimanale'] > 0 ? round(100 * $numberCalls['weekWorks'] / $numberCalls['obiettivoSettimanale'], 1) : 0;
                $numberCalls['percentageMonth'] = $numberCalls['monthWorks'] > 0 && $numberCalls['obiettivoMensile'] > 0 ? round(100 * $numberCalls['monthWorks'] / $numberCalls['obiettivoMensile'], 1) : 0;
                $numberCalls['percentagePeriod'] = $numberCalls['periodWorks'] > 0 && $numberCalls['obiettivoPeriod'] > 0 ? round(100 * $numberCalls['periodWorks'] / $numberCalls['obiettivoPeriod'], 1) : 0;
                return $numberCalls;
            } else return [];
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }
}
