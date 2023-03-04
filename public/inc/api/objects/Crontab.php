<?php

require_once "../config/Config.php";

class Crontab
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function Autocheckin()
    {
        $sql = "SELECT user.id, user.id_organization, department.oraCheckin, department.oraCheckout 
                FROM user JOIN department ON user.idDepartment = department.id
                WHERE department.autoCheckin = 1";

        $today = date("Y-m-d");
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($users as $user) {
                $id = $user['id'];
                $idOrganization = (int)$user['id_organization'] === 0 ? $user['id'] : $user['id_organization'];
                $oraCheckin = date("H:i", strtotime($user['oraCheckin']));
                $oraCheckout = date("H:i", strtotime($user['oraCheckout']));

                $sql = "INSERT INTO `presenze`(`idUser`, `idOrganization`, `allDay`, `title`, `startDate`, 
                        `startTime`, `endDate`, `endTime`, `description`, `confirmed`) 
                        VALUES ($id,$idOrganization,0,'Presenza','$today','$oraCheckin','$today',
                        '$oraCheckout','presenza',0)";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();

                return [];
            }
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }
}
