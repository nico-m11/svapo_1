<?php
require_once '../config/Config.php';
require 'Users.php';

class Products
{

    // var connessione al db e tabella

    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;

    }

    public function GetAllProductsFromDescriptionAndReference()
    {
        $sql = "SELECT ps_product_lang.name, ps_product.ean13, ps_product_attribute.reference 
        FROM ps_product 
        INNER JOIN ps_product_lang 
        ON ps_product.id_product = ps_product_lang.id_product 
        INNER JOIN ps_product_attribute 
        ON ps_product.id_product = ps_product_attribute.id_product 
        LIMIT 50;";


        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $result = [];
        if ($stmt->rowCount() > 0) {
            foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $product){
                $product_name = isset($product['name']) ? $product['name'] : 'NO NAME';
                $product_reference = isset($product['reference']) ? $product['reference'] : 'NO REFERENCE';
                $product_ean13 = isset($product['ean13']) ? $product['ean13'] : 'NO EAN';

                $result [] = [
                    'name' => $product_name,
                    'ean13' => $product_ean13,
                    'reference' => $product_reference
                ];
            };
        }

        if(count($result) !== 0) {
            return $result;
        } else {
            echo 'NO result';
        }
    }

    public function GetNextSku($output)
    {
        // Per la generazione dello sku del prodotto io seguirei questa logica:
        // W = Wine
        // 01 = ID del produttore normalizzato a 2 cifre
        // 001 = ID del vino normalizzato a 3 cifre
        // 21 = Ultime due cifre dell’anno della creazione del vino
        // Quindi un codice prodotto potrebbe essere: W0100321

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

        // DETECT ID_PRODUCT
        $sql_product = "SELECT (COUNT(*) + 1) as total_product FROM products WHERE id_producer = $id_producer";

        //preparo l'istruzione
        $stmt_product = $this->conn->prepare($sql_product);
        //execute query
        $stmt_product->execute();
        $product = $stmt_product->fetch(PDO::FETCH_ASSOC);
        // DETECT ID_PRODUCE END

        $identification_product = "W";
        $id_producer = str_pad($producer["id_producer"], 2, '0', STR_PAD_LEFT);
        $id_normalized_product = str_pad($product["total_product"], 3, '0', STR_PAD_LEFT);
        $year = date("Y");
        $year = substr($year, -2);

        $return = $identification_product . $id_producer . $id_normalized_product . $year;

        return $return;
    }

    // Product handling in the warehouse
    public function handlingProduct($id_user, $id_product, $quantity, $causal, $id_lot = false)
    {

        if ($id_lot) {
            $sql = "INSERT INTO products_handling (id_user,id_product,quantity,causal,id_lot) VALUES ('$id_user','$id_product','$quantity','$causal','$id_lot')";
        } else {
            $sql = "INSERT INTO products_handling (id_user,id_product,quantity,causal) VALUES ('$id_user','$id_product','$quantity','$causal')";
        }

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        return $stmt->rowCount();
    }

    // Story of Product handling in the warehouse
    public function storyHandlingProduct($output)
    {
        $id_product = $output["id_product"];
        $sql = "SELECT * FROM products_handling LEFT JOIN lots ON products_handling.id_lot = lots.id_lot LEFT JOIN users ON users.id_user = products_handling.id_user WHERE id_product = $id_product ORDER BY handling_date ASC";

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

    // Create product
    public function CreateProduct($output)
    {
        $id_user = $output["id_user"];
        $id_producer = $output["id_producer"];
        $sku = $output["sku"];
        $color = addslashes($output["color"]);
        $region = addslashes($output["region"]);
        $appellation = addslashes($output["appellation"]);
        $clos = addslashes($output["clos"]);
        $classification = addslashes($output["classification"]);
        $vintage = addslashes($output["vintage"]);
        $alcool_level = addslashes($output["alcool_level"]);
        $size = addslashes($output["size"]);
        $price = $output["price"];
        $label = addslashes($output["label"]);
        $cork = addslashes($output["cork"]);
        $level_of_wine = addslashes($output["level_of_wine"]);

        $sql1 = "SELECT * FROM products WHERE sku = '$sku' AND deleted=0";

        //preparo l'istruzione
        $stmt1 = $this->conn->prepare($sql1);

        //execute query
        $stmt1->execute();

        if ($stmt1->rowCount() == 0) {

            $sql = "INSERT INTO products (id_user,id_producer,sku,color,region,appellation,clos,classification,vintage,alcool_level,size,price,label,cork,level_of_wine,stato) VALUES ('$id_user','$id_producer','$sku','$color','$region','$appellation','$clos','$classification','$vintage','$alcool_level','$size','$price','$label','$cork','$level_of_wine',1)";

            //preparo l'istruzione
            $stmt = $this->conn->prepare($sql);

            //execute query
            $stmt->execute();

            return $stmt->rowCount();
        } else {
            return -1;
        }
    }

    // Edit Product
    public function EditProduct($output)
    {
        $id_product = $output["id_product"];
        $id_producer = $output["id_producer"];
        $sku = $output["sku"];
        $color = addslashes($output["color"]);
        $region = addslashes($output["region"]);
        $appellation = addslashes($output["appellation"]);
        $clos = addslashes($output["clos"]);
        $classification = addslashes($output["classification"]);
        $vintage = addslashes($output["vintage"]);
        $alcool_level = addslashes($output["alcool_level"]);
        $size = addslashes($output["size"]);
        $price = $output["price"];
        $label = addslashes($output["label"]);
        $cork = addslashes($output["cork"]);
        $level_of_wine = addslashes($output["level_of_wine"]);

        $sql1 = "SELECT * FROM products WHERE id_product = $id_product AND deleted=0";

        //preparo l'istruzione
        $stmt1 = $this->conn->prepare($sql1);

        //execute query
        $stmt1->execute();

        if ($stmt1->rowCount() > 0) {

            $sql2 = "SELECT * FROM products WHERE sku = '$sku'";
            //preparo l'istruzione
            $stmt2 = $this->conn->prepare($sql2);
            //execute query
            $stmt2->execute();

            $product = $stmt1->fetch(PDO::FETCH_ASSOC);
            $sku_actual = $product["sku"];

            $sql = "UPDATE products SET 
            id_producer = '$id_producer',
            sku = '$sku',
            color = '$color',
            region = '$region',
            appellation = '$appellation',
            clos = '$clos',
            classification = '$classification',
            vintage = '$vintage',
            alcool_level = '$alcool_level',
            size = '$size',
            price = '$price',
            label = '$label',
            cork = '$cork',
            level_of_wine = '$level_of_wine',
            edited_date = NOW()
            WHERE id_product = '$id_product'";

            if ($stmt2->rowCount() > 0) {

                if ($sku_actual == $sku) {

                    //preparo l'istruzione
                    $stmt = $this->conn->prepare($sql);

                    //execute query
                    $stmt->execute();

                    return $stmt->rowCount();
                } else {
                    return -1;
                }
            } else {

                //preparo l'istruzione
                $stmt = $this->conn->prepare($sql);

                //execute query
                $stmt->execute();

                return $stmt->rowCount();
            }
        }
    }

    public function NewStockProduct($output)
    {

        $id_user = $output["id_user"];
        $id_product = $output["id_product"];
        $quantity = $output["quantity"];

        return $this->handlingProduct($id_user, $id_product, $quantity, 'PRODUCT-NEW-STOCK');
    }

    // Edit Stato of Product
    public function EditProductStato($output)
    {
        $id_product = $output["id_product"];
        $stato = $output["stato"];

        $sql = "UPDATE products SET 
        stato = '$stato'

        WHERE id_product = '$id_product'";
        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        return $stmt->rowCount();
    }

    // Delete Product
    public function DeleteProduct($output)
    {
        $id_user = $output["id_user"];
        $id_product = $output["id_product"];

        $sql = "UPDATE products SET 
        deleted = -1

        WHERE id_product = '$id_product'";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $quantity_product = intval("-" . $this->GetQuantityProduct($id_product));

            return $this->handlingProduct($id_user, $id_product, $quantity_product, 'PRODUCT-DELETED');
        }
    }


    // Get quantity of product
    private function GetQuantityProduct($id_product)
    {

        $sql = "SELECT SUM(quantity) as total_quantity FROM products_handling WHERE id_product = $id_product";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        return intval($product["total_quantity"]);
    }

    // Get number of Handling of product
    public function GetNumHandlingProduct($id_product)
    {

        $sql_initial = "SELECT quantity FROM products_handling WHERE id_product = $id_product AND causal ='PRODUCT-NEW-STOCK'";

        //preparo l'istruzione
        $stmt_initial = $this->conn->prepare($sql_initial);

        //execute query
        $stmt_initial->execute();
        $initial = $stmt_initial->fetch(PDO::FETCH_ASSOC);

        $sql_total = "SELECT SUM(quantity) as quantity FROM products_handling WHERE id_product = $id_product";

        //preparo l'istruzione
        $stmt_total = $this->conn->prepare($sql_total);

        //execute query
        $stmt_total->execute();
        $total = $stmt_total->fetch(PDO::FETCH_ASSOC);


        if ($initial["quantity"] == $total["quantity"]) {

            $return = 0;
        } else {

            $sql = "SELECT COUNT(*) as total_handling FROM products_handling WHERE id_product = $id_product AND causal !='PRODUCT-NEW-STOCK'";

            //preparo l'istruzione
            $stmt = $this->conn->prepare($sql);

            //execute query
            $stmt->execute();

            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            $return = $product["total_handling"];
        }

        return $return;
    }

    // Get All Product
    public function GetProducts($products)
    {

        $result = array();
        foreach ($products as $key => $product) {

            $user_call = new Users($this->conn);
            $user = $user_call->GetUserInfo($product["id_producer"]);

            $result[] = $product;
            $result[$key]["quantity"] = $this->GetQuantityProduct($product["id_product"]);
            $result[$key]["producer_name"] = $user["fullname"];
            $result[$key]["NumHandling"] = $this->GetNumHandlingProduct($product["id_product"]);
            $result[$key]["id_producer_real"] = $user["id_producer"];

            $appellation = $product["appellation"] ? $product["appellation"] : "";
            $classification = $product["classification"] ? $product["classification"] : "";
            $clos = $product["clos"] ? $product["clos"] : "";
            $size = $product["size"] ? $product["size"]  : "";
            $vintage = $product["vintage"] ? $product["vintage"] : "";
            $producer_name = $user["fullname"];
            $result[$key]["nameProduct"] = "$producer_name $appellation $classification $clos $vintage $size";
            $result[$key]["onlyNameProduct"] = "$appellation $classification $clos $vintage $size";

            $result[$key]["storyHandlingProduct"] = $this->storyHandlingProduct(array("id_product" => $product["id_product"]));
        }


        return $result;
    }

    // Get All Product
    public function GetSingleProduct($id_product)
    {

        $sql = "SELECT * FROM products WHERE id_product = $id_product";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        $user_call = new Users($this->conn);
        $user = $user_call->GetUserInfo($product["id_producer"]);

        $result[] = $product;
        $result["quantity"] = $this->GetQuantityProduct($product["id_product"]);
        $result["producer_name"] = $user["fullname"];
        $result["NumHandling"] = $this->GetNumHandlingProduct($product["id_product"]);
        $result["id_producer_real"] = $user["id_producer"];

        $appellation = $product["appellation"] ? $product["appellation"] : "";
        $classification = $product["classification"] ? $product["classification"] : "";
        $clos = $product["clos"] ? $product["clos"] : "";
        $size = $product["size"] ? $product["size"]  : "";
        $vintage = $product["vintage"] ? $product["vintage"] : "";
        $producer_name = $user["fullname"];
        $result["nameProduct"] = "$producer_name $appellation $classification $clos $vintage";



        return $result;
    }

    // Get All Product
    public function GetAllProducts($output)
    {
        $id_user = $output["id_user"];

        $user_call = new Users($this->conn);
        $user = $user_call->GetUserInfo($id_user);

        if ($user["roles"] == 1 || $user["roles"] == 99) {
            $sql = "SELECT * FROM products WHERE deleted = 0 ORDER BY id_product DESC";
        } else {
            $sql = "SELECT * FROM products WHERE id_producer = $id_user AND deleted = 0 ORDER BY id_product DESC";
        }

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = $this->GetProducts($products);

        return $result;
    }

    // Get All Product Active
    public function GetAllProductsActive($output)
    {
        $id_user = $output["id_user"];

        $user_call = new Users($this->conn);
        $user = $user_call->GetUserInfo($id_user);

        if ($user["roles"] == 1 || $user["roles"] == 99) {
            $sql = "SELECT * FROM products WHERE deleted = 0 AND stato = 1";
        } else {
            $sql = "SELECT * FROM products WHERE id_producer = $id_user AND deleted = 0 AND stato = 1";
        }

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = $this->GetProducts($products);

        return $result;
    }

    // Get All Product Active
    public function GetAllProductsActiveProducer($output)
    {
        $id_user = $output["id_user"];

        $sql = "SELECT * FROM products WHERE id_producer = $id_user AND deleted = 0 AND stato = 1 ORDER BY id_product DESC";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = $this->GetProducts($products);

        return $result;
    }

    // Get All Product Active
    public function GetProductSingle($output)
    {
        $id_product = $output["id_product"];

        $sql = "SELECT * FROM products WHERE deleted = 0 AND id_product = $id_product";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = $this->GetProducts($products);

        return $result;
    }
}



    // Get All Product or Single product | Funcion API woocomerce
    // private function CreateWoo($type,$id){

    //     $product = new Client(
    //         'https://crurated.com',
    //         'ck_2154e99a9564cb886794982c6507858033d2cbf6',
    //         'cs_4853d077212ff5649d9add4725a2bd7941b2632c',
    //         [
    //             'wp_api' => true,
    //             'version' => 'wc/v3',
    //             'query_string_auth' => true
    //         ]
    //     );

    //     if($type == "category"){

    //         $data = [
    //             "per_page" => 100,
    //             "status" => "publish",
    //             "stock_status" => "instock",
    //             "category" => $id
    //         ];

    //         $return = $product->get('products',$data);

    //     }
        
    //     if($type == "single_product"){

    //         if($id > 0){

    //             $return = $product->get('products/'.$id.'');
    //         }
    //     }

    //     return json_encode($return);
    
    // }

        // Get single Product
    // public function GetSingleProductOld($id){
    //     $product = $this->CreateWoo("single_product",$id);
    
    //     return $product;
    // }

    // Get all Product of category Single Lots Auction
    // public function GetWineSingleLots(){
    //     $products = $this->CreateWoo("category",71);
    //     $products = json_decode($products, true);

    //     $return = array();
    //     foreach($products as $key => $product){

    //         $return[$key]["value"] = $product["id"];
    //         $return[$key]["producer_name"] = $product["sku"]." - ".$product["producer_name"];

    //     }

    //     return $return;
    // }

    // Get all Product of category Collections
    // public function GetWineCollections(){

    //     $products = $this->CreateWoo("category",70);      
    //     $products = json_decode($products, true);
        
    //     $return = array();
    //     foreach($products as $key => $product){

    //         $return[$key]["value"] = $product["id"];
    //         $return[$key]["producer_name"] = $product["sku"]." - ".$product["producer_name"];

    //     }

    //     return $return;
    // }

        // Get single Product
    // public function GetSingleProduct($id){
    //     $product = $this->CreateWoo("single_product",$id);
    //     $product = json_decode($product, true);
        
    //     $return = array();

    //     $return["idProduct"] = $product["id"];
    //     $return["producer_name"] = $product["producer_name"];
    //     $return["imageLink"] = $product["images"][0]["src"];
    //     $return["description"] = substr(strip_tags($product["description"]),0,110);
    //     $return["notes"] = substr(strip_tags($product["short_description"]),0,110);
    //     $return["sku"] = $product["sku"];
    //     $return["regular_price"] = $product["regular_price"];
    //     $return["estimate"] = $product["regular_price"]. "$";
    //     $return["price"] = $product["price"];
    //     $return["sale_price"] = $product["sale_price"];
    //     //$return["stockQuantity"] = $product["stock_quantity"];
    //     $return["stockQuantity"] = 10;
    //     $return["permalink"] = $product["permalink"];
    //     $return["packages"] = "6 bottle 750ml";

    //     return json_encode($return);
    // }
