<?php

// require '../../vendor/autoload.php';
require_once '../config/Config.php';
require 'Lots.php';

// use Automattic\WooCommerce\Client;

class Auction
{

    // var connessione al db e tabella

    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ADMIN PANNEL

    // Create Auction
    public function CreateAuction($output)
    {
        $id_user = $output["id_user"];
        $description_auction = $output["description_auction"];
        $initial_price = $output["initial_price"];
        $crurated_estimated_market_price = $output["crurated_estimated_market_price"];
        $id_lot = $output["id_lot"];
        $max_quantity = $output["max_quantity"];
        $increment_selected = $output["increment_selected"];
        $start_data = $output["start_data"];
        $finish_data = $output["finish_data"];

        $disable_for = $output["disable_for"];
        $visible_for = $output["visible_for"];
        $for_user = $output["for_user"];

        $id_auction_type = $output["id_auction_type"];
        $timezone = $output["timezone"];

        $string = $timezone;
        if ($string[0] == '+') {
            $timezone = str_replace("+", "-", $timezone);
        } else {
            $timezone = str_replace("-", "+", $timezone);
        }

        $finish_data = gmdate("Y-m-d H:i:s", strtotime("$finish_data $timezone minutes"));
        $start_data = gmdate("Y-m-d H:i:s", strtotime("$start_data $timezone minutes"));


        // create array for disable_for and visible_for
        $disable_for = explode(",", $disable_for);
        $array_disable_for = array();
        foreach ($disable_for as $customer_roles) {
            $array_disable_for[]["id_customer_role"] = $customer_roles;
        }
        $disable_for = json_encode($array_disable_for);

        $visible_for = explode(",", $visible_for);
        $array_visible_for = array();
        foreach ($visible_for as $customer_roles) {
            $array_visible_for[]["id_customer_role"] = $customer_roles;
        }
        $visible_for = json_encode($array_visible_for);

        $for_user = explode(",", $for_user);
        $array_for_user = array();
        foreach ($for_user as $user) {
            $array_for_user[]["id_user"] = $user;
        }
        $for_user = json_encode($array_for_user);

        $sql = "INSERT INTO auctions (id_user,description_auction,initial_price,crurated_estimated_market_price,id_lot,max_quantity,increment_selected,created_data,finish_data,id_auction_type,disabled_for,visible_for,for_user,stato) VALUES ('$id_user','$description_auction','$initial_price','$crurated_estimated_market_price','$id_lot','$max_quantity','$increment_selected','$start_data','$finish_data','$id_auction_type','$disable_for','$visible_for','$for_user',1)";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $id_auction = $this->conn->lastInsertId();
            // $lots_call = new Lots($this->conn);

            // $quantity_chosen_for_lot = "-" . intval($initial_quantity);
            // $quantity_chosen_for_lot = intval($quantity_chosen_for_lot);


            // $handlingLot = $lots_call->handlingLot($id_user, $id_lot, $quantity_chosen_for_lot, 'AUCTION-CREATED', $id_auction);

            // if ($handlingLot) {

            $input_auctionHadling = array(
                "id_user" => $id_user,
                "id_auction" => $id_auction,
                "causal" => 'AUCTION-CREATED',
                "description_auction" => $description_auction,
                "initial_quantity" =>  'NULL',
                "max_quantity" => $max_quantity,
                "initial_price" => $initial_price,
                "created_data" => $start_data,
                "finish_data" => $finish_data,
                "id_lot" => $id_lot,
                "id_auction_type" => $id_auction_type,
                "stato" => 1,
                "deleted" => 0
            );

            return $this->AuctionHadling($input_auctionHadling);
            // }
        }
    }

    // Edit Auction
    public function EditAuction($output)
    {

        $id_user = $output["id_user_edit"];
        $id_auction = $output["id_auction"];
        $description_auction = $output["description_auction"];
        $initial_price = $output["initial_price"];
        $crurated_estimated_market_price = $output["crurated_estimated_market_price"];
        $id_lot = $output["id_lot"];
        $max_quantity = $output["max_quantity"];
        $increment_selected = $output["increment_selected"];
        $start_data = $output["start_data"];
        $finish_data = $output["finish_data"];

        $disable_for = $output["disable_for"];
        $visible_for = $output["visible_for"];
        $for_user = $output["for_user"];

        $id_auction_type = $output["id_auction_type"];
        $timezone = $output["timezone"];

        $string = $timezone;
        if ($string[0] == '+') {
            $timezone = str_replace("+", "-", $timezone);
        } else {
            $timezone = str_replace("-", "+", $timezone);
        }

        $finish_data = gmdate("Y-m-d H:i:s", strtotime("$finish_data $timezone minutes"));
        $start_data = gmdate("Y-m-d H:i:s", strtotime("$start_data $timezone minutes"));

        // $finish_data = gmdate("Y-m-d H:i:s", strtotime($finish_data));
        // $start_data = gmdate("Y-m-d H:i:s", strtotime($start_data));

        // create array for disable_for and visible_for
        $disable_for = explode(",", $disable_for);
        $array_disable_for = array();
        foreach ($disable_for as $customer_roles) {
            $array_disable_for[]["id_customer_role"] = $customer_roles;
        }
        $disable_for = json_encode($array_disable_for);

        $visible_for = explode(",", $visible_for);
        $array_visible_for = array();
        foreach ($visible_for as $customer_roles) {
            $array_visible_for[]["id_customer_role"] = $customer_roles;
        }
        $visible_for = json_encode($array_visible_for);

        $for_user = explode(",", $for_user);
        $array_for_user = array();
        foreach ($for_user as $user) {
            $array_for_user[]["id_user"] = $user;
        }
        $for_user = json_encode($array_for_user);

        $sql = "UPDATE auctions SET 
        description_auction = '$description_auction',
        initial_price = '$initial_price',
        id_lot = '$id_lot',
        crurated_estimated_market_price = '$crurated_estimated_market_price',
        max_quantity = '$max_quantity',
        increment_selected = '$increment_selected',
        created_data = '$start_data',
        finish_data = '$finish_data',
        disabled_for = '$disable_for',
        visible_for = '$visible_for',
        for_user = '$for_user',
        id_auction_type = '$id_auction_type'

        WHERE id_auction = '$id_auction'";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $input_auctionHadling = array(
                "id_user" => $id_user,
                "id_auction" => $id_auction,
                "causal" => 'AUCTION-EDITED',
                "description_auction" => $description_auction,
                "initial_quantity" =>  'NULL',
                "max_quantity" => $max_quantity,
                "initial_price" => $initial_price,
                "created_data" => $start_data,
                "finish_data" => $finish_data,
                "id_lot" => $id_lot,
                "id_auction_type" => $id_auction_type,
                "stato" => 1,
                "deleted" => 0
            );

            return $this->AuctionHadling($input_auctionHadling);
        }
    }

    // Edit Stato of Auction
    public function EditAuctionStato($output)
    {

        $id_user = $output["id_user"];
        $id_auction = $output["id_auction"];
        $stato = $output["stato"];


        $sql = "UPDATE auctions SET 
        stato = '$stato'

        WHERE id_auction = '$id_auction'";
        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $input_auctionHadling = array(
                "id_user" => $id_user,
                "id_auction" => $id_auction,
                "causal" => 'AUCTION-STATO-EDITED',
                "description_auction" => 'NULL',
                "initial_quantity" =>  'NULL',
                "max_quantity" => 'NULL',
                "initial_price" => 'NULL',
                "created_data" => 'NULL',
                "finish_data" => 'NULL',
                "id_lot" => 'NULL',
                "id_auction_type" => 'NULL',
                "stato" => $stato,
                "deleted" => 'NULL'
            );

            return $this->AuctionHadling($input_auctionHadling);
        }
    }

    // Delete Auction
    public function DeleteAuction($output)
    {
        $id_user = $output["id_user"];
        $id_auction = $output["id_auction"];

        $sql = "UPDATE auctions SET 
        deleted = -1

        WHERE id_auction = '$id_auction'";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $auction = $this->GetAuctions($id_user, "SELECT * FROM auctions WHERE id_auction = $id_auction");
            $auction = $auction[0]["auction"];

            //$lots_call = new Lots($this->conn);

            //$quantity_chosen_for_lot = intval($auction["initialQuantity"]);

            //$handlingLot = $lots_call->handlingLot($id_user, $auction["lots"]["id_lot"], $quantity_chosen_for_lot, 'AUCTION-DELETED', $id_auction);

            // if ($handlingLot) {

            $input_auctionHadling = array(
                "id_user" => $id_user,
                "id_auction" => $id_auction,
                "causal" => 'AUCTION-DELETED',
                "description_auction" => 'NULL',
                "initial_quantity" =>  'NULL',
                "max_quantity" => 'NULL',
                "initial_price" => 'NULL',
                "created_data" => 'NULL',
                "finish_data" => 'NULL',
                "id_lot" => 'NULL',
                "id_auction_type" => 'NULL',
                "stato" => 'NULL',
                "deleted" => -1
            );

            return $this->AuctionHadling($input_auctionHadling);
            // }
        }
    }

    // Assign the winner of Auction Single Lots
    public function AssignWinnerSingleLots()
    {

        $sql = "SELECT * FROM auctions WHERE finish_data <= NOW() AND id_auction_type = 1 AND stato = 1 AND deleted = 0";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $auctions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($auctions as $auction) {

                $input = array(
                    "id_auction" => $auction["id_auction"]
                );

                return $this->AssignWinnerSingleLotsSpecific($input);
            }
        } else {
            return "No offers to check";
        }
    }

    // Assign the winner of Auction Single Lots
    public function AssignWinnerSingleLotsSpecific($output)
    {
        $id_auction = $output["id_auction"];

        $sql = "SELECT * FROM auctions_participant WHERE id_auction = $id_auction AND is_winner = 1 AND deleted = 0";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() == 0) {

            $sql1 = "SELECT *, (single_bid * quantity) as bid FROM auctions_participant WHERE id_auction = $id_auction AND deleted = 0 ORDER BY bid DESC LIMIT 1";

            //preparo l'istruzione
            $stmt1 = $this->conn->prepare($sql1);

            //execute query
            $stmt1->execute();

            if ($stmt1->rowCount() > 0) {
                $participant = $stmt1->fetch(PDO::FETCH_ASSOC);

                $input = array(
                    "id_user" => $participant["id_user"],
                    "id_auction" => $participant["id_auction"],
                    "quantity_winner" => $participant["quantity"],
                    "bid_winner" => $participant["bid"],
                    "id_auction_participant" => $participant["id_auction_participant"]
                );

                $AssignWinner = $this->AssignWinner($input);

                if ($AssignWinner) {
                    return $this->AssignLoser($id_auction);
                }
            }
        }
    }

    // Assign the loser of Auction Collections
    public function AssignLoserCollections()
    {

        $assign_deadline = date('Y-m-d H:i:s', strtotime("-3 days"));
        $sql = "SELECT * FROM auctions WHERE id_auction_type = 2 AND finish_data <= '$assign_deadline' AND stato = 1 AND deleted = 0";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $control_auctions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = '';
            foreach ($control_auctions as $control_auction) {

                $id_auction = $control_auction["id_auction"];

                $AssignLoser = $this->AssignLoser($id_auction);
                if ($AssignLoser) {

                    $result .= "Auction $id_auction Processed | ";
                }
            }
            return $result;
        } else {
            return "No offers to check";
        }
    }

    // Assign the loser of Auction Private Sale
    public function AssignLoserPrivateSale()
    {

        $assign_deadline = date('Y-m-d H:i:s', strtotime("-3 days"));
        $sql = "SELECT * FROM auctions WHERE id_auction_type = 3 AND finish_data <= '$assign_deadline' AND stato = 1 AND deleted = 0";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $control_auctions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = '';
            foreach ($control_auctions as $control_auction) {

                $id_auction = $control_auction["id_auction"];

                $AssignLoser = $this->AssignLoser($id_auction);
                if ($AssignLoser) {

                    $result .= "Auction $id_auction Processed | ";
                }
            }
            return $result;
        } else {
            return "No offers to check";
        }
    }

    // Assign the winner of Auction
    public function AssignWinner($output)
    {
        $id_user = $output["id_user"];
        $id_auction = $output["id_auction"];
        $quantity_winner = $output["quantity_winner"];
        $single_bid_winner = $output["bid_winner"];
        $bid_winner = $single_bid_winner * $quantity_winner;
        $id_auction_participant = $output["id_auction_participant"];
        //$is_pay = $output["is_pay"];
        //$id_payment_stripe = $output["id_payment_stripe"];

        $sql = "SELECT * FROM auctions WHERE finish_data <= NOW() AND stato = 1 AND deleted = 0 AND id_auction = $id_auction";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $auction = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_lot = $auction["id_lot"];

            $sql = "SELECT * FROM auctions_participant WHERE id_auction_participant = $id_auction_participant AND id_user = $id_user AND is_winner = 0 AND deleted = 0";

            //preparo l'istruzione
            $stmt = $this->conn->prepare($sql);

            //execute query
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                $user_call = new Users($this->conn);
                $info_user = $user_call->GetUserInfo($id_user);
                $info_user = $info_user;

                $payment_method = $info_user["method_payment"]["active"]["name"];
                $id_method_payment = $info_user["method_payment"]["active"]["id_method_payment"];

                // Processing fee
                $percentage_insurance = $info_user["permissions"]["insurance_processing_fee"];
                $insurance = ($bid_winner * $percentage_insurance) / 100;

                $total = round(($bid_winner + $insurance), 2);

                // New flow method Payment
                if ($payment_method == "CARD") {

                    $stripe_call = new StripeSistem($this->conn);

                    $amount_stripe = $total * 100;

                    //echo "percentage_insurance: $percentage_insurance | insurance: $insurance | bid_winner: $bid_winner | total: $total | amount_stripe: $amount_stripe";

                    $id_customer_stripe = $info_user["id_customer_stripe"];

                    $input_CreatePaymentIntent = array(
                        "id_customer_stripe" => $id_customer_stripe,
                        "amount" => $amount_stripe
                    );

                    $result_arr = $stripe_call->CreatePaymentIntent($input_CreatePaymentIntent);

                    $is_pay = $result_arr["status"] == "succeeded" ? 1 : 0;

                    $day_for_deadline = "+7 days";
                } else {
                    $is_pay = 0;

                    $day_for_deadline = "+14 days";
                }

                $sql1 = "UPDATE auctions_participant SET 
                is_winner = 1,
                date_winner = NOW(),
                quantity_winner = $quantity_winner,
                single_bid_winner = $single_bid_winner,
                insurance = $insurance
                WHERE id_auction_participant = $id_auction_participant";
                //preparo l'istruzione
                $stmt1 = $this->conn->prepare($sql1);

                //execute query
                $stmt1->execute();

                if ($stmt1->rowCount() > 0) {

                    $sql_result = "SELECT date_winner,bid_date FROM auctions_participant WHERE id_auction_participant = $id_auction_participant AND deleted = 0";
                    //preparo l'istruzione
                    $stmt_result = $this->conn->prepare($sql_result);

                    //execute query
                    $stmt_result->execute();
                    $participant_result = $stmt_result->fetch(PDO::FETCH_ASSOC);

                    $date_winner = $participant_result["date_winner"];

                    $import_chosen_for_wallet = round($bid_winner, 2);

                    $continue = 0;
                    if ($is_pay) {

                        $id_payment_stripe = $result_arr["id_payment_stripe"];

                        $input_handlingWallet = array(
                            "id_user" => $id_user,
                            "import" => $import_chosen_for_wallet,
                            "causal" => 'AUCTION-WIN',
                            "type" => "auction_participant",
                            "id_auction" => $id_auction,
                            "with_card" => 1,
                            "id_method_payment" => $id_method_payment,
                            "id_type" => $id_auction_participant,
                            "id_payment_stripe" => $id_payment_stripe,
                            "status" => 1
                        );

                        $handlingWallet = $user_call->reportTransition($input_handlingWallet);

                        if ($handlingWallet) {

                            $import_chosen_for_wallet = round($insurance, 2);
                            $input_handlingWallet_insurance = array(
                                "id_user" => $id_user,
                                "import" => $import_chosen_for_wallet,
                                "causal" => 'AUCTION-PROCESSING-FEE',
                                "type" => "auction_participant",
                                "id_auction" => $id_auction,
                                "with_card" => 1,
                                "id_method_payment" => $id_method_payment,
                                "id_type" => $id_auction_participant,
                                "id_payment_stripe" => $id_payment_stripe,
                                "status" => 1
                            );
                            $handlingWallet_insurance = $user_call->reportTransition($input_handlingWallet_insurance);

                            if ($handlingWallet_insurance) {

                                $sql2 = "UPDATE auctions_participant SET 
                                            status_availability = '1'  
                                            WHERE id_user = $id_user AND id_auction = $id_auction";
                                //preparo l'istruzione
                                $stmt2 = $this->conn->prepare($sql2);

                                //execute query
                                $stmt2->execute();

                                $continue = 1;
                            }
                        }
                    } else {

                        $input_handlingWallet = array(
                            "id_user" => $id_user,
                            "import" => $import_chosen_for_wallet,
                            "causal" => 'AUCTION-WIN',
                            "type" => "auction_participant",
                            "id_auction" => $id_auction,
                            "with_card" => 1,
                            "id_method_payment" => $id_method_payment,
                            "id_type" => $id_auction_participant,
                            "status" => 0
                        );

                        $handlingWallet = $user_call->reportTransition($input_handlingWallet);

                        if ($handlingWallet) {

                            $import_chosen_for_wallet = round($insurance, 2);
                            $input_handlingWallet_insurance = array(
                                "id_user" => $id_user,
                                "import" => $import_chosen_for_wallet,
                                "causal" => 'AUCTION-PROCESSING-FEE',
                                "type" => "auction_participant",
                                "id_auction" => $id_auction,
                                "with_card" => 1,
                                "id_method_payment" => $id_method_payment,
                                "id_type" => $id_auction_participant,
                                "status" => 0
                            );
                            $handlingWallet_insurance = $user_call->reportTransition($input_handlingWallet_insurance);

                            if ($handlingWallet_insurance) {



                                // get the differenze
                                //$difference_wallet = $wallet - $insurance;

                                // the import is the wallet complete
                                // $input_handlingWallet = array(
                                //     "id_user" => $id_user,
                                //     "import" => $import_chosen_for_wallet,
                                //     "causal" => 'AUCTION-WIN',
                                //     "type" => "auction_participant",
                                //     "id_auction" => $id_auction,
                                //     "with_card" => 0,
                                //     "id_method_payment" => $id_method_payment,
                                //     "id_type" => $id_auction_participant
                                // );

                                // $handlingWallet = $user_call->handlingWallet($input_handlingWallet);

                                // if ($handlingWallet) {

                                //     $import_chosen_for_wallet = round("-" . $insurance, 2);
                                //     $input_handlingWallet_insurance = array(
                                //         "id_user" => $id_user,
                                //         "import" => $import_chosen_for_wallet,
                                //         "causal" => 'AUCTION-PROCESSING-FEE',
                                //         "type" => "auction_participant",
                                //         "id_auction" => $id_auction,
                                //         "with_card" => 0,
                                //         "id_method_payment" => $id_method_payment,
                                //         "id_type" => $id_auction_participant
                                //     );
                                //     $handlingWallet_insurance = $user_call->handlingWallet($input_handlingWallet_insurance);

                                //     if ($handlingWallet_insurance) {

                                //$date = $bid_date;

                                $date = $date_winner;

                                $payment_deadline = date('Y-m-d H:i:s', strtotime($date . " " . $day_for_deadline));

                                $sql2 = "UPDATE auctions_participant SET 
                                    payment_deadline = '$payment_deadline',
                                    status_availability = '0'   
                                    WHERE id_user = $id_user AND id_auction = $id_auction";
                                //preparo l'istruzione

                                $stmt2 = $this->conn->prepare($sql2);

                                //execute query
                                $stmt2->execute();

                                $continue = 1;

                                //}
                                //}
                            }
                        }
                    }

                    if ($continue > 0) {

                        // HANDLING LOT
                        $lots_call = new Lots($this->conn);

                        $quantity_chosen_for_lot = "-" . $quantity_winner;

                        $handlingLot = $lots_call->handlingLot($id_user, $id_lot, $quantity_chosen_for_lot, 'AUCTION-WIN', $id_auction);

                        if ($handlingLot) {

                            $sql_control = "SELECT SUM(quantity) as total_quantity FROM lots_handling WHERE id_lot = $id_lot";
                            //preparo l'istruzione
                            $stmt_control = $this->conn->prepare($sql_control);
                            //execute query
                            $stmt_control->execute();

                            if ($stmt_control->rowCount() > 0) {
                                $info_lot = $stmt_control->fetch(PDO::FETCH_ASSOC);
                                $quantity_lot = $info_lot["total_quantity"];

                                if ($quantity_lot < 1) {

                                    $sql_search = "SELECT * FROM auctions_participant WHERE is_winner = 0 AND id_auction = $id_auction";
                                    //preparo l'istruzione
                                    $smt_search = $this->conn->prepare($sql_search);
                                    //execute query
                                    $smt_search->execute();
                                    $participant_search = $smt_search->fetchAll(PDO::FETCH_ASSOC);

                                    foreach ($participant_search as $participant) {
                                        $id_auction_participant = $participant["id_auction_participant"];
                                        $sql1 = "UPDATE auctions_participant SET 
                                        is_winner = -1
                                        WHERE id_auction_participant = $id_auction_participant";
                                        //preparo l'istruzione
                                        $stmt1 = $this->conn->prepare($sql1);
                                        //execute query
                                        $stmt1->execute();
                                    }
                                }

                                // EMAIL SISTEM
                                //$email_call = new EmailSistem($this->conn);
                                $email = $info_user["email"];
                                $first_name = $info_user["first_name"];
                                $info_auction = $this->GetAuctions($id_user, "SELECT * FROM auctions WHERE id_auction = $id_auction");

                                $name_auction = $info_auction[0]["auction"]["nameAuction"];
                                $bid_date = $participant_result["bid_date"];

                                $input = array(
                                    "from" => "no-reply@crurated.com",
                                    "to" => $email,
                                    "subject" => "Your Crurated offer has been accepted!",
                                    "email" => [
                                        "title" => "Dear $first_name",
                                        "content" => [
                                            [
                                                "format" => "paragraph",
                                                "text" => "<p>Congratulations! Your offer of EUR <b>" . $bid_winner . "€</b> for lot <b>\"$name_auction\"</b> placed on " . $bid_date . " has been accepted. <br><br> This lot has been allocated to your account. Your credit card has been charged <b>" . $total . "€</b>, which includes the 2.5% protection and processing fee.</p> 

                                        <p>Please do not hesitate to contact us by emailing us at <a href='mailto:hello@crurated.com'>hello@crurated.com</a> if you have any questions or need any further assistance.</p>
                                        <p>Kindly note this offer is now closed.</p>
                                        <p>Check the status of your offers</p>
                                        <p>Sincerely,<br>
                                        The Crurated Team</p>",
                                                "type" => "1Col",
                                            ],
                                            [
                                                "format" => "button",
                                                //"link" => DOMAIN . "/my-history",
                                                "button" => "My History",
                                                "type" => "1Col",
                                            ],
                                        ]
                                    ]
                                );

                                //return $email_call->SendEmailSistem($input);
                            }
                        }
                    }
                }
            }
        }
    }

    // Assign the loser of Auction
    public function AssignLoser($id_auction)
    {

        $sql1 = "SELECT * FROM auctions_participant WHERE is_winner = 0 AND id_auction = $id_auction AND deleted = 0";

        //preparo l'istruzione
        $stmt1 = $this->conn->prepare($sql1);

        //execute query
        $stmt1->execute();

        if ($stmt1->rowCount() > 0) {

            $auction_participants = $stmt1->fetchAll(PDO::FETCH_ASSOC);

            $count_participant = count($auction_participants);
            $count_participant_processed = 0;

            foreach ($auction_participants as $auction_participant) {

                $id_user = $auction_participant["id_user"];

                $user_call = new Users($this->conn);
                $info_user = $user_call->GetUserInfo($id_user);
                $info_user = $info_user;

                $id_auction_participant = $auction_participant["id_auction_participant"];

                $count_participant_processed++;
                $event_log = new EventLog($this->conn);
                $input_logSimple = [
                    "parameters" => "ID auction Participant: $id_auction_participant | located in auction was marked as lose via automatic check",
                    "type" => "event",
                    "user" => $info_user["fullname"] . " Email: " . $info_user["email"],
                    "event" => "AssignLoser"
                ];
                $event_log->logSimple($input_logSimple);
            }

            if ($count_participant == $count_participant_processed) {

                $sql2 = "UPDATE auctions_participant SET 
                    is_winner = -1
                    WHERE is_winner = 0 AND id_auction = $id_auction AND deleted = 0";

                //preparo l'istruzione
                $stmt2 = $this->conn->prepare($sql2);

                //execute query
                $stmt2->execute();

                if ($stmt2->rowCount() > 0) {
                    $sql_end = "UPDATE auctions SET processed = 1 WHERE id_auction = $id_auction";

                    //preparo l'istruzione
                    $stmt_end = $this->conn->prepare($sql_end);

                    //execute query
                    $stmt_end->execute();

                    return $stmt_end->rowCount();
                }
            }
        } else {

            $sql_end = "UPDATE auctions SET processed = 1 WHERE id_auction = $id_auction";

            //preparo l'istruzione
            $stmt_end = $this->conn->prepare($sql_end);

            //execute query
            $stmt_end->execute();

            return $stmt_end->rowCount();
        }
    }

    // Get all bid of user
    public function GetTypeAuctions($output)
    {

        $sql = "SELECT * FROM auctions_type WHERE active = 1 AND deleted = 0";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $return = array();
        if ($stmt->rowCount() > 0) {
            $return = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $return;
    }

    // Get all bid of user
    public function GetAllOffers($output)
    {

        $id_user = $output["id_user"];
        $limit = $output["limit"];

        if ($limit == "no") {
            $act_limit = "";
        } else {
            $act_limit = "LIMIT $limit";
        }

        $sql = "SELECT *, (single_bid * quantity) as bid, auctions_participant.id_user as id_user FROM auctions_participant LEFT JOIN auctions ON auctions.id_auction = auctions_participant.id_auction WHERE auctions.finish_data > NOW() AND auctions.stato = 1 AND auctions.deleted = 0 AND auctions_participant.is_winner = 0 AND auctions_participant.deleted = 0 ORDER BY priority ASC $act_limit";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $auctions_participant = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->GetSingleOffer($auctions_participant);
    }

    // Get all bid of user
    public function GetMyOffers($output)
    {

        $id_user = $output["id_user"];
        $limit = $output["limit"];

        if ($limit == "no") {
            $act_limit = "";
        } else {
            $act_limit = "LIMIT $limit";
        }

        $sql = "SELECT *, (single_bid * quantity) as bid, auctions_participant.id_user as id_user FROM auctions_participant LEFT JOIN auctions ON auctions.id_auction = auctions_participant.id_auction WHERE auctions_participant.id_user = $id_user AND auctions.finish_data > NOW() AND auctions.stato = 1 AND auctions.deleted = 0 AND auctions_participant.is_winner = 0 AND auctions_participant.deleted = 0 ORDER BY priority ASC $act_limit";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $auctions_participant = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->GetSingleOffer($auctions_participant);
    }

    private function GetSingleOffer($auctions_participant)
    {
        $result = array();
        foreach ($auctions_participant as $key => $participant) {

            if ($participant["id_auction_participant"] > 0) {

                $id_user = $participant["id_user"];

                $user_call = new Users($this->conn);
                $user = $user_call->GetUserInfo($id_user);

                $id_auction = $participant["id_auction"];

                $info_auction = $this->GetAuctions($id_user, "SELECT * FROM auctions WHERE id_auction = $id_auction");
                $result[] = $participant;
                $result[$key]["total_bid"] = round($participant["bid"], 2);
                $result[$key]["user"] = $user;
                $result[$key]["auction"] = $info_auction[0]["auction"];
                $result[$key]["bid_date_frontend"] = date("Y/m/d H:i:s", strtotime($participant["bid_date"]));
            }
        }

        return $result;
    }

    // Get all bid of user
    public function GetMyHistory($output)
    {

        $id_user = $output["id_user"];
        $limit = $output["limit"];

        if ($limit == "no") {
            $act_limit = "";
        } else {
            $act_limit = "LIMIT $limit";
        }

        $sql = "SELECT *, (single_bid * quantity) as bid, (single_bid_winner * quantity_winner) as bid_winner, auctions_participant.id_user as id_user FROM auctions_participant LEFT JOIN auctions ON auctions.id_auction = auctions_participant.id_auction WHERE auctions_participant.id_user = $id_user AND auctions.stato = 1 AND auctions.deleted = 0 AND auctions_participant.deleted = 0 ORDER BY priority ASC $act_limit";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();


        $result = array();

        if ($stmt->rowCount() > 0) {
            $auctions_participant = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($auctions_participant as $key => $participant) {

                if ($participant["id_auction_participant"] > 0) {

                    $id_user = $participant["id_user"];

                    $user_call = new Users($this->conn);
                    $user = $user_call->GetUserInfo($id_user);

                    $id_auction = $participant["id_auction"];

                    $info_auction = $this->GetAuctions($id_user, "SELECT * FROM auctions WHERE id_auction = $id_auction");
                    $result[] = $participant;
                    $result[$key]["total_bid"] = round($participant["bid"], 2);
                    $result[$key]["user"] = $user;
                    $result[$key]["auction"] = $info_auction[0]["auction"];
                }
            }
        }

        return $result;
    }

    public function SetPriorityBid($output)
    {
        $id_user = $output["id_user"];
        $bid_participant = $output["bid_participant"];

        if (count($bid_participant) > 0) {
            foreach ($bid_participant as $auction_participant) {

                $priority = $auction_participant["priority"];
                $id_auction = $auction_participant["id_auction"];

                $sql = "UPDATE auctions_participant SET 
                priority = $priority
                WHERE id_auction = $id_auction AND id_user= $id_user";

                //preparo l'istruzione
                $stmt = $this->conn->prepare($sql);

                //execute query
                $stmt->execute();
            }

            return 1;
        }
    }

    // Edit Stato of Auction
    public function EditWhatclistAuction($output)
    {

        $id_user = $output["id_user"];
        $id_auction = $output["id_auction"];
        $is_watchlist = $output["is_watchlist"];

        $sql = "SELECT * FROM auctions_watchlist WHERE id_user= $id_user AND id_auction = $id_auction";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() == 0) {

            $sql1 = "INSERT INTO auctions_watchlist (id_user, id_auction) VALUES ('$id_user', '$id_auction')";
            //preparo l'istruzione
            $stmt1 = $this->conn->prepare($sql1);

            //execute query
            $stmt1->execute();

            return $stmt1->rowCount();
        } else {

            $sql1 = "DELETE FROM auctions_watchlist
            WHERE id_user= $id_user AND id_auction = $id_auction";
            //preparo l'istruzione
            $stmt1 = $this->conn->prepare($sql1);

            //execute query
            $stmt1->execute();

            return $stmt1->rowCount();
        }
    }

    // Edit Stato of Auction
    public function Bid($output)
    {
        $id_user = $output["id_user"];
        $id_auction = $output["id_auction"];
        $quantity = $output["quantity"];
        $bid = $output["bid"];

        $sql_a = "SELECT *, auctions_type.slug as type_auction FROM auctions LEFT JOIN auctions_type ON auctions.id_auction_type = auctions_type.id_auction_type WHERE finish_data > NOW() AND id_auction = $id_auction";

        //preparo l'istruzione
        $stmt_a = $this->conn->prepare($sql_a);

        //execute query
        $stmt_a->execute();
        $auction_info = $stmt_a->fetch(PDO::FETCH_ASSOC);

        if ($stmt_a->rowCount() > 0) {
            $type_auction = $auction_info["type_auction"];
            $id_lot = $auction_info["id_lot"];

            if ($type_auction == 'single-lots') {
                $sql = "SELECT * FROM auctions_participant WHERE single_bid = $bid AND id_auction = $id_auction AND deleted = 0";
            } else {
                $sql = "SELECT * FROM auctions_participant WHERE id_user= $id_user AND id_auction = $id_auction AND deleted = 0";
            }

            //preparo l'istruzione
            $stmt = $this->conn->prepare($sql);

            //execute query
            $stmt->execute();

            // control of wallet
            $user_call = new Users($this->conn);
            //$AC_call = new ACSistem($this->conn);

            $info_user = $user_call->GetUserInfo($id_user);
            $info_user = $info_user;

            $sql1 = "INSERT INTO auctions_participant (id_user,id_auction,quantity,single_bid) VALUES ('$id_user', '$id_auction','$quantity','$bid')";
            //preparo l'istruzione
            $stmt1 = $this->conn->prepare($sql1);


            // IF the user have money in the wallet and not have bidded continue
            // if ($type_auction == 'single-lots' && $stmt->rowCount() == 0 && $info_user["wallet"] >= $subscription_value || $type_auction == 'collections' && $info_user["wallet"] >= $subscription_value) {
            if ($type_auction == 'single-lots' && $stmt->rowCount() == 0 || $type_auction == 'collections' || $type_auction == 'private-sale') {
                //execute query
                $stmt1->execute();

                if ($stmt1->rowCount() > 0) {

                    $id_auction_participant = $this->conn->lastInsertId();

                    $sql_producer = "SELECT users.id_ac AS id_ac_producer FROM lots LEFT JOIN users ON lots.id_producer = users.id_user WHERE id_lot = $id_lot";
                    //preparo l'istruzione
                    $stmt_producer = $this->conn->prepare($sql_producer);
                    $stmt_producer->execute();
                    $producer_info = $stmt_producer->fetch(PDO::FETCH_ASSOC);
                    $id_ac_producer = $producer_info["id_ac_producer"];

                    // Edit time if the auction are in 30 second
                    if ($auction_info["type_auction"] == 'single-lots') {

                        $sql_last_bid = "SELECT * FROM auctions_participant WHERE id_auction = $id_auction AND id_auction_participant != $id_auction_participant AND deleted = 0 ORDER BY id_auction_participant LIMIT 1";
                        //preparo l'istruzione
                        $stmt_last_bid = $this->conn->prepare($sql_last_bid);
                        $stmt_last_bid->execute();

                        // if ($stmt_last_bid->rowCount() > 0) {

                        //     $participant_last_bid = $stmt_last_bid->fetch(PDO::FETCH_ASSOC);
                        //     $id_user_last_bid = $participant_last_bid["id_user"];
                        //     //$mail = new EmailSistem($this->conn);

                        //     $info_auction = $this->GetAuctions($id_user, "SELECT * FROM auctions WHERE id_auction = $id_auction");

                        //     $name_auction = $info_auction[0]["auction"]["nameAuction"];
                        //     $finish_data_auction = $info_auction[0]["auction"]["finishData"];
                        //     $bid_date = $participant_last_bid["bid_date"];

                        //     $info_user = $user_call->GetUserInfo($id_user_last_bid);
                        //     $first_name = $info_user["first_name"];
                        //     $email = $info_user["email"];

                        //     $input = array(
                        //         "from" => "no-reply@crurated.com",
                        //         "to" => $email,
                        //         "subject" => "Your bid has been outbid",
                        //         "email" => [
                        //             "title" => "Dear $first_name",
                        //             "content" => [
                        //                 [
                        //                     "format" => "paragraph",
                        //                     "text" => "<p>The bid you submitted on Lot </p> <p><b>\"$name_auction\"</b> <br><small>on $bid_date</small> has been outbid by another member</p>
                        //                     <p>To increase your bid or check the status of this lot, please <a href='" . DOMAIN . "/dashboard'>click here</a>. </p>
                        //                     <p>We will notify you once more before the auction closes on $finish_data_auction. </p>
                        //                     <p>Sincerely,<br>
                        //                     The Crurated Team.</p>",
                        //                     "type" => "1Col",
                        //                 ],
                        //                 [
                        //                     "format" => "button",
                        //                     //"link" => DOMAIN,
                        //                     "button" => "Go on Crurated ",
                        //                     "type" => "1Col",
                        //                 ],
                        //             ]
                        //         ]
                        //     );

                        //     //$mail->SendEmailSistem($input);
                        // }

                        $input_ACAddTag = array("id_user" => $id_user, "id_tag" => 11);
                        //$AC_call->ACAddTag($input_ACAddTag);

                        $input_ACAddTag_producer = array("id_user" => $id_user, "id_tag" => $id_ac_producer);
                        //$AC_call->ACAddTag($input_ACAddTag_producer);

                        $input = array(
                            "id_user" => $id_user,
                            "id_auction" => $id_auction,
                            "time" => "+ 30 seconds"
                        );

                        return $this->EditTimeAuction($input);
                    } else {

                        $input_ACAddTag = array("id_user" => $id_user, "id_tag" => 11);
                        //$AC_call->ACAddTag($input_ACAddTag);

                        $input_ACAddTag_producer = array("id_user" => $id_user, "id_tag" => $id_ac_producer);
                        //$AC_call->ACAddTag($input_ACAddTag_producer);

                        return $id_auction_participant;
                    }
                }
            } else {
                if ($type_auction == 'single-lots' && $stmt->rowCount() > 0) {
                    return -1;
                }
                //elseif ($info_user["wallet"] < $subscription_value) {
                //     return -2;
                // }
            }
        }
    }

    // Edit Stato of Auction
    public function EditBid($output)
    {
        $id_user = $output["id_user"];
        $id_auction = $output["id_auction"];
        $id_auction_participant = $output["id_auction_participant"];
        $quantity = $output["quantity"];
        $bid = $output["bid"];

        $sql_a = "SELECT * FROM auctions WHERE finish_data > NOW() AND id_auction = $id_auction";

        //preparo l'istruzione
        $stmt_a = $this->conn->prepare($sql_a);

        //execute query
        $stmt_a->execute();

        if ($stmt_a->rowCount() > 0) {

            $sql = "SELECT * FROM auctions_participant WHERE id_user= $id_user AND id_auction_participant  = $id_auction_participant  AND deleted = 0";

            //preparo l'istruzione
            $stmt = $this->conn->prepare($sql);

            //execute query
            $stmt->execute();

            // control of wallet
            $user_call = new Users($this->conn);
            //$AC_call = new ACSistem($this->conn);

            $info_user = $user_call->GetUserInfo($id_user);
            $info_user = $info_user;

            $sql1 = "UPDATE auctions_participant SET 
            single_bid = $bid, 
            quantity = $quantity 
            WHERE id_auction_participant = $id_auction_participant";

            //preparo l'istruzione
            $stmt1 = $this->conn->prepare($sql1);
            $stmt1->execute();

            if ($stmt->rowCount() > 0) {

                $input_handlingAuctionParticipant = [
                    "id_auction_participant" => $id_auction_participant,
                    "id_user" => $id_user,
                    "id_auction" => $id_auction,
                    "quantity" => $quantity,
                    "bid" => $bid,
                ];

                $handlingAuctionParticipant = $this->handlingAuctionParticipant($input_handlingAuctionParticipant);

                return $handlingAuctionParticipant;
            }
        }
    }

    private function handlingAuctionParticipant($output)
    {

        $id_auction_participant = $output["id_auction_participant"];
        $id_user = $output["id_user"];
        $id_auction = $output["id_auction"];
        $quantity = $output["quantity"];
        $bid = $output["bid"];


        $sql = "INSERT INTO auctions_participant_handling (id_auction_participant,id_user,id_auction,quantity,single_bid) VALUES ($id_auction_participant,$id_user,$id_auction,'$quantity','$bid')";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        return $stmt->rowCount();
    }

    // Edit Stato of Auction
    public function EditTimeAuction($output)
    {
        $id_user = $output["id_user"];
        $id_auction = $output["id_auction"];
        $time = $output["time"];

        $sql_a = "SELECT * FROM auctions WHERE finish_data > NOW() AND id_auction = $id_auction";

        //preparo l'istruzione
        $stmt_a = $this->conn->prepare($sql_a);

        //execute query
        $stmt_a->execute();

        if ($stmt_a->rowCount() > 0) {
            $auction = $stmt_a->fetch(PDO::FETCH_ASSOC);

            $actual_time = date("Y-m-d H:i:s");
            $finish_data = $auction["finish_data"];

            $origin = new DateTime($actual_time);
            $target = new DateTime($finish_data);
            $interval = $origin->diff($target);
            $diff_day = $interval->format('%R%a');
            $diff_second =  $interval->format('%R%s');

            $timeline_second = 30;


            if ($diff_day <= 0 && $diff_second <= $timeline_second) {

                $finish_data_now = date("Y-m-d H:i:s", strtotime("$finish_data $time"));

                $sql = "UPDATE auctions SET 
                finish_data = '$finish_data_now'
                WHERE id_auction = '$id_auction'";

                //preparo l'istruzione
                $stmt = $this->conn->prepare($sql);

                //execute query
                $stmt->execute();

                if ($stmt->rowCount() > 0) {

                    $input_auctionHadling = array(
                        "id_user" => $id_user,
                        "id_auction" => $id_auction,
                        "causal" => 'FINISH-DATA-EDITED-FROM-BID',
                        "description_auction" => 'NULL',
                        "initial_quantity" =>  'NULL',
                        "max_quantity" => 'NULL',
                        "initial_price" => 'NULL',
                        "created_data" => 'NULL',
                        "finish_data" => $finish_data_now,
                        "id_lot" => 'NULL',
                        "id_auction_type" => 'NULL',
                        "stato" => 'NULL',
                        "deleted" => 'NULL'
                    );

                    return $this->AuctionHadling($input_auctionHadling);
                }
            } else {
                return 1;
            }
        }
    }

    // Create the edited shipping chronology
    public function AuctionHadling($output)
    {

        $id_user = $output["id_user"];
        $id_auction = $output["id_auction"];
        $causal = $output["causal"];
        $description_auction = $output["description_auction"];
        $initial_quantity = $output["initial_quantity"];
        $max_quantity = $output["max_quantity"];
        $initial_price = $output["initial_price"];
        $created_data = $output["created_data"];
        $finish_data = $output["finish_data"];
        $id_lot = $output["id_lot"];
        $id_auction_type = $output["id_auction_type"];
        $stato = $output["stato"];
        $deleted = $output["deleted"];

        // id_user,id_auction,causal,edited_date,description_auction,initial_quantity,max_quantity,initial_price,created_data,finish_data,id_lot,type_auction,stato,deleted
        $sql = "INSERT INTO auctions_handling (id_user,id_auction,causal,description_auction,initial_quantity,max_quantity,initial_price,created_data,finish_data,id_lot,id_auction_type,stato,deleted) VALUES ('$id_user','$id_auction','$causal','$description_auction','$initial_quantity','$max_quantity','$initial_price','$created_data','$finish_data','$id_lot','$id_auction_type','$stato','$deleted')";
        $sql = str_replace("'NULL'", "NULL", $sql);

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        return $stmt->rowCount();
    }

    // FUNCTION FOR THE INFO OF AUCTION

    // Returns if the user needs to upgrade
    private function to_ugrade($output)
    {
        $disabled_for = $output["disabled_for"];
        $id_customer_role = $output["id_customer_role"];
        $roles = $output["roles"];

        if (!strpos($disabled_for, '"id_customer_role":"' . $id_customer_role . '"') || $roles != 2) {
            return 0;
        } else {
            return 1;
        }
    }

    // Return the stato of auction
    private function DetectStato($output)
    {
        $to_ugrade = $output["to_ugrade"];
        $stato = $output["stato"];
        $start_data = $output["start_data"];
        $finish_data = $output["finish_data"];

        $current_data = date("Y-m-d H:i:s");

        // if (!$to_ugrade) {
        if ($stato == 1) {

            if ($current_data >= $start_data) {
                if ($current_data >= $finish_data) {
                    return 2;
                } else {
                    return 1;
                }
            } else {
                return -1;
            }
        } else if ($stato == 0) {

            return 0;
        } else {
            return 2;
        }
        // } else {
        //     if ($stato == 1) {

        //         if ($current_data >= $start_data) {
        //             if ($current_data >= $finish_data) {
        //                 return 2;
        //             } else {
        //                 return 2;
        //             }
        //         } else {
        //             return -1;
        //         }
        //     } else if ($stato == 0) {

        //         return 0;
        //     } else {
        //         return 2;
        //     }
        // }
    }

    // Return the quantoty remaining of auction
    private function DetectQuantityAuction($output)
    {
        $id_auction = $output["id_auction"];

        $sql = "SELECT SUM(quantity) as total_quantity_headling FROM auctions_participant WHERE id_auction = '$id_auction' AND is_winner = 1 AND deleted = 0";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->RowCount() > 0) {

            $auctions_participant = $stmt->fetch(PDO::FETCH_ASSOC);
            $total = intval($auctions_participant["total_quantity_headling"]);

            return $total;
        } else {

            return 0;
        }
    }

    // Return the options for bid of auction
    private function DetectOptionsBid($output)
    {
        $currentBid = $output["currentBid"];
        $crurated_estimated_market_price = $output["crurated_estimated_market_price"];
        $increment_selected = $output["increment_selected"];
        $type_auction = $output["type_auction"];

        $value = $currentBid;
        $value_increment = ($currentBid * $increment_selected) / 100;
        $array_options = array();

        if ($type_auction != "single-lots") {
            $array_options[1]["value"] = $value;
            $array_options[1]["name"] = $value;
        }

        for ($mul = 2; $mul <= 150; ++$mul) {

            $value = $value + round($value_increment, 0);

            // module five
            $value_final = $value - ($value % 5);

            $array_options[$mul]["value"] = $value_final;
            $array_options[$mul]["name"] = $value_final;


            if ($mul >= 10 && $value_final > $crurated_estimated_market_price) {
                break;
            }
        }

        $array_options = array_values($array_options);

        return $array_options;
    }

    // Return options of quantity for bid of auction
    private function DetectOptionsQuantity($output)
    {

        $quantity = $output["quantity"];
        $max_quantity = $output["max_quantity"];
        $max_quantity_customer = $output["max_quantity_customer"];

        $quantity_min = min(array($quantity, $max_quantity, $max_quantity_customer));

        $array_options = array();

        if ($quantity_min > 0) {
            for ($mul = 1; $mul <= $quantity_min; ++$mul) {

                $array_options[$mul]["value"] = $mul;
                $array_options[$mul]["name"] = "Qty: " . $mul;
            }
        } else {
            $array_options[1]["value"] = 0;
            $array_options[1]["name"] = "Qty: 0";
        }

        $array_options = array_values($array_options);

        return $array_options;
    }

    // Return initial price of auction
    private function DetectInitialPrice($output)
    {
        $id_auction = $output["id_auction"];
        $type_auction = $output["type_auction"];
        $initial_price  = $output["initial_price"];

        // if the auction is single lots detect the last bid
        if ($type_auction == 'single-lots') {

            $sql = "SELECT (single_bid * quantity) as bid FROM auctions_participant WHERE id_auction = '$id_auction' AND deleted = 0 ORDER BY bid_date DESC LIMIT 1";

            //preparo l'istruzione
            $stmt = $this->conn->prepare($sql);

            //execute query
            $stmt->execute();

            if ($stmt->RowCount() > 0) {
                $auctions_participant = $stmt->fetch(PDO::FETCH_ASSOC);

                return $auctions_participant["bid"];
            } else {
                return $initial_price;
            }
        } else {
            return $initial_price;
        }
    }

    // Return ratio of price
    private function DetectRatio($output)
    {
        $current_bid = $output["current_bid"];
        $estimate = $output["estimate"];

        // if the auction is single lots detect the last bid
        $calc = ($current_bid / $estimate) * 100;

        return round($calc, 2);
    }

    // Return is whatchlist
    private function DetectIsWatchlist($output)
    {

        $id_user = $output["id_user"];
        $id_auction = $output["id_auction"];

        $sql = "SELECT * FROM auctions_watchlist WHERE id_user=$id_user AND id_auction = $id_auction";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        return $stmt->rowCount();
    }

    // Return is submitted
    private function DetectIsSubmitted($output)
    {
        $id_user = $output["id_user"];
        $id_auction = $output["id_auction"];
        $type = isset($output["type"]) ? $output["type"] : "default";

        $sql = "SELECT * FROM auctions_participant WHERE id_user=$id_user AND id_auction = $id_auction AND deleted = 0";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($output["type"] == "default") {
            return $stmt->rowCount();
        } else {
            $result = array();

            $auctions_participant = $stmt->fetch(PDO::FETCH_ASSOC);
            $result["information_bid"] = $auctions_participant;
            $result["is_submitted"] = $stmt->rowCount();
            return $result;
        }
    }

    // Return the id of user last bidded 
    private function DetectLastBidded($output)
    {
        $id_auction = $output["id_auction"];

        $sql = "SELECT * FROM auctions_participant WHERE id_auction = $id_auction AND deleted = 0 ORDER BY bid_date DESC LIMIT 1";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $auctions_participant = $stmt->fetch(PDO::FETCH_ASSOC);

            return $auctions_participant["id_user"];
        }
    }

    // Return the id of user winning 
    private function DetectWinning($output)
    {
        $id_user = $output["id_user"];
        $id_auction = $output["id_auction"];

        $sql = "SELECT * FROM auctions_participant WHERE id_auction = $id_auction AND deleted = 0 ORDER BY bid DESC LIMIT 1";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $auctions_participant = $stmt->fetch(PDO::FETCH_ASSOC);

            $auctions_participant["id_user"] = isset($auctions_participant["id_user"]) ? $auctions_participant["id_user"] : 'default';
            if ($auctions_participant["id_user"] == $id_user) {
                return 1;
            } else {
                return 0;
            }
        }
    }

    // Return the number of bid
    private function DetectNumBid($output)
    {
        $id_auction = $output["id_auction"];

        $sql = "SELECT COUNT(*) as total_bid FROM auctions_participant WHERE id_auction = '$id_auction' AND deleted = 0";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $auctions_participant = $stmt->fetch(PDO::FETCH_ASSOC);

            return $auctions_participant["total_bid"];
        } else {

            return 0;
        }
    }

    private function DetectTypeAuction($output)
    {
        $id_auction_type = $output["id_auction_type"];

        $sql = "SELECT slug FROM auctions_type WHERE id_auction_type = '$id_auction_type' AND active = 1 AND deleted = 0";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $auctions_type = $stmt->fetch(PDO::FETCH_ASSOC);

            return $auctions_type["slug"];
        }
    }

    private function DetectUsersAbilitate($output)
    {
        $for_user = $output["for_user"];
        $arr = json_decode($for_user, true);

        $return = array();
        foreach ($arr as $key => $user) {
            $id_user = $user["id_user"];
            $sql = "SELECT id_user, CONCAT(first_name, ' ', last_name) as fullname FROM users WHERE id_user = $id_user";

            //preparo l'istruzione
            $stmt = $this->conn->prepare($sql);

            //execute query
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $return[] = $user;
            }
        }

        return json_encode($return);
    }

    // Get the info of auction
    public function GetAuctions($id_user, $query)
    {

        $sql = $query;

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $user_call = new Users($this->conn);
        $user = $user_call->GetUserInfo($id_user);

        if ($user["roles"] == 2) {
            $id_customer_roles = $user["id_customer_role"];
            $roles = $user["roles"];
        } else {
            $id_customer_roles = 0;
            $roles = $user["roles"];
        }

        // Require the info of lot
        $lot = new Lots($this->conn);

        // $user_call = New Users($this->conn);
        // $gmt = $user_call->GetTimeZoneUser($id_user)." hours";



        $result = array();
        $auctions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($auctions as $key => $auction) {

            $type_auction = $this->DetectTypeAuction(["id_auction_type" => $auction["id_auction_type"]]);

            $info_lot = $lot->GetLotSingle(array("id_lot" => $auction["id_lot"]));
            $info_lot = $info_lot[0];

            // FUNCTION DetectInitialPrice
            $input_detectInitialPrice = array(
                "id_auction" => $auction["id_auction"],
                "type_auction" => $type_auction,
                "initial_price" => $auction["initial_price"],
            );
            $DetectInitialPrice = $this->DetectInitialPrice($input_detectInitialPrice);
            // END FUNCTION DetectInitialPrice

            // FUNCTION DetectQuantityAuction
            // $input_detectQuantityAuction = array(
            //     "id_auction" => $auction["id_auction"],
            // );
            // $DetectQuantityAuction = $this->DetectQuantityAuction($input_detectQuantityAuction);
            // END FUNCTION DetectQuantityAuction

            $initial_price = $DetectInitialPrice;
            $crurated_estimated_market_price = $auction["crurated_estimated_market_price"];
            $finishDataInput = date("Y-m-d\TH:i", strtotime($auction["finish_data"]));
            $startDataInput = date("Y-m-d\TH:i", strtotime($auction["created_data"]));
            $quantity = $info_lot["quantity"];

            // FUNCTION TO UPGRADE
            $input_to_upgrade = array(
                "disabled_for" => $auction["disabled_for"],
                "id_customer_role" => $id_customer_roles,
                "roles" => $roles
            );
            $to_ugrade = $this->to_ugrade($input_to_upgrade);
            // END FUNCTION TO UPGRADE

            // FUNCTION DetectStato
            $input_detectStato = array(
                "to_ugrade" => $to_ugrade,
                "stato" => $auction["stato"],
                "start_data" => $auction["created_data"],
                "finish_data" => $auction["finish_data"]
            );
            $DetectStato = $this->DetectStato($input_detectStato);
            // END FUNCTION DetectStato

            // FUNCTION DetectOptionsBid
            $input_detectOptionsBid = array(
                "currentBid" => $initial_price,
                "crurated_estimated_market_price" => $crurated_estimated_market_price,
                "increment_selected" => $auction["increment_selected"],
                "type_auction" => $type_auction,
            );
            $DetectOptionsBid = $this->DetectOptionsBid($input_detectOptionsBid);
            // END FUNCTION DetectOptionsBid

            // FUNCTION DetectNumBid
            $input_detectNumBid = array(
                "id_auction" => $auction["id_auction"],
            );
            $DetectNumBid = $this->DetectNumBid($input_detectNumBid);
            // END FUNCTION DetectNumBid

            // FUNCTION DetectIsWatchlist
            $input_detectIsWatchlist = array(
                "id_auction" => $auction["id_auction"],
                "id_user" => $id_user,
            );
            $DetectIsWatchlist = $this->DetectIsWatchlist($input_detectIsWatchlist);
            // END FUNCTION DetectIsWatchlist

            // FUNCTION DetectRatio
            $input_detectRatio = array(
                "current_bid" => $initial_price,
                "estimate" => $crurated_estimated_market_price,
            );
            $DetectRatio =  $this->DetectRatio($input_detectRatio);
            // END FUNCTION DetectRatio

            // FUNCTION DetectIsSubmitted
            $input_detectIsSubmitted = array(
                "id_user" => $id_user,
                "id_auction" => $auction["id_auction"],
                "type" => "with_information"
            );
            $DetectIsSubmitted = $this->DetectIsSubmitted($input_detectIsSubmitted);
            // END FUNCTION DetectIsSubmitted

            // FUNCTION DetectLastBidded
            $input_detectLastBidded = array(
                "id_auction" => $auction["id_auction"],
            );
            $DetectLastBidded = $this->DetectLastBidded($input_detectLastBidded);
            // END FUNCTION DetectLastBidded


            // FUNCTION DetectWinning
            $input_detectWinning = array(
                "id_auction" => $auction["id_auction"],
                "id_user" => $id_user
            );
            $DetectWinning =  $this->DetectWinning($input_detectWinning);
            // END FUNCTION DetectWinning

            // FUNCTION DetectOptionsQuantity
            $input_detectOptionsQuantity = array(
                "quantity" => $quantity,
                "max_quantity" => $auction["max_quantity"],
                "max_quantity_customer" => $user["permissions"]["maxi_cases"]
            );
            $DetectOptionsQuantity =  $this->DetectOptionsQuantity($input_detectOptionsQuantity);
            // END FUNCTION DetectOptionsQuantity

            // FUNCTION DetectOptionsQuantity
            $input_DetectUsersAbilitate = array(
                "for_user" => $auction["for_user"],
            );
            $DetectUsersAbilitate =  $this->DetectUsersAbilitate($input_DetectUsersAbilitate);
            // END FUNCTION DetectOptionsQuantity


            $result[$key]["auction"]["id"] = $auction["id_auction"];
            $result[$key]["auction"]["id_lot"] = $auction["id_lot"];
            $result[$key]["auction"]["idAuction"] = $auction["id_auction"];
            $result[$key]["auction"]["descriptionAuction"] = $auction["description_auction"];
            $result[$key]["auction"]["currentBid"] = $initial_price;
            $result[$key]["auction"]["initialPrice"] = $auction["initial_price"];
            $result[$key]["auction"]["crurated_estimated_market_price"] = $crurated_estimated_market_price;
            $result[$key]["auction"]["initialPriceOfAuction"] = $auction["initial_price"];
            $result[$key]["auction"]["createdData"] = date("Y-m-d H:i:s", strtotime($auction["created_data"]));
            $result[$key]["auction"]["finishData"] = date("Y-m-d H:i:s", strtotime($auction["finish_data"]));
            $result[$key]["auction"]["finishDataCountdown"] = date("Y/m/d H:i:s", strtotime($auction["finish_data"]));
            $result[$key]["auction"]["createdDataCountdown"] = date("Y/m/d H:i:s", strtotime($auction["created_data"]));
            $result[$key]["auction"]["finishDataInput"] = $finishDataInput;
            $result[$key]["auction"]["startDataInput"] = $startDataInput;
            $result[$key]["auction"]["id_auction_type"] = $auction["id_auction_type"];
            $result[$key]["auction"]["typeAuction"] = $type_auction;
            $result[$key]["auction"]["stato"] = $DetectStato;
            $result[$key]["auction"]["to_upgrade"] = $to_ugrade;
            $result[$key]["auction"]["quantity"] = $quantity;
            $result[$key]["auction"]["optionsBid"] = $DetectOptionsBid;
            $result[$key]["auction"]["numBid"] = $DetectNumBid;
            $result[$key]["auction"]["is_watchlist"] = $DetectIsWatchlist;
            $result[$key]["auction"]["maxQuantity"] = $auction["max_quantity"];
            $result[$key]["auction"]["increment_selected"] = $auction["increment_selected"];

            $result[$key]["auction"]["visible_for"] = $auction["visible_for"];
            $result[$key]["auction"]["disabled_for"] = $auction["disabled_for"];
            $result[$key]["auction"]["for_user"] = $auction["for_user"];
            $result[$key]["auction"]["for_user_multiselect"] = $DetectUsersAbilitate;

            $result[$key]["auction"]["lots"] = $info_lot;

            $result[$key]["auction"]["ratio"] = $DetectRatio;
            $result[$key]["auction"]["is_submitted"] = $DetectIsSubmitted["is_submitted"];
            $result[$key]["auction"]["information_bid"] = $DetectIsSubmitted["information_bid"];
            $result[$key]["auction"]["id_last_bidded"] = $DetectLastBidded;
            $result[$key]["auction"]["is_winning"] = $DetectWinning;
            $result[$key]["auction"]["optionsQuantity"] = $DetectOptionsQuantity;

            $region = $info_lot["products"][0]["region"];

            $result[$key]["auction"]["nameAuction"] = $info_lot["producer_name"] . " | " . $region . " (" . $info_lot["case_"] . ")";

            // definizione di un array
            $vintage_range = array("1950", "1993", "2005", "2011", "1970");
            // estrazione casuale di un singolo valore
            $vintage_range_rand = array_rand($vintage_range, 1);


            $result[$key]["lots"]["yearVintage"] = $vintage_range[$vintage_range_rand];

            $search_products = '';
            $search_products_desc = '';
            foreach ($info_lot["products"] as $product) {
                $search_products .= "" . $product["nameProduct"] . " " . $product["quantity"] . " of " . $product["size"];
                $search_products_desc .= $product["quantity"] . " x "  . $product["nameProduct"] . " + ";
            }

            $result[$key]["auction"]["searchProducts"] = $search_products_desc;
            $result[$key]["auction"]["nameSearchAuction"] = $info_lot["producer_name"] . " | " . $region . " (" . $info_lot["case_"] . ") " . $search_products;
        }

        return $result;
    }

    // Get the participant of specific auction
    public function GetPartecipantAuction($output)
    {
        $id_auction = $output["id_auction"];

        $sql = "SELECT *, (single_bid * quantity) as bid, auctions_participant.id_user as id_user  FROM auctions_participant LEFT JOIN auctions ON auctions_participant.id_auction = auctions.id_auction WHERE auctions_participant.id_auction = '$id_auction' AND auctions_participant.deleted = 0";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $result = array();


        if ($stmt->rowCount() > 0) {

            $auctions_participant = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // control of wallet
            $user_call = new Users($this->conn);
            foreach ($auctions_participant as $key => $participant) {
                if ($participant["id_user"] > 0) {
                    $info_user = $user_call->GetUserInfo($participant["id_user"]);

                    $max_import = $user_call->GetMaxImportDate($info_user["id_user"], $participant["finish_data"]);
                    $info_user["max_import"] = $max_import;
                    $info_user["max_import_calculated"] = $user_call->GetMaxImportCalculatedDate($info_user["id_user"], $max_import, $participant["finish_data"]);

                    $result[] = array_merge($participant, $info_user);
                }
            }
        }
        return $result;
    }

    public function DeleteOffer($output)
    {

        $id_user = $output["id_user"];
        $id_auction_participant = $output["id_auction_participant"];

        $sql = "UPDATE auctions_participant SET 
        deleted = -1

        WHERE id_auction_participant = '$id_auction_participant'";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        return $stmt->rowCount();
    }

    public function GetSingleAuction($output)
    {
        $id_user = $output["id_user"];
        $id_auction = $output["id_auction"];

        $sql = "SELECT * FROM auctions WHERE id_auction = $id_auction";

        return $this->GetAuctions($id_user, $sql);
    }

    public function GetAuctionsSingleLots($output)
    {

        $id_user = $output["id_user"];

        $actual_date = date("Y-m-d H:i:s");

        $user_call = new Users($this->conn);
        $user = $user_call->GetUserInfo($id_user);

        if ($user["roles"] == 1 || $user["roles"] == 99) {

            $sql = "SELECT * FROM auctions WHERE id_auction_type = 1 AND deleted = 0 AND DATE_ADD(finish_data,INTERVAL 2 WEEK) >= '$actual_date' ORDER BY CASE WHEN finish_data < NOW() THEN 1 ELSE 0 END, finish_data";
        } else {

            if ($user["roles"] != 2) {
                $sql = "SELECT * FROM auctions WHERE id_auction_type = 1 AND created_data <= '$actual_date' AND deleted = 0 AND DATE_ADD(finish_data,INTERVAL 2 WEEK) >= '$actual_date' ORDER BY CASE WHEN finish_data < NOW() THEN 1 ELSE 0 END, finish_data";
            } else {

                $id_customer_role = $user["id_customer_role"];
                $tring_like = '"id_customer_role":"' . $id_customer_role . '"';

                $sql = "SELECT * FROM auctions WHERE id_auction_type = 1 AND created_data <= '$actual_date' AND deleted = 0 AND DATE_ADD(finish_data,INTERVAL 2 WEEK) >= '$actual_date' AND visible_for LIKE '%$tring_like%' ORDER BY CASE WHEN finish_data < NOW() THEN 1 ELSE 0 END, finish_data";
            }
        }

        return $this->GetAuctions($id_user, $sql);
    }

    public function GetAuctionsCollections($output)
    {

        $id_user = $output["id_user"];

        $actual_date = date("Y-m-d H:i:s");

        $user_call = new Users($this->conn);
        $user = $user_call->GetUserInfo($id_user);

        if ($user["roles"] == 1 || $user["roles"] == 99) {
            $sql = "SELECT * FROM auctions WHERE id_auction_type = 2 AND deleted = 0 AND DATE_ADD(finish_data,INTERVAL 2 WEEK) >= '$actual_date' ORDER BY CASE WHEN finish_data < NOW() THEN 1 ELSE 0 END, finish_data";
        } else {

            if ($user["roles"] != 2) {
                $sql = "SELECT * FROM auctions WHERE created_data <= '$actual_date' AND id_auction_type = 2 AND deleted = 0 AND DATE_ADD(finish_data,INTERVAL 2 WEEK) >= '$actual_date' ORDER BY CASE WHEN finish_data < NOW() THEN 1 ELSE 0 END, finish_data";
            } else {

                $id_customer_role = $user["id_customer_role"];
                $tring_like = '"id_customer_role":"' . $id_customer_role . '"';

                $sql = "SELECT * FROM auctions WHERE created_data <= '$actual_date' AND id_auction_type = 2 AND deleted = 0 AND DATE_ADD(finish_data,INTERVAL 2 WEEK) >= '$actual_date' AND visible_for LIKE '%$tring_like%' ORDER BY CASE WHEN finish_data < NOW() THEN 1 ELSE 0 END, finish_data";
            }
        }

        return $this->GetAuctions($id_user, $sql);
    }

    public function GetAuctionsPrivateSale($output)
    {

        $id_user = $output["id_user"];

        $actual_date = date("Y-m-d H:i:s");

        $user_call = new Users($this->conn);
        $user = $user_call->GetUserInfo($id_user);

        if ($user["roles"] == 1 || $user["roles"] == 99) {
            $sql = "SELECT * FROM auctions WHERE id_auction_type = 3 AND deleted = 0 AND DATE_ADD(finish_data,INTERVAL 2 WEEK) >= '$actual_date' ORDER BY CASE WHEN finish_data < NOW() THEN 1 ELSE 0 END, finish_data";
        } else {

            if ($user["roles"] != 2) {
                $sql = "SELECT * FROM auctions WHERE created_data <= '$actual_date' AND id_auction_type = 3 AND deleted = 0 AND DATE_ADD(finish_data,INTERVAL 2 WEEK) >= '$actual_date' ORDER BY CASE WHEN finish_data < NOW() THEN 1 ELSE 0 END, finish_data";
            } else {

                $id_customer_role = $user["id_customer_role"];
                $tring_like = '"id_customer_role":"' . $id_customer_role . '"';
                $for_user_like = '"id_user":"' . $id_user . '"';

                $sql = "SELECT * FROM auctions WHERE created_data <= '$actual_date' AND id_auction_type = 3 AND deleted = 0 AND DATE_ADD(finish_data,INTERVAL 2 WEEK) >= '$actual_date' AND visible_for LIKE '%$tring_like%' AND for_user LIKE '%$for_user_like%' ORDER BY CASE WHEN finish_data < NOW() THEN 1 ELSE 0 END, finish_data";
            }
        }

        return $this->GetAuctions($id_user, $sql);
    }

    public function GetAllAuctions($output)
    {

        $id_user = $output["id_user"];
        $sql = "SELECT * FROM auctions WHERE deleted = 0 ORDER BY CASE WHEN finish_data < NOW() THEN 1 ELSE 0 END, finish_data";


        return $this->GetAuctions($id_user, $sql);
    }

    public function sincronizeBid()
    {

        $sql = "SELECT * FROM auctions_participant WHERE processed_temp = 0";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $auctions_participant = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $count_participant = 0;
            $count_participant_processed = 0;
            foreach ($auctions_participant as $participant) {

                $id_auction_participant = $participant["id_auction_participant"];

                $bid_initial = $participant["single_bid"];
                $quantity = $participant["quantity"];

                $bid_winner_initial = isset($participant["single_bid_winner"]) ? $participant["single_bid_winner"] : "'NULL'";
                $quantity_winner = isset($participant["quantity_winner"]) ? $participant["quantity_winner"] : "'NULL'";


                $single_bid = $bid_initial / $quantity;



                if ($bid_winner_initial != "'NULL'" && $quantity_winner != "'NULL'") {
                    $single_bid_winner = $single_bid;
                }else{
                    $single_bid_winner = "'NULL'";
                }


                if ($quantity > 1) {
                    $count_participant++;

                    $sql = "UPDATE auctions_participant SET 
                    single_bid = $single_bid,
                    single_bid_winner = $single_bid_winner,
                    processed_temp = 1
                    WHERE id_auction_participant = $id_auction_participant";
                    $sql = str_replace("'NULL'", "NULL", $sql);
                    //preparo l'istruzione
                    $stmt = $this->conn->prepare($sql);

                    //execute query
                    $stmt->execute();

                    if ($stmt->rowCount() > 0) {
                        $count_participant_processed++;
                        echo "ID Offer: $id_auction_participant | Bid -> Initial: $bid_initial __ Now: $single_bid <- | Quantity: $quantity  - |||| - Bid Winner -> Initial: $bid_winner_initial __ Now: $single_bid_winner <- | Quantity Winner: $quantity_winner \r\r\n\n";
                    }

                   // echo "ID Offer: $id_auction_participant | Bid -> Initial: $bid_initial __ Now: $single_bid <- | Quantity: $quantity  - |||| - Bid Winner -> Initial: $bid_winner_initial __ Now: $single_bid_winner <- | Quantity Winner: $quantity_winner \r\r\n\n";
                }
            }

            echo "Offers: $count_participant | Offers Processed: $count_participant_processed";
        }
    }
}
