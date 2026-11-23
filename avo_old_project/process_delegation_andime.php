<?php
session_start();
include("access.php");
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once 'phpmail/src/PHPMailer.php';
require_once 'phpmail/src/SMTP.php';
require_once 'phpmail/src/Exception.php';

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
  

$created=$_SESSION['logid'];

$user=$_SESSION['user'];

if(isset($_POST['delegate']))
{
	$req=$_POST['req'];
 //	$engnew=$_POST['engnew'];
 	$eng1=$_POST['engnew'];
 
 $engdet=explode(',',$eng1);
 $engnew=$engdet[0];
 $dis=$engdet[1];
 	
 	$engold=$_POST['engold'];
	$atm='';

  	$reason=$_POST['resonrel'];
	$message2="";
	


include('config.php');
$etdt="0000-00-00 00:00:00";

$pendate = date('Y-m-d H:i:s');

//echo strtotime(str_replace("/","-",$_POST['est']));
 //$_POST['time'] $_POST['meri']
 if(isset($_POST['est']) && $_POST['est']!='' && isset($_POST['time']) && $_POST['time']!=''){
 $tm=$_POST['time'].":00:00";
 if($_POST['meri']=='pm')
 $tm=(12+$_POST['time']).":00:00";
 //echo $tm;
 $etdt=date("Y-m-d $tm",strtotime(str_replace("/","-",$_POST['est'])));
 }
$seldel= mysqli_query($con1,"Select * from alert_delegation where alert_id='".$req."'");
$olddel=mysqli_fetch_row($seldel);

$tab2=mysqli_query($con1,"Insert into alert_redelegation(eng_old,eng_new,reason,atm,alert_id,createdby,created_at)Values('".$engold."','".$engnew."','".$reason."','".$atm."','".$req."','".$created."','".$pendate."')");


if($tab2){
$tab=mysqli_query($con1,"update alert_delegation set engineer='".$engnew."',atm='".$atm."' where alert_id='".$req."'");

$alrt=mysqli_query($con1,"update alert set convert_into='".$dis."' where alert_id='".$req."'");
$upt=mysqli_query($con1,"insert into eng_feedback set alert_id='$req',feedback='redelegate', engineer ='".$created."', feed_date='".$pendate."'" );

}

if($tab){

//################### Mail####################
$sql_qry=mysqli_query($con1,"select * from alert where alert_id =".$req."");
$alertrow=mysqli_fetch_row($sql_qry);
$createdby = $alertrow[25];

$atm_qry=mysqli_query($con1,"select atm_id from atm where track_id= '".$alertrow[2]."'");
if(mysqli_num_rows($atm_qry)>0) {
 $row2=mysqli_fetch_row($atm_qry);
 $site_id = $row2[0];
}
else {
    $atm_qry=mysqli_query($con1,"select atmid from Amc where amcid= '".$alertrow[2]."'");
if(mysqli_num_rows($atm_qry)>0) {
 $row2=mysqli_fetch_row($atm_qry);
 $site_id = $row2[0];
} else {  $site_id = $alertrow[2] ;}
}
$eng_qry=mysqli_query($con1,"select engg_name from area_engg where engg_id =".$engnew."");
$engrow=mysqli_fetch_row($eng_qry);
$engname=$engrow[0];


$mailid = $alertrow[14];
$sub = "Call Re-Delegated for Ticket No:".$createdby." ";

if(!empty($mailid)){
$emailBody="<html>
<head>
<title>Switching AVO Electro Power Limited</title>
</head>
<body>
The call is assigned to the Engineer  <font color='blue'>".$engname."</font>
<br><br>
<table border='1' width='700px'>
<tr>
	
	<th>Site/Sol ID</th>
	<th>End User</th>
	<th>City</th>
	<th>Address</th>
	<th>State</th>
</tr>";
$emailBody.="<tr>
		<td>".$site_id."</td>
		<td>".$alertrow[3]."</td>
		<td>".$alertrow[6]."</td>
		<td>".$alertrow[5]."</td>
		<td>".$alertrow[27]."</td>
	</tr>";	
	
$emailBody.="</table><br><br><font color='blue'>Switching <font color='red'>AVO</font> Electro Power Limited</font> 
			<br><br><font color='blue'> Raised By:</font> <font color='red'>".$_SESSION['user']."</font> </body></html>";


$recipientEmails = extract_email_address($alertrow[32]);

$newmail = new PHPMailer(true);
try{

$newmail->isSMTP();
$newmail->Host = 'mail.avoservice.in';
$newmail->SMTPAuth = true;
$newmail->Username = 'avoups@avoservice.in';
$newmail->Password = 'DeeBee@12345';
$newmail->SMTPSecure = 'tls';
$newmail->Port = 587;

$newmail->setFrom('avoups@avoservice.in','Help Desk');

$newmail->addAddress($mailid);
//$newmail->addAddress('boopathy@avoups.com');

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
//echo "Mail Sent";

}catch(Exception $e){
echo "Mailer Error: ".$newmail->ErrorInfo;
}
}


   
 
?>
<script type="text/javascript">
alert("Redelegated successfully");
window.location='view_alert.php';
</script>
<?php

	//header('Location:view_alert.php');
}
else
echo "Error Creating Delegation";
}

?>