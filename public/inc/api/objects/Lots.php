<?php
require_once '../config/Config.php';
require 'Products.php';

class Lots
{

    // var connessione al db e tabella

    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function GetNextSku($output)
    {
        $id_producer = $output["id_producer"];


        // DETECT ID_PRODUCER 
        $sql_producer = "SELECT id_producer FROM users WHERE id_producer = $id_producer AND id_producer != 0";

        //preparo l'istruzione
        $stmt_producer = $this->conn->prepare($sql_producer);
        //execute query
        $stmt_producer->execute();

        if ($stmt_producer->rowCount() > 0) {
            $producer = $stmt_producer->fetch(PDO::FETCH_ASSOC);
        } else {

            $sql_user = "SELECT id_producer FROM users WHERE id_user = $id_producer AND id_producer != 0";

            //preparo l'istruzione
            $stmt_user = $this->conn->prepare($sql_user);

            //execute query
            $stmt_user->execute();

            if ($stmt_user->rowCount() > 0) {
                $producer = $stmt_user->fetch(PDO::FETCH_ASSOC);
            }
        }
        // DETECT ID_PRODUCER END

        // DETECT ID_lot
        $sql_lot = "SELECT (COUNT(*) + 1) as total_lot FROM lots WHERE id_producer = $id_producer";

        //preparo l'istruzione
        $stmt_lot = $this->conn->prepare($sql_lot);
        //execute query
        $stmt_lot->execute();
        $lot = $stmt_lot->fetch(PDO::FETCH_ASSOC);
        // DETECT ID_PRODUCE END

        $identification_lot = "L";
        $id_producer = str_pad($producer["id_producer"], 2, '0', STR_PAD_LEFT);
        $id_normalized_lot = str_pad($lot["total_lot"], 3, '0', STR_PAD_LEFT);
        $year = date("Y");
        $year = substr($year, -2);

        $return = $identification_lot . $id_producer . $id_normalized_lot . $year;

        return $return;
    }

    // Lot handling in the warehouse
    public function handlingLot($id_user, $id_lot, $quantity, $causal, $id_auction = false)
    {

        if ($id_auction) {
            $sql = "INSERT INTO lots_handling (id_user,id_lot,quantity,causal,id_auction) VALUES ($id_user,$id_lot,$quantity,'$causal','$id_auction')";
        } else {
            $sql = "INSERT INTO lots_handling (id_user,id_lot,quantity,causal) VALUES ($id_user,$id_lot,$quantity,'$causal')";
        }

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        return $stmt->rowCount();
    }

    // Upload the img of lot
    private function uploadPhoto($id_lot, $file_tmp, $file_error)
    {
        //upload del file
        if (0 < $file_error) {
            return false;
        } else {

            $file_name = $id_lot . '.png';

            if (strpos($_SERVER['SERVER_NAME'], "localhost")) {
                $path = "../../public/media/lots/";
            } else {
                $path = "../../media/lots/";
            }

            if (move_uploaded_file($file_tmp, $path . $file_name)) {

                $photo = '/media/lots/' . $file_name;

                $sql = "UPDATE lots SET 
                photo = '$photo'
                WHERE id_lot = '$id_lot'";

                //preparo l'istruzione
                $stmt = $this->conn->prepare($sql);
                //execute query
                $stmt->execute();


                return true;
            } else {
                return false;
            }
        }
    }

    // Upload the handling product of lot
    private function handlingProductOfLot($id_user, $id_lot, $products, $quantity)
    {
        //$products = json_decode($products, true);
        $num_products = count($products);
        $return_true = 0;

        foreach ($products as $key => $product) {

            $quantity_chosen_for_lot = intval($product["quantity_chosen"]);

            $quantity_chosen_for_product = "-" . (intval($product["quantity_chosen"]) * $quantity);
            $quantity_chosen_for_product = intval($quantity_chosen_for_product);

            $products_call = new Products($this->conn);
            $handlingProduct = $products_call->handlingProduct($id_user, $product["id_product"], $quantity_chosen_for_product, 'LOT-NEW-STOCK', $id_lot);

            if ($handlingProduct) {
                $return_true++;
            }
        }

        if ($num_products == $return_true) {
            return true;
        }
    }

    // Upload the handling product of lot
    private function AddProductOfLot($id_user, $id_lot, $products)
    {
        $products = json_decode($products, true);
        $num_products = count($products);
        $return_true = 0;

        foreach ($products as $key => $product) {

            $quantity_chosen_for_lot = intval($product["quantity_chosen"]);

            $sql = "INSERT INTO lots_products (id_lot,id_product,quantity) VALUES ('$id_lot','" . $product["id_product"] . "','$quantity_chosen_for_lot')";

            //preparo l'istruzione
            $stmt = $this->conn->prepare($sql);

            //execute query
            $stmt->execute();

            if ($stmt->rowCount()) {
                $return_true++;
            }
        }

        if ($num_products == $return_true) {
            return true;
        }
    }

    // Story of Product handling in the warehouse
    public function storyHandlingLot($output)
    {
        $id_lot = $output["id_lot"];
        $sql = "SELECT * FROM lots_handling LEFT JOIN auctions ON lots_handling.id_auction = auctions.id_auction LEFT JOIN users ON users.id_user = lots_handling.id_user WHERE lots_handling.id_lot = $id_lot ORDER BY handling_date ASC";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = array();
        foreach ($items as $key => $item) {
            $result[] = $item;
            $result[$key]["causal"] = ucfirst(strtolower(str_replace("-", " ", $item["causal"])));
        }


        return $result;
    }

    // Create lot
    public function CreateLot($output, $file)
    {

        $id_user = $output["id_user"];
        $id_producer = $output["id_producer"];
        $sku = $output["sku"];
        $case_ = addslashes($output["case_"]);
        $products = $output["products"];
        $available_date = $output["available_date"];

        $sql1 = "SELECT * FROM lots WHERE sku = '$sku' AND deleted=0";

        //preparo l'istruzione
        $stmt1 = $this->conn->prepare($sql1);

        //execute query
        $stmt1->execute();

        if ($stmt1->rowCount() == 0) {

            $sql = "INSERT INTO lots (id_user,id_producer,sku,case_,available_date,stato) VALUES ('$id_user','$id_producer','$sku','$case_','$available_date',1)";

            //preparo l'istruzione
            $stmt = $this->conn->prepare($sql);

            //execute query
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                $id_lot = $this->conn->lastInsertId();

                $AddProductOfLot =  $this->AddProductOfLot($id_user, $id_lot, $products);

                if ($AddProductOfLot) {
                    return true;
                }
            }
        } else {
            return -1;
        }
    }

    // Edit lot
    public function EditLot($output, $file)
    {
        $id_lot = $output["id_lot"];
        $id_user = $output["id_user"];
        $id_producer = $output["id_producer"];
        $sku = $output["sku"];
        $case_ = addslashes($output["case_"]);
        $available_date = $output["available_date"];

        $sql1 = "SELECT * FROM lots WHERE id_lot = $id_lot AND deleted = 0";

        //preparo l'istruzione
        $stmt1 = $this->conn->prepare($sql1);

        //execute query
        $stmt1->execute();

        if ($stmt1->rowCount() > 0) {

            $sql2 = "SELECT * FROM lots WHERE sku = '$sku'";
            //preparo l'istruzione
            $stmt2 = $this->conn->prepare($sql2);
            //execute query
            $stmt2->execute();

            $lot = $stmt1->fetch(PDO::FETCH_ASSOC);
            $sku_actual = $lot["sku"];

            $sql = "UPDATE lots SET 
            id_producer = '$id_producer',
            sku = '$sku',
            case_ = '$case_',
            available_date = '$available_date'
            WHERE id_lot = '$id_lot'";

            if ($stmt2->rowCount() > 0) {

                if ($sku_actual == $sku) {

                    //preparo l'istruzione
                    $stmt = $this->conn->prepare($sql);

                    //execute query
                    $stmt->execute();
                } else {
                    return -1;
                }
            } else {

                //preparo l'istruzione
                $stmt = $this->conn->prepare($sql);

                //execute query
                $stmt->execute();
            }


            if ($file) {

                $photo_name = $file["name"];
                $photo_tmp_name = $file["tmp_name"];
                $photo_error = $file["error"];

                $photo = $this->uploadPhoto($id_lot, $photo_tmp_name, $photo_error);
            }

            if (isset($photo) && $photo || $stmt->rowCount() > 0) {
                return true;
            }
        } else {
            return -1;
        }
    }

    public function NewStockLot($output)
    {

        $id_user = $output["id_user"];
        $id_lot = $output["id_lot"];
        $quantity = $output["quantity"];

        $handlingLot = $this->handlingLot($id_user, $id_lot, $quantity, 'LOT-NEW-STOCK');

        if ($handlingLot) {

            $sql = "SELECT id_product,quantity as quantity_chosen FROM lots_products WHERE id_lot = $id_lot";

            //preparo l'istruzione
            $stmt = $this->conn->prepare($sql);
            //execute query
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $handlingProductOfLot =  $this->handlingProductOfLot($id_user, $id_lot, $products, $quantity);

            if ($handlingProductOfLot) {
                return true;
            }
        }
    }

    // Edit Stato of Lot
    public function EditLotStato($output)
    {
        $id_lot = $output["id_lot"];
        $stato = $output["stato"];

        $sql = "UPDATE lots SET 
        stato = '$stato'

        WHERE id_lot = '$id_lot'";
        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        return $stmt->rowCount();
    }

    // Delete Lot
    public function DeleteLot($output)
    {

        $id_user = $output["id_user"];
        $id_lot = $output["id_lot"];

        $sql = "UPDATE lots SET 
        deleted = -1

        WHERE id_lot = '$id_lot'";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $quantity_lot = intval("-" . $this->GetQuantityLot($id_lot));

            $handlingLot = $this->handlingLot($id_user, $id_lot, $quantity_lot, 'LOT-DELETED');

            if ($handlingLot) {

                $products_lot = $this->GetQuantityPorductOfLot($id_lot);

                $num_products = count($products_lot);
                $return_true = 0;

                foreach ($products_lot as $key => $product) {

                    $quantity_product = intval($product["quantity"]);
                    if ($quantity_product < 0) {
                        $quantity_product = $quantity_product * -1;
                    }

                    $products_call = new Products($this->conn);
                    $handlingProduct = $products_call->handlingProduct($id_user, $product["id_product"], $quantity_product, 'LOT-DELETED', $id_lot);

                    if ($handlingProduct) {
                        $return_true++;
                    }
                }

                if ($num_products == $return_true) {
                    return true;
                }
            }
        }
    }

    // Get quantity of lot
    private function GetQuantityLot($id_lot)
    {

        $sql = "SELECT SUM(quantity) as total_quantity FROM lots_handling WHERE id_lot = $id_lot";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $lot = $stmt->fetch(PDO::FETCH_ASSOC);


        return intval($lot["total_quantity"]);
    }

    // Get product quantity of lot
    private function GetQuantityPorductOfLot($id_lot)
    {

        $sql = "SELECT id_product,quantity FROM products_handling WHERE id_lot = $id_lot";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $products_lot = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $products_lot;
    }

    // Get number of Handling of lot
    private function GetNumHandlingLot($id_lot)
    {

        $sql_initial = "SELECT quantity FROM lots_handling WHERE id_lot = $id_lot AND causal ='LOT-NEW-STOCK'";

        //preparo l'istruzione
        $stmt_initial = $this->conn->prepare($sql_initial);

        //execute query
        $stmt_initial->execute();
        $initial = $stmt_initial->fetch(PDO::FETCH_ASSOC);

        $sql_total = "SELECT SUM(quantity) as quantity FROM lots_handling WHERE id_lot = $id_lot";

        //preparo l'istruzione
        $stmt_total = $this->conn->prepare($sql_total);

        //execute query
        $stmt_total->execute();
        $total = $stmt_total->fetch(PDO::FETCH_ASSOC);


        if ($initial["quantity"] == $total["quantity"]) {

            $return = 0;
        } else {

            $sql = "SELECT COUNT(*) as total_handling FROM lots_handling WHERE id_lot = $id_lot AND causal !='LOT-NEW-STOCK'";

            //preparo l'istruzione
            $stmt = $this->conn->prepare($sql);

            //execute query
            $stmt->execute();

            $lot = $stmt->fetch(PDO::FETCH_ASSOC);

            $return = $lot["total_handling"];
        }

        return $return;
    }

    // Get the products of lot
    private function GetProductOfLot($id_lot)
    {
        $sql1 = "SELECT * FROM lots_products WHERE id_lot= $id_lot";

        //preparo l'istruzione
        $stmt1 = $this->conn->prepare($sql1);

        //execute query
        $stmt1->execute();
        $lots_products = $stmt1->fetchAll(PDO::FETCH_ASSOC);
        $result = array();

        $user_call = new Users($this->conn);
        $products_call = new Products($this->conn);
        foreach ($lots_products as $key => $lot_product) {

            $sql = "SELECT *, SUM(quantity) as quantity_available FROM products LEFT JOIN products_handling ON products_handling.id_product = products.id_product WHERE products.id_product = " . $lot_product["id_product"] . "";

            //preparo l'istruzione
            $stmt = $this->conn->prepare($sql);

            //execute query
            $stmt->execute();

            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            $user = $user_call->GetUserInfo($product["id_producer"]);

            $result[] = $product;

            $result[$key]["quantity"] = $lot_product["quantity"];
            $result[$key]["producer_name"] = $user["fullname"];

            $result[$key]["NumHandling"] = $products_call->GetNumHandlingProduct($product["id_product"]);
            $result[$key]["id_producer_real"] = $user["id_producer"];

            $appellation = $product["appellation"] ? $product["appellation"] : "";
            $classification = $product["classification"] ? $product["classification"] : "";
            $clos = $product["clos"] ? $product["clos"] : "";
            $size = $product["size"] ? $product["size"]  : "";
            $vintage = $product["vintage"] ? $product["vintage"] : "";
            $producer_name = $user["fullname"];
            $result[$key]["nameProduct"] = "$producer_name $appellation $classification $clos $vintage $size";
            $result[$key]["onlyNameProduct"] = "$appellation $classification $clos $vintage $size";
            $result[$key]["notes"] = array("level_of_wine" => $product["level_of_wine"], "cork" => $product["cork"], "label" => $product["label"]);
        }

        return $result;
    }

    // Get the information of the lot, it is declared in other functions
    private function GetLot($lots)
    {

        $result = array();
        foreach ($lots as $key => $lot) {

            $user_call = new Users($this->conn);
            $user = $user_call->GetUserInfo($lot["id_producer"]);

            $ProductOfLot = $this->GetProductOfLot($lot["id_lot"]);
            $QuantityLot = $this->GetQuantityLot($lot["id_lot"]);

            $export_price = 0;
            $packages = array();

            foreach ($ProductOfLot as $product) {

                $product_total_price = $product["price"] * $product["quantity"];

                $export_price = $export_price + $product_total_price;
            }

            $result[] = $lot;

            $availableDateInput = date("Y-m-d", strtotime($lot["available_date"]));
            $result[$key]["availableDateInput"] = $availableDateInput;

            $availableDateFormat = date("F Y", strtotime($lot["available_date"]));
            $result[$key]["available_date"] = $availableDateFormat;

            $result[$key]["quantity"] = $QuantityLot;
            $result[$key]["NumHandling"] = $this->GetNumHandlingLot($lot["id_lot"]);
            $result[$key]["producer_name"] = $user["fullname"];
            $result[$key]["exportPrice"] = $export_price;
            $result[$key]["products"] = $ProductOfLot;

            $result[$key]["storyHandlingLot"] = $this->storyHandlingLot(array("id_lot" => $lot["id_lot"]));
        }

        // if($lot["sku"] == "L0100121"){
        //     print_r($ProductOfLot);
        // }

        return $result;
    }

    // Get Single Lot
    public function GetLotSingle($output)
    {

        $id_lot = $output["id_lot"];

        $sql = "SELECT * FROM lots WHERE deleted = 0 AND id_lot=$id_lot";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $result = array();
        $lots = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->GetLot($lots);
    }

    // Get All Lot
    public function GetAllLots($output)
    {
        $id_user = $output["id_user"];

        $user_call = new Users($this->conn);
        $user = $user_call->GetUserInfo($id_user);

        if ($user["roles"] == 1 || $user["roles"] == 99) {
            $sql = "SELECT * FROM lots WHERE deleted = 0 ORDER BY id_lot DESC";
        } else {
            $sql = "SELECT * FROM lots WHERE id_producer = $id_user AND deleted = 0 ORDER BY id_lot DESC";
        }

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $result = array();
        $lots = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->GetLot($lots);
    }

    // Get All Active Lot
    public function GetAllActiveLots($output)
    {
        $id_user = $output["id_user"];

        $user_call = new Users($this->conn);
        $user = $user_call->GetUserInfo($id_user);

        if ($user["roles"] == 1 || $user["roles"] == 99) {
            $sql = "SELECT * FROM lots WHERE stato = 1 AND deleted = 0 ORDER BY id_lot DESC";
        } else {
            $sql = "SELECT * FROM lots WHERE id_producer = $id_user AND stato = 1 AND deleted = 0 ORDER BY id_lot DESC";
        }

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $result = array();
        $lots = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->GetLot($lots);
    }

    public function SincronizeHandlingLots()
    {


        $sql = "SELECT *, auctions_participant.id_user as id_user FROM auctions_participant LEFT JOIN auctions ON auctions_participant.id_auction = auctions.id_auction WHERE auctions_participant.is_winner = 1 AND (status_availability = '0' AND auctions_participant.payment_deadline > NOW() OR status_availability != '0') AND auctions_participant.deleted = 0";


        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();
        $auctions_participant = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $i = 0;
        $i2 = 0;
        foreach ($auctions_participant as $auction_participant) {
            $i2++;

            $id_auction = $auction_participant["id_auction"];
            $id_user = $auction_participant["id_user"];
            $quantity_winner = $auction_participant["quantity_winner"];
            $id_lot = $auction_participant["id_lot"];

            $sql_control = "SELECT * FROM lots_handling WHERE id_auction = $id_auction AND id_user = $id_user AND id_lot = $id_lot AND causal = 'AUCTION-WIN'";
            //preparo l'istruzione
            $stmt_control = $this->conn->prepare($sql_control);

            //execute query
            $stmt_control->execute();

            if ($stmt_control->rowCount() == 0) {

                $quantity_chosen_for_lot = "-" . intval($quantity_winner);
                $quantity_chosen_for_lot = intval($quantity_chosen_for_lot);

                $handlingLot = $this->handlingLot($id_user, $id_lot, $quantity_chosen_for_lot, 'AUCTION-WIN', $id_auction);
                //$handlingLot = 1;
                if ($handlingLot) {
                    $i++;
                }
            }
        }

        return $i . "Record Elaborati su " . $i2;
    }
}
