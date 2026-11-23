<? session_start();
include("config.php");
include("access.php");

require_once 'phpmail/src/PHPMailer.php';
require_once 'phpmail/src/SMTP.php';
require_once 'phpmail/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


    function extract_email_address($string) {
            $emails = [];
           // foreach (preg_split('/\s/', $string) as $token) {
            foreach (preg_split("/[\s,]+/", $string) as $token) {
                $email = filter_var(filter_var($token, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
                if ($email !== false) {
                    $emails[] = $email;
                }
            }
            return $emails;
        }

$so_trackid='277494';

$qryal=mysqli_query($con1,"Select * from new_sales_order where so_trackid='".$so_trackid."'");
$sorow=mysqli_fetch_row($qryal);

$poqry=mysqli_query($con1,"Select po_no from purchase_order where id='".$sorow[1]."'");
$po=mysqli_fetch_row($poqry);

$cusal=mysqli_query($con1,"Select cust_name from customer where cust_id='".$sorow[3]."'");
$cust=mysqli_fetch_row($cusal);

$atmqry=mysqli_query($con1,"select * from demo_atm where so_id='".$so_trackid."'");
$atmrow=mysqli_fetch_row($atmqry);


//================mail==========================

$mailid = $sorow[11];
$sub = "Sales Order Generated Against PO: ".$po[0]." ";

if(!empty($mailid)){
$emailBody="<html>
<head>
<title>Switching AVO Electro Power Limited</title>
</head>
<body>
<table border='1' width='700px'>
<tr>
	
	<th>Site/Sol ID</th>
	<th>Customer</th>
	<th>End User</th>
	<th>City</th>
	<th>Address</th>
	<th>State</th>

	
</tr>";
$emailBody.="<tr>
		<td>".$sorow[7]."</td>
		<td>".$cust[0]."</td>
		<td>".$atmrow[6]."</td>
		<td>".$atmrow[9]."</td>
		<td>".$atmrow[11]."</td>
		<td>".$atmrow[13]."</td>
		       </tr>";
$emailBody.="</table><br><br><font color='blue'>Switching <font color='red'>AVO</font> Electro Power Limited</font> 
			<br><br><font color='blue'> SO Raised By:</font> <font color='red'>".$_SESSION['user']."</font> </body></html>";


$recipientEmails = extract_email_address($sorow[18]);


$newmail = new PHPMailer(true);
try{

$newmail->isSMTP();
$newmail->Host = 'mail.avoservice.in';
$newmail->SMTPAuth = true;
$newmail->Username = 'avoups@avoservice.in';
$newmail->Password = 'DeeBee@12345';
$newmail->SMTPSecure = 'tls';
$newmail->Port = 587;

$newmail->setFrom('avoups@avoservice.in','e-Sales');

$newmail->addAddress($mailid);
$newmail->addAddress('boopathy@avoups.com');

foreach ($recipientEmails as $email) {
$newmail->addCC($email);
}

$newmail->isHTML(true);
$newmail->Subject = $sub;

$newmail->Body = $emailBody;

$newmail->SMTPOptions = [
'ssl' => [
'verify_peer' => false,
'verify_peer_name' => false,
'allow_self_signed' => true
]
];
$newmail->send();
echo "Mail Sent";

}catch(Exception $e){
echo "Mailer Error: ".$newmail->ErrorInfo;
}
}
