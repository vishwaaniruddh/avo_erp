<?php

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require_once 'phpmail/src/PHPMailer.php';
    require_once 'phpmail/src/SMTP.php';
    require_once 'phpmail/src/Exception.php';
    
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
        

$mailid = 'boopathy@avoups.com';
$sub = 'Testing';

if(!empty($mailid)){
    
     $emailBody = "<html><head><title>Switching AVO Electro Power Limited</title></head><body><table border='1' width='700px'> <tr> <td>Hello AVO Helpdesk !!<br>
            Sorry! I Can't Generate the call. Speak to the Account Manager First.<br><br>
            
            This is a system-generated mail. No response is required.<br></td></tr> </table><br><br><font color='blue'>Switching <font color='red'>AVO</font> Electro Power Limited</font> <br><br><font color='red'>Welcome</font></body></html>";
           

$recipientEmails = extract_email_address($mailid);

$newmail = new PHPMailer(true);

try{

$newmail->isSMTP();
// $newmail->Host = 'mail.avoservice.in';
$newmail->Host = 'smtp.hostinger.com';
$newmail->SMTPAuth = true;
$newmail->Username = 'avoups@avoservice.in';
$newmail->Password = 'DeeBee@12345';
$newmail->SMTPSecure = 'tls';
$newmail->Port = 587;

$newmail->setFrom('avoups@avoservice.in','Helpdesk');

$newmail->addAddress('boopathy@avoups.com');
$newmail->addAddress('prabir.d06@gmail.com');

foreach ($recipientEmails as $email) {
$newmail->addCC($email);
}

$newmail->isHTML(true);
$newmail->Subject = "Testing Subject";

$newmail->Body = $emailBody;

$newmail->SMTPOptions = [
'ssl' => [
'verify_peer' => false,
'verify_peer_name' => false,
'allow_self_signed' => true
]
];

$newmail->send();
//die;
echo "Mail Sent Successfully";

}catch(Exception $e){

echo "Mailer Error: ".$newmail->ErrorInfo;

}

}
