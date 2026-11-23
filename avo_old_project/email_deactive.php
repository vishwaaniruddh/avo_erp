<?php
session_start();
include("access.php");
include('config.php');

include("menubar.php");  
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>New Sales Order</title>
    <link href="style.css" rel="stylesheet" type="text/css" />
    <link href="menu.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.4.1.js" integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU=" crossorigin="anonymous"></script>
    <style>
        input:focus { outline: none; }
        .custom_radio { width: 5%; height: 20px; }
        .cust_column { display: flex; }
        .submit_btn { display: flex; justify-content: center; }
        .submit_btn input { width: 15%; margin: 2%; }
        input[type="text"] { width: 100%; }
        .optional_input, .hide { display: none; }
        .show { display: block !important; }
        body { background-color: #4D9494; margin-top: 20px; }
        #menu-bar { width: 100%; }
        label { color: white; }
        table { width: 50% !important; margin: auto; }
        input[type=checkbox] { height: 20px; width: 20px; margin: 0; }
        #po_qty { background-color: transparent; border: none; text-align: center; width: 100%; }
    </style>
</head>
<body>
    <div class="search_fields" style="margin: 5%;">
        <form class="form-inline" method="POST">    
            <div class="form-group col-md-4">
                <label for="mailid" class="sr-only">Emails for auto message</label>
                <input type="text" class="form-control" id="mailid" name="mailid" placeholder="emails">
            </div>
            <div class="form-group col-md-4">
                <label for="subject" class="sr-only">Copy Subject Line</label>
                <input type="text" class="form-control" id="subject" name="subject" placeholder="subject">
            </div>
            <input class="btn btn-success" type="submit" name="search" value="Send">
        </form>
    </div>
    <?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require_once 'phpmail/src/PHPMailer.php';
    require_once 'phpmail/src/SMTP.php';
    require_once 'phpmail/src/Exception.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $mailid = $_POST['mailid'];
        $sub = $_POST['subject'];

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
        
        if ($mailid) {
            $emailBody = "<html><head><title>Switching AVO Electro Power Limited</title></head><body>";
            $emailBody .= "<table border='1' width='700px'> <tr> <td>Hello AVO Helpdesk !!<br>
            Sorry! I Can't Generate the call. Speak to the Account Manager First.<br><br>
            
            This is a system-generated mail. No response is required.<br></td></tr> </table><br><br><font color='blue'>Switching <font color='red'>AVO</font> Electro Power Limited</font> <br><br><font color='red'>".$_SESSION['user']."</font></body></html>";
            
            $recipientEmails = extract_email_address($mailid);
            
            $newmail = new PHPMailer(true);
            try {
                $newmail->isSMTP();
                $newmail->Host = 'mail.avoservice.in';
                $newmail->SMTPAuth = true;
                $newmail->Username = 'avoups@avoservice.in';
                $newmail->Password = 'DeeBee@12345'; // Secure this
                $newmail->SMTPSecure = 'tls';
                $newmail->Port = 587;
                $newmail->setFrom('avoups@avoservice.in', 'Helpdesk');
                $newmail->addAddress('boopathy@avoups.com');
              //  $newmail->addAddress('prabir.d06@gmail.com');
                foreach ($recipientEmails as $email) {
                    $newmail->addCC($email);
                }
                $newmail->isHTML(true);
                $newmail->Subject = "Oops! Something Went Wrong! " . $sub;
                $newmail->Body = $emailBody;
                
                $newmail->SMTPOptions = [
                            'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                            ]
                            ];
                
                if ($newmail->send()) {
                    echo "Mail Sent Successfully";
                } else {
                    echo "Sorry, some issue occurred";
                }
            } catch (Exception $e) {
                echo "Mailer Error: " . $newmail->ErrorInfo;
            }
        }
    }
    ?>
</body>
</html>
