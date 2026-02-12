<?php
// --- CONFIGURATION ---
$admin_email      = "intpanelshop@gmail.com"; // Your email
$email_from       = "noreply@intpanelshop.co.za"; // Use a "no-reply" address from your domain
$company_name     = "International Panel Shop";
$thank_you_page   = "signup-thank-you.html"; // The page shown after successful sign-up
$error_page       = "error.html"; // The page shown if there's an error
// -------------------

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form data
    $first_name = htmlspecialchars($_POST['first_name']);
    $last_name  = htmlspecialchars($_POST['last_name']);
    $email      = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    // --- THIS IS THE RANDOM CODE GENERATOR ---
    // 1. Takes the first 4 letters of the last name (or fewer if shorter) and makes them uppercase.
    $name_part = strtoupper(substr($last_name, 0, 4)); 
    // 2. Adds 3 random digits (e.g., a number between 100 and 999).
    $number_part = rand(100, 999); 
    // 3. Combines them to create the final code (e.g., REIT987).
    $referral_code = $name_part . $number_part;

    // --- 1. Email to You (Admin Notification) ---
    $admin_subject = "New Referrer Sign-Up: " . $first_name . " " . $last_name;
    $admin_message = "A new person has signed up for the referral program.\n\n";
    $admin_message .= "Name: " . $first_name . " " . $last_name . "\n";
    $admin_message .= "Email: " . $email . "\n";
    $admin_message .= "Generated Referral Code: " . $referral_code . "\n\n";
    $admin_message .= "They have digitally agreed to the Terms & Conditions.";
    $admin_headers = "From: " . $email_from;
    
    mail($admin_email, $admin_subject, $admin_message, $admin_headers);

    // --- 2. Email to the New Referrer (Welcome Email) ---
    $referrer_subject = "Welcome to the " . $company_name . " Referral Program!";
    $referrer_message = "Hi " . $first_name . ",\n\n";
    $referrer_message .= "Thank you for joining our Referral Program! We're excited to have you on board.\n\n";
    $referrer_message .= "Your unique referral code is: " . $referral_code . "\n\n";
    $referrer_message .= "How to use it:\n";
    $referrer_message .= "When a customer you refer requests a quote from our website, they must enter this code. You can also get a unique QR code from us that automatically includes your code.\n\n";
    $referrer_message .= "You can review the program rules at any time on our Terms & Conditions page: https://www.intpanelshop.co.za/referral-terms.html\n\n";
    $referrer_message .= "We look forward to a successful partnership!\n\n";
    $referrer_message .= "Regards,\nThe Team at " . $company_name;
    $referrer_headers = "From: " . $email_from;

    $mail_sent = mail($email, $referrer_subject, $referrer_message, $referrer_headers);

    // --- Redirect User ---
    if ($mail_sent) {
        header("Location: " . $thank_you_page);
    } else {
        header("Location: " . $error_page);
    }
    exit();
}
?>