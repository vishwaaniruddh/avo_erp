<?php
session_start();
require 'vendor/autoload.php'; // Include PHPMailer autoload file (via Composer)

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//    $mailid = $_POST['mailid'];
  $mailid= 'rajeshrungta719@gmail.com';
//    $sub = $_POST['subject'];
  $sub = "Testing";

    // Function to extract and validate email addresses
    function extract_email_address($string) {
        $emails = [];
        foreach (preg_split('/\s/', $string) as $token) {
            $email = filter_var(filter_var($token, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
            if ($email !== false) {
                $emails[] = $email;
            }
        }
        return $emails;
    }

    // Email body content
    $tbl = "
    <html>
    <head>
        <title>Switching AVO Electro Power Limited</title>
    </head>
    <body>
        <table border='1' width='700px'>
            <tr>
                <td>
                    Hello AVO Helpdesk!!<br>
                    Sorry! I Can't Generate the call. Speak to the Account Manager First.<br><br>
                    Dear Customer!!<br>
                    We regret that calls cannot be generated as the site was deactivated automatically. Please check whether the site is under AMC or Warranty, or whether a valid PO is provided.<br><br>
                    Contact the concerned account manager for further assistance or support.<br><br>
                    <strong>This is a system-generated email. Please do not respond to this email.</strong>
                </td>
            </tr>
        </table>
        <br><br>
        <font color='blue'>Switching <font color='red'>AVO</font> Electro Power Limited</font><br><br>
        <font color='red'>{$_SESSION['user']}</font>
    </body>
    </html>";

    // PHPMailer setup
    $mail = new PHPMailer(true);
    try {
        // Server settings
      	$mail->SMTPDebug = 1; 
        $mail->isSMTP();                                      // Use SMTP
        $mail->Host = 'smtp.gmail.com';                     // SMTP server (replace with your SMTP host)
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'hellbinderkumar@gmail.com';           // SMTP username
        $mail->Password = 'hell@5672';              // SMTP password
        $mail->SMTPSecure = 'tls';   // Enable encryption
        $mail->Port = 587;                                    // TCP port (587 is common for TLS)

        // Recipients
        $mail->setFrom('hellbinderkumar@gmail.com', 'System Admin');
        $mail->addAddress('work.rajeshb@gmail.com');             // Hardcoded recipient

        // Add CC recipients from the input
        $ccEmails = extract_email_address($mailid);
        foreach ($ccEmails as $ccEmail) {
            $mail->addCC($ccEmail);
        }

        // Content
        $mail->isHTML(true);                                  // Email format: HTML
        $mail->Subject = "Oops !! Something Went Wrong !! $sub";
        $mail->Body = $tbl;

        // Send email
        $mail->send();
        echo "Mail Sent Successfully";
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
