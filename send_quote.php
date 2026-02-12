<?php
// ENABLE DEBUGGING
ini_set('display_errors', 1);
error_reporting(E_ALL);

// LOAD LIBRARIES
require('checkin/libs/PHPMailer/src/PHPMailer.php');
require('checkin/libs/PHPMailer/src/SMTP.php');
require('checkin/libs/PHPMailer/src/Exception.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Honeypot
    if (!empty($_POST['website_url'])) { exit(); }

    $mail = new PHPMailer(true);

    try {
        // --- SERVER SETTINGS ---
        $mail->SMTPDebug = 2; // ENABLE VERBOSE DEBUG OUTPUT
        $mail->Debugoutput = function($str, $level) {
            // Keep the log clean for the alert box
            if(strpos($str, 'CLIENT -> SERVER') === false) {
               echo "$str\n";
            }
        };

        $mail->isSMTP();
        $mail->Host       = 'mail.intpanelshop.co.za'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = 'checkin@intpanelshop.co.za'; 
        $mail->Password   = 's.oBAn!,aDPmbZ6X'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // --- RECIPIENTS ---
        $mail->setFrom('checkin@intpanelshop.co.za', 'IPS Website Quote');
        $mail->addAddress('intpanelshop@gmail.com');
        $mail->addCC('admin@intpanelshop.co.za');
        
        // Reply-To Customer
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $name = strip_tags($_POST['name']);
        if($email) $mail->addReplyTo($email, $name);

        // --- CONTENT ---
        $mail->isHTML(false);
        $mail->Subject = "New Quote: $name";
        $mail->Body    = "Name: $name\nPhone: $_POST[phone]\nEmail: $email\nVehicle: $_POST[Vehicle_Make] $_POST[Vehicle_Model]\nDesc: $_POST[Damage_Description]";

        // Attachments
        if (!empty($_FILES['attachment']['name'][0])) {
            foreach ($_FILES['attachment']['name'] as $i => $fname) {
                if ($_FILES['attachment']['error'][$i] === 0) {
                    $mail->addAttachment($_FILES['attachment']['tmp_name'][$i], $fname);
                }
            }
        }

        $mail->send();
        echo "SUCCESS"; // Tells Javascript it worked

    } catch (Exception $e) {
        // RETURN THE ERROR TO THE BROWSER
        http_response_code(500);
        echo "MAILER ERROR: " . $mail->ErrorInfo;
    }
}
?>