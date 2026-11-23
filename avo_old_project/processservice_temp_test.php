<?php 

// var_dump($_POST); 
// die;

session_start();
include('config.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// PHPMailer के लिए फाइलें इंपोर्ट करें
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$datetime = date('Y-m-d h:i:s');

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

$dt = date("Y-m-d H:i:s");
$qrr = mysqli_query($con1, "SELECT * FROM alert WHERE entry_date LIKE ('".date('Y-m-d')."%')");
$num = mysqli_num_rows($qrr);
$num2 = $num + 1;
$num3 = ($num2 > 0 && $num2 <= 9) ? "0".$num2 : $num2;

$wnatsno = $_POST['whatsno'];
$createdby = $_SESSION['logid']."_".date("ymd").$num3;

// $chksql = mysqli_query($con1, "SELECT alert_id FROM alert WHERE createdby='".$createdby."'");
// if(mysqli_num_rows($chksql) > 0){
//     echo "This docket no is already given";
// } else {

//     $adate = date('Y-m-d');
//     $add = mysqli_real_escape_string($con1, $_POST['add']);
//     $prob = mysqli_real_escape_string($con1, $_POST['prob']);
//     $alert_type = $_POST['alert_type'];
//     $asset_type = $_POST['type'];
$mailid = $_POST['ccemail'];

//     $alertsql = "INSERT INTO `alert` (`alert_id`, `cust_id`, `atm_id`, `bank_name`, `area`, `address`, `city`, `branch_id`, `pincode`, `problem`, `entry_date`, `alert_date`, `caller_name`, `caller_phone`, `caller_email`, `status`, `call_status`, `alert_type`, `standby`, `po`, `assetstatus`, `appby`, `appref`, `state1`, `createdby`, `subject`, `custdoctno`, `ccmail`, `whatsapp`) 
//     VALUES (NULL, '".$_POST['cust']."','".$_POST['ref']."' , '".$_POST['bank']."', '".$_POST['area']."', '".$add."', '".$_POST['city']."', '".$_POST['branch']."', '".$_POST['pin']."', '".$prob."', '".$dt."', '".$adate."', '".$_POST['cname']."', '".$_POST['cphone']."', '".$_POST['cemail']."', 'Pending', 'Pending', '$alert_type', '', '".$_POST['po']."','".$asset_type."', '".$_POST['appby']."', '".$_POST['how']."','".$_POST['state']."','".$createdby."','".$_POST['sub']."','".$_POST['docket']."','".$ccm."' ,'".$wnatsno."')"; 

//     $sql = mysqli_query($con1, $alertsql);
//     $alert_id = mysqli_insert_id($con1);
    
    if($sql == 1) {
        echo "<center><br><br><br>Call logged successfully, Docket no. is ".$createdby."<br><br> <a href=service1.php >click here</a> to log another call</center>";
        
        $recipientEmails = extract_email_address($mailid);
        
        // **ईमेल भेजने के लिए PHPMailer उपयोग करें**
        $mail = new PHPMailer(true);
        try {
            // **SMTP सेटअप**
            $mail->SMTPDebug = 2;  
            $mail->isSMTP();
            $mail->Host       = 'mail.avoservice.in'; 
            $mail->SMTPAuth   = true;
            $mail->Username = 'avoups@avoservice.in';
            $mail->Password = 'DeeBee@12345'; // Secure this
            $mail->SMTPSecure = "tls"; 
            $mail->Port       = 587;
            // **ईमेल डिटेल्स**
            $mail->setFrom('avoups@avoservice.in', 'Support');
            $mail->addAddress($_POST['cemail']);
            $mail->addReplyTo('avoups@avoservice.in', 'Support');
            foreach ($recipientEmails as $email) {
                    $mail->addCC($email);
                }

            // **ईमेल कंटेंट**
            $mail->isHTML(true);
            $mail->Subject = 'New Alert Created - Docket No: '.$createdby;
            $mail->Body    = "<p>Dear ".$_POST['cname'].",</p>
                              <p>Your alert has been created successfully.</p>
                              <p><strong>Docket No:</strong> ".$createdby."</p>
                              <p>Thank you.</p>";

            // **ईमेल भेजें**
            $mail->send();
            echo "<br>Email has been sent successfully!";
        } catch (Exception $e) {
            echo "<br>Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "error, Please try again.<a href=service1_test.php >click here</a>";
    }
// }
?>
