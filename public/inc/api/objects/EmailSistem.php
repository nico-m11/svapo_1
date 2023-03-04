<?php
require_once '../config/Config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../resources/PHPMailer-master/src/Exception.php';
require_once '../resources/PHPMailer-master/src/PHPMailer.php';
require_once '../resources/PHPMailer-master/src/SMTP.php';

class EmailSistem
{

    // var connessione al db e tabella



    public function __construct()
    {
    }

    public function Content($content)
    {

        $result = "";
        foreach ($content as $item) {

            if ($item["format"] == "paragraph") {

                if ($item["type"] == "2Col") {

                    // "paragraph" => [
                    //     "textLeft" => "Paragrafo Test, col 2",
                    //     "textRight" => "Paragrafo Test, col 2",
                    //     "type" => "2Col",
                    // ],

                    $textLeft = $item["textLeft"];
                    $textRight = $item["textRight"];

                    $result .= '  
                        <div>
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td valign="middle"
                                        style="text-align: center; font-family: sans-serif; font-size: 20px; line-height: 20px; color: #292929; font-weight:200;">
                                        <h4>' . $textLeft . '</h4>
                                    </td>
                                    <td valign="middle"
                                        style="text-align: center; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #292929; font-weight:200;">
                                        <h4>' . $textRight . '</h4>
                                    </td>
                                </tr>
                            </table>  
                        </div>';
                } else if ($item["type"] == "1Col") {

                    // "paragraph" => [
                    //     "text" => "Paragrafo Test, col 1",
                    //     "type" => "1Col",
                    // ]

                    $text = $item["text"];

                    $result .= ' 
                    <div>                       
                        <h4 style="text-align: center; padding: 0px 40px 0px 40px; font-family: sans-serif; font-size: 20px; color: #292929; font-weight:200;">
                        ' . $text . '
                        </h4>
                    </div>';
                }
            }

            if ($item["format"] == "button") {
                if ($item["type"] == "2Col") {

                    // "button" => [
                    //     "buttonLeft" => "Button Left",
                    //     "buttonRight" => "Button Right",
                    //     "type" => "2Col",
                    // ]

                    $linkLeft = $item["linkLeft"];
                    $buttonLeft = $item["buttonLeft"];
                    $linkRight = $item["linkRight"];
                    $buttonRight = $item["buttonRight"];

                    $result .= '                   
                 <div>
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td style="padding: 0 20px 20px;" align="center">
                                <a href="' . $linkLeft . '">
                                    <button style="cursor:pointer; padding:20px; border:3px solid #D6A878; background:rgba(214, 168, 120,0.8); color:#fff; border-radius:10px; font-size:20px;">' . $buttonLeft . '</button>
                                </a>

                                <a href="' . $linkRight . '">
                                    <button style="cursor:pointer; padding:20px; border:3px solid #D6A878; background:rgba(214, 168, 120,0.8); color:#fff; border-radius:10px; font-size:20px;">' . $buttonRight . '</button>
                                </a>
                            </td>
                        </tr>
                    </table>
                </div>';
                } else if ($item["type"] == "1Col") {

                    // "button" => [
                    //     "button" => "Button Center",
                    //     "type" => "1Col",
                    // ]

                    $link = $item["link"];
                    $button = $item["button"];

                    $result .= '                   
                 <div>
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td style="padding: 0 20px 20px;" align="center">
                                <a href="' . $link . '">
                                    <button style="cursor:pointer; padding:20px; border:3px solid #D6A878; background:rgba(214, 168, 120,0.8); color:#fff; border-radius:10px; font-size:20px;">' . $button . '</button>
                                </a>
                            </td>
                        </tr>
                    </table>
                </div>';
                }
            }
        }

        return $result;
    }

    public function SendMail($email, $oggetto, $messaggio, $pathAllegato = "")
    {
        /* $headers = 'From: noreply-dokyhr@dokyhr.com' . "\r\n".
        "Content-type:text/html;charset=UTF-8" . "\r\n";

        mail($to, $subject, $body, $headers); */
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->isHTML(true);
        $mail->SMTPAuth = true;
        $mail->Host = "smtp.hostinger.com";
        $mail->Port = 465;
        $mail->SMTPAutoTLS = false;
        $mail->SMTPSecure = "ssl";
        $mail->Username = "no-reply@dokyhr.it";
        $mail->Password = "dge_4rTW,*.";
        $mail->Priority = 1; //(1 = High, 3 = Normal, 5 = Low)
        $mail->setFrom("no-reply@dokyhr.it", "Doky HR");
        $domain = $_SERVER['SERVER_NAME'];
        //SE SI LAVORA DA LOCALE NON INVIERA EMAIL AGLI UTENTI
        /* if (strpos($domain, "localhost") != true) { */

        $mail->addAddress($email);
        /* } */

        $mail->addAddress("luca.gentile@genesismobile.it");
        $mail->isHTML(true);
        $mail->Subject = $oggetto;
        $mail->Body = $messaggio;
        $mail->AltBody = "";
        $mail->addAttachment($pathAllegato);

        if ($mail->Send()) {
            return true;
        } else {
            echo "Errore invio email: " . $mail->ErrorInfo;
            return false;
        }
    }

    // template of email
    public function templateMail($output)
    {

        // DOC
        // the content 

        $title = isset($output["title"]) ? $output["title"] : "";
        $link_logo = "../../../public/media/logos/doky-bianco.png";

        $content = count($output["content"]) > 0 ? $this->Content($output["content"]) : "";

        if (count($output["content"]) > 0) {


            $meta = '
                <head>
                    <meta charset="utf-8">
                    <!-- utf-8 works for most cases -->
                    <meta name="viewport" content="width=device-width">
                    <!-- Forcing initial-scale shouldn t be necessary -->
                    <meta http-equiv="X-UA-Compatible" content="IE=edge">
                    <!-- Use the latest (edge) version of IE rendering engine -->
                    <meta name="x-apple-disable-message-reformatting">
                    <!-- Disable auto-scale in iOS 10 Mail entirely -->
                    <title></title>
                    <!-- The title tag shows in email notifications, like Android 4.4. -->
                    <!-- Web Font / @font-face : BEGIN -->
                    <!-- NOTE: If web fonts are not required, lines 10 - 27 can be safely removed. -->
                    <!-- Desktop Outlook chokes on web font references and defaults to Times New Roman, so we force a safe fallback font. -->
                    <!--[if mso]>
                                        <style>
                            * {
                            font-family: sans-serif !important;
                            }
                        </style>
                                        <![endif]-->
                    <!-- All other clients get the webfont reference; some will render the font and others will silently fail to the fallbacks. More on that here: http://stylecampaign.com/blog/2015/02/webfont-support-in-email/ -->
                    <!--[if !mso]>
                                        <!-->
                    <!--
                                        <![endif]-->
                    <!-- Web Font / @font-face : END -->
                    <!-- CSS Reset : BEGIN -->
                    <style>
                        /* What it does: Remove spaces around the email design added by some email clients. */
                        /* Beware: It can remove the padding / margin and add a background color to the compose a reply window. */
                        html,
                        body {
                            margin: 0 auto !important;
                            padding: 0 !important;
                            height: 100% !important;
                            width: 100% !important;
                        }
                
                        /* What it does: Stops email clients resizing small text. */
                        * {
                            -ms-text-size-adjust: 100%;
                            -webkit-text-size-adjust: 100%;
                        }
                
                        /* What it does: Centers email on Android 4.4 */
                        div[style*="margin: 16px 0"] {
                            margin: 0 !important;
                        }
                
                        /* What it does: Stops Outlook from adding extra spacing to tables. */
                        table,
                        td {
                            mso-table-lspace: 0pt !important;
                            mso-table-rspace: 0pt !important;
                        }
                
                        /* What it does: Fixes webkit padding issue. */
                        table {
                            border-spacing: 0 !important;
                            border-collapse: collapse !important;
                            table-layout: fixed !important;
                            margin: 0 auto !important;
                        }
                
                        /* What it does: Prevents Windows 10 Mail from underlining links despite inline CSS. Styles for underlined links should be inline. */
                        a {
                            text-decoration: none;
                        }
                
                        /* What it does: Uses a better rendering method when resizing images in IE. */
                        img {
                            -ms-interpolation-mode: bicubic;
                        }
                
                        /* What it does: A work-around for email clients meddling in triggered links. */
                        *[x-apple-data-detectors],
                        /* iOS */
                        .unstyle-auto-detected-links *,
                        .aBn {
                            border-bottom: 0 !important;
                            cursor: default !important;
                            color: inherit !important;
                            text-decoration: none !important;
                            font-size: inherit !important;
                            font-family: inherit !important;
                            font-weight: inherit !important;
                            line-height: inherit !important;
                        }
                
                        /* What it does: Prevents Gmail from changing the text color in conversation threads. */
                        .im {
                            color: inherit !important;
                        }
                
                        /* What it does: Prevents Gmail from displaying a download button on large, non-linked images. */
                        .a6S {
                            display: none !important;
                            opacity: 0.01 !important;
                        }
                
                        /* If the above doesn t work, add a .g-img class to any image in question. */
                        img.g-img+div {
                            display: none !important;
                        }
                
                        /* What it does: Removes right gutter in Gmail iOS app: https://github.com/TedGoas/Cerberus/issues/89  */
                        /* Create one of these media queries for each additional viewport size you d like to fix */
                
                        /* iPhone 4, 4S, 5, 5S, 5C, and 5SE */
                        @media only screen and (min-device-width: 320px) and (max-device-width: 374px) {
                            u~div .email-container {
                                min-width: 320px !important;
                            }
                        }
                
                        /* iPhone 6, 6S, 7, 8, and X */
                        @media only screen and (min-device-width: 375px) and (max-device-width: 413px) {
                            u~div .email-container {
                                min-width: 375px !important;
                            }
                        }
                
                        /* iPhone 6+, 7+, and 8+ */
                        @media only screen and (min-device-width: 414px) {
                            u~div .email-container {
                                min-width: 414px !important;
                            }
                        }
                    </style>
                    <!-- What it does: Makes background images in 72ppi Outlook render at correct size. -->
                    <!--[if gte mso 9]>
                                        <xml>
                                            <o:OfficeDocumentSettings>
                                                <o:AllowPNG/>
                                                <o:PixelsPerInch>96</o:PixelsPerInch>
                                            </o:OfficeDocumentSettings>
                                        </xml>
                                        <![endif]-->
                    <!-- CSS Reset : END -->
                    <!-- Progressive Enhancements : BEGIN -->
                    <style>
                        /* What it does: Hover styles for buttons */
                        .button-td,
                        .button-a {
                            transition: all 100ms ease-in;
                        }
                
                        .button-td-primary:hover,
                        .button-a-primary:hover {
                            background: #555555 !important;
                            border-color: #555555 !important;
                        }
                
                        /* Media Queries */
                        @media screen and (max-width: 600px) {
                
                            .email-container {
                                width: 100% !important;
                                margin: auto !important;
                            }
                
                            /* What it does: Forces table cells into full-width rows. */
                            .stack-column,
                            .stack-column-center {
                                display: block !important;
                                width: 100% !important;
                                max-width: 100% !important;
                                direction: ltr !important;
                            }
                
                            /* And center justify these ones. */
                            .stack-column-center {
                                text-align: center !important;
                            }
                
                            /* What it does: Generic utility class for centering. Useful for images, buttons, and nested tables. */
                            .center-on-narrow {
                                text-align: center !important;
                                display: block !important;
                                margin-left: auto !important;
                                margin-right: auto !important;
                                float: none !important;
                            }
                
                            table.center-on-narrow {
                                display: inline-block !important;
                            }
                
                            /* What it does: Adjust typography on small screens to improve readability */
                            .email-container p {
                                font-size: 17px !important;
                            }
                        }
                    </style>
                    <!-- Progressive Enhancements : END -->
                </head>';

            $header = '  
                <div style="display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;"> ' . $title . ' </div>
                <div style="display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;">&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;</div>
                ';

            $body = '
                <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="margin: auto;" class="email-container">
                    <!-- Email Header : BEGIN -->
                    <tr>
                        <td style="padding: 0; text-align: center" />
                    </tr>
                    <!-- Email Header : END -->
                    <!-- Hero Image, Flush : BEGIN -->
                    <tr>
                        <td style="background-color: #fff; padding: 50px;border-bottom:#ddd 1px solid ">
                            <img src="' . $link_logo . '" width="600"
                                height="" alt="alt_text" border="0"
                                style="width: 100%; max-width: 300px; height: auto; background: #fff; font-family: sans-serif; font-size: 15px; line-height: 15px; color: #555555; margin: auto; display: block;"
                                class="g-img">
                        </td>
                    </tr>

                    <tr>
                        <!-- Bulletproof Background Images c/o https://backgrounds.cm -->
                        <td valign="middle"
                            style="text-align: center; background-color: #F1F1F1; background-position: center center !important; background-size: cover !important; padding:3%;">

                            <div>                       
                                <h3 style="text-align: center; padding: 0px 40px 0px 40px; font-family: sans-serif; font-size: 25px; color: rgba(214, 168, 120,0.8); border-bottom:1px solid rgba(214, 168, 120,0.8)">
                                ' . $title . '
                                </h3>
                            </div>

                            ' . $content . '

                        </td>
                    </tr>
                    <!-- Background Image with Text : END -->
                    <!-- Clear Spacer : BEGIN -->
                    <tr>
                        <td aria-hidden="true" height="40" style="font-size: 0px; line-height: 0px;">
                            &nbsp;
                        </td>
                    </tr>
                    <!-- Clear Spacer : END -->
                </table>';

            $footer = '        
                <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="margin: auto;" class="email-container">
                    <tr>
                        <td
                            style="padding: 5px; font-family: sans-serif; font-size: 12px; line-height: 15px; text-align: center; color: #fff;">
                            <h3>CRURATED LIMITED</h3>
                            
                            <small>
                                <span class="unstyle-auto-detected-links">
                                    1st Floor 90 Chancery Lane,
                                    <br>
                                    London, United Kingdom, WC2A 1EU
                                    <br><br>
                                    hello@crurated.com
                                    <br>
                                </span>
                            </small>
                        </td>
                    </tr>
                </table>

                <tr>
                    <td aria-hidden="true" height="40" style="font-size: 0px; line-height: 0px;">
                        &nbsp;
                    </td>
                </tr>';

            return '
                <!DOCTYPE html>
                <html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
                <body width="100%" style="margin: 0; padding: 0 !important; mso-line-height-rule: exactly; background-color: #783233;">
                <center style="width: 100%; background-color: #783233;">
                ' . $meta . '
                ' . $header . '
                ' . $body . '
                ' . $footer . '
                </center>
                </body>
                </html>';
        } else {
            return "";
        }
    }

    // Sistem of email
    public function SendEmailSistem($output)
    {
        // EXAMPLE ARRAY
        // The content of email must consist of arrays. 
        //Arrays can be of "format" -> "paragraph" or "button", they can be of "type" -> "2Col" or "1Col". 
        //Example of Array:
        // $input = array(
        //     "from" => "noreply@crurated.com",
        //     "to" => "eliseo@genesismobile.it",
        //     "subject" => "Email Test",
        //     "email" => [
        //         "title" => "Titolo Test",
        //         "content" => [
        //             [
        //                 "format" => "paragraph",
        //                 "textLeft" => "Paragrafo Test, col 2",
        //                 "textRight" => "Paragrafo Test, col 2",
        //                 "type" => "2Col",
        //             ],
        //             [
        //                 "format" => "paragraph",
        //                 "text" => "Paragrafo Test, col 1",
        //                 "type" => "1Col",
        //             ],
        //             [
        //                 "format" => "button",
        //                 "linkLeft" => "www.goolge.com",
        //                 "buttonLeft" => "Button Le",
        //                 "linkRight" => "www.goolge.com",
        //                 "buttonRight" => "Button Ri",
        //                 "type" => "2Col",
        //             ],
        //             [
        //                 "format" => "button",
        //                 "link" => "www.goolge.com",
        //                 "button" => "Button Center",
        //                 "type" => "1Col",
        //             ],
        //         ]
        //     ]
        // );
        // END EXAMPLE ARRAY

        $only_dev = isset($output["onlyDev"]) ? $output["onlyDev"] : 0;
        $from = $output["from"];
        $to =  IS_LOCAL ? "lucagentile31@gmail.com" : $output["to"];
        $subject = $output["subject"];

        // Message
        $result_message = $this->templateMail($output["email"]);

        // simpiazzo i caratteri speciali
        $result_message = str_replace('à', '&agrave;', $result_message);
        $result_message = str_replace('è', '&egrave;', $result_message);
        $result_message = str_replace('é', '&eacute;', $result_message);
        $result_message = str_replace('ì', '&igrave;', $result_message);
        $result_message = str_replace('ò', '&ograve;', $result_message);
        $result_message = str_replace('ù', '&ugrave;', $result_message);

        // Mail it
        /* Send mail */
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = 0; // Enable verbose debug output
            $mail->isSMTP(); // Set mailer to use SMTP
            $mail->Host       = 'smtp.hostinger.com'; // Specify main and backup SMTP servers
            $mail->SMTPAuth   = true; // Enable SMTP authentication
            $mail->Username   = 'no-reply@dokyhr.it'; // SMTP username
            $mail->Password   = 'dge_4rTW,*.'; // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption, `ssl` also accepted
            $mail->Port       = 465; // TCP port to connect to

            //Recipients
            $mail->setfrom($from, 'DOKY HR');

            if ($only_dev) {
                $mail->addAddress($to); // Add a recipient
            } else {
                $mail->addAddress($to); // Add a recipient
                $mail->addBCC('lucagentile31@gmail.com');
            }


            // $mail->SMTPOptions = array(
            //     'ssl' => array(
            //         'verify_peer' => false,
            //         'verify_peer_name' => false,
            //         'allow_self_signed' => true
            //     )
            // );

            // Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $result_message;
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            $mail->send();
        } catch (Exception $e) {

            $event_log = new EventLog($this->conn);
            $input_logSimple = [
                "parameters" => $mail->ErrorInfo,
                "type" => "error",
                "user" => $to,
                "event" => "SendEmail"
            ];
            $event_log->logSimple($input_logSimple);
        }

        return 1;
    }

    // Send a email test
    public function SendEmailTest()
    {

        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = "smtp.hostinger.com";
        $mail->Port = 465;
        $mail->SMTPAutoTLS = false;
        $mail->SMTPSecure = "ssl";
        $mail->Username = "no-reply@dokyhr.it";
        $mail->Password = "dge_4rTW,*.";
        $mail->Priority = 1; //(1 = High, 3 = Normal, 5 = Low)
        $mail->setFrom("no-reply@dokyhr.it", "Doky HR");
        $mail->addAddress("lucagentile31@gmail.com");
        $mail->isHTML(true);
        $mail->Subject = "test";
        $mail->Body = "test";
        $mail->AltBody = "";
        $mail->addAttachment($pathAllegato = "");

        if ($mail->Send()) {
            return true;
        } else {
            echo "Errore invio email: " . $mail->ErrorInfo;
            return false;
        }
        /* 
        $to      = 'lucagentile31@gmail.com';
        $subject = 'HTML TEST';
        $message = '
        <html>
        <head>
        <title>BENVENUTO!</title>
        </head>
        <body>
        <p>Benvenuto su DOKY HR!</p><br/>
        <p>Usa queste credenziali per accedere al tuo profilo</p>
        <table>
        <tr>
        <th>Username</th>
        <th>Password</th>
        </tr>
        <tr>
        <td>test</td>
        <td>test12345</td>
        </tr>
        </table>
        </body>
        </html>
        ';
        $headers = 'From: noreply-dokyhr@dokyhr.com' . "\r\n".
        "Content-type:text/html;charset=UTF-8" . "\r\n";

        mail($to, $subject, $message, $headers); */
    }

    // GET the template of email
    public function GetEmailTemplate()
    {

        // $input = array(
        //     "title" => "Titolo Test",
        //     "content" => [
        //         [
        //             "format" => "paragraph",
        //             "textLeft" => "Paragrafo Test, col 2",
        //             "textRight" => "Paragrafo Test, col 2",
        //             "type" => "2Col",
        //         ],
        //         [
        //             "format" => "paragraph",
        //             "text" => "Paragrafo Test, col 1",
        //             "type" => "1Col",
        //         ],
        //         [
        //             "format" => "button",
        //             "linkLeft" => "www.goolge.com",
        //             "buttonLeft" => "Button Le",
        //             "linkRight" => "www.goolge.com",
        //             "buttonRight" => "Button Ri",
        //             "type" => "2Col",
        //         ],
        //         [
        //             "format" => "button",
        //             "link" => "www.goolge.com",
        //             "button" => "Button Center",
        //             "type" => "1Col",
        //         ],
        //     ]
        // );

        $input = array(

            "title" => "Dear Alessandro",
            "content" => [


                [
                    "format" => "paragraph",
                    "text" => "<p>Congratulations! Your offer of EUR <b>4000€</b> for lot <b>\"Fourrier | Burgundy (JMF - Mixed Case 7)\"</b> placed on 2021-06-01 has been accepted. <br><br> This lot has been allocated to your account. Your credit card has been charged <b>4200€</b>, which includes the 2.5% protection and processing fee.</p> 

                        <p>Please do not hesitate to contact us by emailing us at <a href='mailto:hello@crurated.com'>hello@crurated.com</a> if you have any questions or need any further assistance.</p>
                        <p>Kindly note this offer is now closed.</p>
                        <p>Sincerely,<br>
                        The Crurated Team</p>",
                    "type" => "1Col",
                ],
                [
                    "format" => "button",
                    "link" => DOMAIN,
                    "button" => "View my account",
                    "type" => "1Col",
                ],


            ]

        );


        return $this->templateMail($input);
    }
}
