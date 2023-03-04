<?php

require_once '../config/Config.php';

class GeoLocal
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function GetCountries()
    {
        $sql = "SELECT id, country_name FROM countries";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            $countries = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result = [];

            foreach ($countries as $country) {
                $arr = [
                    "value" => $country['id'],
                    "label" => $country['country_name'],
                ];
                array_push($result, $arr);
            }

            return $result;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    public function GetRegions()
    {
        $sql = "SELECT id, nome FROM regioni";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            $regions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result = [];

            foreach ($regions as $region) {
                $arr = [
                    "value" => $region['id'],
                    "label" => $region['nome'],
                ];
                array_push($result, $arr);
            }

            return $result;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    public function GetProvinces($input)
    {
        $idRegion = isset($input['idRegion']) || (int)$input['idRegion'] > 0 || $input['idRegion'] !== "" ? $input['idRegion'] : 0;

        $sql = "SELECT id, nome FROM province WHERE id_regione = $idRegion";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            $provinces = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result = [];

            foreach ($provinces as $province) {
                $arr = [
                    "value" => $province['id'],
                    "label" => $province['nome'],
                ];
                array_push($result, $arr);
            }

            return $result;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    public function GetCities($input)
    {
        $idProvince = $input['idProvince'];

        $sql = "SELECT id, nome FROM comuni WHERE id_provincia = $idProvince";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            $cities = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result = [];

            foreach ($cities as $city) {
                $arr = [
                    "value" => $city['id'],
                    "label" => $city['nome'],
                ];
                array_push($result, $arr);
            }

            return $result;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }
}
