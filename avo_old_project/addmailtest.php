<?php
$to = "prabir.d06@gmail.com";
$subject = "Test Email from VPS";
$message = "This is a test email sent from the server.";
$headers = "From: officialbimmi@gmail.com";

if (mail($to, $subject, $message, $headers)) {
    echo "Email sent successfully!";
} else {
    echo "Email sending failed.";
}
?>