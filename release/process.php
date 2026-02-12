<?php
require('libs/fpdf/fpdf.php');
require('libs/PHPMailer/src/PHPMailer.php');
require('libs/PHPMailer/src/SMTP.php');
require('libs/PHPMailer/src/Exception.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Capture Data
    $name = $_POST['customer_name'];
    $email = $_POST['customer_email'];
    $vehicle = $_POST['vehicle_make'];
    $reg = $_POST['reg_number'];
    $job = $_POST['job_card'];
    $date = date("Y-m-d H:i:s");
    $sigData = $_POST['customer_sig_data'];
    
    // Checklist Data
    $items = $_POST['items'];
    $checked = isset($_POST['checked']) ? $_POST['checked'] : [];
    $notes = $_POST['notes'];

    // 2. Process Signature
    $sigData = str_replace('data:image/png;base64,', '', $sigData);
    $sigData = str_replace(' ', '+', $sigData);
    $sigImage = base64_decode($sigData);
    $sigFilePath = 'temp_sig_' . time() . '.png';
    file_put_contents($sigFilePath, $sigImage);

    // 3. Generate PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    
    if(file_exists('assets/logo.png')){
        $pdf->Image('assets/logo.png', 10, 10, 30);
        $pdf->Ln(5);
    }
    
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'INTERNATIONAL PANEL SHOP', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Vehicle Repair Release Checklist', 0, 1, 'C');
    $pdf->Ln(15);

    // Info Block
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(50, 10, 'Customer Name:', 0, 0); $pdf->Cell(0, 10, $name, 0, 1);
    $pdf->Cell(50, 10, 'Vehicle:', 0, 0); $pdf->Cell(0, 10, $vehicle, 0, 1);
    $pdf->Cell(50, 10, 'Registration:', 0, 0); $pdf->Cell(0, 10, $reg, 0, 1);
    $pdf->Cell(50, 10, 'Job Card:', 0, 0); $pdf->Cell(0, 10, $job, 0, 1);
    $pdf->Cell(50, 10, 'Date Completed:', 0, 0); $pdf->Cell(0, 10, $date, 0, 1);
    $pdf->Ln(10);

    // Checklist Table
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(90, 10, 'Item', 1, 0, 'L');
    $pdf->Cell(30, 10, 'Checked', 1, 0, 'C');
    $pdf->Cell(70, 10, 'Notes', 1, 1, 'L');
    
    $pdf->SetFont('Arial', '', 10);
    foreach($items as $i => $item_name) {
        $is_checked = isset($checked[$i]) ? 'YES' : '-';
        $note_text = isset($notes[$i]) ? $notes[$i] : '';
        
        $pdf->Cell(90, 8, $item_name, 1, 0, 'L');
        $pdf->Cell(30, 8, $is_checked, 1, 0, 'C');
        $pdf->Cell(70, 8, $note_text, 1, 1, 'L');
    }
    $pdf->Ln(10);

    // Legal Text
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 10, 'Customer Confirmation:', 0, 1);
    $pdf->SetFont('Arial', '', 9);
    $pdf->MultiCell(0, 6, "I hereby confirm that I have inspected my vehicle and I am satisfied with the panelbeating and spraypainting repairs completed by International Panel Shop.\n\nI understand that any defects or issues found after the release of the vehicle which are unrelated to the completed repairs will not be the responsibility of the workshop.");
    $pdf->Ln(5);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 10, 'Guarantee:', 0, 1);
    $pdf->SetFont('Arial', '', 9);
    $pdf->MultiCell(0, 6, "- 12 months guarantee applies to all paint work.\n- No guarantee is provided for rust repairs.");
    $pdf->Ln(10);

    // Signatures
    $pdf->Cell(0, 10, 'Customer Signature:', 0, 1);
    $pdf->Image($sigFilePath, $pdf->GetX(), $pdf->GetY(), 50); 
    $pdf->Ln(30);
    
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell(0, 10, 'Digitally signed via IPS Release System on ' . $date, 0, 1);

    $pdfOutput = $pdf->Output('S');
    if(file_exists($sigFilePath)) unlink($sigFilePath);

    // 4. Send Email
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'mail.intpanelshop.co.za'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = 'checkin@intpanelshop.co.za'; // We reuse the same account
        $mail->Password   = 's.oBAn!,aDPmbZ6X';           // Your generated password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('checkin@intpanelshop.co.za', 'IPS Release System');
        $mail->addReplyTo('admin@intpanelshop.co.za', 'International Panel Shop');
        $mail->addAddress('intpanelshop@gmail.com');
        $mail->addCC('admin@intpanelshop.co.za');
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = "Vehicle Release: $vehicle ($reg)";
        $mail->Body    = "<h3>Vehicle Release Confirmation</h3><p>The vehicle $vehicle ($reg) has been inspected and released to the customer.</p><p>Please find the signed Release Checklist attached.</p>";
        
        $mail->addStringAttachment($pdfOutput, "Release_$reg.pdf");

        $mail->send();
        echo '<div style="font-family: sans-serif; text-align: center; padding: 50px;"><h1>Success!</h1><p>Vehicle Released. Document sent.</p><a href="index.php">New Release</a></div>';

    } catch (Exception $e) {
        echo "Mailer Error: {$mail->ErrorInfo}";
    }
}
?>