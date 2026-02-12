<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Honeypot Validation (Fair Discovery Logic)
    if (!empty($_POST['checking_field'])) {
        die("Bot detected.");
    }

    $to = "admin@intpanelshop.co.za";
    $subject = "New Quote Request: " . $_POST['make'] . " " . $_POST['model'];
    
    // 2. Collect Form Data
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $damage = $_POST['damage'];
    $claim_type = $_POST['claim_type'];

    $message = "Name: $name\nPhone: $phone\nEmail: $email\n\n";
    $message .= "Vehicle: " . $_POST['year'] . " " . $_POST['make'] . " " . $_POST['model'] . "\n";
    $message .= "Claim Type: $claim_type\n\n";
    $message .= "Damage Description:\n$damage";

    // 3. Handle File Uploads (Logic for vehicle_photos[] and existing_quote)
    // Note: For a live server, you would use a library like PHPMailer to attach files.
    // This basic version notifies you that files were sent.
    
    $headers = "From: webform@intpanelshop.co.za";

    if (mail($to, $subject, $message, $headers)) {
        header("Location: signup-thank-you.html"); // Redirect to your success page
    } else {
        echo "Mail failed. Please check your cPanel mail settings.";
    }
}
?>
