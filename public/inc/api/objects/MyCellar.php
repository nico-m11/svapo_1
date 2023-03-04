<?php

// require '../../vendor/autoload.php';
require_once '../config/Config.php';
require 'Auctions.php';


class MyCellar
{

    // var connessione al db e tabella

    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    private function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    // OUTSTANDING PAYMENTS

    private function DetectStatoPayment($payment_deadline, $notice_as_paid)
    {
        $current_data = date("Y-m-d H:i:s");

        if ($notice_as_paid > 0) {
            return 1;
        } else {


            if ($payment_deadline >= $current_data) {
                return 1;
            } else {
                return 0;
            }
        }
    }

    // Get the auction won but to be paid
    public function GetAuctionPayments($output)
    {
        $id_user = $output["id_user"];

        $sql = "SELECT *, (single_bid * quantity) as bid, (single_bid_winner * quantity_winner) as bid_winner, ((single_bid_winner * quantity_winner) + insurance) as total_bid FROM auctions_participant WHERE auctions_participant.is_winner = 1 AND auctions_participant.status_availability = '0' AND auctions_participant.id_user = $id_user AND auctions_participant.deleted = 0";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $auctions_participant = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->GetSingleAuctionPayments($auctions_participant);
    }

    public function GetAllAuctionPayments($output)
    {
        $id_user = $output["id_user"];

        $sql = "SELECT *, (single_bid * quantity) as bid, (single_bid_winner * quantity_winner) as bid_winner, ((single_bid_winner * quantity_winner) + insurance) as total_bid FROM auctions_participant WHERE auctions_participant.is_winner = 1 AND auctions_participant.status_availability = '0' AND auctions_participant.deleted = 0";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $auctions_participant = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->GetSingleAuctionPayments($auctions_participant);
    }

    private function GetSingleAuctionPayments($auctions_participant)
    {

        // Require the info of product
        $auction = new Auction($this->conn);

        $result = array();
        foreach ($auctions_participant as $key => $participant) {

            $id_auction_participant = $participant["id_auction_participant"];
            $id_user = $participant["id_user"];

            // $sql1 = "SELECT SUM(import) as total_pay FROM wallet_handling WHERE id_user = $id_user AND id_auction_participant = $id_auction_participant";

            // //preparo l'istruzione
            // $stmt1 = $this->conn->prepare($sql1);

            // //execute query
            // $stmt1->execute();

            // $wallet_handling = $stmt1->fetch(PDO::FETCH_ASSOC);

            $user_call = new Users($this->conn);
            $user = $user_call->GetUserInfo($id_user);

            $id_auction = $participant["id_auction"];
            $participant["total_pay"] = 0;
            $participant["stato"] = $this->DetectStatoPayment($participant["payment_deadline"], $participant["notice_as_paid"]);
            $participant["payment_deadline_countdown"] = date("Y/m/d H:i:s", strtotime($participant["payment_deadline"]));
            $participant["total_bid"] = round($participant["total_bid"], 2);
            $info_auction = $auction->GetAuctions($id_user, "SELECT * FROM auctions WHERE id_auction = $id_auction");
            $result[] = $participant;
            $result[$key]["user"] = $user;
            $result[$key]["auction"] = $info_auction[0]["auction"];
        }

        return $result;
    }
    // END

    // Controll the payment after tot days
    public function ControlPaymentDeadline()
    {

        $sql = "SELECT *, (single_bid * quantity) as bid, (single_bid_winner * quantity_winner) as bid_winner, ((single_bid_winner * quantity_winner) + insurance) AS total_bid_winner FROM auctions_participant WHERE is_winner = 1 AND status_availability = '0' AND payment_deadline <= NOW() AND auctions_participant.notice_as_paid = 0 AND auctions_participant.deleted = 0";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $result = array();
        $auctions_participant = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $auctions_participant_count = count($auctions_participant);
        $auctions_participant_processed = 0;
        $auctions_participant_count_pay = 0;
        $auctions_participant_count_not_pay = 0;
        foreach ($auctions_participant as $key => $participant) {
            $auctions_participant_processed++;

            $id_user = $participant["id_user"];
            $id_auction_participant = $participant["id_auction_participant"];
            $quantity_winner = $participant["quantity_winner"];
            $id_auction = $participant["id_auction"];

            $user_call = new Users($this->conn);
            $info_user = $user_call->GetUserInfo($id_user);
            $info_user = $info_user;
            $payment_method = $info_user["method_payment"]["active"]["name"];
            $is_loser = 0;

            if ($payment_method == "CARD") {
                $stripe_call = new StripeSistem($this->conn);

                $id_customer_stripe = $info_user["id_customer_stripe"];

                $sql = "SELECT *, (single_bid * quantity) as bid, (single_bid_winner * quantity_winner) as bid_winner, ((single_bid_winner * quantity_winner) + insurance) AS total_bid_winner FROM auctions_participant WHERE id_auction_participant = $id_auction_participant AND id_user = $id_user AND deleted = 0";

                //preparo l'istruzione
                $stmt = $this->conn->prepare($sql);

                //execute query
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $auctions_participant = $stmt->fetch(PDO::FETCH_ASSOC);
                    $id_auction = $auctions_participant["id_auction"];

                    $import_chosen_for_wallet = round($auctions_participant["total_bid_winner"], 2);
                    $amount_stripe = $import_chosen_for_wallet * 100;

                    $input_CreatePaymentIntent = array(
                        "id_customer_stripe" => $id_customer_stripe,
                        "amount" => $amount_stripe
                    );

                    $result_arr = $stripe_call->CreatePaymentIntent($input_CreatePaymentIntent);

                    $is_pay = $result_arr["status"] == "succeeded" ? 1 : 0;

                    $method_payment = $info_user["method_payment"]["active"]["name"];
                    $id_method_payment = $info_user["method_payment"]["active"]["id_method_payment"];

                    if ($is_pay) {
                        $auctions_participant_count_pay++;

                        $input_handlingWallet = array(
                            "id_user" => $id_user,
                            "import" => $import_chosen_for_wallet,
                            "causal" => 'AUCTION-WIN-BALANCE',
                            "type" => "auction_participant",
                            "id_auction" => $id_auction,
                            "with_card" => 1,
                            "id_method_payment" => $id_method_payment,
                            "id_type" => $id_auction_participant,
                            "status" => 1
                        );
                        $handlingWallet = $user_call->reportTransition($input_handlingWallet);

                        if ($handlingWallet) {

                            $sql2 = "UPDATE auctions_participant SET 
                            status_availability = '1',
                            notice_as_paid = 1
                            WHERE id_user = $id_user AND id_auction_participant = $id_auction_participant";

                            //preparo l'istruzione
                            $stmt2 = $this->conn->prepare($sql2);

                            //execute query
                            $stmt2->execute();

                            if ($stmt2->rowCount() > 0) {
                                $event_log = new EventLog($this->conn);
                                $input_logSimple = [
                                    "parameters" => "ID auction Participant: $id_auction_participant | located in outstanding payment was paid through the automatic check",
                                    "type" => "event",
                                    "user" => $info_user["fullname"] . " Email: " . $info_user["email"],
                                    "event" => "ControlPaymentDeadline"
                                ];
                                $event_log->logSimple($input_logSimple);
                            }
                        }
                    } else {

                        $auctions_participant_count_not_pay++;

                        $input_handlingWallet = array(
                            "id_user" => $id_user,
                            "import" => $import_chosen_for_wallet,
                            "causal" => 'AUCTION-WIN-BALANCE',
                            "type" => "auction_participant",
                            "id_auction" => $id_auction,
                            "with_card" => 1,
                            "id_method_payment" => $id_method_payment,
                            "id_type" => $id_auction_participant,
                            "status" => 0
                        );
                        $handlingWallet = $user_call->reportTransition($input_handlingWallet);

                        if ($handlingWallet) {

                            $sql2 = "UPDATE auctions_participant SET 
                            is_winner = -1
                            WHERE id_user = $id_user AND id_auction_participant = $id_auction_participant";

                            //preparo l'istruzione
                            $stmt2 = $this->conn->prepare($sql2);

                            //execute query
                            $stmt2->execute();

                            if ($stmt2->rowCount() > 0) {
                                $QualityScoreUserHandling = $user_call->QualityScoreUserHandling($id_user, 1, "AUCTION-NOT-PAY");

                                if ($QualityScoreUserHandling > 0) {

                                    $event_log = new EventLog($this->conn);
                                    $input_logSimple = [
                                        "parameters" => "ID auction Participant: $id_auction_participant | located in outstanding payment was not paid through the automatic check",
                                        "type" => "event",
                                        "user" => $info_user["fullname"] . " Email: " . $info_user["email"],
                                        "event" => "ControlPaymentDeadline"
                                    ];
                                    $event_log->logSimple($input_logSimple);
                                    $is_loser = 1;
                                }
                            }
                        }
                    }
                }
            } else {

                $auctions_participant_count_not_pay++;

                // if ($stmt1->rowCount() > 0) {

                $sql2 = "UPDATE auctions_participant SET 
                is_winner = -1
                WHERE id_user = $id_user AND id_auction_participant = $id_auction_participant";

                //preparo l'istruzione
                $stmt2 = $this->conn->prepare($sql2);

                //execute query
                $stmt2->execute();

                if ($stmt2->rowCount() > 0) {
                    $QualityScoreUserHandling = $user_call->QualityScoreUserHandling($id_user, 1, "AUCTION-NOT-PAY");

                    if ($QualityScoreUserHandling > 0) {
                        $event_log = new EventLog($this->conn);
                        $input_logSimple = [
                            "parameters" => "ID auction Participant: $id_auction_participant | located in outstanding payment was not paid through the automatic check",
                            "type" => "event",
                            "user" => $info_user["fullname"] . " Email: " . $info_user["email"],
                            "event" => "ControlPaymentDeadline"
                        ];
                        $event_log->logSimple($input_logSimple);
                        $is_loser = 1;
                    }
                }
            }

            if ($is_loser > 0) {

                // Require the info of product
                $auction = new Auction($this->conn);
                $info_auction = $auction->GetAuctions($id_user, "SELECT * FROM auctions WHERE id_auction = $id_auction");
                $id_lot = $info_auction[0]["auction"]["id_lot"];
                // HANDLING LOT
                $lots_call = new Lots($this->conn);

                $quantity_chosen_for_lot = $quantity_winner;

                $lots_call->handlingLot($id_user, $id_lot, $quantity_chosen_for_lot, 'AUCTION-LOSE', $id_auction);
            }
        }

        return "Offer in outstanding payment: $auctions_participant_count | Processed: $auctions_participant_processed | Paid: $auctions_participant_count_pay | Non Paid: $auctions_participant_count_not_pay";
    }

    // API CUSTOMER Payment of Auction selected
    public function PaymentAuction($output)
    {
        $id_user = $output["id_user"];
        $auctions_participant = $output["auctions_participant"];
        $total_pay = $output["total_pay"];

        $user_call = new Users($this->conn);
        $info_user = $user_call->GetUserInfo($id_user);
        $info_user = $info_user;

        $stripe_call = new StripeSistem($this->conn);

        $id_customer_stripe = $info_user["id_customer_stripe"];


        $count_auction = count($auctions_participant);
        $count_true = 0;
        foreach ($auctions_participant as $key => $id_auction_participant) {
            $sql = "SELECT *, (single_bid * quantity) as bid, (single_bid_winner * quantity_winner) as bid_winner, ((single_bid_winner * quantity_winner) + insurance) AS total_bid_winner FROM auctions_participant WHERE id_auction_participant = $id_auction_participant AND id_user = $id_user AND deleted = 0";

            //preparo l'istruzione
            $stmt = $this->conn->prepare($sql);

            //execute query
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $auctions_participant = $stmt->fetch(PDO::FETCH_ASSOC);
                $id_auction = $auctions_participant["id_auction"];

                $import_chosen_for_wallet = round($auctions_participant["total_bid_winner"], 2);
                $amount_stripe = $import_chosen_for_wallet * 100;

                $input_CreatePaymentIntent = array(
                    "id_customer_stripe" => $id_customer_stripe,
                    "amount" => $amount_stripe
                );

                $result_arr = $stripe_call->CreatePaymentIntent($input_CreatePaymentIntent);

                $is_pay = $result_arr["status"] == "succeeded" ? 1 : 0;

                $method_payment = $info_user["method_payment"]["active"]["name"];
                $id_method_payment = $info_user["method_payment"]["active"]["id_method_payment"];

                if ($is_pay) {


                    $input_handlingWallet = array(
                        "id_user" => $id_user,
                        "import" => $import_chosen_for_wallet,
                        "causal" => 'AUCTION-WIN-BALANCE',
                        "type" => "auction_participant",
                        "id_auction" => $id_auction,
                        "with_card" => 1,
                        "id_method_payment" => $id_method_payment,
                        "id_type" => $id_auction_participant,
                        "status" => 1
                    );
                    $handlingWallet = $user_call->reportTransition($input_handlingWallet);

                    if ($handlingWallet) {

                        $sql2 = "UPDATE auctions_participant SET 
                        status_availability = '1',
                        notice_as_paid = 1
                        WHERE id_user = $id_user AND id_auction_participant = $id_auction_participant";

                        //preparo l'istruzione
                        $stmt2 = $this->conn->prepare($sql2);

                        //execute query
                        $stmt2->execute();

                        $count_true = $count_true + $stmt2->rowCount();
                    }
                } else {
                    $input_handlingWallet = array(
                        "id_user" => $id_user,
                        "import" => $import_chosen_for_wallet,
                        "causal" => 'AUCTION-WIN-BALANCE',
                        "type" => "auction_participant",
                        "id_auction" => $id_auction,
                        "with_card" => 1,
                        "id_method_payment" => $id_method_payment,
                        "id_type" => $id_auction_participant,
                        "status" => 0
                    );

                    $user_call->reportTransition($input_handlingWallet);
                }
            }
        }

        if ($count_auction == $count_true) {
            return 1;
        } else if ($count_auction != $count_true && $count_true > 0) {
            return -2;
        } else {
            return -1;
        }
    }

    // API CUSTOMER Payment of Auction selected
    public function MarkPaid($output)
    {
        $id_user = $output["id_user"];
        $auctions_participant = $output["auctions_participant"];
        $total_pay = $output["total_pay"];

        $user_call = new Users($this->conn);
        $info_user = $user_call->GetUserInfo($id_user);
        $info_user = $info_user;

        $stripe_call = new StripeSistem($this->conn);

        $id_customer_stripe = $info_user["id_customer_stripe"];


        $count_auction = count($auctions_participant);
        $count_true = 0;
        foreach ($auctions_participant as $key => $id_auction_participant) {
            $sql = "SELECT * FROM auctions_participant WHERE id_auction_participant = $id_auction_participant AND id_user = $id_user AND deleted = 0";

            //preparo l'istruzione
            $stmt = $this->conn->prepare($sql);

            //execute query
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $auctions_participant = $stmt->fetch(PDO::FETCH_ASSOC);
                $payment_deadline = $auctions_participant["payment_deadline"];

                $day_for_deadline = "+14 days";
                $payment_deadline = date('Y-m-d H:i:s', strtotime($payment_deadline . " " . $day_for_deadline));

                $sql2 = "UPDATE auctions_participant SET 
                payment_deadline = '$payment_deadline',
                notice_as_paid = 2
                WHERE id_user = $id_user AND id_auction_participant = $id_auction_participant";

                //preparo l'istruzione
                $stmt2 = $this->conn->prepare($sql2);

                //execute query
                $stmt2->execute();

                $count_true = $count_true + $stmt2->rowCount();
            }
        }

        if ($count_auction == $count_true) {
            return 1;
        } else if ($count_auction != $count_true && $count_true > 0) {
            return -2;
        }
    }

    // API CUSTOMER Payment of Auction selected
    public function SendInformationViaEmail($output)
    {
        $id_user = $output["id_user"];

        //$mail = new EmailSistem($this->conn);
        $user_call = new Users($this->conn);
        $info_user = $user_call->GetUserInfo($id_user);

        $email = $info_user["email"];
        $first_name = $info_user["first_name"];

        $purpose = $output["purpose"];
        $import = $output["import"];
        $Beneficiary = $output["dataBankTransfer"]["Beneficiary"];
        $IBAN = $output["dataBankTransfer"]["IBAN"];
        $BIC = $output["dataBankTransfer"]["BIC"];
        $BeneficiaryAddress = $output["dataBankTransfer"]["BeneficiaryAddress"];
        $PaymentInstitution = $output["dataBankTransfer"]["PaymentInstitution"];
        $PaymentInstitutionAddress = $output["dataBankTransfer"]["PaymentInstitutionAddress"];

        $input = array(
            "from" => "no-reply@crurated.com",
            "to" => $email,
            "subject" => "Information for Bank Transfer",
            "email" => [
                "title" => "Dear $first_name",
                "content" => [
                    [
                        "format" => "paragraph",
                        "text" => "This are the information for the bank transfer:",
                        "type" => "1Col",
                    ],
                    [
                        "format" => "paragraph",
                        "textLeft" => "Import",
                        "textRight" => $import . "€",
                        "type" => "2Col",
                    ],
                    [
                        "format" => "paragraph",
                        "textLeft" => "Purpose",
                        "textRight" => $purpose,
                        "type" => "2Col",
                    ],
                    [
                        "format" => "paragraph",
                        "textLeft" => "Beneficiary",
                        "textRight" => $Beneficiary,
                        "type" => "2Col",
                    ],
                    [
                        "format" => "paragraph",
                        "textLeft" => "IBAN",
                        "textRight" => $IBAN,
                        "type" => "2Col",
                    ],
                    [
                        "format" => "paragraph",
                        "textLeft" => "BIC",
                        "textRight" => $BIC,
                        "type" => "2Col",
                    ],
                    [
                        "format" => "paragraph",
                        "textLeft" => "Beneficiary Address",
                        "textRight" => $BeneficiaryAddress,
                        "type" => "2Col",
                    ],
                    [
                        "format" => "paragraph",
                        "textLeft" => "Payment Institution",
                        "textRight" => $PaymentInstitution,
                        "type" => "2Col",
                    ],
                    [
                        "format" => "paragraph",
                        "textLeft" => "Payment Institution Address",
                        "textRight" => $PaymentInstitutionAddress,
                        "type" => "2Col",
                    ]
                ]
            ]
        );

        //return $mail->SendEmailSistem($input);
    }

    public function PaymentAuctionForAdmin($output)
    {
        $id_user = $output["id_user"];
        $id_auction_participant = $output["id_auction_participant"];
        $decision = $output["decision"];

        $user_call = new Users($this->conn);

        $sql = "SELECT *, (single_bid * quantity) as bid, (single_bid_winner * quantity_winner) as bid_winner, ((single_bid_winner * quantity_winner) + insurance) AS total_bid_winner FROM auctions_participant WHERE id_auction_participant = $id_auction_participant AND id_user = $id_user AND deleted = 0";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $auctions_participant = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_auction = $auctions_participant["id_auction"];

            $import_chosen_for_wallet = round($auctions_participant["total_bid_winner"], 2);
            $quantity_winner = $auctions_participant["quantity_winner"];

            $info_user = $user_call->GetUserInfo($id_user);
            $method_payment = $info_user["method_payment"]["active"]["name"];
            $id_method_payment = $info_user["method_payment"]["active"]["id_method_payment"];

            $is_loser = 0;

            if ($decision > 0) {
                $is_pay = 1;
            } else {
                $is_pay = 0;
            }


            if ($is_pay) {

                $input_handlingWallet = array(
                    "id_user" => $id_user,
                    "import" => $import_chosen_for_wallet,
                    "causal" => 'AUCTION-WIN-BALANCE',
                    "type" => "auction_participant",
                    "id_auction" => $id_auction,
                    "with_card" => 1,
                    "id_method_payment" => $id_method_payment,
                    "id_type" => $id_auction_participant,
                    "status" => 1
                );
                $handlingWallet = $user_call->reportTransition($input_handlingWallet);

                if ($handlingWallet) {

                    $sql2 = "UPDATE auctions_participant SET 
                        status_availability = '1',
                        notice_as_paid = 1
                        WHERE id_user = $id_user AND id_auction_participant = $id_auction_participant";

                    //preparo l'istruzione
                    $stmt2 = $this->conn->prepare($sql2);

                    //execute query
                    $stmt2->execute();

                    return $stmt2->rowCount();
                } else {
                    return -1;
                }
            } else {

                $input_handlingWallet = array(
                    "id_user" => $id_user,
                    "import" => $import_chosen_for_wallet,
                    "causal" => 'AUCTION-WIN-BALANCE',
                    "type" => "auction_participant",
                    "id_auction" => $id_auction,
                    "with_card" => 1,
                    "id_method_payment" => $id_method_payment,
                    "id_type" => $id_auction_participant,
                    "status" => 0
                );
                $handlingWallet = $user_call->reportTransition($input_handlingWallet);

                if ($handlingWallet) {

                    $sql2 = "UPDATE auctions_participant SET 
                    is_winner = -1
                    WHERE id_user = $id_user AND id_auction_participant = $id_auction_participant";

                    //preparo l'istruzione
                    $stmt2 = $this->conn->prepare($sql2);

                    //execute query
                    $stmt2->execute();

                    if ($stmt2->rowCount() > 0) {
                        $QualityScoreUserHandling = $user_call->QualityScoreUserHandling($id_user, 1, "AUCTION-NOT-PAY");

                        if ($QualityScoreUserHandling > 0) {

                            $event_log = new EventLog($this->conn);
                            $input_logSimple = [
                                "parameters" => "ID auction Participant: $id_auction_participant | located in outstanding payment was not paid through the admin check",
                                "type" => "event",
                                "user" => $info_user["fullname"] . " Email: " . $info_user["email"],
                                "event" => "PaymentAuctionForAdmin"
                            ];
                            $event_log->logSimple($input_logSimple);
                            $is_loser = 1;
                        }
                    }
                }
            }

            if ($is_loser > 0) {

                // Require the info of product
                $auction = new Auction($this->conn);
                $info_auction = $auction->GetAuctions($id_user, "SELECT * FROM auctions WHERE id_auction = $id_auction");
                $id_lot = $info_auction[0]["auction"]["id_lot"];
                // HANDLING LOT
                $lots_call = new Lots($this->conn);

                $quantity_chosen_for_lot = $quantity_winner;

                return $lots_call->handlingLot($id_user, $id_lot, $quantity_chosen_for_lot, 'AUCTION-LOSE', $id_auction);
            }
        }
    }

    // END OUTSTANDING PAYMENTS


    // My Cellar
    // Get the auction won and In our warehouse
    public function GetAuctionWon($output)
    {
        $id_user = $output["id_user"];

        $sql = "SELECT *, (single_bid * quantity) as bid, (single_bid_winner * quantity_winner) as bid_winner, (single_bid_winner * quantity_winner) as bid_winner, ((single_bid_winner * quantity_winner) + insurance) as total_bid FROM auctions_participant WHERE auctions_participant.is_winner = 1 AND auctions_participant.status_availability > '0' AND auctions_participant.id_user = $id_user AND auctions_participant.deleted = 0";


        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $auctions_participant = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->GetSingleAuctionWon($auctions_participant);
    }

    public function GetAllAuctionWon($output)
    {
        $id_user = $output["id_user"];

        $sql = "SELECT *, ((single_bid_winner * quantity_winner) + insurance) as total_bid FROM auctions_participant WHERE auctions_participant.is_winner = 1 AND auctions_participant.status_availability > '0' AND auctions_participant.status_availability != '3' AND auctions_participant.deleted = 0";


        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $auctions_participant = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->GetSingleAuctionWon($auctions_participant);
    }

    public function GetAuctionWonUser($output)
    {
        $id_user = $output["id_user"];

        $sql = "SELECT *, (single_bid * quantity) as bid, (single_bid_winner * quantity_winner) as bid_winner, ((single_bid_winner * quantity_winner) + insurance) as total_bid FROM auctions_participant WHERE auctions_participant.is_winner = 1 AND auctions_participant.status_availability > '0' AND auctions_participant.status_availability != '3' AND auctions_participant.deleted = 0 and auctions_participant.id_user = $id_user";


        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $auctions_participant = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->GetSingleAuctionWon($auctions_participant);
    }

    private function GetSingleAuctionWon($auctions_participant)
    {
        // Require the info of product
        $auction = new Auction($this->conn);

        $result = array();

        $shipping = array();
        foreach ($auctions_participant as $key => $participant) {

            $id_auction_participant = $participant["id_auction_participant"];
            $id_user = $participant["id_user"];

            // $sql1 = "SELECT *, SUM(import) as total_pay FROM wallet_handling AND id_user = $id_user AND id_auction_participant = $id_auction_participant";

            // //preparo l'istruzione
            // $stmt1 = $this->conn->prepare($sql1);

            // //execute query
            // $stmt1->execute();

            // $wallet_handling = $stmt1->fetch(PDO::FETCH_ASSOC);

            $user_call = new Users($this->conn);
            $user = $user_call->GetUserInfo($id_user);

            $id_auction = $participant["id_auction"];
            $participant["total_pay"] = round($participant["total_bid"], 2);
            $participant["total_bid"] = round($participant["total_bid"], 2);

            $info_auction = $auction->GetAuctions($id_user, "SELECT * FROM auctions WHERE id_auction = $id_auction");
            $result[] = $participant;
            $result[$key]["user"] = $user;
            $result[$key]["auction"] = $info_auction[0]["auction"];

            if ($participant["status_availability"] == 1) {
                $type_won = "WithProducers";
            } elseif ($participant["status_availability"] == 2) {
                $type_won = "OnHold";
            } elseif ($participant["status_availability"] == 3) {

                $string_search = '"id_auction_participant":"' . $id_auction_participant . '"';
                $sql2 = "SELECT * FROM shipping WHERE id_user = $id_user AND (id_auctions_participant LIKE '%$string_search%')";
                //preparo l'istruzione
                $stmt2 = $this->conn->prepare($sql2);
                //execute query
                $stmt2->execute();

                if ($stmt2->rowCount() > 0) {

                    $shipping_arr = $stmt2->fetch(PDO::FETCH_ASSOC);
                    $shipping = $this->GetAuctionSingleShipping($shipping_arr["id_shipping"]);

                    if ($shipping_arr["status_shipping"] == 4) {
                        $type_won = "InMyHouse";
                    } else if ($shipping_arr["status_shipping"] < 4) {
                        $type_won = "UnderProcess";
                    }
                }
            }

            $result[$key]["shipping"] = $shipping;
            $result[$key]["type_won"] = $type_won;
        }

        return $result;
    }

    // API ADMIN Change the status of auction won
    public function ChangeStatusAviability($output)
    {
        $id_user = $output["id_user"];
        $id_auction_participant = $output["id_auction_participant"];
        $status_availability = $output["status_availability"];

        $sql = "UPDATE auctions_participant SET 
        status_availability = '$status_availability'
        WHERE id_user = $id_user AND id_auction_participant = $id_auction_participant";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        return $stmt->rowCount();
    }

    // API CUSTOMER Request the shippment of the auctions selected
    public function RequestShipping($output)
    {

        $id_user = $output["id_user"];
        $auctions_participant = $output["auctions_participant"];
        $id_address = $output["id_address"];

        $id_order = $this->generateRandomString(10);

        $i = 1;
        $count_auctions_participant = count($auctions_participant);
        $count_true = 0;

        $array_auction_participant = array();
        foreach ($auctions_participant as $key => $id_auction_participant) {
            $array_auction_participant[]["id_auction_participant"] = $id_auction_participant;

            $sql2 = "UPDATE auctions_participant SET 
            status_availability = '3'
            WHERE id_user = $id_user AND id_auction_participant = $id_auction_participant";

            //preparo l'istruzione
            $stmt2 = $this->conn->prepare($sql2);

            //execute query
            $stmt2->execute();

            $count_true = $count_true + $stmt2->rowCount();

            $i++;
        }

        $id_auctions_participant = json_encode($array_auction_participant);

        if ($count_auctions_participant == $count_true) {

            $sql3 = "INSERT INTO shipping (id_order,id_user,id_address,id_auctions_participant) VALUES ('$id_order','$id_user','$id_address','$id_auctions_participant')";

            //preparo l'istruzione
            $stmt3 = $this->conn->prepare($sql3);

            //execute query
            $stmt3->execute();

            return $stmt3->rowCount();
        }
    }

    // END My Cellar

    // COURIER SISTEM

    // Get courier active
    public function GetCourierService($output)
    {
        $id_user = $output["id_user"];
        $sql = "SELECT * FROM shipping_courier_service WHERE active = 1 AND deleted = 0";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    // Get all cuorier
    public function GetAllCourierService($output)
    {
        $id_user = $output["id_user"];

        $sql = "SELECT * FROM shipping_courier_service WHERE deleted = 0";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    // Get courier active
    public function GetCountries($output)
    {
        $id_user = $output["id_user"];
        $sql = "SELECT * FROM countries WHERE active = 1 AND deleted = 0";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    // Get all cuorier
    public function GetAllCountries($output)
    {
        $id_user = $output["id_user"];

        $sql = "SELECT * FROM countries";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    // Edit stato courier
    public function EditStatoCountry($output)
    {
        $id_user = $output["id_user"];
        $id_country = $output["id_country"];
        $stato = $output["stato"];

        $sql = "UPDATE countries SET 
            active = $stato
            WHERE id_country = $id_country";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            return 1;
        }
    }

    // Create courier
    public function CreateCourier($output)
    {

        $id_user = $output["id_user"];
        $courier_name = $output["courier_name"];
        $business_day = $output["business_day"];

        $sql = "INSERT INTO shipping_courier_service (id_user,courier_name,courier_business_day) VALUES ('$id_user','$courier_name','$business_day')";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $causal = 'COURIER-CREATED';
            $id_courier_service = $this->conn->lastInsertId();
            $id_notes = 'NULL';
            $tracking = 'NULL';
            $shipping_price = 'NULL';
            $taxes = 'NULL';
            $id_shipping = 'NULL';
            $status_shipping = 'NULL';

            $ShippingHadling = $this->ShippingHadling($id_user, $id_shipping, $causal, $id_notes, $tracking, $shipping_price, $taxes, $id_courier_service,  $status_shipping);

            return $ShippingHadling;
        }
    }

    // Edit courier
    public function EditCourier($output)
    {

        $id_user = $output["id_user"];
        $id_courier = $output["id_courier_service"];
        $courier_name = $output["courier_name"];
        $business_day = $output["business_day"];

        $sql = "UPDATE shipping_courier_service SET 
        courier_name = '$courier_name',
        courier_business_day = '$business_day'
        WHERE id_courier_service = $id_courier";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $causal = 'COURIER-EDITED';
            $id_courier_service = $this->conn->lastInsertId();
            $id_notes = 'NULL';
            $tracking = 'NULL';
            $shipping_price = 'NULL';
            $taxes = 'NULL';
            $id_shipping = 'NULL';
            $status_shipping = 'NULL';

            $ShippingHadling = $this->ShippingHadling($id_user, $id_shipping, $causal, $id_notes, $tracking, $shipping_price, $taxes, $id_courier_service,  $status_shipping);

            return $ShippingHadling;
        }
    }

    // Edit stato courier
    public function EditStatoCourier($output)
    {
        $id_user = $output["id_user"];
        $id_courier = $output["id_courier_service"];
        $stato = $output["stato"];

        $sql = "UPDATE shipping_courier_service SET 
        active = '$stato'
        WHERE id_courier_service = $id_courier";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $causal = 'COURIER-STATO-EDITED';
            $id_courier_service = $this->conn->lastInsertId();
            $id_notes = 'NULL';
            $tracking = 'NULL';
            $shipping_price = 'NULL';
            $taxes = 'NULL';
            $id_shipping = 'NULL';
            $status_shipping = 'NULL';

            $ShippingHadling = $this->ShippingHadling($id_user, $id_shipping, $causal, $id_notes, $tracking, $shipping_price, $taxes, $id_courier_service,  $status_shipping);

            return $ShippingHadling;
        }
    }

    // Delete courier
    public function DeleteCourier($output)
    {

        $id_user = $output["id_user"];
        $id_courier = $output["id_courier_service"];

        $sql = "UPDATE shipping_courier_service SET 
            deleted = -1
            WHERE id_courier_service = $id_courier";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $causal = 'COURIER-STATO-EDITED';
            $id_courier_service = $this->conn->lastInsertId();
            $id_notes = 'NULL';
            $tracking = 'NULL';
            $shipping_price = 'NULL';
            $taxes = 'NULL';
            $id_shipping = 'NULL';
            $status_shipping = 'NULL';

            $ShippingHadling = $this->ShippingHadling($id_user, $id_shipping, $causal, $id_notes, $tracking, $shipping_price, $taxes, $id_courier_service,  $status_shipping);

            return $ShippingHadling;
        }
    }

    // END COURIER SISTEM


    // SHIPMENTS  

    public function GetAuctionSingleShipping($id_shipping)
    {

        $sql = "SELECT *, (shipping.shipping_price + shipping.taxes) as total_price FROM shipping LEFT JOIN shipping_courier_service ON shipping_courier_service.id_courier_service = shipping.id_courier_service WHERE shipping.id_shipping = $id_shipping";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $result = array();
        if ($stmt->rowCount() > 0) {

            $shipping = $stmt->fetch(PDO::FETCH_ASSOC);

            $id_user = $shipping["id_user"];
            $id_address = $shipping["id_address"];

            $sql2 = "SELECT * FROM users_address WHERE id_address = $id_address";

            //preparo l'istruzione
            $stmt2 = $this->conn->prepare($sql2);

            //execute query
            $stmt2->execute();

            $sql3 = "SELECT *, edited_date as shipped_on FROM shipping_handling  WHERE id_shipping = $id_shipping AND status_shipping = 3 ORDER BY edited_date DESC LIMIT 1";

            //preparo l'istruzione
            $stmt3 = $this->conn->prepare($sql3);

            //execute query
            $stmt3->execute();

            $shipping_address = $stmt2->fetch(PDO::FETCH_ASSOC);
            $shipping_handling = $stmt3->fetch(PDO::FETCH_ASSOC);
            $shipping["request_date_frontend"] = date("Y/m/d H:i:s", strtotime($shipping["request_date"]));
            $result["shipping_info"] = $shipping;

            if ($stmt3->rowCount() > 0) {
                $result["shipping_info"]["shipped_on"] = $shipping_handling["shipped_on"];
                $result["shipping_info"]["shipped_on_frontend"] = date("Y/m/d H:i:s", strtotime($shipping_handling["shipped_on"]));
            } else {
                $result["shipping_info"]["shipped_on"] = '';
                $result["shipping_info"]["shipped_on_frontend"] = '';
            }

            $input = array(
                "id_user" => $id_user,
                "id_shipping" => $shipping["id_shipping"]
            );

            $result["notes"] = $this->GetNotesShipping($input);
            $result["shipping_address"] = $shipping_address;

            if ($shipping_address["company_name"] != 'NULL') {
                $recipient = $shipping_address["company_name"];
            } else {
                $recipient = $shipping_address["full_name"];
            }

            $result["shipping_address"]["addressComplete"] = $recipient . " " . $shipping_address["addressline1"] . " " . $shipping_address["addressline2"] . " " . $shipping_address["country"] . " " . $shipping_address["region"] . " " . $shipping_address["city"] . " " . $shipping_address["postal_code"];


            return $result;
        }
    }

    // Get the auction to request shipping
    public function GetAuctionShipping($output)
    {
        $id_user = $output["id_user"];

        $sql = "SELECT * FROM shipping WHERE id_user = $id_user";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $shippings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->GetSingleAuctionShipping($shippings);
    }

    public function GetAllAuctionShipping($output)
    {

        $sql = "SELECT * FROM shipping";


        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $shippings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->GetSingleAuctionShipping($shippings);
    }

    private function GetSingleAuctionShipping($shippings)
    {

        $result = array();
        foreach ($shippings as $key => $shipping) {

            $id_user = $shipping["id_user"];
            $id_shipping = $shipping["id_shipping"];

            $result[] = $this->GetAuctionSingleShipping($id_shipping);

            $id_auctions_participant = json_decode($shipping["id_auctions_participant"], true);

            foreach ($id_auctions_participant as $key_a => $id_auction_participant) {

                $id_auction_participant = $id_auction_participant["id_auction_participant"];
                $sql = "SELECT *, (single_bid * quantity) as bid, (single_bid_winner * quantity_winner) as bid_winner, ((single_bid_winner * quantity_winner) + insurance) as total_bid FROM auctions_participant WHERE id_user = $id_user AND id_auction_participant = $id_auction_participant AND auctions_participant.deleted = 0";

                //preparo l'istruzione
                $stmt = $this->conn->prepare($sql);

                //execute query
                $stmt->execute();

                if ($stmt->rowCount() > 0) {

                    $participant = $stmt->fetch(PDO::FETCH_ASSOC);

                    // $sql1 = "SELECT *, SUM(import) as total_pay FROM wallet_handling AND id_user = $id_user AND id_auction_participant = $id_auction_participant";

                    // //preparo l'istruzione
                    // $stmt1 = $this->conn->prepare($sql1);

                    // //execute query
                    // $stmt1->execute();

                    // $wallet_handling = $stmt1->fetch(PDO::FETCH_ASSOC);

                    $auction = new Auction($this->conn);

                    $user_call = new Users($this->conn);
                    $user = $user_call->GetUserInfo($id_user);

                    $id_auction = $participant["id_auction"];
                    $participant["total_pay"] = round($participant["total_bid"], 2);
                    $participant["total_bid"] = round($participant["total_bid"], 2);
                    $info_auction = $auction->GetAuctions($id_user, "SELECT * FROM auctions WHERE id_auction = $id_auction");
                    $result[$key]["auctions"][$key_a]["participant"] = $participant;
                    $result[$key]["auctions"][$key_a]["user"] = $user;
                    $result[$key]["auctions"][$key_a]["auction"] = $info_auction[0]["auction"];
                }
            }
        }

        return $result;
    }

    // Get notes
    public function GetNotesShipping($output)
    {
        $id_user = $output["id_user"];
        $id_shipping = $output["id_shipping"];

        $sql = "SELECT * FROM shipping_notes WHERE id_shipping = $id_shipping AND deleted = 0";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = array();
        foreach ($notes as $key => $note) {
            $user_call = new Users($this->conn);
            $user = $user_call->GetUserInfo($note["id_user"]);

            $result[] = $note;
            $result[$key]["user"] = $user;
        }

        return $result;
    }

    // Delete notes
    public function DeleteNotesShipping($output)
    {
        $id_user = $output["id_user"];
        $id_shipping_notes = $output["id_shipping_notes"];

        $sql = "SELECT * FROM shipping_notes WHERE id_shipping_notes = $id_shipping_notes AND deleted = 0";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $sql = "UPDATE shipping_notes SET 
            deleted = -1
    
            WHERE id_shipping_notes = '$id_shipping_notes'";

            //preparo l'istruzione
            $stmt = $this->conn->prepare($sql);

            //execute query
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $causal = 'NOTES-DELETED';
                $id_notes = $id_shipping_notes;
                $id_shipping = 0;
                $tracking = 'NULL';
                $shipping_price = 'NULL';
                $taxes = 'NULL';
                $id_courier_service = 'NULL';
                $status_shipping = 'NULL';

                return $this->ShippingHadling($id_user, $id_shipping, $causal, $id_notes, $tracking, $shipping_price, $taxes, $id_courier_service, $status_shipping);
            }
        }
    }

    // Add notes
    public function CreateNotesShipping($output)
    {
        $id_user = $output["id_user"];
        $id_shipping = $output["id_shipping"];
        $notes = addslashes($output["notes"]);

        $sql = "INSERT INTO shipping_notes (id_user,id_shipping,notes) VALUES ('$id_user','$id_shipping','$notes')";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $causal = 'NOTES-CREATED';
            $id_notes = $this->conn->lastInsertId();
            $tracking = 'NULL';
            $shipping_price = 'NULL';
            $taxes = 'NULL';
            $id_courier_service = 'NULL';
            $status_shipping = 'NULL';

            $ShippingHadling = $this->ShippingHadling($id_user, $id_shipping, $causal, $id_notes, $tracking, $shipping_price, $taxes, $id_courier_service,  $status_shipping);

            if ($ShippingHadling) {
                $sql = "SELECT * FROM shipping_notes WHERE id_shipping_notes = $id_notes AND deleted = 0";

                //preparo l'istruzione
                $stmt = $this->conn->prepare($sql);

                //execute query
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $result = array();
                    foreach ($notes as $key => $note) {
                        $user_call = new Users($this->conn);
                        $user = $user_call->GetUserInfo($note["id_user"]);

                        $result[] = $note;
                        $result[$key]["user"] = $user;
                    }

                    return $result;
                }
            }
        }
    }

    // API ADMIN Send Payment request
    public function SendPaymentRequest($output)
    {

        $id_user = $output["id_user"];
        $id_shipping = $output["id_shipping"];
        $shipping_price = $output["shipping_price"];
        $taxes = $output["taxes"];
        $id_courier_service = $output["id_courier_service"];

        $sql = "SELECT * FROM shipping WHERE id_shipping = $id_shipping";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $sql1 = "UPDATE shipping SET 
            shipping_price = $shipping_price,
            taxes = $taxes,
            id_courier_service = $id_courier_service,
            status_shipping = '1'
            WHERE id_shipping = $id_shipping";

            //preparo l'istruzione
            $stmt1 = $this->conn->prepare($sql1);

            //execute query
            $stmt1->execute();

            if ($stmt1->rowCount() > 0) {

                $causal = 'PAYMENT-REQUEST';
                $id_notes = 'NULL';
                $tracking = 'NULL';
                $shipping_price = $shipping_price;
                $taxes = $taxes;
                $id_courier_service = $id_courier_service;
                $status_shipping = 1;
                return $this->ShippingHadling($id_user, $id_shipping, $causal, $id_notes, $tracking, $shipping_price, $taxes, $id_courier_service, $status_shipping);
            }
        }
    }

    // API ADMIN Send Payment request
    public function SetShipped($output)
    {

        $id_user = $output["id_user"];
        $id_shipping = $output["id_shipping"];
        $tracking = addslashes($output["tracking"]);
        $status_shipping = $output["status_shipping"];

        $sql = "SELECT * FROM shipping WHERE id_shipping = $id_shipping";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $sql1 = "UPDATE shipping SET 
            tracking = '$tracking',
            status_shipping = '$status_shipping'
            WHERE id_shipping = $id_shipping";

            //preparo l'istruzione
            $stmt1 = $this->conn->prepare($sql1);

            //execute query
            $stmt1->execute();

            if ($stmt1->rowCount() > 0) {

                $causal = 'EDIT-SET-SHIPPED';
                $id_notes = 'NULL';
                $tracking = $tracking;
                $shipping_price = 'NULL';
                $taxes = 'NULL';
                $id_courier_service = 'NULL';
                $status_shipping = $status_shipping;

                return $this->ShippingHadling($id_user, $id_shipping, $causal, $id_notes, $tracking, $shipping_price, $taxes, $id_courier_service, $status_shipping);
            }
        }
    }

    // API CUSTOMER Payment of Shippment
    public function PaymentShippment($output)
    {

        $id_user = $output["id_user"];
        $shipping_price = round($output["shipping_price"], 2);
        $id_shipping = $output["id_shipping"];

        $user_call = new Users($this->conn);
        $info_user = $user_call->GetUserInfo($id_user);
        $info_user = $info_user;

        $stripe_call = new StripeSistem($this->conn);

        $id_customer_stripe = $info_user["id_customer_stripe"];

        $amount_stripe = $shipping_price * 100;

        $input_CreatePaymentIntent = array(
            "id_customer_stripe" => $id_customer_stripe,
            "amount" => $amount_stripe
        );

        $result_arr = $stripe_call->CreatePaymentIntent($input_CreatePaymentIntent);

        $is_pay = $result_arr["status"] == "succeeded" ? 1 : 0;

        $method_payment = $info_user["method_payment"]["active"]["name"];
        $id_method_payment = $info_user["method_payment"]["active"]["id_method_payment"];

        if ($is_pay) {

            $sql = "SELECT * FROM shipping WHERE id_shipping= $id_shipping AND status_shipping = '1'";

            //preparo l'istruzione
            $stmt = $this->conn->prepare($sql);

            //execute query
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                $import_chosen_for_wallet = round($shipping_price, 2);

                $id_payment_stripe = $result_arr["id_payment_stripe"];

                $input_handlingWallet = array(
                    "id_user" => $id_user,
                    "import" => $import_chosen_for_wallet,
                    "causal" => 'SHIPPING-PAYMENT',
                    "type" => "shipping",
                    "with_card" => 1,
                    "id_method_payment" => $id_method_payment,
                    "id_type" => $id_shipping,
                    "id_payment_stripe" => $id_payment_stripe,
                    "status" => 1
                );
                $handlingWallet = $user_call->reportTransition($input_handlingWallet);

                if ($handlingWallet) {

                    $sql2 = "UPDATE shipping SET 
                    status_shipping = '2'
                    WHERE id_user = $id_user AND id_shipping = $id_shipping";

                    //preparo l'istruzione
                    $stmt2 = $this->conn->prepare($sql2);

                    //execute query
                    $stmt2->execute();

                    if ($stmt2->rowCount() > 0) {
                        $causal = 'PAYMENT-SHIPPMENT';
                        $id_notes = 'NULL';
                        $tracking = 'NULL';
                        $shipping_price = 'NULL';
                        $taxes = 'NULL';
                        $id_courier_service = 'NULL';
                        $status_shipping = 2;
                        return $this->ShippingHadling($id_user, $id_shipping, $causal, $id_notes, $tracking, $shipping_price, $taxes, $id_courier_service, $status_shipping);
                    }
                }
            }
        } else {

            $input_handlingWallet = array(
                "id_user" => $id_user,
                "import" => $shipping_price,
                "causal" => 'SHIPPING-PAYMENT',
                "type" => "shipping",
                "with_card" => 1,
                "id_method_payment" => $id_method_payment,
                "id_type" => $id_shipping,
                "status" => 0
            );

            $reportTransition = $user_call->reportTransition($input_handlingWallet);

            if ($reportTransition) {
                return -1;
            }
        }
    }

    // Create the edited shipping chronology
    public function ShippingHadling($id_user, $id_shipping, $causal, $id_notes, $tracking, $shipping_price, $taxes, $id_courier_service, $status_shipping)
    {

        $sql = "INSERT INTO shipping_handling (id_user,id_shipping,causal,id_notes,id_courier_service,tracking,shipping_price,taxes,status_shipping) VALUES ('$id_user','$id_shipping','$causal','$id_notes','$id_courier_service','$tracking','$shipping_price','$taxes','$status_shipping')";
        $sql = str_replace("'NULL'", "NULL", $sql);

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        return $stmt->rowCount();
    }

    // API ADMIN Send Payment request
    // public function SendPaymentRequest($id_user,$id_shipping,$shipping_price){

    //     $sql = "SELECT * FROM shipping WHERE id_shipping = $id_shipping";

    //     //preparo l'istruzione
    //     $stmt = $this->conn->prepare($sql);

    //     //execute query
    //     $stmt->execute();

    //     if($stmt->rowCount() > 0){

    //         $sql1 = "UPDATE shipping SET 
    //         shipping_price = $shipping_price,
    //         status_shipping = '1'
    //         WHERE id_shipping = $id_shipping";

    //         //preparo l'istruzione
    //         $stmt1 = $this->conn->prepare($sql1);

    //         //execute query
    //         $stmt1->execute();

    //         return $stmt1->rowCount();

    //     }

    // }

    // END SHIPMENTS

}
