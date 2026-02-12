<?php
// Include Libraries
require('libs/fpdf/fpdf.php');
require('libs/PHPMailer/src/PHPMailer.php');
require('libs/PHPMailer/src/SMTP.php');
require('libs/PHPMailer/src/Exception.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- 1. CAPTURE FORM DATA ---
    $name = $_POST['customer_name'];
    $email = $_POST['customer_email'];
    $vehicle = $_POST['vehicle_make'];
    $reg = $_POST['reg_number'];
    $job = $_POST['job_card'];
    $date = date("Y-m-d H:i:s");
    $sigData = $_POST['customer_sig_data'];

    // --- 2. PROCESS SIGNATURE IMAGE ---
    if (!empty($sigData)) {
        $sigData = str_replace('data:image/png;base64,', '', $sigData);
        $sigData = str_replace(' ', '+', $sigData);
        $sigImage = base64_decode($sigData);
        $sigFilePath = 'temp_sig_' . time() . '.png';
        file_put_contents($sigFilePath, $sigImage);
    } else {
        die("Error: Signature missing.");
    }

    // --- 3. GENERATE PDF JOB CARD ---
    $pdf = new FPDF();
    $pdf->AddPage();
    
    // Logo & Header
    // Note: Ensure logo.png is in assets/ folder. If missing, this line might error, so we check.
    if(file_exists('assets/logo.png')){
        $pdf->Image('assets/logo.png', 10, 10, 30); // X, Y, Width
        $pdf->Ln(5);
    }
    
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'INTERNATIONAL PANEL SHOP', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Vehicle Check-In & Repair Consent', 0, 1, 'C');
    $pdf->Ln(15);

    // Customer Details Section
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(50, 10, 'Customer Name:', 0, 0);
    $pdf->Cell(0, 10, $name, 0, 1);
    
    $pdf->Cell(50, 10, 'Vehicle:', 0, 0);
    $pdf->Cell(0, 10, $vehicle, 0, 1);
    
    $pdf->Cell(50, 10, 'Registration:', 0, 0);
    $pdf->Cell(0, 10, $reg, 0, 1);
    
    $pdf->Cell(50, 10, 'Job Card / Ref:', 0, 0);
    $pdf->Cell(0, 10, $job, 0, 1);
    
    $pdf->Cell(50, 10, 'Date In:', 0, 0);
    $pdf->Cell(0, 10, $date, 0, 1);
    $pdf->Ln(10);

    // Legal Text
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 10, 'Terms & Conditions:', 0, 1);
    $pdf->SetFont('Arial', '', 9);
    $text_terms = "1. The vehicle has been brought to International Panel Shop for repairs.\n" .
                  "2. We are not liable for loss of personal belongings left in the vehicle.\n" .
                  "3. Customer authorizes inspection, repairs, and necessary testing.\n" .
                  "4. Additional work will be quoted first and executed upon approval.\n" .
                  "5. Replaced parts may be disposed of unless requested otherwise.\n" .
                  "6. Repair timelines may vary due to parts availability or paint curing.";
    $pdf->MultiCell(0, 6, $text_terms);
    $pdf->Ln(5);
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 10, 'Guarantee Policy:', 0, 1);
    $pdf->SetFont('Arial', '', 9);
    $text_guarantee = "- 12 months guarantee applies to all paint work.\n" .
                      "- No guarantee is provided for rust repairs, glass, and electronics.\n" .
                      "- Diagnostics (if applicable) to be sorted by customer at their own cost.";
    $pdf->MultiCell(0, 6, $text_guarantee);
    $pdf->Ln(15);

    // Signatures
    $pdf->Cell(0, 10, 'Customer Signature:', 0, 1);
    $pdf->Image($sigFilePath, $pdf->GetX(), $pdf->GetY(), 50); 
    $pdf->Ln(25);
    
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell(0, 10, 'Digitally signed via IPS Check-In System on ' . $date, 0, 1);

    // Save PDF to memory string
    $pdfOutput = $pdf->Output('S');
    
    // Clean up temp image
    if(file_exists($sigFilePath)) unlink($sigFilePath);

    // --- 4. SEND EMAIL VIA SMTP ---
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'mail.intpanelshop.co.za'; 
        $mail->SMTPAuth   = true;
        
        // LOGIN DETAILS (UPDATED)
        $mail->Username   = 'checkin@intpanelshop.co.za'; 
        $mail->Password   = 's.oBAn!,aDPmbZ6X'; 
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL/TLS
        $mail->Port       = 465;

        // Sender Info
        $mail->setFrom('checkin@intpanelshop.co.za', 'IPS Check-In System');
        $mail->addReplyTo('admin@intpanelshop.co.za', 'International Panel Shop');

        // Recipients
        $mail->addAddress('intpanelshop@gmail.com');     // Main Business Email
        $mail->addCC('admin@intpanelshop.co.za');        // Backup
        
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mail->addAddress($email);                   // The Customer
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Vehicle Check-In: $vehicle ($reg)";
        
        $emailBody = "<h3>Vehicle Check-In Confirmation</h3>" .
                     "<p><strong>Customer:</strong> $name<br>" .
                     "<strong>Vehicle:</strong> $vehicle ($reg)<br>" .
                     "<strong>Date:</strong> $date</p>" .
                     "<p>Thank you for choosing International Panel Shop. Please find your signed Job Card attached.</p>" .
                     "<hr><p><small>International Panel Shop | 021 801 8007</small></p>";
        
        $mail->Body    = $emailBody;
        $mail->AltBody = "Vehicle Check-In Confirmation for $vehicle ($reg). Please see attached PDF.";

        // Attach the PDF from memory
        $mail->addStringAttachment($pdfOutput, "CheckIn_$reg.pdf");

        $mail->send();
        
        // Success Page
        echo '<div style="font-family: Arial, sans-serif; text-align: center; padding: 50px;">';
        echo '<div style="color: green; font-size: 60px;">&#10003;</div>';
        echo '<h2>Check-In Successful!</h2>';
        echo '<p>The job card has been emailed to the customer and the office.</p>';
        echo '<br><a href="index.php" style="background: #333; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Start New Check-In</a>';
        echo '</div>';

    } catch (Exception $e) {
        echo '<div style="color: red; padding: 20px;">Message could not be sent. Mailer Error: ' . $mail->ErrorInfo . '</div>';
    }
} else {
    // If someone tries to open process.php directly without submitting the form
    header("Location: index.php");
    exit();
}
?>