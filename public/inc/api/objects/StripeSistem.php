<?php
require_once '../config/Config.php';
// require_once '../../../vendor/autoload.php';
require_once 'EventLog.php';
// use Automattic\WooCommerce\Client;

require '../../../vendor/autoload.php';

use Slim\Http\Request;
use Slim\Http\Response;
use Stripe\Stripe;

// PUBLIC KEY: STRIPE_PK
// SECRET KEY: STRIPE_SK

\Stripe\Stripe::setApiKey(STRIPE_SK);

class StripeSistem
{

    // var connessione al db e tabella

    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // SECTION CUSTOMER
    public function CreateCustomer($output)
    {

        try {

            $email = isset($output["email"]) ? $output["email"] : "";
            $name = isset($output["name"]) ? $output["name"] : "";
            $phone = isset($output["phone"]) ? $output["phone"] : "";
            $description = isset($output["description"]) ? $output["description"] : "";

            // and attach the PaymentMethod to a new Customer
            $customer = \Stripe\Customer::create([
                'description' => $description,
                'email' => $email,
                'name' => $name,
                'phone' => $phone
            ]);

            $intent = \Stripe\SetupIntent::create([
                'customer' => $customer->id,
                "usage" => 'off_session',
            ]);

            $output = [
                'id_customer' => $customer->id,
                'clientSecret' => $intent->client_secret,
            ];
        } catch (Exception $excp) {
            $event_log = new EventLog($this->conn);
            $input_logSimple = [
                "parameters" => $excp->getMessage(),
                "type" => "error",
                "user" => "",
                "event" => "CreateCustomerStripe"
            ];
            $event_log->logSimple($input_logSimple);

            $output = [
                'id_customer' => "",
                'clientSecret' => "",
            ];
        }

        return $output;
    }

    public function RetriveCustomerClientSecret($output)
    {

        try {

            $id_customer_stripe = isset($output["id_customer_stripe"]) ? $output["id_customer_stripe"] : "";

            $intent = \Stripe\SetupIntent::create([
                'customer' => $id_customer_stripe,
                "usage" => 'off_session',
            ]);

            $output = [
                'clientSecret' => $intent->client_secret,
            ];
        } catch (Exception $excp) {
            $event_log = new EventLog($this->conn);
            $input_logSimple = [
                "parameters" => $excp->getMessage(),
                "type" => "error",
                "user" => "",
                "event" => "RetriveCustomerStripe"
            ];
            $event_log->logSimple($input_logSimple);

            $output = [
                'id_customer' => "",
                'clientSecret' => "",
            ];
        }

        return $output;
    }

    public function DeleteCustomer($output)
    {
        $id_customer_stripe = isset($output["id_customer_stripe"]) ? $output["id_customer_stripe"] : 0;

        try {


            $stripe = new \Stripe\StripeClient(
                STRIPE_SK
            );
            $deleteIntent = $stripe->customers->delete(
                $id_customer_stripe,
                []
            );

            $deleted = $deleteIntent->deleted;
        } catch (Exception $excp) {
            $event_log = new EventLog($this->conn);
            $input_logSimple = [
                "parameters" => $excp->getMessage(),
                "type" => "error",
                "user" => $id_customer_stripe,
                "event" => "DeleteCustomerStripe"
            ];
            $event_log->logSimple($input_logSimple);

            $deleted = 0;
        }

        return $deleted;
        //return 1;
    }

    public function UpdateCustomer($output)
    {

        $id_customer_stripe = isset($output["id_customer_stripe"]) ? $output["id_customer_stripe"] : 0;

        $email = isset($output["email"]) ? $output["email"] : "";
        $name = isset($output["name"]) ? $output["name"] : "";
        $phone = isset($output["phone"]) ? $output["phone"] : "";

        $stripe = new \Stripe\StripeClient(
            STRIPE_SK
        );

        $deleteIntent = $stripe->customers->update(
            $id_customer_stripe,
            [
                'email' => $email,
                'name' => $name,
                'phone' => $phone
            ]
        );

        return $deleteIntent->email != '' ? 1 : 0;
    }
    // END SECTION CUSTOMER

    // SECTION SUBSCRIPTIONS
    public function CreateSubscription($output)
    {
        $id_customer_stripe = isset($output["id_customer_stripe"]) ? $output["id_customer_stripe"] : 0;

        try {
            $type = isset($output["type"]) ? $output["type"] : '';

            $id_price_stripe = $output["id_price_stripe"];
            $is_trial = isset($output["is_trial"]) ? $output["is_trial"] : 0;
            $trial_end = isset($output["trial_end"]) ? $output["trial_end"] : "";


            $stripe = new \Stripe\StripeClient(
                STRIPE_SK
            );

            if ($type == 'first') {

                // ADD payment method for the customer
                $PaymentMethod = $stripe->paymentMethods->all([
                    'customer' => $id_customer_stripe,
                    'type' => 'card',
                ]);

                $payment = $PaymentMethod->data;

                if (isset($payment[0])) {
                    $id_payment_method = isset($payment[0]["id"]) ? $payment[0]["id"] : '';
                } else {
                    $id_payment_method = '';
                }


                $stripe->customers->update(
                    $id_customer_stripe,
                    ['invoice_settings' => ['default_payment_method' => $id_payment_method]]
                );
            }


            if ($is_trial > 0) {

                // CONTINUE FOR SUBSCRIPTION
                $createIntent = $stripe->subscriptions->create([
                    'customer' => $id_customer_stripe,
                    'items' => [
                        ['price' => $id_price_stripe],
                    ],
                    'trial_end' => $trial_end,
                    "off_session" => true,
                ]);
            } else {

                // ADD payment method for the customer
                $PaymentMethod = $stripe->paymentMethods->all([
                    'customer' => $id_customer_stripe,
                    'type' => 'card',
                ]);

                $payment = $PaymentMethod->data;
                $id_payment_method = isset($payment[0]["id"]) ? $payment[0]["id"] : '';

                if ($id_payment_method != '') {

                    // CONTINUE FOR SUBSCRIPTION
                    $createIntent = $stripe->subscriptions->create([
                        'customer' => $id_customer_stripe,
                        'items' => [
                            ['price' => $id_price_stripe],
                        ],
                        "off_session" => true,
                    ]);
                } else {
                    $createIntent = 0;
                }
            }

            // $price = $result_arr["items"]["data"][0]["plan"]["amount"] / 100;
            // $id_subscription_stripe = $result_arr["id"];

            // $date_subscription = date('Y-m-d', $result_arr["current_period_start"]);
            // $date_end_subscription = date('Y-m-d', $result_arr["current_period_end"]);
            // $date_start_trial = $result_arr["trial_start"] ? date('Y-m-d', $result_arr["trial_start"]) : "";
            // $date_end_trial = $result_arr["trial_end"] ? date('Y-m-d', $result_arr["trial_end"]) : "";

            $output = [
                "price" => $createIntent["items"]["data"][0]["plan"]["amount"],
                "id_subscription_stripe" => $createIntent["id"],
                "date_subscription" => $createIntent["current_period_start"],
                "date_end_subscription" => $createIntent["current_period_end"],
                "date_start_trial" => $createIntent["trial_start"],
                "date_end_trial" => $createIntent["trial_end"],
                "status" => $createIntent["status"],
            ];
        } catch (Exception $excp) {
            $event_log = new EventLog($this->conn);
            $input_logSimple = [
                "parameters" => $excp->getMessage(),
                "type" => "error",
                "user" => $id_customer_stripe,
                "event" => "CreateSubscription"
            ];
            $event_log->logSimple($input_logSimple);

            $output = [
                "price" => '',
                "id_subscription_stripe" => '',
                "date_subscription" => '',
                "date_end_subscription" => '',
                "date_start_trial" => '',
                "date_end_trial" => '',
                "status" => "canceled"
            ];
        }

        $output = json_encode($output);
        return $output;
    }

    public function CancelSubscription($output)
    {
        $id_subscription_stripe = isset($output["id_subscription_stripe"]) ? $output["id_subscription_stripe"] : 0;

        try {

            $stripe = new \Stripe\StripeClient(
                STRIPE_SK
            );

            $deleteIntent = $stripe->subscriptions->cancel(
                $id_subscription_stripe,
                []
            );

            $canceled = $deleteIntent["status"] == "canceled" ? 1 : 0;
        } catch (Exception $excp) {
            $event_log = new EventLog($this->conn);
            $input_logSimple = [
                "parameters" => $excp->getMessage(),
                "type" => "error",
                "user" => $id_subscription_stripe,
                "event" => "CancelSubscription"
            ];
            $event_log->logSimple($input_logSimple);

            $canceled = 0;
        }

        return $canceled;
    }
    // END SECTION SUBSCRIPTIONS


    // SECTION PAYMENTS
    public function CreatePaymentIntent($output)
    {

        $id_customer_stripe = isset($output["id_customer_stripe"]) ? $output["id_customer_stripe"] : 0;

        try {

            $amount = isset($output["amount"]) ? $output["amount"] : 0;

            $stripe = new \Stripe\StripeClient(
                STRIPE_SK
            );

            // ADD payment method for the customer
            $PaymentMethod = $stripe->paymentMethods->all([
                'customer' => $id_customer_stripe,
                'type' => 'card',
            ]);

            $payment = $PaymentMethod->data;
            $id_payment_method = isset($payment[0]["id"]) ? $payment[0]["id"] : '';

            if ($id_payment_method != '') {

                $createpaymentIntent = \Stripe\PaymentIntent::create([
                    'amount' => $amount,
                    'currency' => 'eur',
                    'customer' => $id_customer_stripe,
                    'payment_method' => $id_payment_method,
                    'off_session' => true,
                    'confirm' => true,
                ]);

                $output = [
                    "client_secret" => $createpaymentIntent->client_secret,
                    "payment_method" => $createpaymentIntent->payment_method,
                    "id_payment_stripe" => $createpaymentIntent->id,
                    "status" => $createpaymentIntent->status
                ];
            } else {
                $output = [
                    "status" => "declined"
                ];
            }
        } catch (\Stripe\Exception\CardException $e) {
            // Error code will be authentication_required if authentication is needed
            $event_log = new EventLog($this->conn);
            $input_logSimple = [
                "parameters" => $e->getMessage(),
                "type" => "error",
                "user" => $id_customer_stripe,
                "event" => "CreatePaymentIntent"
            ];
            $event_log->logSimple($input_logSimple);

            $payment_intent_id = $e->getError()->payment_intent->id;
            $payment_intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);

            $output = [
                "status" => "declined"
            ];
        }

        return $output;
    }

    // Da completare
    public function CancelPayment($output)
    {
        $id_subscription_stripe = isset($output["id_subscription_stripe"]) ? $output["id_subscription_stripe"] : 0;


        try {

            $stripe = new \Stripe\StripeClient(
                STRIPE_SK
            );

            $deleteIntent = $stripe->subscriptions->cancel(
                $id_subscription_stripe,
                []
            );

            $canceled = $deleteIntent["status"] == "canceled" ? 1 : 0;
        } catch (Exception $excp) {
            $event_log = new EventLog($this->conn);
            $input_logSimple = [
                "parameters" => $excp->getMessage(),
                "type" => "error",
                "user" => $id_subscription_stripe,
                "event" => "CancelPayment"
            ];
            $event_log->logSimple($input_logSimple);

            $canceled = 0;
        }

        return $canceled;
    }
    // END SECTION SUBSCRIPTIONS

    // SISTEM CARD
    public function GetAllCards($output)
    {
        $id_customer_stripe = isset($output["id_customer_stripe"]) ? $output["id_customer_stripe"] : 0;

        try {

            $stripe = new \Stripe\StripeClient(
                STRIPE_SK
            );

            $info_customer = $stripe->customers->retrieve(
                $id_customer_stripe,
                []
            );

            $selected_payment_method = isset($info_customer["invoice_settings"]["default_payment_method"]) ? $info_customer["invoice_settings"]["default_payment_method"] : '';

            $cards = $stripe->paymentMethods->all([
                'customer' => $id_customer_stripe,
                'type' => 'card',
            ]);

            $result = array();
            foreach ($cards as $key => $card) {

                $result[$key]["id_payment_method"] = $card["id"];
                $result[$key]["brand"] = $card["card"]["brand"];
                $result[$key]["country"] = $card["card"]["country"];
                $result[$key]["exp_month"] = $card["card"]["exp_month"];
                $result[$key]["exp_year"] = $card["card"]["exp_year"];
                $result[$key]["last4"] = $card["card"]["last4"];

                $result[$key]["is_selected"] = $selected_payment_method == $card["id"] ? 1 : 0;
            }

            $output = $result;
        } catch (Exception $excp) {
            $event_log = new EventLog($this->conn);
            $input_logSimple = [
                "parameters" => $excp->getMessage(),
                "type" => "error",
                "user" => $id_customer_stripe,
                "event" => "GetAllCard"
            ];
            $event_log->logSimple($input_logSimple);

            $output = [];
        }

        return $output;
    }

    public function ChooseDefaultCard($output)
    {
        $id_customer_stripe = isset($output["id_customer_stripe"]) ? $output["id_customer_stripe"] : 0;
        $id_payment_method = isset($output["id_payment_method"]) ? $output["id_payment_method"] : 0;

        try {

            $stripe = new \Stripe\StripeClient(
                STRIPE_SK
            );

            $update_user = $stripe->customers->update(
                $id_customer_stripe,
                ['invoice_settings' => ['default_payment_method' => $id_payment_method]]
            );

            $id_payment_method_defined = $update_user["invoice_settings"]["default_payment_method"];

            if ($id_payment_method_defined == $id_payment_method) {
                return 1;
            }
        } catch (Exception $excp) {
            $event_log = new EventLog($this->conn);
            $input_logSimple = [
                "parameters" => $excp->getMessage(),
                "type" => "error",
                "user" => $id_customer_stripe,
                "event" => "GetAllCard"
            ];
            $event_log->logSimple($input_logSimple);

            return 0;
        }
    }

    public function AddCard($output)
    {
        $id_customer_stripe = isset($output["id_customer_stripe"]) ? $output["id_customer_stripe"] : 0;

        try {

            $stripe = new \Stripe\StripeClient(
                STRIPE_SK
            );

            $AddPaymentMethods = $stripe->paymentMethods->create([
                'type' => 'card',
                'card' => [
                    'number' => '4242424242424242',
                    'exp_month' => 6,
                    'exp_year' => 2022,
                    'cvc' => '314',
                ],
            ]);

            $id_payment_method = isset($AddPaymentMethods["id"]) ? $AddPaymentMethods["id"] : '';

            if ($id_payment_method != '') {

                $attachPaymentMethodUser = $stripe->paymentMethods->attach(
                    $id_payment_method,
                    ['customer' => $id_customer_stripe]
                );

                $id_payment_method_definite = isset($attachPaymentMethodUser["id"]) ? $attachPaymentMethodUser["id"] : '';

                if ($id_payment_method_definite != '') {
                    $stripe->customers->update(
                        $id_customer_stripe,
                        ['invoice_settings' => ['default_payment_method' => $id_payment_method_definite]]
                    );

                    return 1;
                }
            }
        } catch (Exception $excp) {
            $event_log = new EventLog($this->conn);
            $input_logSimple = [
                "parameters" => $excp->getMessage(),
                "type" => "error",
                "user" => $id_customer_stripe,
                "event" => "GetAllCard"
            ];
            $event_log->logSimple($input_logSimple);

            return 0;
        }
    }

    public function DeleteCard($output)
    {

        $id_customer_stripe = isset($output["id_customer_stripe"]) ? $output["id_customer_stripe"] : 0;
        $id_payment_method = isset($output["id_payment_method"]) ? $output["id_payment_method"] : 0;

        try {

            $stripe = new \Stripe\StripeClient(
                STRIPE_SK
            );
            $card = $stripe->paymentMethods->detach(
                $id_payment_method,
                []
            );

            $id_payment_method = isset($card["id"]) ? $card["id"] : '';

            if ($id_payment_method != '') {

                return 1;
            }

        } catch (Exception $excp) {
            $event_log = new EventLog($this->conn);
            $input_logSimple = [
                "parameters" => $excp->getMessage(),
                "type" => "error",
                "user" => $id_customer_stripe,
                "event" => "DeleteCard"
            ];
            $event_log->logSimple($input_logSimple);

            return 0;
        }
    }

    private function AddCardAuction($output){
     

    }

    private function ChooseDefaultCardAuction($output){
        
    }

    private function DeleteCardAuction($output){
        
    }
    // END SISTEM CARD

}
