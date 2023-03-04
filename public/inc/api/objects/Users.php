<?php
require_once '../config/Config.php';
require_once '../config/Costanti.php';
// require_once '../../../vendor/autoload.php';
// use Automattic\WooCommerce\Client;


require 'EventLog.php';
require "../resources/PHPMailer-master/src/Exception.php";
require "../resources/PHPMailer-master/src/PHPMailer.php";
require "../resources/PHPMailer-master/src/SMTP.php";

require 'EmailSistem.php';
// require 'StripeSistem.php';
// require_once 'ACSistem.php';

class Users
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

    private function nextIdProducer($id_user = false)
    {
        $sql_control = "SELECT (id_producer + 1) as id_producer FROM users WHERE roles = 3 AND deleted = 0 ORDER BY id_producer DESC";
        //preparo l'istruzione
        $stmt_control = $this->conn->prepare($sql_control);
        //execute query
        $stmt_control->execute();
        $producer = $stmt_control->fetch(PDO::FETCH_ASSOC);

        $id_producer = $producer["id_producer"];

        if ($id_user > 0 || $id_user) {

            $sql = "SELECT id_producer FROM users WHERE id_user = $id_user";

            //preparo l'istruzione
            $stmt = $this->conn->prepare($sql);
            //execute query
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user["id_producer"] > 0) {
                $id_producer = $user["id_producer"];
            } else {
                $id_producer = $id_producer;
            }
        }

        return $id_producer;
    }

    public function GetAllRoles($input)
    {
        $idOrganization = $input['idOrganization'];
        $idUser = $input['idUtente'];



        if ($idOrganization == 0) {
            $idOrganization = $idUser;
        }

        $sql = "SELECT * FROM roles WHERE id_organization = 0 OR  $idOrganization";


        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];

        foreach ($roles as $role) {

            $id = $role['id'];
            $sqlCheckRoles = "SELECT COUNT(*) AS total FROM user WHERE id_role = $id;";
            $stmtCheck = $this->conn->prepare($sqlCheckRoles);
            $stmtCheck->execute();
            $count = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            $sqlFeature = "SELECT * FROM features_by_roles WHERE id_organization = $idOrganization AND id_roles = $id; ";
            $stmtFeature = $this->conn->prepare($sqlFeature);
            $stmtFeature->execute();
            $features = $stmtFeature->fetch(PDO::FETCH_ASSOC);
            $featureArray = isset($features['features']) ? json_decode($features['features'], true) : null;
            $newRole = [
                "id"             => $role['id'],
                "role"           => strtoupper($role['role']),
                "deletable"      => isset($count['total']) && $count['total'] > 0 ? true : false,
                $featureArray = isset($featureArray) ? $featureArray : $featureArray
            ];

            if ($id != 4) {
                array_push($result, $newRole);
            }
        }

        return $result;
    }

    public function normalizeSku()
    {
        // DETECT ID_PRODUCER 
        $sql_producer = "SELECT id_user,id_producer FROM users WHERE roles= 3 AND id_producer != 0";

        //preparo l'istruzione
        $stmt_producer = $this->conn->prepare($sql_producer);
        //execute query
        $stmt_producer->execute();
        $producers = $stmt_producer->fetchAll(PDO::FETCH_ASSOC);
        $return = '';
        foreach ($producers as $producer) {

            $return .= $this->normalizeSkuProducer($producer["id_user"], $producer["id_producer"]);
        }

        return $return;
    }

    private function normalizeSkuProducer($id_producer, $id_producer_real)
    {

        $return = '';
        // CONTROL PRODUCT
        $sql_control_product = "SELECT * FROM products WHERE id_producer = $id_producer";
        //preparo l'istruzione
        $stmt_control_product = $this->conn->prepare($sql_control_product);
        //execute query
        $stmt_control_product->execute();
        $products = $stmt_control_product->fetchAll(PDO::FETCH_ASSOC);

        $countProducts = 0;
        $countProductsUpdate = 0;
        foreach ($products as $product) {

            $id_product = $product["id_product"];
            $countProducts++;

            $identification_product = "W";
            $id_producer_string = str_pad($id_producer_real, 2, '0', STR_PAD_LEFT);
            $id_normalized_product = str_pad($countProducts, 3, '0', STR_PAD_LEFT);
            $year = date("Y");
            $year = substr($year, -2);

            $result = $identification_product . $id_producer_string . $id_normalized_product . $year;

            $sql_update_product = "UPDATE products SET 
            sku = '$result'
            WHERE id_product = '$id_product'";

            //preparo l'istruzione
            $stmt_update_product = $this->conn->prepare($sql_update_product);
            //execute query
            $stmt_update_product->execute();

            if ($stmt_update_product->rowCount() > 0) {
                $countProductsUpdate++;
            }
        }

        if ($countProducts == $countProductsUpdate) {
            $return .= "Products of $id_producer are OKAY | ";
        } else {
            $return .= "Products of $id_producer are not OKAY | ";
        }
        // END CONTROL PRODUCT

        // CONTROL LOT
        $sql_control_lot = "SELECT * FROM lots WHERE id_producer = $id_producer";
        //preparo l'istruzione
        $stmt_control_lot = $this->conn->prepare($sql_control_lot);
        //execute query
        $stmt_control_lot->execute();
        $lots = $stmt_control_lot->fetchAll(PDO::FETCH_ASSOC);

        $countLots = 0;
        $countLotsUpdate = 0;
        foreach ($lots as $lot) {

            $id_lot = $lot["id_lot"];
            $countLots++;

            $identification_lot = "L";
            $id_producer_string = str_pad($id_producer_real, 2, '0', STR_PAD_LEFT);
            $id_normalized_lot = str_pad($countLots, 3, '0', STR_PAD_LEFT);
            $year = date("Y");
            $year = substr($year, -2);

            $result = $identification_lot . $id_producer_string . $id_normalized_lot . $year;

            $sql_update_lot = "UPDATE lots SET 
                    sku = '$result'
                    WHERE id_lot = '$id_lot'";

            //preparo l'istruzione
            $stmt_update_lot = $this->conn->prepare($sql_update_lot);
            //execute query
            $stmt_update_lot->execute();

            if ($stmt_update_lot->rowCount() > 0) {
                $countLotsUpdate++;
            }
        }

        if ($countLots == $countLotsUpdate) {
            $return .= "Lots of $id_producer are OKAY | ";
        } else {
            $return .= "Lots of $id_producer are not OKAY | ";
        }
        // END CONTROL LOT

        return $return;
    }

    // Login user
    public function LoginUser($output)
    {

        // fields
        $email = addslashes($output["email"]);
        //$password = hash("sha256", $output["password"]);
        $password = $output["password"];

        $sql_control = "SELECT * FROM users WHERE email = '$email' AND password = '$password' AND active = 0 AND deleted = 0";

        //preparo l'istruzione
        $stmt_control = $this->conn->prepare($sql_control);

        //execute query
        $stmt_control->execute();

        if ($stmt_control->rowCount() == 0) {

            $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password' AND active != 0 AND deleted = 0";

            //preparo l'istruzione
            $stmt = $this->conn->prepare($sql);

            //execute query
            $stmt->execute();

            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->GetUser($users);
        } else {
            return -1;
        }
    }

    // LOST PASSWORD SISTEM
    // Create log of request
    public function LostPassword($output)
    {

        // fields
        $email = addslashes($output["email"]);
        $token = $this->generateRandomString(20);
        $date_request = date("Y-m-d H:i:s");
        $date_deadline = date("Y-m-d H:i:s", strtotime("+ 5 minutes"));
        $browser = $output["browser"];

        $sql_control = "SELECT * FROM users WHERE email = '$email' AND active = 1 AND deleted = 0";

        //preparo l'istruzione
        $stmt_control = $this->conn->prepare($sql_control);

        //execute query
        $stmt_control->execute();

        if ($stmt_control->rowCount() > 0) {

            $sql = "INSERT INTO users_request_password (token,date_request,date_deadline,email,browser) VALUES ('$token','$date_request','$date_deadline','$email','$browser')";

            //preparo l'istruzione
            $stmt = $this->conn->prepare($sql);

            //execute query
            $stmt->execute();

            $req = $this->conn->lastInsertId();

            // if ($stmt->rowCount() > 0) {
            //     //$mail = new EmailSistem($this->conn);

            //     $user = $stmt_control->fetch(PDO::FETCH_ASSOC);

            //     $info_user = $this->GetUserInfo($user["id_user"]);
            //     $firstName = $info_user["firstName"];

            //     //$code = md5($email);
            //     $code = hash("sha256", $email);

            //     $input = array(
            //         "from" => "no-reply@crurated.com",
            //         "to" => $email,
            //         "subject" => "Reset Password",
            //         "email" => [
            //             "title" => "Dear $firstName",
            //             "content" => [
            //                 // [
            //                 //     "format" => "paragraph",
            //                 //     "text" => "<p>We received a request to reset your Crurated account password. To set a new password, please follow the link below:</p>
            //                 //     <p>If you did not request a password reset, please ignore this email. </p>
            //                 //     <p><a href='" . DOMAIN . "/auth/reset-password?code=$code&prn=$token&req=$req'>" . DOMAIN . "/auth/reset-password?code=$code&prn=$token&req=$req</a></p>
            //                 //     <p>You can change your password again at any time  by visiting  My Account on members.crurated.com .</p>
            //                 //     <p>Sincerely,<br>
            //                 //     The Crurated Team.</p>",
            //                 //     "type" => "1Col",
            //                 // ],
            //             ]
            //         ]
            //     );

            //     return ""//$mail->SendEmailSistem($input);
            // }
        } else {
            return -1;
        }
    }

    // control the request of change password
    public function RequestChangePassword($output)
    {

        $code = $output["code"];
        $token = $output["token"];
        $req = $output["req"];

        $now = date("Y-m-d H:i:s");

        $sql_control = "SELECT * FROM users_request_password WHERE id_request = '$req' AND token = '$token' AND date_deadline >= '$now' ORDER BY id_request DESC LIMIT 1";

        //preparo l'istruzione
        $stmt_control = $this->conn->prepare($sql_control);

        //execute query
        $stmt_control->execute();

        if ($stmt_control->rowCount() > 0) {

            $request = $stmt_control->fetch(PDO::FETCH_ASSOC);
            $email = $request["email"];
            //$email_code = md5($email);
            $email_code = hash("sha256", $email);

            if ($email_code == $code) {
                $sql = "SELECT * FROM users WHERE email = '$email' AND active = 1 AND deleted = 0";

                //preparo l'istruzione
                $stmt = $this->conn->prepare($sql);

                //execute query
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    $info_user = $this->GetUserInfo($user["id_user"]);

                    $result = array(
                        "id_user" => $info_user["id_user"],
                        "password" => $info_user["password"],
                        "email" => $info_user["email"]
                    );

                    return [$result];
                } else {
                    return -2;
                }
            }
        } else {
            return -1;
        }
    }
    // END LOST PASSWORD SISTEM


    // Create User withRegistration
    public function ActivateAccount($output)
    {
        $id_user = $output["person"];
        $code = $output["code"];

        $sql_control = "SELECT email FROM users WHERE id_user = $id_user AND deleted = 0";
        //preparo l'istruzione
        $stmt_control = $this->conn->prepare($sql_control);
        //execute query
        $stmt_control->execute();

        if ($stmt_control->rowCount() > 0) {
            $user = $stmt_control->fetch(PDO::FETCH_ASSOC);
            //$email = md5($user["email"]);
            $email = hash("sha256", $user["email"]);

            if ($email == $code) {

                $sql = "UPDATE users SET 
                active = 1
                WHERE id_user = '$id_user'";

                //preparo l'istruzione
                $stmt = $this->conn->prepare($sql);
                //execute query
                $stmt->execute();

                $sql_control2 = "SELECT * FROM users WHERE id_user = $id_user AND active = 1 AND deleted = 0";
                //preparo l'istruzione
                $stmt_control2 = $this->conn->prepare($sql_control2);
                //execute query
                $stmt_control2->execute();

                return $stmt_control2->rowCount();
            }
        }
    }

    // Create User 
    public function CreateUser($output)
    {
        $id_user = $output["id_user"];
        $firstName = addslashes($output["firstName"]);
        $lastName = addslashes($output["lastName"]);
        $companyName = $output["companyName"] ? addslashes($output["companyName"]) : 'NULL';
        $birthday = $output["birthday"] != '' ? "'" . $output["birthday"] . "'" : 'NULL';
        $email = $output["email"];
        $password = addslashes($output["password"]);
        $role = $output["role"];
        $role_customer = $output["role_customer"];
        $id_manager = $output["id_manager"];
        $is_created_backend = $output["is_created_backend"];
        $commissions_producer_collections = $output["commissions_producer_collections"];
        $commissions_producer_single_lots = $output["commissions_producer_single_lots"];
        $id_customer_stripe = isset($output["id_customer_stripe"]) && $output["id_customer_stripe"] != '' ? $output["id_customer_stripe"] : 0;
        $active = $output["active"];

        //$stripe_call = new StripeSistem($this->conn);

        $sql_control = "SELECT email FROM users WHERE email = '$email' AND deleted = 0";
        //preparo l'istruzione
        $stmt_control = $this->conn->prepare($sql_control);
        //execute query
        $stmt_control->execute();

        if ($stmt_control->rowCount() == 0) {

            $sql2 = "SELECT email FROM users WHERE email = '$email' AND deleted = 0";
            //preparo l'istruzione
            $stmt2 = $this->conn->prepare($sql2);
            //execute query
            $stmt2->execute();
            $user = $stmt_control->fetch(PDO::FETCH_ASSOC);
            $email_actual = $user["email"];

            // $password = md5($password);
            $password = hash("sha256", $password);

            // if the user not are customer set the role_customer 0
            if ($role != 2) {
                $role_customer = 0;
            }

            if ($role == 3) {
                $id_producer = $this->nextIdProducer();
            } else {
                $id_producer = 0;
            }

            // 32
            $accesToken = "access-token-" . $this->generateRandomString(32);
            $refreshToken = "access-token-" . $this->generateRandomString(32);
            $sql = "INSERT INTO users (id_manager,id_customer_stripe,id_producer,firstName,lastName,companyName,birthday,email,password,roles,accessToken,refreshToken,is_created_backend,active) VALUES ('$id_manager','$id_customer_stripe','$id_producer','$firstName','$lastName','$companyName',$birthday,'$email','$password','$role','$accesToken','$refreshToken','$is_created_backend','$active')";

            if ($stmt2->rowCount() > 0) {

                if ($email_actual == $email) {

                    //preparo l'istruzione
                    $stmt = $this->conn->prepare($sql);

                    //execute query
                    $stmt->execute();

                    return $stmt->rowCount();
                } else {

                    //}

                    return -1;
                }
            } else {

                //preparo l'istruzione
                $stmt = $this->conn->prepare($sql);
                //execute query
                $stmt->execute();
            }

            if ($stmt->rowCount() > 0) {

                $id_user_profile = $this->conn->lastInsertId();

                if ($role_customer > 0) {

                    $hadlingCustomerRoleUser = $this->hadlingCustomerRoleUser($id_user, $id_user_profile, $role_customer, "ROLE-CREATED");

                    if ($hadlingCustomerRoleUser) {

                        $hadlingUser = $this->hadlingUser($id_user, $id_user_profile, "USER-CREATED");

                        if ($hadlingUser) {
                            if ($role == 2) {
                                $QualityScoreUserHandling = $this->QualityScoreUserHandling($id_user_profile, 5, "USER-CREATED");
                                if ($QualityScoreUserHandling) {
                                    return $id_user_profile;
                                }
                            } else {
                                return $id_user_profile;
                            }
                        }
                    }
                } else {

                    if ($role == 3) {

                        $input_hadlingCommissions_single_lots = array(
                            "id_user" => $id_user_profile,
                            "commissions_now" => $commissions_producer_single_lots,
                            "causal" => "USER-CREATED",
                            "id_auction_type" => 1
                        );

                        $hadlingCommissions_single_lots = $this->hadlingCommissions($input_hadlingCommissions_single_lots);

                        $input_hadlingCommissions_collections = array(
                            "id_user" => $id_user_profile,
                            "commissions_now" => $commissions_producer_collections,
                            "causal" => "USER-CREATED",
                            "id_auction_type" => 2
                        );

                        $hadlingCommissions_collections = $this->hadlingCommissions($input_hadlingCommissions_collections);

                        if ($hadlingCommissions_single_lots && $hadlingCommissions_collections) {

                            // CREATE AND ADD TAG FOR AC
                            //$AC_call = new ACSistem($this->conn);

                            $input_ACCreateTag = ["name" => $firstName . " " . $lastName, "tagType" => "contact"];
                            //$ACCreateTag = $AC_call->ACCreateTag($input_ACCreateTag);
                            //$id_ac = $ACCreateTag;

                            // if ($id_ac > 0) {

                            //     $sql_ac = "UPDATE users SET 
                            //     id_ac = '$id_ac'
                            //     WHERE id_user = '$id_user_profile'";

                            //     //preparo l'istruzione
                            //     $stmt_ac = $this->conn->prepare($sql_ac);

                            //     //execute query
                            //     $stmt_ac->execute();

                            //     $continue = $stmt_ac->rowCount();
                            // }
                        }
                    } else {
                        $continue = 1;
                    }

                    if ($continue) {

                        $hadlingUser = $this->hadlingUser($id_user, $id_user_profile, "USER-CREATED");

                        if ($hadlingUser) {
                            if ($role == 2) {
                                $QualityScoreUserHandling = $this->QualityScoreUserHandling($id_user_profile, 5, "USER-CREATED");

                                if ($QualityScoreUserHandling) {
                                    return $id_user_profile;
                                }
                            } else {
                                return $id_user_profile;
                            }
                        }
                    }
                }
            }
        } else {

            // if ($is_created_backend == 0) {

            //     $stripe_call->DeleteCustomer(["id_customer_stripe" => $id_customer_stripe]);
            // }

            // return -1;
        }
    }

    // Edit personal information User 
    public function EditPersonalInformationUser($output)
    {
        $id_user = $output["id_user"];
        $id_user_profile = $output["id_user_profile"];
        $firstName = addslashes($output["firstName"]);
        $lastName = addslashes($output["lastName"]);
        $companyName = $output["companyName"] ? addslashes($output["companyName"]) : 'NULL';
        $vat = $output["vat"] ? addslashes($output["vat"]) : 'NULL';
        $birthday = $output["birthday"] ? $output["birthday"] : 'NULL';
        $phone = $output["phone"] ? $output["phone"] : 'NULL';

        $sql = "UPDATE users SET 
        firstName = '$firstName',
        lastName = '$lastName',
        companyName = '$companyName',
        birthday = '$birthday',
        vat = '$vat',
        phone = '$phone'

        WHERE id_user = '$id_user_profile'";
        $sql = str_replace("'NULL'", "NULL", $sql);

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $hadlingUser = $this->hadlingUser($id_user, $id_user_profile, "USER-PERSONAL-INFORMATION-EDITED");

            $sql_control = "SELECT email,id_user_wordpress,id_customer_stripe FROM users WHERE id_user = '$id_user_profile' AND deleted = 0";
            //preparo l'istruzione
            $stmt_control = $this->conn->prepare($sql_control);
            //execute query
            $stmt_control->execute();
            $user_info = $stmt_control->fetch(PDO::FETCH_ASSOC);

            $id_customer_stripe = $user_info["id_customer_stripe"];
            $email = $user_info["email"];

            if ($hadlingUser && $stmt_control->rowCount() > 0 && $id_customer_stripe != '0') {

                //$stripe_call = new StripeSistem($this->conn);

                $input_UpdateCustomer = array(
                    "email" => $email,
                    "name" => $firstName . ' ' . $lastName,
                    "id_customer_stripe" => $id_customer_stripe,
                );

                //return $stripe_call->UpdateCustomer($input_UpdateCustomer);
            } else {
                return $hadlingUser;
            }
        }
    }

    // Edit personal information User 
    public function EditAccountInformationUser($output)
    {
        $id_user = $output["id_user"];
        $id_user_profile = $output["id_user_profile"];
        $email = $output["email"];
        $language = $output["language"];
        $time_zone = $output["time_zone"];

        $sql_control = "SELECT email FROM users WHERE id_user = '$id_user_profile'";
        //preparo l'istruzione
        $stmt_control = $this->conn->prepare($sql_control);
        //execute query
        $stmt_control->execute();

        if ($stmt_control->rowCount() > 0) {

            $sql2 = "SELECT email FROM users WHERE email = '$email'";
            //preparo l'istruzione
            $stmt2 = $this->conn->prepare($sql2);
            //execute query
            $stmt2->execute();
            $user = $stmt_control->fetch(PDO::FETCH_ASSOC);
            $email_actual = $user["email"];

            $sql = "UPDATE users SET 
            email = '$email',
            language = '$language',
            time_zone = $time_zone

            WHERE id_user = '$id_user_profile'";

            $sql = str_replace("'NULL'", "NULL", $sql);

            if ($stmt2->rowCount() > 0) {

                if ($email_actual == $email) {

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

            if ($stmt->rowCount() > 0) {
                $hadlingUser = $this->hadlingUser($id_user, $id_user_profile, "USER-ACCOUNT-INFORMATION-EDITED");

                $sql_control = "SELECT id_customer_stripe,firstName,lastName FROM users WHERE id_user = '$id_user_profile' AND deleted = 0";
                //preparo l'istruzione
                $stmt_control = $this->conn->prepare($sql_control);
                //execute query
                $stmt_control->execute();
                $user_info = $stmt_control->fetch(PDO::FETCH_ASSOC);

                $firstName = $user_info["firstName"];
                $lastName = $user_info["lastName"];
                $id_customer_stripe = $user_info["id_customer_stripe"];

                if ($hadlingUser && $stmt_control->rowCount() > 0 && $id_customer_stripe != '0') {

                    //$stripe_call = new StripeSistem($this->conn);

                    $input_UpdateCustomer = array(
                        "email" => $email,
                        "name" => $firstName . ' ' . $lastName,
                        "id_customer_stripe" => $id_customer_stripe,
                    );

                    //return $stripe_call->UpdateCustomer($input_UpdateCustomer);
                } else {
                    return $hadlingUser;
                }
            }
        }
    }

    // Edit password User 




    // Add address for user
    public function AddAddressUser($output)
    {

        $id_user = $output["id_user"];
        $id_user_profile = $output["id_user_profile"];
        $full_name = addslashes($output["full_name"]);
        $companyName = $output["companyName"] ? addslashes($output["companyName"]) : 'NULL';
        $vat = $output["vat"] ? addslashes($output["vat"]) : 'NULL';
        $phone = $output["phone"];

        $addressline1 = addslashes($output["addressline1"]);
        $addressline2 = isset($output["addressline2"]) ? addslashes($output["addressline2"]) : 'NULL';
        $country = addslashes($output["country"]);
        $region = addslashes($output["region"]);
        $city = addslashes($output["city"]);
        $postal_code = addslashes($output["postal_code"]);
        $type = addslashes($output["type"]);

        if ($type == 'shipping') {

            $sql = "SELECT * FROM users_address WHERE id_user = '$id_user_profile' AND type = 'shipping'";
            //preparo l'istruzione
            $stmt = $this->conn->prepare($sql);

            //execute query
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                $sql = "UPDATE users_address SET 
                selected = 0
    
                WHERE id_user = '$id_user_profile' AND type = 'shipping'";
                //preparo l'istruzione
                $stmt = $this->conn->prepare($sql);

                //execute query
                $stmt->execute();

                $continue = $stmt->rowCount();
            } else {
                $continue = 1;
            }
        } else {

            $continue = 1;
        }

        if ($continue > 0) {

            $sql = "INSERT INTO users_address (id_user,full_name,companyName,vat,phone,addressline1,addressline2,country,region,city,postal_code,type,selected) values ('$id_user_profile','$full_name','$companyName','$vat','$phone','$addressline1','$addressline2','$country','$region','$city','$postal_code','$type',1)";

            //preparo l'istruzione
            $stmt = $this->conn->prepare($sql);

            //execute query
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $id_address = $this->conn->lastInsertId();
                $type = strtoupper($type);
                $hadlingUser = $this->hadlingUser($id_user, $id_user_profile, "USER-ADDRESS-$type-ADDED");
                if ($hadlingUser) {

                    return $id_address;
                }
            }
        }
    }

    // Delete Address User
    public function DeleteAddressUser($output)
    {
        $id_user = $output["id_user"];
        $id_user_profile = $output["id_user_profile"];
        $id_address = $output["id_address"];

        $sql = "UPDATE users_address SET 
        deleted = -1

        WHERE id_address = '$id_address' AND id_user = $id_user_profile";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $hadlingUser = $this->hadlingUser($id_user, $id_user_profile, "USER-ADDRESS-DELETED");
            if ($hadlingUser) {
                return $hadlingUser;
            }
        }
    }

    // Delete Address User
    public function SelectAddressUser($output)
    {
        $id_user = $output["id_user"];
        $id_user_profile = $output["id_user_profile"];
        $id_address = $output["id_address"];

        $sql = "UPDATE users_address SET 
        selected = 0

        WHERE id_user = $id_user_profile";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();


        $sql1 = "UPDATE users_address SET 
        selected = 1
        WHERE id_address = '$id_address' AND id_user = $id_user_profile";

        //preparo l'istruzione
        $stmt1 = $this->conn->prepare($sql1);

        //execute query
        $stmt1->execute();

        if ($stmt1->rowCount() > 0) {

            $hadlingUser = $this->hadlingUser($id_user, $id_user_profile, "USER-ADDRESS-SELECTED");
            if ($hadlingUser) {
                return $hadlingUser;
            }
        }
    }

    // Edit Stato User    
    public function EditStatoUser($output)
    {
        $id_user = $output["id_user"];
        $id_user_profile = $output["id_user_profile"];
        $stato = $output["stato"];

        $sql = "UPDATE users SET 
        active = '$stato'

        WHERE id_user = '$id_user_profile'";
        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            if ($stato > 0) {
                $stato_result = "ACTIVATED";
            } else {
                $stato_result = "DISABLED";
            }

            if ($stato == 0) {

                //$mail = new EmailSistem($this->conn);

                $info_user = $this->GetUserInfo($id_user_profile);
                $firstName = $info_user["firstName"];
                $email = $info_user["email"];

                $input = array(
                    "from" => "no-reply@crurated.com",
                    "to" => $email,
                    "subject" => "Account suspended",
                    "email" => [
                        "title" => "Dear $firstName",
                        "content" => [
                            [
                                "format" => "paragraph",
                                "text" => "We are very sad to inform you that yout account are suspended.",
                                "type" => "1Col",
                            ]
                        ]
                    ]
                );

                //$mail->SendEmailSistem($input);
            }

            return $this->hadlingUser($id_user, $id_user_profile, "USER-$stato_result");
        }
    }



    // Create the edited shipping chronology
    private function hadlingCommissions($output)
    {

        $id_user = $output["id_user"];
        $commissions_now = $output["commissions_now"];
        $commissions_last = $this->DetectCommissionsProducer($id_user);
        $causal = $output["causal"];
        $id_auction_type = $output["id_auction_type"];

        if ($commissions_now == $commissions_last) {
            return 1;
        } else {

            $sql = "INSERT INTO commissions_handling (id_user,commissions_now,commissions_last,causal,id_auction_type) VALUES ('$id_user','$commissions_now','$commissions_last','$causal','$id_auction_type')";

            //preparo l'istruzione
            $stmt = $this->conn->prepare($sql);

            //execute query
            $stmt->execute();

            return $stmt->rowCount();
        }
    }

    private function DetectCommissionsProducer($id_user)
    {
        $sql = "SELECT * FROM commissions_handling WHERE id_user = $id_user ORDER BY date_handling DESC LIMIT 1";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $commissions_handling = $stmt->fetch(PDO::FETCH_ASSOC);

            return $commissions_handling["commissions_now"];
        } else {
            return 0;
        }
    }

    // Create the edited shipping chronology
    public function hadlingUser($id_user, $id_user_profile, $causal)
    {

        $sql = "INSERT INTO users_handling (id_user,id_user_profile,causal) VALUES ('$id_user','$id_user_profile','$causal')";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        return $stmt->rowCount();
    }

    // Create the edited shipping chronology
    public function hadlingCustomerRoleUser($id_user, $id_user_profile, $id_customer_role, $causal)
    {

        $sql = "INSERT INTO users_customer_roles_handling (id_user,id_user_profile,id_customer_role,causal) VALUES ('$id_user','$id_user_profile','$id_customer_role','$causal')";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        return $stmt->rowCount();
    }

    // Wallet handling
    public function handlingWallet($output)
    {
        $id_user = $output["id_user"];
        $import = $output["import"];
        $causal = $output["causal"];
        $type = $output["type"];
        $id_auction = isset($output["id_auction"]) ? $output["id_auction"] : 'NULL';
        $id_type = $output["id_type"] ? $output["id_type"] : '';
        $id_payment_stripe = isset($output["id_payment_stripe"]) ? $output["id_payment_stripe"] : 0;
        $id_method_payment = isset($output["id_method_payment"]) ? $output["id_method_payment"] : 0;

        $with_card = isset($output["with_card"]) ? $output["with_card"] : 0;

        if ($id_type) {

            if ($type == 'shipping') {
                $sql = "INSERT INTO wallet_handling (id_user,import,causal,id_method_payment,id_shipping) VALUES ($id_user,$import,'$causal','$id_method_payment','$id_type')";
            } elseif ($type == 'auction_participant') {
                $sql = "INSERT INTO wallet_handling (id_user,import,causal,id_method_payment,id_auction,id_auction_participant) VALUES ($id_user,$import,'$causal','$id_method_payment','$id_auction','$id_type')";
            } elseif ($type == 'subscription') {
                $sql = "INSERT INTO wallet_handling (id_user,import,causal,id_method_payment,id_subscription_handling) VALUES ($id_user,$import,'$causal','$id_method_payment','$id_type')";
            }
        } else {
            $sql = "INSERT INTO wallet_handling (id_user,import,causal,id_method_payment) VALUES ($id_user,$import,'$causal','$id_method_payment')";
        }

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $first_transition = $stmt->rowCount();

        // If is a payment with card
        if ($with_card > 0 && $first_transition > 0) {

            // opposite of number negative
            if ($import < 0) {
                $import = $import * -1;
            }

            $causal = "PAYMENT-" . $causal;

            if ($id_type) {

                if ($type == 'shipping') {
                    $sql = "INSERT INTO wallet_handling (id_user,import,causal,id_method_payment,id_shipping) VALUES ($id_user,$import,'$causal','$id_method_payment','$id_type')";
                } elseif ($type == 'auction_participant') {
                    $sql = "INSERT INTO wallet_handling (id_user,import,causal,id_method_payment,id_auction,id_auction_participant) VALUES ($id_user,$import,'$causal','$id_method_payment','$id_auction','$id_type')";
                } elseif ($type == 'subscription') {
                    $sql = "INSERT INTO wallet_handling (id_user,import,causal,id_method_payment,id_subscription_handling) VALUES ($id_user,$import,'$causal','$id_method_payment','$id_type')";
                }
            } else {
                $sql = "INSERT INTO wallet_handling (id_user,import,causal,id_method_payment) VALUES ($id_user,$import,'$causal','$id_method_payment')";
            }
            $sql = str_replace("'NULL'", "NULL", $sql);

            //preparo l'istruzione
            $stmt = $this->conn->prepare($sql);

            //execute query
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                $input_handlingWallet = array(
                    "id_user" => $id_user,
                    "import" => $import,
                    "causal" => $causal,
                    "type" => $type,
                    "id_auction" => $id_auction,
                    "id_type" => $id_type,
                    "id_payment_stripe" => $id_payment_stripe,
                    "id_method_payment" => $id_method_payment,
                    "status" => 1
                );

                return $this->reportTransition($input_handlingWallet);
            }
        } else {
            return $first_transition;
        }
    }

    // Wallet handling
    public function reportTransition($output)
    {
        $id_user = $output["id_user"];
        $import = $output["import"];
        $causal = $output["causal"];
        $type = $output["type"];
        $id_auction = isset($output["id_auction"]) ? $output["id_auction"] : 'NULL';
        $id_type = $output["id_type"] ? $output["id_type"] : 'NULL';
        $id_payment_stripe = isset($output["id_payment_stripe"]) ? $output["id_payment_stripe"] : 0;
        $id_method_payment = isset($output["id_method_payment"]) ? $output["id_method_payment"] : 0;

        $status = isset($output["status"]) ? $output["status"] : 0;

        $with_card = isset($output["with_card"]) ? $output["with_card"] : 0;

        if ($id_type) {

            if ($type == 'shipping') {
                $sql = "INSERT INTO report_transition (id_payment_stripe,id_user,import,causal,id_method_payment,id_shipping,status) VALUES ('$id_payment_stripe',$id_user,$import,'$causal','$id_method_payment','$id_type','$status')";
            } elseif ($type == 'auction_participant') {
                $sql = "INSERT INTO report_transition (id_payment_stripe,id_user,import,causal,id_method_payment,id_auction,id_auction_participant,status) VALUES ('$id_payment_stripe',$id_user,$import,'$causal','$id_method_payment','$id_auction','$id_type','$status')";
            } elseif ($type == 'subscription') {
                $sql = "INSERT INTO report_transition (id_payment_stripe,id_user,import,causal,id_method_payment,id_subscription_handling,status) VALUES ('$id_payment_stripe',$id_user,$import,'$causal','$id_method_payment','$id_type','$status')";
            }
        } else {
            $sql = "INSERT INTO report_transition (id_payment_stripe,id_user,import,causal,id_method_payment,status) VALUES ('$id_payment_stripe',$id_user,$import,'$causal','$id_method_payment','$status')";
        }

        $sql = str_replace("'NULL'", "NULL", $sql);

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();
        return $stmt->rowCount();
    }


    // CUSTOMER ROLES 

    // Get all roles
    public function GetAllCustomerRoles()
    {

        $sql = "SELECT * FROM users_customer_roles WHERE deleted = 0 ORDER BY sorting ASC";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $roles;
    }

    // Get all roles
    public function GetAllCustomerRolesActive()
    {

        $sql = "SELECT * FROM users_customer_roles WHERE active = 1 AND deleted = 0 ORDER BY sorting ASC";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $roles;
    }

    // Create Customer Role  
    public function CreateCustomerRole($output)
    {
        $id_user = $output["id_user"];
        $name = $output["name"];
        $maxi_cases = $output["maxi_cases"];
        $max_free_storage_bottles = isset($output["max_free_storage_bottles"]) && $output["max_free_storage_bottles"] != '' ? $output["max_free_storage_bottles"] : 'NULL';
        $extra_storage_annual_cost = isset($output["extra_storage_annual_cost"]) && $output["extra_storage_annual_cost"] != '' ? $output["extra_storage_annual_cost"] : 'NULL';
        $insurance_processing_fee = $output["insurance_processing_fee"];

        $sql_control = "SELECT sorting FROM users_customer_roles WHERE deleted = 0 ORDER BY sorting DESC LIMIT 1";

        //preparo l'istruzione
        $stmt_control = $this->conn->prepare($sql_control);

        //execute query
        $stmt_control->execute();

        $last_sorting = $stmt_control->fetch(PDO::FETCH_ASSOC);

        $sorting = $last_sorting["sorting"] + 1;

        $sql = "INSERT INTO users_customer_roles (name,maxi_cases,max_free_storage_bottles,extra_storage_annual_cost,insurance_processing_fee,sorting) VALUES ('$name','$maxi_cases','$max_free_storage_bottles','$extra_storage_annual_cost','$insurance_processing_fee','$sorting')";
        $sql = str_replace("'NULL'", "NULL", $sql);

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        return $stmt->rowCount();
    }
    // Edit Customer Role  
    public function EditCustomerRole($output)
    {
        $id_user = $output["id_user"];
        $id_customer_role = $output["id_customer_role"];
        $name = $output["name"];
        $maxi_cases = $output["maxi_cases"];
        $max_free_storage_bottles = strtoupper($output["max_free_storage_bottles"]);
        $extra_storage_annual_cost = strtoupper($output["extra_storage_annual_cost"]);
        $insurance_processing_fee = $output["insurance_processing_fee"];

        $sql = "UPDATE users_customer_roles SET 
        name = '$name',
        maxi_cases = '$maxi_cases',
        max_free_storage_bottles = '$max_free_storage_bottles',
        extra_storage_annual_cost = '$extra_storage_annual_cost',
        insurance_processing_fee = '$insurance_processing_fee'

        WHERE id_customer_role = '$id_customer_role'";
        $sql = str_replace("'NULL'", "NULL", $sql);

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        return $stmt->rowCount();
    }

    // Edit Stato Customer Role   
    public function EditStatoCustomerRole($output)
    {
        $id_user = $output["id_user"];
        $id_customer_role = $output["id_customer_role"];
        $stato = $output["stato"];

        $sql = "UPDATE users_customer_roles SET 
        active = '$stato'

        WHERE id_customer_role = '$id_customer_role'";
        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        return $stmt->rowCount();
    }

    // Delete User
    public function DeleteCustomerRole($output)
    {
        $id_user = $output["id_user"];
        $id_customer_role = $output["id_customer_role"];

        $sql = "UPDATE users_customer_roles SET 
            deleted = -1
    
            WHERE id_customer_role = '$id_customer_role'";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        return $stmt->rowCount();
    }

    // END CUSTOMER ROLES

    // Get quantity of lot
    private function GetImportWallet($id_user)
    {

        $sql = "SELECT SUM(import) as total_import FROM wallet_handling WHERE id_user = $id_user";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $wallet = $stmt->fetch(PDO::FETCH_ASSOC);

        return round($wallet["total_import"], 2);
    }

    public function GetReportTransitionUser($id_user)
    {


        $sql = "SELECT *, CONCAT(users.firstName, ' ', users.lastName) as fullname FROM report_transition LEFT JOIN users ON report_transition.id_user = users.id_user LEFT JOIN method_payments ON method_payments.id_method_payment = report_transition.id_method_payment WHERE report_transition.id_user = $id_user";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $report_transition = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = array();
        foreach ($report_transition as $key => $report) {
            $report["causal"] = str_replace("PAYMENT-WITH-CARD-", "", $report["causal"]);
            $report["causal"] = str_replace("PAYMENT-", "", $report["causal"]);
            $report["causal"] = ucfirst(strtolower(str_replace("-", " ", $report["causal"])));

            $result[] = $report;
        }

        return $result;
    }

    // get the user logged
    public function GetUser($users)
    {
        $result = array();
        foreach ($users as $key => $user) {

            $result[] = $this->GetUserInfo($user["id_user"]);
        }

        return $result;
    }

    // get the quality score of user
    private function GetQualityScoreUser($id_user)
    {

        $sql = "SELECT COUNT(*) AS total_row, SUM(point) AS total_point FROM quality_score WHERE id_user = $id_user";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $quality_score = $stmt->fetch(PDO::FETCH_ASSOC);

            $total_row = $quality_score["total_row"];
            $total_point = $quality_score["total_point"];

            if ($total_row > 0) {
                $media = $total_point / $total_row;
            } else {
                $media = 0;
            }

            return intval($media);
        }
    }

    // get the quality score of user
    public function QualityScoreUserHandling($id_user, $point, $causal)
    {

        $sql = "INSERT INTO quality_score (id_user,point,causal) VALUES ('$id_user','$point','$causal')";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        return $stmt->rowCount();
    }

    // Set the ax import for the bid
    public function SetMaxImport($output)
    {
        $id_user = $output["id_user"];
        $import = $output["import"] ? $output["import"] : 'NULL';

        $week_start = date("Y-m-d", strtotime('monday this week'));

        $week_end = date("Y-m-d", strtotime('sunday this week'));

        $sql = "SELECT * FROM auctions_maximum_amount WHERE start_date = '$week_start' AND end_date = '$week_end' AND id_user = $id_user";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() == 0) {

            $sql1 = "INSERT INTO auctions_maximum_amount (id_user,import,start_date,end_date) VALUE ('$id_user','$import','$week_start','$week_end')";
        } else {

            $sql1 = "UPDATE auctions_maximum_amount SET 
            id_user = '$id_user',
            import = '$import',
            start_date = '$week_start',
            end_date = '$week_end'

            WHERE id_user = '$id_user'";
        }

        $sql1 = str_replace("'NULL'", "NULL", $sql1);

        //preparo l'istruzione
        $stmt1 = $this->conn->prepare($sql1);

        //execute query
        $stmt1->execute();

        return $stmt1->rowCount();
    }

    // Get the maximum amount for user
    public function GetMaxImportDate($id_user, $date)
    {
        $actual_date = date("Y-m-d H:i:s");

        if ($date < $actual_date) {
            $week_start = date("Y-m-d", strtotime('monday this week', strtotime($date)));
            $week_end = date("Y-m-d", strtotime('sunday this week', strtotime($date)));
        } else {
            $week_start = date("Y-m-d", strtotime('monday this week'));
            $week_end = date("Y-m-d", strtotime('sunday this week'));
        }

        $sql = "SELECT * FROM auctions_maximum_amount WHERE start_date = '$week_start' AND end_date = '$week_end' AND id_user = $id_user";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $maximum_amount = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($maximum_amount["import"] == '') {
                $max_import = 'no';
            } else {
                $max_import = $maximum_amount["import"];
            }
        } else {
            $max_import = "no";
        }

        return $max_import;
    }

    // Get the maximum amount for user
    public function GetMaxImportCalculatedDate($id_user, $max_import, $date)
    {

        if ($max_import == 'no') {
            $max_import_calculated = 'no';
        } else {

            $actual_date = date("Y-m-d H:i:s");

            if ($date < $actual_date) {
                $week_start = date("Y-m-d", strtotime('monday this week', strtotime($date)));
                $week_end = date("Y-m-d", strtotime('sunday this week', strtotime($date)));
            } else {
                $week_start = date("Y-m-d", strtotime('monday this week'));
                $week_end = date("Y-m-d", strtotime('sunday this week'));
            }

            $sql = "SELECT SUM(single_bid * quantity) as bid FROM auctions_participant WHERE bid_date >= '$week_start' AND bid_date <= '$week_end' AND id_user = $id_user AND is_winner = 1 AND deleted = 0";

            //preparo l'istruzione
            $stmt = $this->conn->prepare($sql);

            //execute query
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                $auctions_participant = $stmt->fetch(PDO::FETCH_ASSOC);
                $total_bid = $auctions_participant["bid"];

                $max_import_calculated = $max_import - $total_bid;
            } else {
                $max_import_calculated = $max_import;
            }
        }


        return round($max_import_calculated, 2);
    }

    public function GetUserToken($id_user)
    {
        $sql = "SELECT accessToken FROM user WHERE id_user = $id_user";

        //preparo istruzione
        $stmt = $this->conn->prepare($sql);

        //eseguo query
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user;
    }

    // get user info 
    public function GetUserInfo($id_user)
    {


        $sql = "SELECT * FROM `users`  WHERE users.id_user = $id_user";


        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);


        $result = [
            "id"              => $user['id_user'],
            "email"           => $user['email'],
            "authToken"       => $user['accessToken'],
        ];

        return $result;
    }

    // get user info 
    public function GetTimeZoneUser($id_user)
    {

        $sql = "SELECT *, CONCAT('(GTM', time_zone.gmt,') ', time_zone.timezone_name) as time_zone FROM users LEFT JOIN time_zone ON users.time_zone = time_zone.id_time_zone WHERE id_user = $id_user";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user["gmt"];
    }

    // get the user logged
    public function GetAllUsersActive()
    {
        $sql = "SELECT * FROM users WHERE active = 1 AND deleted = 0";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->GetUser($users);
    }

    // get the user logged
    public function GetAllCustomersActive()
    {
        $sql = "SELECT id_user, CONCAT(firstName,' ', lastName) as fullname FROM users WHERE roles = 2 AND active = 1 AND deleted = 0";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $users;
    }

    // get all user 
    public function GetAllUsers($output)
    {
        $id_user = $output["id_user"];
        $type = $output["type"];
        $limit = $output["limit"];

        $user = $this->GetUserInfo($id_user);

        if ($type == 'customer') {
            $where2 = "roles = 2 AND";
        } else {
            $where2 = "";
        }

        if ($user["roles"] == 1) {
            $where = "$where2 roles != 1 AND roles != 99";
        } else {
            $where = "$where2 roles != 99";
        }

        if ($limit == "no") {
            $act_limit = "";
        } else {
            $act_limit = "LIMIT $limit";
        }

        $sql = "SELECT * FROM users WHERE deleted = 0 AND $where $act_limit";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->GetUser($users);
    }

    // get all user Producers 
    public function GetAllProducersActive()
    {

        $sql = "SELECT * FROM users WHERE roles = 3 AND active = 1 AND deleted = 0";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->GetUser($users);
    }


    // SUBRSCRIPTION SISTEM
    // Get all subscriptions Acive
    public function GetAllSubscriptionsActive($id_user)
    {
        $sql = "SELECT * FROM profile WHERE active = 1 AND deleted = 0";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $subscriptions;
    }

    public function GetUserByToken($token)
    {

        $token = $token['accessToken'];

        $sql = "SELECT * FROM users WHERE users.accessToken LIKE '$token' AND users.deleted = 0";

        //preparo l'istruzione
        try {
            $stmt = $this->conn->prepare($sql);

            //execute query
            $stmt->execute();
            if ($stmt->rowCount() == 1) {

                $user = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $user = $user[0];

                $result = [
                    "id"              => $user['id_user'],
                    "email"           => $user['email'],
                    "authToken"       => $user['accessToken'],
                ];

                return $result;
            } else return ['code' => 403, 'message' => 'Forbidden'];
        } catch (PDOException $e) {
            return ['code' => 500, 'message' => "Error code: " . $e->getCode() . "\n" . $e->getMessage()];
        }
    }

    public function deleteRole($idRole)
    {
        $sql = "DELETE FROM roles WHERE id = $idRole;
                DELETE FROM features_by_roles WHERE id_roles = $idRole";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return true;
    }

    public function updateRole($input)
    {

        $idUser = $input['idUtente'];

        if ($input['idOrganization'] == 0) {
            $idOrganization = $idUser;
        }

        $idRole = $input['role'];



        $authorization = [
            'dashboard' =>  isset($input['dashboard']) && $input['dashboard'] == true ? true : false,
            'bustePaga' =>  isset($input['bustePaga']) && $input['bustePaga'] == true ? true : false,
            'pannelloAdmin' =>  isset($input['pannelloAdmin']) && $input['pannelloAdmin'] == true ? true : false,
            'gestioneGruppi' =>  isset($input['gestioneGruppi']) && $input['gestioneGruppi'] == true ? true : false,
            'gestioneRuoli' =>  isset($input['gestioneRuoli']) && $input['gestioneRuoli'] == true ? true : false,
            'impostazioni' =>  isset($input['impostazioni']) && $input['impostazioni'] == true ? true : false,
            'presenze' =>  isset($input['presenze']) && $input['presenze'] == true ? true : false,
            'documenti' =>  isset($input['documenti']) && $input['documenti'] == true ? true : false,
            'dipendenti' => isset($input['dipendenti']) && $input['dipendenti'] == true ? true : false
        ];

        $json = json_encode($authorization);

        $sql = "SELECT COUNT(*) AS roleOrganization FROM features_by_roles  WHERE id_organization = $idOrganization AND id_roles = $idRole";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $roleControl = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($roleControl['roleOrganization'] > 0) {
            $sql = "UPDATE `features_by_roles`
             SET features='$json' 
             WHERE id_roles = $idRole AND id_organization = $idOrganization";
        } else {
            $sql = "INSERT INTO `features_by_roles`(`id`, `id_organization`, `id_roles`, `features`) 
           VALUES (NULL,$idOrganization,$idRole,'$json')";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return true;
    }

    public function createRole($input)
    {
        $idUser = $input['idUtente'];

        if ($input['idOrganization'] == 0) {
            $idOrganization = $idUser;
        }

        $nameRole = strtolower($input['nameRole']);
        $autoCheckin = isset($input['autoCheckin']) && $input['autoCheckin'] == true ? true : false;
        $oraCheckin = $input['oraCheckin'];
        $oraCheckout = $input['oraCheckout'];
        $minutiPausa = $input['minutiPausa'];
        $deltaObiettivi = $input['deltaObiettivi'];

        $authorization = [
            'dashboard' =>  isset($input['dashboard']) && $input['dashboard'] == true ? true : false,
            'bustePaga' =>  isset($input['bustePaga']) && $input['bustePaga'] == true ? true : false,
            'pannelloAdmin' =>  isset($input['pannelloAdmin']) && $input['pannelloAdmin'] == true ? true : false,
            'gestioneGruppi' =>  isset($input['gestioneGruppi']) && $input['gestioneGruppi'] == true ? true : false,
            'gestioneRuoli' =>  isset($input['gestioneRuoli']) && $input['gestioneRuoli'] == true ? true : false,
            'impostazioni' =>  isset($input['impostazioni']) && $input['impostazioni'] == true ? true : false,
            'presenze' =>  isset($input['presenze']) && $input['presenze'] == true ? true : false,
            'documenti' =>  isset($input['documenti']) && $input['documenti'] == true ? true : false,
            'dipendenti' => isset($input['dipendenti']) && $input['dipendenti'] == true ? true : false
        ];

        $json = json_encode($authorization);

        $sql = "INSERT INTO `roles` (`id`, `role`, `id_organization`) VALUES (NULL,'$nameRole',$idOrganization);
        INSERT INTO `features_by_roles` (`id`, `id_organization`, `id_roles`, `features`, `oraCheckin`, `oraCheckout`, `minutiPausa`, `deltaObiettivi`, autoCheckin) 
        VALUES (NULL,$idOrganization,(SELECT id FROM roles WHERE id=(SELECT LAST_INSERT_ID())),'$json','$oraCheckin','$oraCheckout',$minutiPausa,$deltaObiettivi,$autoCheckin)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return true;
    }

    // Get all subscriptions Acive
    public function GetAllSubscriptions($id_user)
    {
        $sql = "SELECT * FROM profile";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $subscriptions;
    }

    public function RoleControl()
    {
    }

    // Get all subscriptions Acive
    public function GetSingleSubscription($id_subscription)
    {
        // Get information for subscription selected
        $sql = "SELECT * FROM subscriptions WHERE id_subscription = $id_subscription";
        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);
        //execute query
        $stmt->execute();

        $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

        return $subscription;
    }

    // Get report for all subscription for user
    public function GetMySubscriptionHandling($output)
    {
        $id_user = $output["id_user"];

        //-- Story of Handling Auction
        $sql = "SELECT MAX(subscriptions_handling.id_subscription_handling) as max_id FROM subscriptions_handling WHERE id_user = $id_user GROUP BY id_type_subscription ORDER BY id_subscription_handling DESC";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);
        //execute query
        $stmt->execute();
        $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = array();
        foreach ($subscriptions as $item) {

            $id_subscription_handling = $item["max_id"];

            $sql_info = "SELECT * FROM subscriptions_handling LEFT JOIN subscriptions ON subscriptions.id_subscription = subscriptions_handling.id_subscription WHERE subscriptions_handling.id_subscription_handling = $id_subscription_handling";

            //preparo l'istruzione
            $stmt_info = $this->conn->prepare($sql_info);
            //execute query
            $stmt_info->execute();
            $subscription = $stmt_info->fetch(PDO::FETCH_ASSOC);

            $id_subscription_handling = $subscription["id_subscription_handling"];
            $id_type_subscription = $subscription["id_type_subscription"];
            $status = $subscription["status"];
            $date_end_subscription = $subscription["date_end_subscription"];
            $date_start_trial = $subscription["date_start_trial"];

            $sql1 = "SELECT * FROM subscriptions_handling LEFT JOIN subscriptions ON subscriptions.id_subscription = subscriptions_handling.id_subscription WHERE subscriptions_handling.id_user = $id_user AND subscriptions_handling.id_type_subscription = $id_type_subscription AND subscriptions_handling.id_subscription_handling != $id_subscription_handling ORDER BY subscriptions_handling.id_subscription_handling DESC";

            //preparo l'istruzione
            $stmt1 = $this->conn->prepare($sql1);
            //execute query
            $stmt1->execute();
            $story_subscriptions = $stmt1->fetchAll(PDO::FETCH_ASSOC);

            foreach ($story_subscriptions as $story_item) {

                $story_id_subscription_handling = $story_item["id_subscription_handling"];
                $story_id_type_subscription = $story_item["id_type_subscription"];
                $story_status = $story_item["status"];
                $story_date_end_subscription = $story_item["date_end_subscription"];

                if ($story_status != '') {
                    $story_item["class"] = $story_status;
                } else {
                    $story_item["class"] = "danger";
                }

                $story_item["is_expired"] = $this->SubscriptionIsExpired($story_date_end_subscription);
                $story_item["active"] = $this->StatoSubscription($story_date_end_subscription, $story_status);
                $subscription["story_subscription"][] = $story_item;
            }

            if ($status != '') {
                $subscription["class"] = $status;
            } else {
                $subscription["class"] = "danger";
            }

            $subscription["is_trial"] = $date_start_trial ? 1 : 0;
            $subscription["is_expired"] = $this->SubscriptionIsExpired($date_end_subscription);
            $subscription["active"] = $this->StatoSubscription($date_end_subscription, $status);

            $result[] = $subscription;
        }

        return $result;
    }

    // Get all subscriptions for user
    public function GetAllSubscriptionsActiveUser($output)
    {
        $id_user = $output["id_user"];

        $role_active = $this->GetAllCustomerRolesActive();

        $result = array();
        if (count($role_active) > 0) {
            foreach ($role_active as $key => $role) {
                $id_customer_role = $role["id_customer_role"];
                $sql = "SELECT *, DATE_FORMAT(date_on_end_trial, '%d, %M %Y') AS dateFrontendComplete FROM subscriptions WHERE active = 1 AND deleted = 0 AND id_customer_role = $id_customer_role";

                //preparo l'istruzione
                $stmt = $this->conn->prepare($sql);

                //execute query
                $stmt->execute();

                $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $result[] = $role;


                foreach ($subscriptions as $subscription) {
                    $date_on_end_trial = $subscription["date_on_end_trial"];
                    $is_trial = $date_on_end_trial != NULL || $date_on_end_trial >= date("Y-m-d H:i:s") ? 1 : 0;

                    $subscription["is_trial"] = $is_trial;
                    $result[$key]["subscriptions"][] = $subscription;
                }
            }
        }

        return $result;
    }

    public function StatoSubscription($date_end_subscription, $status)
    {

        $date_now = date("Y-m-d H:i:s");

        if ($date_now < $date_end_subscription) {

            if ($status == 1) {

                // active
                return 1;
            } else {

                return 0;
            }
        } else {

            // expired
            return 0;
        }
    }

    public function SubscriptionIsExpired($date_end_subscription)
    {

        $date_now = date("Y-m-d H:i:s");

        if ($date_now < $date_end_subscription) {
            return 0;
        } else {
            // expired
            return 1;
        }
    }

    // Check for the subscription for user
    public function CheckSubscriptionUser($id_user)
    {
        $sql_control = "SELECT * FROM subscriptions_handling LEFT JOIN subscriptions ON subscriptions_handling.id_subscription = subscriptions.id_subscription WHERE subscriptions_handling.id_user = $id_user AND subscriptions_handling.status = 1 ORDER BY subscriptions_handling.date_subscription,subscriptions_handling.id_subscription_handling DESC LIMIT 1";

        //preparo l'istruzione
        $stmt_control = $this->conn->prepare($sql_control);

        //execute query
        $stmt_control->execute();

        $subscriptions = array();
        if ($stmt_control->rowCount() > 0) {

            $user_subscription = $stmt_control->fetch(PDO::FETCH_ASSOC);
            $subscriptions[] = $user_subscription;
            $subscriptions[0]["active"] = $this->StatoSubscription($user_subscription["date_end_subscription"], $user_subscription["status"]);

            $date_end_subscription = $user_subscription["date_end_subscription"];
            $price = $user_subscription["price"];
            $recurring_type = $user_subscription["recurring_type"];

            $actual_time = date("Y-m-d H:i:s");
            $finish_data = $date_end_subscription;

            $origin = new DateTime($actual_time);
            $target = new DateTime($finish_data);
            $interval = $origin->diff($target);
            $diff_day = $interval->format('%R%a');

            if ($recurring_type == "monthly") {

                $total_day = date("t");
            } else if ($recurring_type == "annual") {
                $total_day = 365;
            }

            $price_daily = $price / $total_day;
            $price_day_remaning = $price_daily * $diff_day;
            $diff_price = $price_day_remaning;

            $subscriptions[0]["is_expired"] = $this->SubscriptionIsExpired($date_end_subscription);
            $subscriptions[0]["unspent_budget"] = round($diff_price, 2);
        }

        return $subscriptions;
    }

    // Send Subscription for User
    public function NewSubscription($output)
    {

        //$stripe_call = new StripeSistem($this->conn);

        $id_user = $output["id_user"];
        $id_subscription = $output["id_subscription"];
        $id_customer_stripe = $output["id_customer_stripe"];

        $subscription_info = $this->GetSingleSubscription($id_subscription);

        $id_price_stripe = $subscription_info["id_price_stripe"];

        $date_on_end_trial = $subscription_info["date_on_end_trial"];
        $is_trial = $date_on_end_trial != NULL ? 1 : 0;

        $trial_end = strtotime($date_on_end_trial);

        $input_CreateSubscription = array(
            "id_customer_stripe" => $id_customer_stripe,
            "id_price_stripe" => $id_price_stripe,
            "type" => "first",
            "is_trial" => $is_trial,
            "trial_end" => $trial_end
        );

        //$CreateSubscription = $stripe_call->CreateSubscription($input_CreateSubscription);
        //$result_arr = json_decode($CreateSubscription, true);

        //$is_subscribe  = $result_arr["status"] == "succeeded" || $result_arr["status"] == "trialing" ? 1 : 0;

        // if ($is_subscribe > 0) {

        //     $CheckSubscriptionUser = $this->CheckSubscriptionUser($id_user);

        //     if (isset($CheckSubscriptionUser[0]["id_subscription_handling"])) {
        //         $id_subscription_handling = $CheckSubscriptionUser[0]["id_subscription_handling"];

        //         $input_CancelSubscription = [
        //             "id_user" => $id_user,
        //             "id_subscription_handling" => $id_subscription_handling
        //         ];

        //         $CancelSubscription = $this->CancelSubscription($input_CancelSubscription);

        //         if ($CancelSubscription) {
        //             $continue = 1;
        //         }
        //     } else {
        //         $continue = 1;
        //     }

        //     if ($continue) {

        //         $price = $result_arr["price"] / 100;
        //         $id_subscription_stripe = $result_arr["id_subscription_stripe"];

        //         $date_subscription = date('Y-m-d', $result_arr["date_subscription"]);
        //         $date_end_subscription = date('Y-m-d', $result_arr["date_end_subscription"]);
        //         $date_start_trial = $result_arr["date_start_trial"] ? date('Y-m-d', $result_arr["date_start_trial"]) : "";
        //         $date_end_trial = $result_arr["date_start_trial"] ? date('Y-m-d', $result_arr["date_end_trial"]) : "";

        //         $date_now = date("Y-m-d");
        //         if ($date_start_trial != '' && $date_end_trial != '') {

        //             if($date_end_trial > $date_now){
        //                 $price = 0;
        //             }

        //         }

        //         $input_handlingSubscription = [
        //             "id_user" => $id_user,
        //             "id_subscription" => $id_subscription,
        //             "price" => $price,
        //             "id_subscription_stripe" => $id_subscription_stripe,
        //             "date_subscription" => $date_subscription,
        //             "date_end_subscription" => $date_end_subscription,
        //             "date_start_trial" => $date_start_trial,
        //             "date_end_trial" => $date_end_trial,
        //         ];

        //         $handlingSubscription = $this->handlingSubscription($input_handlingSubscription);

        //         if ($handlingSubscription) {

        //             $causal = "PLAN-SUBSCRIPTION";

        //             $import_chosen_for_wallet = $price;    

        //             $id_subscription_handling = $handlingSubscription;

        //             $input_handlingWallet = array(
        //                 "id_user" => $id_user,
        //                 "import" => $import_chosen_for_wallet,
        //                 "causal" => $causal,
        //                 "type" => "subscription",
        //                 "with_card" => 1,
        //                 "id_method_payment" => 3,
        //                 "id_type" => $id_subscription_handling,
        //                 "status" => 1
        //             );

        //             return $this->reportTransition($input_handlingWallet);
        //         }
        //     }
        // } else {
        //     return -1;
        // }
    }

    public function handlingSubscription($output)
    {
        $id_user = $output["id_user"];
        $id_subscription = $output["id_subscription"];
        $price = $output["price"];
        $id_subscription_stripe = $output["id_subscription_stripe"];
        $date_subscription = $output["date_subscription"];
        $date_end_subscription = $output["date_end_subscription"];
        $date_start_trial = $output["date_start_trial"] ? $output["date_start_trial"] : 'NULL';
        $date_end_trial = $output["date_end_trial"] ? $output["date_end_trial"] : 'NULL';

        $user_info = $this->GetUserInfo($id_user);
        $subscription_info = $this->GetSingleSubscription($id_subscription);

        $id_customer_role_old = $user_info["id_customer_role"];
        $id_customer_role_now = $subscription_info["id_customer_role"];

        $sql_control = "SELECT id_type_subscription FROM subscriptions_handling WHERE id_user = $id_user ORDER BY id_type_subscription DESC LIMIT 1";

        //preparo l'istruzione
        $stmt_control = $this->conn->prepare($sql_control);
        //execute query
        $stmt_control->execute();
        $subscription_controll = $stmt_control->fetch(PDO::FETCH_ASSOC);

        $id_type_subscription = $subscription_controll["id_type_subscription"] + 1;

        $status = 1;

        $sql = "INSERT INTO subscriptions_handling (id_subscription,id_subscription_stripe,id_type_subscription,id_user,id_customer_role_old,id_customer_role_now,status,date_subscription,date_end_subscription,date_start_trial,date_end_trial,price) VALUES ('$id_subscription','$id_subscription_stripe','$id_type_subscription','$id_user','$id_customer_role_old','$id_customer_role_now','$status','$date_subscription','$date_end_subscription','$date_start_trial','$date_end_trial','$price')";
        $sql = str_replace("'NULL'", "NULL", $sql);

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $id_subscriptions_handling_last = $this->conn->lastInsertId();

        if ($stmt->rowCount() > 0) {


            $causal = "PLAN-SUBSCRIPTION";

            $hadlingCustomerRoleUser = $this->hadlingCustomerRoleUser($id_user, $id_user, $id_customer_role_now, $causal);

            if ($hadlingCustomerRoleUser) {
                return $id_subscriptions_handling_last;
            }
        }
    }

    public function CancelSubscription($output)
    {
        //$stripe_call = new StripeSistem($this->conn);

        $id_user = $output["id_user"];
        $id_subscription_handling = $output["id_subscription_handling"];

        $sql_control = "SELECT id_subscription_stripe FROM subscriptions_handling WHERE id_user = $id_user AND id_subscription_handling = $id_subscription_handling";

        //preparo l'istruzione
        $stmt_control = $this->conn->prepare($sql_control);
        //execute query
        $stmt_control->execute();
        $subscription_controll = $stmt_control->fetch(PDO::FETCH_ASSOC);

        $id_subscription_stripe = $subscription_controll["id_subscription_stripe"];

        $input_CancelSubscription = array(
            "id_subscription_stripe" => $id_subscription_stripe,
        );

        //$CancelSubscription = $stripe_call->CancelSubscription($input_CancelSubscription);

        // if ($CancelSubscription) {

        //     $sql = "UPDATE subscriptions_handling SET 
        //     processed = 1,
        //     status = 0
        //     WHERE id_subscription_handling = '$id_subscription_handling'";

        //     //preparo l'istruzione
        //     $stmt = $this->conn->prepare($sql);

        //     //execute query
        //     $stmt->execute();

        //     return $stmt->rowCount();
        // }
    }
    // END SUBRSCRIPTION SISTEM


    public function GetMethodPaymentsActive()
    {

        $sql = "SELECT * FROM method_payments WHERE active = 1 AND deleted = 0";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $method_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $method_payments;
        }
    }

    public function GetMethodPaymentsRequest($output)
    {

        $sql = "SELECT * FROM users_method_payments_handling WHERE confirmed >= 0";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        $result = array();
        if ($stmt->rowCount() > 0) {
            $request_change_method_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($request_change_method_payments as $key => $method_payments) {
                $method_payments["handling_date_frontend"] = date("Y/m/d H:i:s", strtotime($method_payments["handling_date"]));
                $result[] = $method_payments;
                $result[$key]["method_payment"] = $this->GetMethodInfoPayment($method_payments["id_method_payment"]);
                $result[$key]["user"] = $this->GetUserInfo($method_payments["id_user"]);
            }
        }
        return $result;
    }

    private function GetMethodInfoPayment($id_method_payment)
    {

        $sql = "SELECT * FROM method_payments WHERE id_method_payment = $id_method_payment";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $method_payment = $stmt->fetch(PDO::FETCH_ASSOC);
            return $method_payment;
        }
    }

    public function ChangeMethodPayment($output)
    {

        $id_user = $output["id_user"];
        $id_method_payment = $output["id_method_payment"];

        $sql_control = "SELECT * FROM users_method_payments_handling WHERE id_user = $id_user AND id_method_payment = $id_method_payment AND confirmed = 1";

        //preparo l'istruzione
        $stmt_control = $this->conn->prepare($sql_control);

        //execute query
        $stmt_control->execute();

        $info_method_payment = $this->GetMethodInfoPayment($id_method_payment);
        $to_confirm = $info_method_payment["to_confirm"];

        if ($stmt_control->rowCount() > 0) {

            $input_handlingMethodPayment = [
                "id_user" => $id_user,
                "id_method_payment" => $id_method_payment,
                "confirmed" => 1
            ];

            $handlingMethodPayment = $this->handlingMethodPayment($input_handlingMethodPayment);
            if ($handlingMethodPayment) {
                return 1;
            }
        } else {

            $input_handlingMethodPayment = [
                "id_user" => $id_user,
                "id_method_payment" => $id_method_payment,
                "confirmed" => $to_confirm ? 0 : 1
            ];

            $handlingMethodPayment = $this->handlingMethodPayment($input_handlingMethodPayment);
            if ($handlingMethodPayment) {
                return $to_confirm ? 2 : 1;
            }
        }
    }

    public function ConfirmChangeMethodPayment($output)
    {

        $id_users_method_payments = $output["id_users_method_payments"];
        $confirmed = $output["confirmed"];
        $id_user = $output["id_user"];

        $sql = "UPDATE users_method_payments_handling SET 
        confirmed = '$confirmed'
        WHERE id_users_method_payments = '$id_users_method_payments'";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            //$email_call = new EmailSistem($this->conn);
            $info_user = $this->GetUserInfo($id_user);
            $email = $info_user["email"];
            $firstName = $info_user["firstName"];

            $input = array(
                "from" => "no-reply@crurated.com",
                "to" => $email,
                "subject" => "Method Payment confirmed",
                "email" => [
                    "title" => "Dear $firstName",
                    "content" => [
                        [
                            "format" => "paragraph",
                            "text" => "<p>The request to change the payment method has been approved</p>",
                            "type" => "1Col",
                        ],
                        [
                            "format" => "button",
                            //"link" => DOMAIN,
                            "button" => "View my account",
                            "type" => "1Col",
                        ],
                    ]
                ]
            );

            //return $email_call->SendEmailSistem($input);
        }
    }

    public function handlingMethodPayment($output)
    {

        $id_user = $output["id_user"];
        $id_method_payment = $output["id_method_payment"];
        $confirmed = $output["confirmed"];

        $sql = "INSERT INTO users_method_payments_handling (id_user, id_method_payment, confirmed) VALUES ('$id_user', '$id_method_payment','$confirmed')";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);

        //execute query
        $stmt->execute();

        return $stmt->rowCount();
    }

    // END METHOD PAYMENTS

    public function ChargingWallet($output)
    {
        $id_user = $output["id_user"];
        $id_user_profile = $output["id_user_profile"];
        $amount = $output["amount"];

        $input_handlingWallet = array(
            "id_user" => $id_user_profile,
            "import" => $amount,
            "causal" => 'CHARGING',
            "type" => "",
            "with_card" => 0,
            "id_type" => ""
        );

        return $this->handlingWallet($input_handlingWallet);
    }

    public function GetTimeZoneList()
    {
        $sql = "SELECT * FROM time_zone";

        //preparo l'istruzione
        $stmt = $this->conn->prepare($sql);
        //execute query
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function GetRichieste($input)
    {
        $idUser = $input['idUtente'];
        $idRole = $input['idRole'];
        $idDepartment = $input['idDepartment'];

        $sql = "SELECT * FROM requests 
        WHERE idUtente = $idUser 
        AND tipoRichiesta LIKE 'rol' 
        OR tipoRichiesta LIKE 'malattia' 
        OR tipoRichiesta LIKE 'centoQuattro'
        ORDER BY dateRequest DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $notifiche = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($notifiche as $notifica) {
            if ($notifica['tipoRichiesta'] == "centoQuattro") {
                $richiesta = "Permesso per 104";
            } else if ($notifica['tipoRichiesta'] == "malattia") {
                $richiesta = "Malattia";
            } else if ($notifica['tipoRichiesta'] == "rol") {
                $richiesta = "R.O.L.";
            }
            $notification = [
                'idNotifica' => $notifica['id'],
                'tipoRichiesta'       => $richiesta,
                'extra'       => $notifica['extra'],
                'start'       => date('d-m-Y', strtotime($notifica['startDate'])),
                'startTime'   => date('H:s', strtotime($notifica['startTime'])),
                'end'         => $notifica['endDate'] == '01-01-1970' ? "" : date('d-m-Y', strtotime($notifica['endDate'])),
                'endTime'     => date("H:s", strtotime($notifica['endTime'])),
                'dateRequest' => date('d-m-Y, H:s', strtotime($notifica['dateRequest'])),
                'status'      => strtoupper(ucfirst($notifica['status']))
            ];

            array_push($result, $notification);
        }

        return $result;
    }
    public function GetStraordinari($input)
    {
        $idUser = $input['idUtente'];
        $idRole = $input['idRole'];
        $idDepartment = $input['idDepartment'];

        $sql = "SELECT * FROM requests 
        WHERE idUtente = $idUser 
        AND tipoRichiesta LIKE 'straordinario'
        ORDER BY dateRequest DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $notifiche = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($notifiche as $notifica) {
            $notification = [
                'idNotifica' => $notifica['id'],
                'tipoRichiesta'       => 'Straordinario',
                'extra'       => $notifica['extra'],
                'start'       => date('d-m-Y', strtotime($notifica['startDate'])),
                'hours'       => round($notifica['hours'] / 60, 1),
                'dateRequest' => date('d-m-Y, H:s', strtotime($notifica['dateRequest'])),
                'status'      => strtoupper(ucfirst($notifica['status']))
            ];

            array_push($result, $notification);
        }

        return $result;
    }

    public function getRichiesteNotification($input)
    {
        $idUser = $input['idUtente'];
        $idRole = $input['idRole'];
        $idDepartment = $input['idDepartment'];

        $sql = "SELECT requests.*, user_data.firstname, user_data.lastname FROM requests
        INNER JOIN user ON requests.idUtente = user.id
        INNER JOIN user_data ON user.id = user_data.id_user 
        WHERE requests.idDepartment = $idDepartment 
        AND requests.tipoRichiesta LIKE 'rol' 
        OR requests.tipoRichiesta LIKE 'malattia' 
        OR requests.tipoRichiesta LIKE 'centoQuattro'
        ORDER BY requests.dateRequest DESC;";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $notifiche = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($notifiche as $notifica) {

            if ($notifica['tipoRichiesta'] == "centoQuattro") {
                $richiesta = "104";
            } else if ($notifica['tipoRichiesta'] == "malattia") {
                $richiesta = "Malattia";
            } else if ($notifica['tipoRichiesta'] == "rol") {
                $richiesta = "R.O.L.";
            }

            $notification = [
                'idNotifica' => $notifica['id'],
                'idUtente' => $notifica['idUtente'],
                'utenteRichiesta' => ucfirst($notifica['firstname']) . " " . ucfirst($notifica['lastname']),
                'tipoRichiesta'       => $richiesta,
                'extra'       => $notifica['extra'],
                'start'       => date('d-m-Y', strtotime($notifica['startDate'])),
                'end'         => $notifica['endDate'] == '01-01-1970' ? "" : date('d-m-Y', strtotime($notifica['endDate'])),
                'startTime'   => date('H:s', strtotime($notifica['startTime'])),
                'endTime'     => date("H:s", strtotime($notifica['endTime'])),
                'dateRequest' => date('d-m-Y H:s', strtotime($notifica['dateRequest'])),
                'status'      => strtoupper($notifica['status'])
            ];

            array_push($result, $notification);
        }

        return $result;
    }
    public function getAdminStaordinari($input)
    {
        $idUser = $input['idUtente'];
        $idRole = $input['idRole'];
        $idDepartment = $input['idDepartment'];

        $sql = "SELECT requests.*, user_data.firstname, user_data.lastname FROM requests
        INNER JOIN user ON requests.idUtente = user.id
        INNER JOIN user_data ON user.id = user_data.id_user 
        WHERE requests.idDepartment = $idDepartment 
        AND requests.tipoRichiesta LIKE 'straordinario' 
        ORDER BY requests.dateRequest DESC;";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $notifiche = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($notifiche as $notifica) {



            $notification = [
                'idNotifica' => $notifica['id'],
                'idUtente' => $notifica['idUtente'],
                'utenteRichiesta' => ucfirst($notifica['firstname']) . " " . ucfirst($notifica['lastname']),
                'tipoRichiesta'       => "Straordinario",
                'extra'       => $notifica['extra'],
                'start'       => date('d-m-Y', strtotime($notifica['startDate'])),
                'hours'       => round($notifica['hours'] / 60, 1),
                'dateRequest' => date('d-m-Y H:s', strtotime($notifica['dateRequest'])),
                'status'      => strtoupper($notifica['status'])
            ];

            array_push($result, $notification);
        }

        return $result;
    }

    public function CountData($input)
    {
        $idUser = $input['idUtente'];

        $sql = "SELECT COUNT(*) AS permesso FROM event_handling WHERE event_type LIKE 'permesso' AND id_user=$idUser";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $permessi = $stmt->fetch(PDO::FETCH_ASSOC);

        $permessi = $permessi['permesso'];

        return $permessi;
    }

    public function GetLastSettings($input)
    {
        if ($input['idOrganization'] == 0) {
            $idOrganization = $input['idUser'];
        } else {
            $idOrganization = $input['idOrganization'];
        }

        $sql = "SELECT *,COUNT(*) AS settingExists FROM settings WHERE idOrganization = $idOrganization";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $settings = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($settings['settingExists'] > 0) {

            $result = [
                "aggiungiChiamata" => isset($settings['aggiungiChiamata']) && $settings['aggiungiChiamata'] == true ? true : false,
                'importCsv'        => isset($settings['importCsv']) && $settings['importCsv'] == true ? true : false,
                'exportCsv'        => isset($settings['exportCsv']) && $settings['exportCsv'] == true ? true : false
            ];
        } else {

            $result = [
                "aggiungiChiamata" => true,
                'importCsv'        => true,
                'exportCsv'        => true
            ];
        }

        return $result;
    }

    public function updateSetting($input)
    {

        if ($input['idOrganization'] == 0) {
            $idOrganization = $input['idUser'];
        } else {
            $idOrganization = $input['idOrganization'];
        }

        print_r($input);

        $aggiungiChiamata = $input['aggiungiChiamata'] == true ? 1 : 0;
        $importCsv = $input['importCsv'] == true ? 1 : 0;
        $exportCsv = $input['exportCsv'] == true ? 1 : 0;

        $sql = "SELECT COUNT(*) AS settingExists FROM settings WHERE idOrganization = $idOrganization";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $settingExists = $stmt->fetch(PDO::FETCH_ASSOC);
        $settingExists = $settingExists['settingExists'];

        if ($settingExists > 0) {

            $sql = "UPDATE `settings` SET `aggiungiChiamata`= $aggiungiChiamata,`importCsv`=$importCsv,
                    `exportCsv`=$exportCsv,`lastEdit`=CURRENT_TIMESTAMP() WHERE idOrganization = $idOrganization";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        } else {

            $sql = "INSERT INTO `settings`(`aggiungiChiamata`, `importCsv`, `exportCsv`, `idOrganization`, `lastEdit`) 
                    VALUES ($aggiungiChiamata,$importCsv,$exportCsv,$idOrganization,CURRENT_TIMESTAMP())";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        }
    }

    public function GetAllDepartments($input)
    {

        $page = $input['page'];
        $idOrganization = '';

        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUtente'];
        }

        $result = [];
        if ($page == 'gestioneDipartimenti') {
            $sql = "SELECT department.*, modules.name AS moduleName FROM department 
                    LEFT JOIN modules ON modules.id = department.idModule 
                    WHERE department.idOrganization = $idOrganization AND department.idPadre = 0";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);


            foreach ($departments as $department) {
                $arr = [
                    'idDepartment' => $department['id'],
                    'nameDepartment' => $department['name'],
                    'autoCheckin' => $department['autoCheckin'],
                    'deltaObiettivi' => $department['deltaObiettivi'],
                    'minutiPausa' => $department['minutiPausa'],
                    'kMinuti' => date("H:i:s", strtotime($department['kMinuti'])),
                    'autoCheckin' => $department['autoCheckin'],
                    'oraCheckin' => date("H:i", strtotime($department['oraCheckin'])),
                    'oraCheckout' => date("H:i", strtotime($department['oraCheckout'])),
                    'idModule' => $department['idModule'],
                    'nameModule' => $department['moduleName']
                ];
                array_push($result, $arr);
            }
        } else if ($page == 'tabellaDipendenti') {
            $sql = "SELECT  * FROM department WHERE idOrganization = $idOrganization";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($departments as $department) {
                $arr = [
                    'nameDepartment' => ucfirst($department['name']),
                    'idDepartment'   => $department['id'],
                ];
                array_push($result, $arr);
            }
        }

        return $result;
    }

    public function GetCustomerCareUsers($input)
    {
        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUtente'];
        }
        $idRole = $input['idRole'];
        $idDepartment = $input['idDepartment'];

        $result = [];
        if ((int)$idRole == _ADMIN_) {
            $sql = "SELECT user.id, user_data.firstname, user_data.lastname FROM 
            user LEFT JOIN user_data ON user.id = user_data.id_user
            LEFT JOIN department ON user.idDepartment = department.id 
            WHERE  user.id_organization = $idOrganization AND department.idModule = " . _CUSTOMERCARE_ . " AND user.deleted != 1";
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($users as $user) {
                    $arr = [
                        "firstname" => ucwords($user['firstname']),
                        "lastname" => ucwords($user['lastname']),
                        "fullname" => ucwords($user['firstname']) . " " . ucwords($user['lastname']),
                        "idDipendente" => $user['id']
                    ];
                    array_push($result, $arr);
                }
            } catch (PDOException $e) {
                return "Errore inaspettato: " . $e->getMessage();
            }
            return $result;
        } else if ((int)$idRole == _HR_) {
            $sql = "SELECT idPadre FROM department WHERE id = $idDepartment";
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $checkDepartment = $stmt->fetch(PDO::FETCH_ASSOC);
                $checkDepartment = $checkDepartment['idPadre'];
            } catch (PDOException $e) {
                return "Errore imprevisto " . $e->getMessage();
            }
            if ($checkDepartment == 0) {
                $sql = "SELECT user_data.firstname, user_data.lastname, user.id FROM
                 user LEFT JOIN user_data ON user.id = user_data.id_user 
                 LEFT JOIN department ON user.idDepartment = department.id 
                 WHERE (department.id = $idDepartment OR department.idPadre = $idDepartment)  AND user.deleted != 1";
                try {
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute();
                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);


                    foreach ($users as $user) {
                        $firstname = ucwords($user['firstname']);
                        $lastname = ucwords($user['lastname']);
                        $arr = [
                            "firstname" => $firstname,
                            "lastname" => $lastname,
                            "fullname" => $firstname . " " . $lastname,
                            "idDipendente" => $user['id'],
                        ];
                        array_push($result, $arr);
                    }
                } catch (PDOException $e) {
                    return "Errore imprevisto " . $e->getMessage();
                }
            } else {
                $sql = "SELECT user.id, user_data.firstname, user_data.lastname FROM user LEFT JOIN user_data ON user.id = user_data.id_user WHERE user.idDepartment = $idDepartment  AND user.deleted != 1";
                try {
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute();
                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);


                    foreach ($users as $user) {
                        $firstname = ucwords($user['firstname']);
                        $lastname = ucwords($user['lastname']);
                        $arr = [
                            "firstname" => $firstname,
                            "lastname" => $lastname,
                            "fullname" => $firstname . " " . $lastname,
                            "idDipendente" => $user['id'],
                        ];
                        array_push($result, $arr);
                    }
                } catch (PDOException $e) {
                    return "Errore inaspettato: " . $e->getMessage();
                }
            }
            return $result;
        } else {
            return "Utente non autorizzato";
        }
    }

    public function GetOfficinaUsers($input)
    {
        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUtente'];
        }
        $idRole = $input['idRole'];
        $idDepartment = $input['idDepartment'];

        $result = [];
        if ((int)$idRole == _ADMIN_) {
            $sql = "SELECT user.id, user_data.firstname, user_data.lastname FROM 
            user LEFT JOIN user_data ON user.id = user_data.id_user
            LEFT JOIN department ON user.idDepartment = department.id 
            WHERE  user.id_organization = $idOrganization AND department.idModule = " . _OFFICINA_ . " AND user.deleted != 1";
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($users as $user) {
                    $arr = [
                        "firstname" => ucwords($user['firstname']),
                        "lastname" => ucwords($user['lastname']),
                        "fullname" => ucwords($user['firstname']) . " " . ucwords($user['lastname']),
                        "idDipendente" => $user['id']
                    ];
                    array_push($result, $arr);
                }
            } catch (PDOException $e) {
                return "Errore inaspettato: " . $e->getMessage();
            }
            return $result;
        } else if ((int)$idRole == _HR_) {
            $sql = "SELECT idPadre FROM department WHERE id = $idDepartment";
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $checkDepartment = $stmt->fetch(PDO::FETCH_ASSOC);
                $checkDepartment = $checkDepartment['idPadre'];
            } catch (PDOException $e) {
                return "Errore imprevisto " . $e->getMessage();
            }
            if ($checkDepartment == 0) {
                $sql = "SELECT user_data.firstname, user_data.lastname, user.id FROM
                 user LEFT JOIN user_data ON user.id = user_data.id_user 
                 LEFT JOIN department ON user.idDepartment = department.id 
                 WHERE (department.id = $idDepartment OR department.idPadre = $idDepartment)  AND user.deleted != 1";
                try {
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute();
                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);


                    foreach ($users as $user) {
                        $firstname = ucwords($user['firstname']);
                        $lastname = ucwords($user['lastname']);
                        $arr = [
                            "firstname" => $firstname,
                            "lastname" => $lastname,
                            "fullname" => $firstname . " " . $lastname,
                            "idDipendente" => $user['id'],
                        ];
                        array_push($result, $arr);
                    }
                } catch (PDOException $e) {
                    return "Errore imprevisto " . $e->getMessage();
                }
            } else {
                $sql = "SELECT user.id, user_data.firstname, user_data.lastname FROM user LEFT JOIN user_data ON user.id = user_data.id_user WHERE user.idDepartment = $idDepartment  AND user.deleted != 1";
                try {
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute();
                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);


                    foreach ($users as $user) {
                        $firstname = ucwords($user['firstname']);
                        $lastname = ucwords($user['lastname']);
                        $arr = [
                            "firstname" => $firstname,
                            "lastname" => $lastname,
                            "fullname" => $firstname . " " . $lastname,
                            "idDipendente" => $user['id'],
                        ];
                        array_push($result, $arr);
                    }
                } catch (PDOException $e) {
                    return "Errore inaspettato: " . $e->getMessage();
                }
            }
            return $result;
        } else {
            return "Utente non autorizzato";
        }
    }

    public function GetUserProgressPage($input)
    {

        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUtente'];
        }
        $idRole = $input['idRole'];
        $idDepartment = $input['idDepartment'];
        $result = [];
        if ($idRole == 4 || $idRole == "4") {
            $sql = "SELECT user.id, user_data.firstname, user_data.lastname FROM user LEFT JOIN user_data ON user.id = user_data.id_user WHERE user.id_organization = $idOrganization AND user.deleted != 1";
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($users as $user) {
                    $arr = [
                        "firstname" => ucwords($user['firstname']),
                        "lastname" => ucwords($user['lastname']),
                        "fullname" => ucwords($user['firstname']) . " " . ucwords($user['lastname']),
                        "idDipendente" => $user['id']
                    ];
                    array_push($result, $arr);
                }
            } catch (PDOException $e) {
                return "Errore non previsto";
            }
        } else if ($idRole == 3 || $idRole == "3") {
            $sql = "SELECT idPadre FROM department WHERE id = $idDepartment";
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $checkDepartment = $stmt->fetch(PDO::FETCH_ASSOC);
                $checkDepartment = $checkDepartment['idPadre'];
            } catch (PDOException $e) {
                return "Errore imprevisto " . $e->getMessage();
            }
            if ($checkDepartment == 0) {
                $sql = "SELECT user_data.firstname, user_data.lastname, user.id FROM
                 user LEFT JOIN user_data ON user.id = user_data.id_user 
                 LEFT JOIN department ON user.idDepartment = department.id 
                 WHERE (department.id = $idDepartment OR department.idPadre = $idDepartment)  AND user.deleted != 1";
                try {
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute();
                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);


                    foreach ($users as $user) {
                        $firstname = ucwords($user['firstname']);
                        $lastname = ucwords($user['lastname']);
                        $arr = [
                            "firstname" => $firstname,
                            "lastname" => $lastname,
                            "fullname" => $firstname . " " . $lastname,
                            "idDipendente" => $user['id'],
                        ];
                        array_push($result, $arr);
                    }
                } catch (PDOException $e) {
                    return "Errore imprevisto " . $e->getMessage();
                }
            } else {
                $sql = "SELECT user.id, user_data.firstname, user_data.lastname FROM user LEFT JOIN user_data ON user.id = user_data.id_user WHERE user.idDepartment = $idDepartment  AND user.deleted != 1";
                try {
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute();
                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);


                    foreach ($users as $user) {
                        $firstname = ucwords($user['firstname']);
                        $lastname = ucwords($user['lastname']);
                        $arr = [
                            "firstname" => $firstname,
                            "lastname" => $lastname,
                            "fullname" => $firstname . " " . $lastname,
                            "idDipendente" => $user['id'],
                        ];
                        array_push($result, $arr);
                    }
                } catch (PDOException $e) {
                    return "Errore inaspettato: " . $e->getMessage();
                }
            }
        }
        return $result;
    }

    public function GetUserByDepartment($input)
    {

        $idUser = $input['idUtente'];
        $idRole = $input['idRole'];
        $idDepartment = $input['idDepartment'];
        $userDepartment = $input['userDepartment'];
        $idOrganization = '';

        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUtente'];
        }

        if ($idRole == 4) {
            if ($idDepartment == 'all') {
                $sql = "SELECT *, department.name,user_data.customfield, department.idPadre, roles.role  FROM user 
            LEFT JOIN user_data ON user.id = user_data.id_user 
            LEFT JOIN department ON user.idDepartment = department.id 
            LEFT JOIN roles ON user.id_role = roles.id 
            WHERE user.id_organization  = $idOrganization AND user.deleted = 0";
            } else {
                $sql = "SELECT *,department.idPadre,user_data.customfield, department.name, roles.role  FROM user 
            LEFT JOIN user_data ON user.id = user_data.id_user 
            LEFT JOIN department ON user.idDepartment = department.id 
            LEFT JOIN roles ON user.id_role = roles.id 
            WHERE user.idDepartment = $idDepartment AND user.deleted = 0";
            }
        } else if ($idRole == 3) {
            $sql = "SELECT idPadre FROM department WHERE id = $userDepartment";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $idPadre = $stmt->fetch(PDO::FETCH_ASSOC);
            $idPadre = $idPadre['idPadre'];

            if ($idPadre == 0) {

                $sql = "SELECT *,user_data.customfield,department.idPadre, department.name, roles.role  FROM user 
                LEFT JOIN user_data ON user.id = user_data.id_user 
                LEFT JOIN department ON user.idDepartment = department.id 
                LEFT JOIN roles ON user.id_role = roles.id 
                WHERE user.idDepartment IN (SELECT id FROM department WHERE idPadre = $userDepartment) OR user.idDepartment = $userDepartment AND  user.id != $idUser AND user.deleted = 0";
            } else {
                $sql = "SELECT *, department.idPadre,user_data.customfield, department.name, roles.role  FROM user 
                LEFT JOIN user_data ON user.id = user_data.id_user 
                LEFT JOIN department ON user.idDepartment = department.id 
                LEFT JOIN roles ON user.id_role = roles.id 
                WHERE AND user.idDepartment = $userDepartment AND user.id != $idUser AND user.deleted = 0";
            }
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);


        $result = [];
        foreach ($users as $user) {

            $idPadre = $user['idPadre'];

            if ($idPadre != 0) {
                $sql = "SELECT name FROM department WHERE id = $idPadre";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $departmentFather = $stmt->fetch(PDO::FETCH_ASSOC);
                $departmentFather = $departmentFather['name'];
            } else {
                $departmentFather = $user['name'];
            }

            $customfield = isset($user['customfield']) || $user['customfield'] != "" ?  json_decode($user['customfield'], true) : "";

            $arr = [
                "idUser"     => $user["id_user"],
                "nome"       => $user['firstname'],
                "cognome"    => $user['lastname'],
                "customfield" => $customfield,
                "email"      => $user['email'],
                "emailAziendale" => $user['emailAziendale'],
                'idDepartment' => $user['idDepartment'],
                'idRole'     => $user['id_role'],
                "department" => $idPadre != 0 ? $user['name'] : "-",
                "departmentFather" => $departmentFather,
                "role"       => ucfirst($user['role']),
                "nomeCompleto" => ucfirst($user['firstname']) . " " . ucfirst($user['lastname']),
                'email' => $user['email'],
                'active' => isset($user['active']) && $user['active'] == 1 ? "Attivo" : "Inattivo",
                'nomeCompleto' => $user['firstname'] . " " . $user['lastname'],
                'birthday' => date('d-m-Y', strtotime($user['birthday'])),
                'birth' => date('Y-m-d', strtotime($user['birthday'])),
                'pic' => isset($user['pic']) || $user['pic'] != '' ? $user['pic'] : 'N/D',
                'companyName' => isset($user['companyName']) || $user['companyName'] != '' ? $user['companyName'] : 'N/D',
                'address' => isset($user['address']) || $user['address'] != '' ? $user['address'] : 'N/D',
                'phone'  => isset($user['phone']) || $user['phone'] != '' ? $user['phone'] : 'N/D',
                'city'   => isset($user['city']) || $user['city'] != '' ? $user['city'] : 'N/D',
                'zip'   => isset($user['zip_code']) || $user['zip_code'] != '' ? $user['zip_code'] : 'N/D',
                'region' => isset($user['region']) || $user['region'] != '' ? $user['region'] : "N/D",
                'country' => isset($user['country']) || $user['country'] != '' ? $user['country'] : 'N/D',
                'province' => isset($user['province']) || $user['province'] != '' ? $user['province'] : "N/D",
                'vatNumber' => isset($user['vat_number']) || $user['vat_number'] != "" ? $user['vat_number'] : 'N/D',
                'iban' => isset($user['iban']) || $user['iban'] != '' ? $user['iban'] : 'N/D',
                'oraCheckin' => isset($user['oraCheckin']) || $user['oraCheckin'] != '' ? $user['oraCheckin'] : 'N/D',
                'oraCheckout' => isset($user['oraCheckout']) || $user['oraCheckout'] != '' ? $user['oraCheckout'] : 'N/D',
                'minutiPausa' => isset($user['minutiPausa']) || $user['minutiPausa'] != '' ? $user['minutiPausa'] : 'N/D',
                'deltaObiettivi' => isset($user['deltaObiettivi']) || $user['deltaObiettivi'] == '' ? $user['deltaObiettivi'] : 'N/D',
                'kMinuti' => isset($user['kMinuti']) || $user['kMinuti'] == '' ? $user['kMinuti'] : 'N/D',
                'actions' => [
                    "idUser"     => $user["id_user"],
                    "nome"       => $user['firstname'],
                    "cognome"    => $user['lastname'],
                    "customfield" => $customfield,
                    "email"      => $user['email'],
                    'idDepartment' => $user['idDepartment'],
                    'idRole'     => $user['id_role'],
                    "department" => $idPadre != 0 ? $user['name'] : "-",
                    "departmentFather" => $departmentFather,
                    "role"       => ucfirst($user['role']),
                    "nomeCompleto" => ucfirst($user['firstname']) . " " . ucfirst($user['lastname']),
                    'email' => $user['email'],
                    'active' => isset($user['active']) && $user['active'] == 1 ? "Attivo" : "Inattivo",
                    'nomeCompleto' => $user['firstname'] . " " . $user['lastname'],
                    'birthday' => date('d-m-Y', strtotime($user['birthday'])),
                    'birth' => date('Y-m-d', strtotime($user['birthday'])),
                    'pic' => isset($user['pic']) || $user['pic'] != '' ? $user['pic'] : 'N/D',
                    'companyName' => isset($user['companyName']) || $user['companyName'] != '' ? $user['companyName'] : 'N/D',
                    'address' => isset($user['address']) || $user['address'] != '' ? $user['address'] : 'N/D',
                    'phone'  => isset($user['phone']) || $user['phone'] != '' ? $user['phone'] : 'N/D',
                    'city'   => isset($user['city']) || $user['city'] != '' ? $user['city'] : 'N/D',
                    'zip'   => isset($user['zip_code']) || $user['zip_code'] != '' ? $user['zip_code'] : 'N/D',
                    'region' => isset($user['region']) || $user['region'] != '' ? $user['region'] : "N/D",
                    'province' => isset($user['province']) || $user['province'] != '' ? $user['province'] : "N/D",
                    'country' => isset($user['country']) || $user['country'] != '' ? $user['country'] : 'N/D',
                    'vatNumber' => isset($user['vat_number']) || $user['vat_number'] != "" ? $user['vat_number'] : 'N/D',
                    'iban' => isset($user['iban']) || $user['iban'] != '' ? $user['iban'] : 'N/D',
                    'oraCheckin' => isset($user['oraCheckin']) || $user['oraCheckin'] != '' ? $user['oraCheckin'] : 'N/D',
                    'oraCheckout' => isset($user['oraCheckout']) || $user['oraCheckout'] != '' ? $user['oraCheckout'] : 'N/D',
                    'minutiPausa' => isset($user['minutiPausa']) || $user['minutiPausa'] != '' ? $user['minutiPausa'] : 'N/D',
                    'deltaObiettivi' => isset($user['deltaObiettivi']) || $user['deltaObiettivi'] == '' ? $user['deltaObiettivi'] : 'N/D',
                    'kMinuti' => isset($user['kMinuti']) || $user['kMinuti'] == '' ? $user['kMinuti'] : 'N/D',
                ]
            ];
            array_push($result, $arr);
        }

        return $result;
    }

    public function GetUserDetailByDepartment($input)
    {
        $idUser = $input['idUtente'];
        $idRole = $input['idRole'];
        $idDepartment = $input['idDepartment'];
        $userDepartment = $input['userDepartment'];
        $idOrganization = '';

        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUtente'];
        }

        if ($idRole == 4) {
            if ($idDepartment == 'all') {
                $sql = "SELECT *, department.*, roles.role  FROM user 
            LEFT JOIN user_data ON user.id = user_data.id_user 
            LEFT JOIN department ON user.idDepartment = department.id 
            LEFT JOIN roles ON user.id_role = roles.id 
            WHERE user.id_organization  = $idOrganization";
            } else {
                $sql = "SELECT *, department.*, roles.role  FROM user 
            LEFT JOIN user_data ON user.id = user_data.id_user 
            LEFT JOIN department ON user.idDepartment = department.id 
            LEFT JOIN roles ON user.id_role = roles.id 
            WHERE user.idDepartment = $idDepartment";
            }
        } else if ($idRole == 3) {
            $sql = "SELECT *, department.*, roles.role  FROM user 
            LEFT JOIN user_data ON user.id = user_data.id_user 
            LEFT JOIN department ON user.idDepartment = department.id 
            LEFT JOIN roles ON user.id_role = roles.id 
            WHERE user.idDepartment = $userDepartment AND user.id != $idUser";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($users as $user) {

            $arr['id_user'] = [
                'idUser' => $user['id_user'],
                'email' => $user['email'],
                'active' => isset($user['active']) && $user['active'] == 1 ? "Attivo" : "Inattivo",
                'nomeCompleto' => $user['firstname'] . " " . $user['lastname'],
                'birthday' => date('d-m-Y', strtotime($user['birthday'])),
                'pic' => isset($user['pic']) || $user['pic'] != '' ? $user['pic'] : 'N/D',
                'companyName' => isset($user['companyName']) || $user['companyName'] != '' ? $user['companyName'] : 'N/D',
                'address' => isset($user['address']) || $user['address'] != '' ? $user['address'] : 'N/D',
                'phone'  => isset($user['phone']) || $user['phone'] != '' ? $user['phone'] : 'N/D',
                'city'   => isset($user['city']) || $user['city'] != '' ? $user['city'] : 'N/D',
                'zip'   => isset($user['zip_code']) || $user['zip_code'] != '' ? $user['zip_code'] : 'N/D',
                'region' => isset($user['region']) || $user['region'] != '' ? $user['region'] : "N/D",
                'country' => isset($user['country']) || $user['country'] != '' ? $user['country'] : 'N/D',
                'vatNumber' => isset($user['vat_number']) || $user['vat_number'] != "" ? $user['vat_number'] : 'N/D',
                'iban' => isset($user['iban']) || $user['iban'] != '' ? $user['iban'] : 'N/D',
                'oraCheckin' => isset($user['oraCheckin']) || $user['oraCheckin'] != '' ? $user['oraCheckin'] : 'N/D',
                'oraCheckout' => isset($user['oraCheckout']) || $user['oraCheckout'] != '' ? $user['oraCheckout'] : 'N/D',
                'minutiPausa' => isset($user['minutiPausa']) || $user['minutiPausa'] != '' ? $user['minutiPausa'] : 'N/D',
                'deltaObiettivi' => isset($user['deltaObiettivi']) || $user['deltaObiettivi'] == '' ? $user['deltaObiettivi'] : 'N/D',
                'kMinuti' => isset($user['kMinuti']) || $user['kMinuti'] == '' ? $user['kMinuti'] : 'N/D',
                'role' => isset($user['role']) || $user['role'] != '' ? $user['role'] : 'N/D'
            ];
            array_push($result, $arr);
        }

        return $result;
    }

    public function UpdateDepartment($input)
    {
        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUtente'];
        }

        $idDepartment = $input['idDepartment'];
        $name = $input['nameDepartment'];
        $oraCheckin = $input['oraCheckin'];
        $oraCheckout = $input['oraCheckout'];
        $minutiPausa = $input['minutiPausa'];
        $deltaObiettivi = $input['deltaObiettivi'];
        $autoCheckin = isset($input['autoCheckin']) && $input['autoCheckin'] == true ? 1 : 0;
        $kMinuti = $input['kMinuti'];

        $sql = "SELECT COUNT(*) AS writeble FROM department WHERE name LIKE '$name' AND idOrganization = $idOrganization AND id != $idDepartment";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $writeble = $stmt->fetch(PDO::FETCH_ASSOC);
        $writeble = $writeble['writeble'];


        if ($writeble > 0) {
            return ["Error", "Nome dipartimento già esistente"];
        } else {


            $sql = "UPDATE `department` 
            SET `name`='$name',`oraCheckin`='$oraCheckin',`oraCheckout`='$oraCheckout',`minutiPausa`=$minutiPausa,
            `deltaObiettivi`=$deltaObiettivi,`autoCheckin` = $autoCheckin, `kMinuti` = '$kMinuti' WHERE id=$idDepartment";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            if (isset($idDipendente)) {
                $sql = "UPDATE `user` SET idDepartment = $idDepartment WHERE id = $idDipendente";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
            }

            return ["Success", "Dipartimento aggiornato con successo"];
        }
    }

    public function CreateDepartment($input)
    {

        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUtente'];
        }

        $input = $input['values'];


        $name = $input['nameDepartment'];
        $idPadre = $input['idPadre'];
        $oraCheckin = $input['orarioCheckIn'];
        $oraCheckout = $input['orarioCheckout'];
        $minutiPausa = $input['minutiPausa'];
        $deltaObiettivi = $input['delta'];
        $autoCheckin = isset($input['autoCheckin']) && $input['autoCheckin'] == 1 ? 'true' : 'false';
        $kMinuti = $input['minutiGestionePratica'];

        if ($idPadre != 0) {
            $sql = "SELECT idModule FROM department WHERE id = $idPadre";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            $module = $stmt->fetch(PDO::FETCH_ASSOC);
            $module = $module['idModule'];
        } else {
            $module = $input['module'];
        }

        $sql = "SELECT COUNT(*) AS writeble FROM department WHERE name LIKE '$name' AND idOrganization = $idOrganization";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $writeble = $stmt->fetch(PDO::FETCH_ASSOC);
        $writeble = $writeble['writeble'];

        if ($writeble > 0) {
            return ["red", "Nome dipartimento già esistente"];
        } else {
            $sql = "INSERT INTO `department`(idPadre, `name`, idModule, `idOrganization`, `oraCheckin`, `oraCheckout`, 
            `minutiPausa`, `deltaObiettivi`, `autoCheckin`, `kMinuti`) 
            VALUES ($idPadre, '$name', $module, $idOrganization,'$oraCheckin','$oraCheckout',$minutiPausa,$deltaObiettivi,$autoCheckin,'$kMinuti');";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            return ["green", "Dipartimento creato correttamente"];
        }
    }

    public function GetAllHr($input)
    {
        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUtente'];
        }

        $sql = "SELECT user.id, user_data.firstname, user_data.lastname 
        FROM user LEFT JOIN user_data ON user.id = user_data.id_user
        WHERE user.id_role = 3 AND user.id_organization = $idOrganization";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $utentiHr = $stmt->fetchAll(PDO::FETCH_ASSOC);


        $result = [];
        foreach ($utentiHr as $utenteHr) {
            $arr = [
                "idDipendente" => $utenteHr['id'],
                "nomeCompleto" => ucfirst($utenteHr['firstname']) . " " . ucfirst($utenteHr['lastname'])
            ];
            array_push($result, $arr);
        }

        return $result;
    }

    public function CreateDipendente($input)
    {
        //Generatore password e authtoken
        function generateRandomString($length = 10)
        {
            $characters = '0123456789!%()?*abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }

        //configuratore email


        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUser'];
        }

        $email = $input['email'];
        $emailAziendale = $input['emailAziendale'];
        $password = generateRandomString(12);
        $hashedPassword = hash("sha256", $password);
        $idDepartment = $input['idDepartment'];
        $idRole = $input['idRole'];
        $idLocation = 0;
        $authToken = generateRandomString(56);
        $active = 1;
        $deleted = 0;
        $firstname = addslashes($input['firstname']);
        $lastname = addslashes($input['lastname']);
        $birthday = $input['birthday'];
        $pic = '';
        $companyName = '';
        $address = '';
        $phone = $input['phone'];
        $city = '';
        $zip = 0;
        $region = '';
        $country = '';
        $vatNumber = $input['vatNumber'];
        $iban = $input['iban'];

        $sql = "SELECT slug FROM customfield WHERE idOrganization = $idOrganization";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $slugs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $customfield = [];
        if (isset($slugs)) {
            foreach ($slugs as $slug) {
                if (isset($input[$slug['slug']])) {
                    $customfield[$slug['slug']] = $input[$slug['slug']];
                }
            }
        }

        if ($customfield == []) {
            $customfield = "{}";
        } else {
            $customfield = json_encode($customfield);
        }

        $sql = "SELECT COUNT(*) AS checkUser FROM user WHERE email LIKE '$email'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $check = $stmt->fetch(PDO::FETCH_ASSOC);

        $check = $check['checkUser'];

        if ($check == 0) {

            $sql = "INSERT INTO `user`(`email`, `emailAziendale`, `password`, `id_organization`, `idDepartment`,
            `id_role`, `id_location`, `authToken`, `active`, `deleted`, `date_created`) 
            VALUES ('$email', '$emailAziendale', '$hashedPassword',$idOrganization,$idDepartment,
            $idRole,$idLocation,'$authToken',$active,$deleted,CURRENT_TIMESTAMP);
            INSERT INTO `user_data`(`id_user`,  `firstname`, `lastname`, `birthday`,
            `pic`, `companyName`, `address`, `phone`, `city`, `zip_code`, `region`, `country`, 
            `vat_number`, `iban`, `customField`, `date_modified`) 
            VALUES ((SELECT id FROM user WHERE id=(SELECT LAST_INSERT_ID())),'$firstname','$lastname','$birthday','$pic',
            '$companyName','$address','$phone','$city','$zip','$region','$country','$vatNumber',
            '$iban', '$customfield',CURRENT_TIMESTAMP)";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            $mail = new EmailSistem();
            if ($emailAziendale == "") {
                $emailInvio = $email;
            } else {
                $emailInvio = $emailAziendale;
            }
            $oggetto = "Credenziali accesso Doky HR";
            $messaggio = "<p>Ciao $firstname $lastname!</p>
            <p>Benvenuto sulla piattaforma gestionale DokyHR!</p>
             <p>Utilizza questi dati per accedere.</p>
             <p>Username: $email Password: $password</p>
             <p>Distinti saluti,<br>
             Il team di DokyHR.</p>";

            $mail->SendMail($emailInvio, $oggetto, $messaggio);

            if ($input['idOrganization'] != 0) {
                $idOrganization = $input['idOrganization'];
            } else {
                $idOrganization = $input['idUser'];
            }

            return "Dipendente creato con successo";
        } else {

            return "Dipendente già esistente";
        }
    }

    public function GetColumnField($input)
    {

        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUser'];
        }

        $sql = "SELECT * FROM customfield WHERE idOrganization = $idOrganization";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $colonne = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($colonne as $colonna) {
            $arr = [
                'idColumnField' => $colonna['id'],
                'nomeCampo' => isset($colonna['nomeCampo']) ? $colonna['nomeCampo'] : "",
                'slug'      => isset($colonna['slug']) ? $colonna['slug'] : ""
            ];
            array_push($result, $arr);
        }

        return $result;
    }

    public function RemoveColumn($input)
    {
        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUser'];
        }

        $idCustomerField = $input['idCustomField'];

        $sql = "SELECT COUNT(*) AS checkfield FROM customfield WHERE id = $idCustomerField AND assigned = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $checkField = $stmt->fetch(PDO::FETCH_ASSOC);
        $checkField = $checkField['checkfield'];

        if ($checkField == 0) {
            $sql = "DELETE FROM customfield WHERE id = $idCustomerField";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            return '';
        } else {
            return 'Campo personalizzato assegnato, impossibile eliminare';
        }
    }

    public function AddCustomField($input)
    {
        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUser'];
        }

        $customField = $input['customField'];
        $fieldName = strtolower($customField);
        $slug = str_replace(' ', '_', $fieldName);

        $sql = "SELECT COUNT(*) AS checkfield FROM customfield 
        WHERE nomeCampo LIKE '$fieldName'
        AND slug LIKE '$slug' 
        AND idOrganization = $idOrganization";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $checkField = $stmt->fetch(PDO::FETCH_ASSOC);
        $checkField = $checkField['checkfield'];

        if ($checkField == 0) {
            $sql = "INSERT INTO `customfield`(`idOrganization`, `nomeCampo`, `slug`) 
            VALUES ($idOrganization,'$fieldName','$slug')";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            return '';
        } else {
            return 'Nome campo già esistente';
        }
    }

    public function createSubDepartment($input)
    {
        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUser'];
        }

        $idPadre = $input['values'][0]['idDepartment'];
        $idSupervisor = $input['values'][0]['idDipendente'];
        $autoCheckin = $input['values'][0]['autoCheckin'];
        $oraCheckin = $input['values'][0]['oraCheckin'];
        $oraCheckout = $input['values'][0]['oraCheckout'];
        $minutiPausa = $input['values'][0]['minutiPausa'];
        $deltaObiettivi = $input['values'][0]['deltaObiettivi'];
        $kMinuti = $input['values'][0]['kMinuti'];
        $nameSubDepartment = strtolower($input['nameSubDepartment']);

        $sql = "SELECT COUNT(*) AS checkName FROM department WHERE `name` LIKE '$nameSubDepartment'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $checkName = $stmt->fetch(PDO::FETCH_ASSOC);
        $checkName = $checkName['checkName'];

        if ($checkName > 0) {
            return ['Error', 'Nome dipartimento già esistente'];
        } else {
            $sql = "INSERT INTO `department`(`idPadre`, `idSupervisor`, `name`, `idOrganization`, 
            `oraCheckin`, `oraCheckout`, `minutiPausa`, `deltaObiettivi`, `autoCheckin`, `kMinuti`) 
            VALUES ($idPadre,$idSupervisor,'$nameSubDepartment',$idOrganization,'$oraCheckin',
            '$oraCheckout','$minutiPausa','$deltaObiettivi','$autoCheckin','$kMinuti')";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            return ['Success', 'Sotto dipartimento creato con successo'];
        }
    }

    public function GetAllSubDepartment($input)
    {
        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUser'];
        }

        $sql = "SELECT * FROM department WHERE idOrganization = $idOrganization AND idPadre != 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $subDepartments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($subDepartments as $subDepartment) {
            $idPadre = $subDepartment['idPadre'];
            $sql = "SELECT name FROM department WHERE id = $idPadre";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $nameDepartment = $stmt->fetch(PDO::FETCH_ASSOC);
            $nameDepartment = $nameDepartment['name'];

            $idDepartment = $subDepartment['id'];

            $sql = "SELECT user.id, user_data.firstname, user_data.lastname FROM user LEFT JOIN user_data
            ON user.id = user_data.id_user WHERE user.idDepartment = $idDepartment AND (user.id_role = 3 OR user.id_role = 4)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $hr = $stmt->fetch(PDO::FETCH_ASSOC);
            $fullName = isset($hr['firstname']) && isset($hr['lastname']) ? $hr['firstname'] . " " . $hr['lastname'] : "";
            $idHr = isset($hr['id']) ? $hr['id'] : "";


            $arr = [
                'idDepartment' => $subDepartment['id'],
                'nameDepartment' => $subDepartment['name'],
                'nameFather' => $nameDepartment,
                'idPadre' => $subDepartment['idPadre'],
                'hr'      => $fullName,
                'idHr'    => $idHr,
                'autoCheckin' => $subDepartment['autoCheckin'],
                'oraCheckin' => date("H:i", strtotime($subDepartment['oraCheckin'])),
                'oraCheckout' => date("H:i", strtotime($subDepartment['oraCheckout'])),
                'minutiPausa' => $subDepartment['minutiPausa'],
                'deltaObiettivi' => $subDepartment['deltaObiettivi'],
                'kMinuti' => date("H:i:s", strtotime($subDepartment['kMinuti'])),
            ];
            array_push($result, $arr);
        }

        return $result;
    }

    public function RemoveDeparmtent($input)
    {
        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUser'];
        }

        $idDepartment = $input['idDepartment'];

        $sql = "SELECT COUNT(*) AS checkDepartment FROM user WHERE idDepartment = $idDepartment AND (id_organization = $idOrganization OR id = $idOrganization)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $checkDepartment = $stmt->fetch(PDO::FETCH_ASSOC);
        $checkDepartment = $checkDepartment['checkDepartment'];

        if ($checkDepartment == 0) {
            $sql = "DELETE FROM department WHERE id = $idDepartment";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            return true;
        } else {
            return false;
        }
    }

    public function ImportCsvDipendenti($input)
    {
        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUser'];
        }


        $mailTo = $input['mailTo'];

        //PREPROCESSING DIPARTIMENTI -> CREO UN DIZIONARIO [NOME: ID]
        $sql = "SELECT * FROM department WHERE idOrganization = $idOrganization";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $dictionaryDepartments = [];
        foreach ($departments as $department) {
            $dictionaryDepartments[strtolower($department['name'])] =  $department['id'];
        }

        //PREPROCESSING ROLES -> CREO DIZIONARIO [NOME: ID]
        $sql = "SELECT * FROM roles WHERE id_organization = 0 OR id_organization = $idOrganization";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $dictionaryRoles = [];
        foreach ($roles as $role) {
            $dictionaryRoles[strtolower($role['role'])] = $role['id'];
        }

        //PREPROCESSING CUSTOM FIELD -> RECUPERO EVENTUALI CUSTOM FIELD [SLUG: ""]
        $sql = "SELECT slug FROM customfield WHERE idOrganization";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $dictionarySlugs = [];
        if ($stmt->rowCount() > 0) {
            $slugs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($slugs as $slug) {
                $dictionarySlugs[strtolower($slug['slug'])] = "";
            }
        } else {
            $dictionarySlugs = false;
        }

        //ESTRAGGO LE RIGHE E COLONNE DAL CSV
        $dati = $input['dati'];
        $colonne = $input['colonne'];


        //RIORDINO LE COLONNE E RIGHE
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

        //SOSTITUISCO LE CHIAVI CON IL NOME DEL CAMPO
        $dati = $newArray;
        $newDati = [];
        foreach ($dati as $dato) {
            $newArr = [];
            foreach ($colonne as $chiave => $colonna) {

                $newArr[$chiave] = $dato[$colonna];
            }
            array_push($newDati, $newArr);
        }

        $dati = $newDati;

        //SOSTITUISCO IL NOME DIPARTIMENTO E RUOLO CON GLI ID 
        //(SE IL RUOLO/DIPARTIMENTO NON ESISTE VERRÀ ASSEGNATO "NESSUN DIPARTIMENTO/RUOLO DI DEFAULT)
        $result = [];
        foreach ($dati as $dato) {
            $chiaveDep = strtolower($dato['dipartimento']);
            $chiaveRole = strtolower($dato['ruolo']);
            $dato['dipartimento'] = isset($dictionaryDepartments[$chiaveDep]) ? $dictionaryDepartments[$chiaveDep] : 0;
            $dato['ruolo'] = isset($dictionaryRoles[$chiaveRole]) ? $dictionaryRoles[$chiaveRole] : 0;
            array_push($result, $dato);
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


        //FUNZIONA PER GENERARE PASSWORD E AUTHTOKEN
        function generateString($length = 10)
        {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }

        //ELIMINO LE RIGHE VUOTE
        foreach ($result as $key => $values) {
            $a = array_keys($values);
            $n = count($a);
            for ($i = 0, $count = 0; $i < $n; $i++) {
                if ($values[$a[$i]] == NULL) {
                    $count++;
                }
            }
            if ($count == $n) {

                unset($result[$key]);
            }
        }

        //CONTROLLO SE LA MAIL È GIÀ ESISTENTE
        for ($i = 0; $i < count($result); $i++) {
            $email = isset($result[$i]['mail']) || $result[$i]['mail'] == "" ? $result[$i]['mail'] : $result[$i]['mailAziendale'];
            $sql = "SELECT COUNT(*) AS checkmail FROM user WHERE email LIKE '$email'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $checkmail = $stmt->fetch(PDO::FETCH_ASSOC);
            $checkmail = $checkmail['checkmail'];
        }
        if ($checkmail > 0) {

            unset($result[$key]);
        }

        //CREO LA STRINGA PER AGGIUNGERE I DIPENDENTI NELLA TABELLA USER
        $stringa = "";

        for ($i = 0; $i < count($result); $i++) {

            $stringa .= "(";

            $email = isset($result[$i]['mail']) ? $result[$i]['mail'] : $result[$i]['mailAziendale'];
            $emailAziendale = isset($result[$i]['mailAziendale']) && isset($result[$i]['mail']) ? $result[$i]['mailAziendale'] : "";
            $ruolo = isset($result[$i]['ruolo']) ? $result[$i]['ruolo'] : 0;
            $dipartimento = isset($result[$i]['dipartimento']) ? $result[$i]['dipartimento'] : 0;
            $authToken = generateString(16);
            $password = generateString(8);
            $hashedPassword = hash("sha256", $password);
            //AGGIUNGO ALLA STRINGA IL VALORE PER CREARE LA QUERY
            $stringa .= "'$email', '$emailAziendale', '$hashedPassword', $idOrganization, $dipartimento, $ruolo, 0, '$authToken', -1, 0,";

            if ($i == count($result) - 1) {
                $stringa .= "CURRENT_TIMESTAMP())";
            } else {
                $stringa .= "CURRENT_TIMESTAMP()), ";
            }
            //CONFIGURO EMAIL PER INVIO CREDENZIALI AD OGNI UTENTE INSERITO SE SI DECIDE DI INVIARLE
            if ($mailTo == true) {
                $mail = new EmailSistem();

                $nome = ucwords($result[$i]['nome']);
                $cognome = ucwords($result[$i]['cognome']);

                //IN AREA TEST INVIO A DEVELOPER
                $domain = $_SERVER['SERVER_NAME'];
                if (strpos($domain, "localhost") == true) {
                    $username = "development@genesismobile.it";
                    $oggetto = "TEST - Credenziali accesso DOKY HR";
                } else {
                    $username = $email;
                    $oggetto = "Credenziali accesso Doky HR";
                }

                $messaggio =
                    "<html>
                <head>
                <title>BENVENUTO!</title>
                </head>
                <body>
                <p>Benvenuto su DOKY HR!</p><br/>
                <p>Ciao $nome $cognome,</p><br/>
                <p>usa queste credenziali per accedere al tuo profilo</p>
                <p>USERNAME: <b>$username</b> PASSWORD: <b>$password</b></p>
                </body>
                </html>";


                $mail->SendMail($username, $oggetto, $messaggio);
            }
        }

        //POPOLO LA TABELLA USER CON GLI UTENTI
        $sql = "INSERT INTO `user`(`email`, `emailAziendale`, `password`, `id_organization`, `idDepartment`, `id_role`, 
        `id_location`, `authToken`, `active`, `deleted`, `date_created`) VALUES $stringa";
        $esito = true;
        $messaggio = "Importazione riuscita per " . count($result) . " righe";
        $title = "Esito importazione:";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        } catch (PDOException $e) {
            $esito = false;
            $messaggio = "Errore: " . $e->getMessage();
        }

        $stringa = "";
        for ($i = 0; $i < count($result); $i++) {
            //AGGIUNGO ALLA STRINGA IL VALORE PER CREARE LA QUERY
            $email = isset($result[$i]['mail']) ? $result[$i]['mail'] : "";
            $emailAziendale = isset($result[$i]['mailAziendale']) ? $result[$i]['mailAziendale'] : "";
            if ($email != "" || $emailAziendale != "") {


                $nome = isset($result[$i]['nome']) ? RemoveSpecialChar(ucwords(strtolower($result[$i]['nome']))) : "";
                $cognome = isset($result[$i]['cognome']) ? RemoveSpecialChar(ucwords(strtolower($result[$i]['cognome']))) : "";
                $customField = "{}";
                if (count($dictionarySlugs) > 0) {
                    $customField = [];
                    foreach ($dictionarySlugs as $slug => $a) {
                        $n = isset($result[$i][$slug]) ? $result[$i][$slug] : "";
                        $customField[$slug] = $n;
                    }
                }
                $customField = json_encode($customField);

                if ($i == 0) {

                    $stringa .= "((SELECT id FROM user WHERE email LIKE '$email' AND emailAziendale like '$emailAziendale' AND active = -1), '$nome', '$cognome', '1999-09-09', '', '', '','', '', '', '', '', '', '', '$customField', CURRENT_TIMESTAMP())";
                } else {
                    $stringa .= ", ((SELECT id FROM user WHERE email LIKE '$email' AND emailAziendale like '$emailAziendale' AND active = -1), '$nome', '$cognome', '1999-09-09', '', '', '','', '', '', '', '', '', '', '$customField', CURRENT_TIMESTAMP())";
                }
            }
        }

        $sql = "INSERT INTO `user_data`(`id_user`, `firstname`, `lastname`, `birthday`, `pic`, `companyName`, 
        `address`, `phone`, `city`, `zip_code`, `region`, `country`, `vat_number`, `iban`, `customField`, `date_modified`) 
        VALUES $stringa";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        } catch (PDOException $e) {
            $esito = false;
            $messaggio = "Errore: " . $e->getMessage();
        }

        return [
            "title" => $title,
            "loading" => false,
            "result" => $esito,
            "message" => $messaggio,
            "open" => true
        ];
    }

    public function DeleteDipendente($input)
    {
        $idDipendente = $input['idDipendente'];

        $sql = "UPDATE `user` SET `deleted` = 1, active = 0 WHERE id = $idDipendente";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return [
            'result' => true,
            'message' => 'Utente eliminato con successo'
        ];
    }

    public function UpdateDipendente($input, $file)
    {
        $idDipendente = isset($input['idDipendente']) ? $input['idDipendente'] : null;
        $firstname = isset($input['nome']) ? "firstname = '" . addslashes($input['nome']) . "'," : null;
        $lastname = isset($input['cognome']) ? "lastname = '" . addslashes($input['cognome']) . "'," : null;
        $birthday = isset($input['birthday']) ? "birthday = '" . $input['birthday'] . "'," : null;
        $password = isset($input['password']) ? $input['password'] : null;
        $hashedPassword = isset($input['password']) ? "password = '" . hash("sha256", $password) . "'," : null;
        $phone = isset($input['phone']) ? "phone = '" . $input['phone'] . "'," : null;
        $email = isset($input['email']) ? "email = '" . $input['email'] . "'," : null;
        $emailAziendale = isset($input['emailAziendale']) && !empty($input['emailAziendale']) ? "emailAziendale = '" . $input['emailAziendale'] . "'," : null;
        $mailTo = $input['emailAziendale'] == "" || !isset($input['emailAziendale']) ? $input['email'] : $input['emailAziendale'];
        $country = isset($input['country']) ? "country = '" . addslashes($input['country']) . "'," : null;
        $city = isset($input['city']) || $input['city'] != -1 ? "city = '" . addslashes($input['city']) . "'," : null;
        $zip = isset($input['zip']) ? "zip_code = '" . $input['zip'] . "'," : null;
        $address = isset($input['address']) ? "address = '" . $input['address'] . "'," : null;
        $vatNumber = isset($input['vatNumber']) ? "vat_number = '" . $input['vatNumber'] . "'," : null;
        $iban = isset($input['iban']) ? "iban = '" . $input['iban'] . "'," : null;
        $idRole = isset($input['idRole']) ? "id_role = " . $input['idRole'] . "," : null;
        $idDepartment = isset($input['idDepartment']) ? "idDepartment = " . $input['idDepartment'] . "," : null;
        $idOrganization = isset($input['idOrganization']) ? $input['idOrganization'] : null;
        $region = isset($input['region']) ? "region = '" . addslashes($input['region']) . "'," : null;
        $province = isset($input['province']) ? "province = '" . addslashes($input['province']) . "'," : null;

        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUser'];
        }

        if ($file == false) {
            $sql = "SELECT pic FROM user_data WHERE id_user = $idDipendente";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $pic = $stmt->fetch(PDO::FETCH_ASSOC);
            $pic = "pic = '" . $pic['pic'] . "',";
        } else {
            $pic = $file;
            move_uploaded_file($file['tmp_name'], "../../../media/users/" . $idDipendente . $pic['name']);
            $pic = "pic = '" . '/media/users/' . $idDipendente . $pic['name'] . "',";
        }


        $sql = "SELECT slug FROM customfield WHERE idOrganization = $idOrganization";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $slugs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $arrCustomField = [];
        if (isset($slugs)) {
            foreach ($slugs as $slug) {
                if (isset($input[$slug['slug']])) {
                    $arrCustomField[$slug['slug']] = $input[$slug['slug']];
                }
            }
        }

        $customfield =  $arrCustomField != [] ? "customField = '" . json_encode($arrCustomField) . "'," : null;
        $mail = isset($input['email']) ? $input['email'] : "";

        //CHECK EMAIL-EMAIL AZIENDALE-NUMERO TELEFONO
        $sql = "SELECT COUNT(*) AS checkemail FROM user 
        WHERE email LIKE '$mail' AND id != $idDipendente";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $checkEmail = $stmt->fetch(PDO::FETCH_ASSOC);
        $checkEmail = $checkEmail['checkemail'];

        $checkEmailAziendale = 0;
        if ($input['emailAziendale'] != "") {
            $mailAziendale = isset($input['emailAziendale']) || $input['emailAziendale'] != "undefined" ? $input['emailAziendale'] : "";
            $sql = "SELECT COUNT(*) AS checkemail FROM user 
            WHERE (email LIKE '$mail' OR email LIKE '$mailAziendale') AND id != $idDipendente";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $checkEmail = $stmt->fetch(PDO::FETCH_ASSOC);
            $checkEmail = $checkEmail['checkemail'];

            $sql = "SELECT COUNT(*) AS checkemailaziendale FROM user 
            WHERE (emailAziendale LIKE '$mail' OR emailAziendale LIKE '$mailAziendale') AND id != $idDipendente";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $checkEmailAziendale = $stmt->fetch(PDO::FETCH_ASSOC);
            $checkEmailAziendale = $checkEmailAziendale['checkemailaziendale'];

            $sql = "SELECT COUNT(*) AS undefinedMails FROM user WHERE emailAziendale LIKE 'undefined'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $undefinedEmailAziendale = $stmt->fetch(PDO::FETCH_ASSOC);
            $undefinedEmailAziendale = $undefinedEmailAziendale['undefinedMails'];

            $checkEmailAziendale = $checkEmailAziendale - $undefinedEmailAziendale;
        }

        $phoneCheck = isset($input['phone']) ? $input['phone'] : "";
        $sql = "SELECT COUNT(*) AS checkphone FROM user_data
        WHERE phone='$phoneCheck' AND id_user != $idDipendente";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $checkPhone = $stmt->fetch(PDO::FETCH_ASSOC);
        $checkPhone = $phoneCheck == "" ? 0 : $checkPhone['checkphone'];

        $result = ["result" => true];
        if ($checkEmail == 0 && $checkEmailAziendale == 0 && $checkPhone == 0) {

            if ($password == "") {

                $sql = "SELECT password FROM user WHERE id = $idDipendente";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $hashedPassword = $stmt->fetch(PDO::FETCH_ASSOC);
                $hashedPassword = "password = '" . $hashedPassword['password'] . "',";
            } else {
                $sql = "SELECT firstname, lastname FROM user_data WHERE id_user = $idDipendente";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $nomeCompleto = $stmt->fetch(PDO::FETCH_ASSOC);
                $nomeCompleto = $nomeCompleto['firstname'] . " " . $nomeCompleto['lastname'];

                $mail = new EmailSistem();
                $oggetto = "Cambio password da admin";
                $messaggio =
                    "<html>
                
                <body>
                <p>Ciao $nomeCompleto,</p><br/>
                <p>un admin ha modificato il tuo profilo.</p>
                <p>Usa queste nuove credenziali per accedere</p>
                <p>USERNAME: <b>$email</b> PASSWORD: <b>$password</b></p>
                </body>
                </html>";

                $mail->SendMail($mailTo, $oggetto, $messaggio);
            }


            //UPDATE USER
            $sql = "UPDATE `user` SET $email $emailAziendale
            $hashedPassword $idDepartment
            $idRole date_modified = CURRENT_TIMESTAMP WHERE id = $idDipendente";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            //UPDATE USER DATA
            $sql = "UPDATE `user_data` SET 
            $firstname $lastname $birthday $pic
            $address $phone $city $customfield
             $zip $country $vatNumber $region $province
            $iban `date_modified`=CURRENT_TIMESTAMP WHERE id_user = $idDipendente";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();


            array_push($result, ["message" => "Profilo modificato con successo"]);
            $result['result'] = false;
        } else if ($checkEmail > 0) {
            array_push($result, [
                "emailAziendaleMessage" => false,
                "phoneMessage" => false,
                "emailMessage" => true,
                "message" => "Email già esistente"
            ]);
        } else if ($checkEmailAziendale > 0) {
            array_push($result, [
                "emailAziendaleMessage" => true,
                "phoneMessage" => false,
                "emailMessage" => false,
                "message" => "Email aziendale già esistente"
            ]);
        } else if ($checkPhone > 0) {
            array_push($result, [
                "emailAziendaleMessage" => false,
                "emailMessage" => false,
                "phoneMessage" => true,
                "message" => "Numero telefono già esistente"
            ]);
        }
        return $result;
    }

    public function GetModules($input)
    {
        $sql = "SELECT * FROM modules";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($modules as $module) {
            $arr = [
                "id"   => $module['id'],
                "name" => ucwords($module['name']),
                "slug" => $module['slug']
            ];
            array_push($result, $arr);
        }
        return $result;
    }

    public function GetDepartment($input)
    {
        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUtente'];
        }

        $sql = "SELECT * FROM department WHERE idOrganization = $idOrganization";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($departments as $department) {

            $slug = str_replace(' ', '_', $department['name']);
            $slug = strtolower($slug);

            $arr = [
                "id" => $department['id'],
                "name" => ucwords($department['name']),
                "slug" => $slug,
            ];
            array_push($result, $arr);
        }
        return $result;
    }

    public function GetUsers($input)
    {
        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idDipendente'];
        }

        $authToken = $input['authToken'];
        $idRole = $input['idRole'];
        $idDepartment = $input['idDepartment'];

        $sql = "SELECT COUNT(*) AS auth FROM user WHERE authToken LIKE '$authToken'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $auth = $stmt->fetch(PDO::FETCH_ASSOC);
        $auth = $auth['auth'];

        if ($auth > 0) {
            if ((int)$idRole === _ADMIN_) {

                $sql = "SELECT user_data.firstname, user_data.lastname, user_data.id_user 
                FROM user_data LEFT JOIN user 
                ON user.id = user_data.id_user 
                WHERE user.id_organization = $idOrganization";
            } else if ((int)$idRole === _HR_) {

                $sql = "SELECT user_data.firstname, user_data.lastname, user_data.id_user
                FROM user_data LEFT JOIN user
                ON user.id = user_data.id_user
                LEFT JOIN department ON user.idDepartment = department.id
                WHERE (department.id = $idDepartment OR department.idPadre = $idDepartment) AND user.deleted = 0";
            }

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [];
            foreach ($users as $user) {
                $nomeCompleto = isset($user['firstname']) && isset($user['lastname']) ? ucwords($user['firstname']) . ' ' . ucwords($user['lastname']) : "";
                $arr = [
                    "nomeCompleto" => $nomeCompleto,
                    "id" => isset($user['id_user']) ? $user['id_user'] : ""
                ];
                array_push($result, $arr);
            }
            return $result;
        } else return "Non autorizzato";
    }
    public function UpdateUserInformation($input, $file)
    {

        $idUser = $input['idUser'];
        $firstname = isset($input['firstname']) ? "firstname = '" . addslashes($input['firstname']) . "'," : null;
        $lastname = isset($input['lastname']) ? "lastname = '" . addslashes($input['lastname']) . "'," : null;
        $birthday = isset($input['birthday']) ? "birthday = '" . $input['birthday'] . "'," : null;
        $ssn = isset($input['ssn']) ? "ssn = '" . $input['ssn'] . "'," : "";
        $companyName = isset($input['companyName']) ? "companyName = '" . $input['companyName'] . "'," : "";
        $phone = isset($input['phone']) ? "phone = '" . $input['phone'] . "'," : null;
        $email = isset($input['email']) ? "email = '" . $input['email'] . "'," : null;
        $emailAziendale = isset($input['emailAziendale']) && !empty($input['emailAziendale']) ? "emailAziendale = '" . $input['emailAziendale'] . "'," : null;
        $country = isset($input['country']) ? "country = '" . addslashes($input['country']) . "'," : null;
        $city = isset($input['city']) || $input['city'] != -1 ? "city = '" . addslashes($input['city']) . "'," : null;
        $zipCode = isset($input['zip_code']) ? "zip_code = '" . $input['zip_code'] . "'," : null;
        $address = isset($input['address']) ? "address = '" . addslashes($input['address']) . "'," : null;
        $vatNumber = isset($input['vatNumber']) ? "vat_number = '" . $input['vatNumber'] . "'," : null;
        $iban = isset($input['iban']) ? "iban = '" . $input['iban'] . "'," : null;
        $region = isset($input['region']) ? "region = '" . addslashes($input['region']) . "'," : null;
        $province = isset($input['province']) ? "province = '" . addslashes($input['province']) . "'," : null;


        if ($file == false) {
            $sql = "SELECT pic FROM user_data WHERE id_user = $idUser";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $pic = $stmt->fetch(PDO::FETCH_ASSOC);
            $pic = "pic = '" . $pic['pic'] . "',";
        } else {
            $pic = $file;
            move_uploaded_file($file['tmp_name'], "../../../media/users/" . $idUser . $pic['name']);
            $pic = "pic = '" . '/media/users/' . $idUser . $pic['name'] . "',";
        }

        $mail = isset($input['email']) ? $input['email'] : "";
        //CHECK EMAIL-EMAIL AZIENDALE-NUMERO TELEFONO
        $sql = "SELECT COUNT(*) AS checkemail FROM user 
        WHERE email LIKE '$mail' AND id != $idUser";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $checkEmail = $stmt->fetch(PDO::FETCH_ASSOC);
        $checkEmail = $checkEmail['checkemail'];

        $checkEmailAziendale = 0;
        if ($input['emailAziendale'] != "") {
            $mailAziendale = isset($input['emailAziendale']) || $input['emailAziendale'] != "undefined" ? $input['emailAziendale'] : "";
            $sql = "SELECT COUNT(*) AS checkemail FROM user 
            WHERE (email LIKE '$mail' OR email LIKE '$mailAziendale') AND id != $idUser";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $checkEmail = $stmt->fetch(PDO::FETCH_ASSOC);
            $checkEmail = $checkEmail['checkemail'];

            $sql = "SELECT COUNT(*) AS checkemailaziendale FROM user 
            WHERE (emailAziendale LIKE '$mail' OR emailAziendale LIKE '$mailAziendale' OR emailAziendale) AND id != $idUser";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $checkEmailAziendale = $stmt->fetch(PDO::FETCH_ASSOC);
            $checkEmailAziendale = $checkEmailAziendale['checkemailaziendale'];

            $sql = "SELECT COUNT(*) AS undefinedMails FROM user WHERE emailAziendale LIKE 'undefined' AND id != $idUser";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $undefinedEmailAziendale = $stmt->fetch(PDO::FETCH_ASSOC);
            $undefinedEmailAziendale = $undefinedEmailAziendale['undefinedMails'];

            $checkEmailAziendale = $checkEmailAziendale - $undefinedEmailAziendale;
        }



        $phoneCheck = isset($input['phone']) ? $input['phone'] : "";
        $sql = "SELECT COUNT(*) AS checkphone FROM user_data
        WHERE phone LIKE '$phoneCheck' AND id_user != $idUser";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $checkPhone = $stmt->fetch(PDO::FETCH_ASSOC);
        $checkPhone = $checkPhone['checkphone'];

        $result = [
            "result" => true,
            "emailAziendale" => [
                "result" => false,
                "message" => "Email aziendale già esistente"
            ],
            "email" => [
                "result" => false,
                "message" => "Email già esistente"
            ],
            "phone" => [
                "result" => false,
                "message" => "Numero telefono già esistente"
            ]
        ];
        if ($checkEmail == 0 && $checkEmailAziendale == 0 && $checkPhone == 0) {

            //UPDATE USER
            $sql = "UPDATE `user` SET $email $emailAziendale
            date_modified = CURRENT_TIMESTAMP WHERE id = $idUser";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            //UPDATE USER DATA
            $sql = "UPDATE `user_data` SET 
            $firstname $lastname $ssn $companyName $birthday $pic
            $address $phone $city $province $region 
            $zipCode $country $vatNumber 
            $iban `date_modified`=CURRENT_TIMESTAMP WHERE id_user = $idUser";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        }
        if ($checkEmail > 0) {
            $result['result'] = false;
            $result['email']['result'] = true;
        }
        if ($checkEmailAziendale > 0) {
            $result['result'] = false;
            $result['emailAziendale']['result'] = true;
        }
        if ($checkPhone > 0) {
            $result['result'] = false;
            $result['phone']['result'] = true;
        }
        return $result;
    }

    public function UpdatePassword($input)
    {
        $oldPassword = $input['values']['currentPassword'];
        $newPassword = $input['values']['password'];
        $cPassword = $input['values']['cPassword'];
        $idUser = $input['idUser'];
        $nomeCompleto = $input['fullname'];
        $email = $input['email'];

        $hashedOldPassword = hash("sha256", $oldPassword);
        $sql = "SELECT COUNT(*) AS checkOldPassword FROM user WHERE password LIKE '$hashedOldPassword' AND id = $idUser";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $checkOldPassword = $stmt->fetch(PDO::FETCH_ASSOC);
        $checkOldPassword = $checkOldPassword['checkOldPassword'];

        if ($checkOldPassword > 0 && $newPassword == $cPassword) {
            $hashedPassword = hash("sha256", $newPassword);
            $sql = "UPDATE user SET password = '$hashedPassword' WHERE id = $idUser";
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();

                $mail = new EmailSistem();
                $oggetto = "Modifica della password di DokyHR";
                $messaggio =
                    "<html>
                
                <body>
                <p>Ciao $nomeCompleto,</p><br/>
                <p>La tua password di DokyHR è stata modificata alle " . date("H:i") . " del" . date("d-m-Y") . "</p>
                <p><strong>Se sei stato tu</strong>, puoi tranquillamente ignorare questa e-mail</p><br/>
                <p>Grazie, il team di DokyHR.</p>
                </body>
                </html>";

                $mail->SendMail($email, $oggetto, $messaggio);
            } catch (PDOException $e) {
            }
            return [
                "message" => "Password cambiata correttamente",
                "color" => "green"
            ];
        } else return [
            "message" => "Password errata",
            "color" => "red"
        ];
    }

    public function ForgottenPassword($input)
    {
        $email = $input['email'];

        $sql = "SELECT emailAziendale FROM user WHERE email LIKE '$email' AND emailAziendale NOT LIKE 'undefined'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $newPassword = $this->generateRandomString(16);
        $hashedPassword = hash("sha256", $newPassword);
        if ($stmt->rowCount() > 0) {
            $email = $stmt->fetch(PDO::FETCH_ASSOC);
            $email = $email['emailAziendale'];
        } else {
            $email = $input['email'];
        }


        try {

            $mail = $input['email'];
            $sql = "SELECT user.email, user_data.firstname, user_data.lastname 
                    FROM user JOIN user_data ON user.id = user_data.id_user 
                    WHERE user.email LIKE '$mail'";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($stmt->rowCount() > 0) {



                $sql = "UPDATE `user` SET `password` = '$hashedPassword' WHERE email LIKE '$mail'";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();

                $nomeCompleto = $user['firstname'] . " " . $user['lastname'];

                $messaggio =
                    "<html>
                
                <body>
                <p>Ciao $nomeCompleto,</p><br/>
                <p>Hai richiesto di recuperare la tua password di DokyHR alle " . date("H:i") . " del" . date("d-m-Y") . "</p>
                <p>Utilizza questa password per effettuare l'accesso:</p><br/>
                <p>$newPassword</p>
                <p>Ti consigliamo di cambiarla al più presto nell'apposita sezione.</p>
                <p>Grazie, il team di DokyHR.</p>
                </body>
                </html>";

                $mail = new EmailSistem();
                $oggetto = "DOKYHR - Recupero password";

                $mail->SendMail($email, $oggetto, $messaggio);

                return [
                    "message" => "Email di recupero inviata con successo!",
                    "color" => "green"
                ];
            } else {
                return [
                    "message" => "Email non trovata!",
                    "color" => "red"
                ];
            }
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    public function GetVenditoriUsers($input)
    {
        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUtente'];
        }
        $idRole = $input['idRole'];
        $idDepartment = $input['idDepartment'];

        if ($idRole == _ADMIN_) {
            $sql = "SELECT user.id, user_data.firstname, user_data.lastname FROM 
            user LEFT JOIN user_data ON user.id = user_data.id_user
            LEFT JOIN department ON user.idDepartment = department.id 
            WHERE  user.id_organization = $idOrganization AND department.idModule = " . _VENDITORI_ . " AND user.deleted != 1";
        } else if ($idRole == _HR_) {
            $sql = "SELECT user.id, user_data.firstname, user_data.lastname FROM 
            user LEFT JOIN user_data ON user.id = user_data.id_user
            LEFT JOIN department ON user.idDepartment = department.id 
            WHERE department.id = $idDepartment OR department.idPadre = $idDepartment AND user.deleted != 1";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($users as $user) {
            $arr = [
                "idDipendente" => $user['id'],
                "fullname" => ucwords($user['firstname']) . " " . ucwords($user['lastname']),
            ];
            array_push($result, $arr);
        }
        return $result;
    }

    public function GetRiepilogoDashboard($input)
    {
        $idUser = $input['idUser'];

        $sql = "SELECT * FROM presenze WHERE idUser = $idUser AND confirmed = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $riepilogo = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [
            "permessi" => 0,
            "straordinari" => 0,
            "ferie" => 0
        ];
        foreach ($riepilogo as $confirmed) {
            if ($confirmed['title'] == "Ferie") {
                $result['ferie'] += 1;
            } else if ($confirmed['title'] == "Permesso") {
                $result['permessi'] += 1;
            } else if ($confirmed['title'] == "Straordinario") {
                $result['straordinari'] += 1;
            }
        }
        return $result;
    }

    public function GetDashboardValutation($input)
    {
        if ($input['idOrganization'] != 0) {
            $idOrganization = $input['idOrganization'];
        } else {
            $idOrganization = $input['idUser'];
        }
        $idUser = $input['idUser'];
        $idRole = $input['idRole'];
        $idDepartment = $input['idDepartment'];

        if ($idRole == _ADMIN_) {
            $sql = "SELECT valutazioni.*, user_data.firstname, user_data.lastname, user.emailAziendale, user.email, department.name AS nameDepartment FROM valutazioni 
            JOIN user_data ON valutazioni.idDipendente = user_data.id_user
            JOIN user ON user_data.id_user = user.id
            JOIN department ON user.idDepartment = department.id
            WHERE valutazioni.idOrganization = $idOrganization AND valutazioni.type LIKE 'valutazione' ORDER BY dataValutazione asc LIMIT 5";
        } else if ($idRole == _HR_) {
            $sql = "SELECT valutazioni.*, user_data.firstname, user_data.lastname, user.emailAziendale, user.email, department.name AS nameDepartment FROM valutazioni 
            JOIN user_data ON valutazioni.idDipendente = user_data.id_user
            JOIN user ON user_data.id_user = user.id
            JOIN department ON user.idDepartment = department.id
            WHERE user.id = $idDepartment AND valutazioni.type LIKE 'valutazione' ORDER BY dataValutazione asc LIMIT 5";
        } else {
            $sql = "SELECT valutazioni.*, user_data.firstname, user_data.lastname, user.emailAziendale, user.email, department.name AS nameDepartment FROM valutazioni 
            JOIN user_data ON valutazioni.idDipendente = user_data.id_user
            JOIN user ON user_data.id_user = user.id
            JOIN department ON user.idDepartment = department.id
            WHERE user_data.id_user = $idUser AND valutazioni.type LIKE 'valutazione' ORDER BY dataValutazione asc LIMIT 5";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $valutations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($valutations as $valutation) {
            $valutazione = json_decode($valutation['valutazione'], true);
            $valComplessiva = 0;
            foreach ($valutazione as $n) {
                $valComplessiva += $n;
            }
            $valComplessiva = round($valComplessiva / count($valutazione) * 2) / 2;
            $arr = [
                "nomeDipendente" => ucwords($valutation['firstname']) . " " . ucwords($valutation['lastname']),
                "email" => $valutation['email'],
                "dipartimento" => ucwords($valutation['nameDepartment']),
                "valutatione" => $valComplessiva,
            ];
            array_push($result, $arr);
        }
        return $result;
    }

    public function GetAllRolesSettings($input)
    {
        $idOrganization = $input['idOrganization'];

        $sql = "SELECT role, id FROM roles WHERE id_organization = 0 OR id_organization = $idOrganization";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [];
            foreach ($roles as $role) {
                if ($role['role'] !== "admin") {

                    $arr = [
                        "id" => $role['id'],
                        "role" => strtoupper($role['role'])
                    ];
                    array_push($result, $arr);
                }
            }

            return $result;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    public function GetOptionsRole($input)
    {
        $idOrganization = $input['idOrganization'];
        $idRole = (int)$input['idRole'];

        $sql = "SELECT features FROM features_by_roles WHERE id_organization = $idOrganization AND id_roles = $idRole";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            $features = $stmt->fetch(PDO::FETCH_ASSOC);
            $features = json_decode($features['features'], true);

            return $features;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    public function SaveSettings($input)
    {
        $idOrganization = $input['idOrganization'];
        $idRole = $input['idRole'];

        $settings = json_encode($input['settings']);

        $sql = "UPDATE `features_by_roles` SET `features`='$settings' 
            WHERE id_roles = $idRole AND id_organization = $idOrganization";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            return [];
        } catch (PDOException $e) {
            $e->getMessage();
        }
    }

    public function UpdateUserProfile($input)
    {
        $idUser = $input['idUser'];
        $firstname = addslashes($input['firstname']);
        $lastname = addslashes($input['lastname']);
        $email = $input['email'];
        $emailAziendale = $input['emailAziendale'];
        $ssn = $input['ssn'];
        $birthday = $input['birthday'];
        $phone = $input['phone'];
        $iban = $input['iban'];
        $country = $input['countryUser'];
        $region = addslashes($input['regionUser']);
        $province = addslashes($input['provinceUser']);
        $city = addslashes($input['cityUser']);
        $address = addslashes($input['address']);
        $zipCode = addslashes($input['zipCode']);
        $vatNumber = $input['vatNumber'];
        $companyName = addslashes($input['companyName']);

        $sqlEmail = "SELECT email FROM user WHERE email LIKE '$email' AND id != $idUser";
        $sqlEmailAziendale = "SELECT emailAziendale FROM user WHERE emailAziendale LIKE '$emailAziendale' AND id != $idUser";
        $sqlPhone = "SELECT phone FROM user_data WHERE phone LIKE '$phone' AND id_user != $idUser";

        $check = [
            "email" => false,
            "emailAziendale" => false,
            "phone" => false,
        ];

        try {
            $stmt = $this->conn->prepare($sqlEmail);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $check['email'] = true;
            }

            $stmt = $this->conn->prepare($sqlEmailAziendale);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $check['emailAziendale'] = true;
            }

            $stmt = $this->conn->prepare($sqlPhone);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $check['phone'] = true;
            }

            $sql = "UPDATE `user_data` SET 
                    `firstname`='$firstname',`lastname`='$lastname',`ssn`='$ssn',`birthday`='$birthday',
                    `companyName`='$companyName',`address`='$address',`phone`='$phone',`city`='$city',
                    `province`='$province',`zip_code`='$zipCode',`region`='$region',`country`='$country',
                    `vat_number`='$vatNumber',`iban`='$iban',`date_modified`=CURRENT_TIMESTAMP 
                    WHERE id_user = $idUser;
                    UPDATE `user` SET `email`='$email',`emailAziendale`='$emailAziendale', `date_modified`=CURRENT_TIMESTAMP 
                    WHERE id = $idUser";
            if (!$check['email'] && !$check['emailAziendale'] && !$check['phone']) {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();

                return [
                    "result" => true,
                    $check
                ];
            } else {
                return [
                    "result" => false,
                    $check
                ];
            }
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }
}
