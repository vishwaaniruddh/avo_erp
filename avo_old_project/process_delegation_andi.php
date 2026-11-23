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
  

if(isset($_POST['delegate']))
{
 $req=$_POST['req']; //-alert_id of alert table
 $eng1=$_POST['eng'];
 
 $engdet=explode(',',$eng1);
 $eng=$engdet[0];
 $dis=$engdet[1];
 
 $atm=$_POST['atm'];
 $br=$_POST['br'];
 $message2="";


include('config.php');
//======================For categery query update in atm table ===============================

$etdt="0000-00-00 00:00:00";
//echo strtotime(str_replace("/","-",$_POST['est']));
 //$_POST['time'] $_POST['meri']
 if(isset($_POST['est']) && $_POST['est']!='' && isset($_POST['time']) && $_POST['time']!=''){
 $tm=$_POST['time'].":".$_POST['min'].":00";
 if($_POST['meri']=='pm')
 $tm=(12+$_POST['time']).":".$_POST['min'].":00";
 //echo $tm;
 $etdt=date("Y-m-d $tm",strtotime(str_replace("/","-",$_POST['est'])));
 }




$cdate = date('Y-m-d H:i:s');

$checkqry=mysqli_query($con1,"Select * from alert_delegation where alert_id='$req'");
if(mysqli_num_rows($checkqry)>0){
 $olddel=mysqli_fetch_row($checkqry);
    $engold=$olddel[1];
   $created=$_SESSION['user'];
   
$tab2=mysqli_query($con1,"update alert_delegation set engineer='".$eng."', status=0, call_close_status=0, delby='".$_SESSION['user']."' where alert_id='$req' "); 

$redtab=mysqli_query($con1,"Insert into alert_redelegation(eng_old,eng_new,reason,atm,alert_id,createdby,created_at)Values('".$engold."','".$eng."','".$reason."','".$atm."','".$req."','".$created."','".$cdate."')");
} else {
  
  
$tab2=mysqli_query($con1,"Insert into alert_delegation(engineer,atm,alert_id,date,delby) Values('".$eng."','".$atm."','".$req."','".$cdate."','".$_SESSION['user']."')");
}

if($tab2){
 
   
$tab=mysqli_query($con1,"update alert set status='Delegated',call_status='1', convert_into='".$dis."' where alert_id='$req'");

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
$eng_qry=mysqli_query($con1,"select engg_name from area_engg where engg_id =".$eng."");
$engrow=mysqli_fetch_row($eng_qry);
$engname=$engrow[0];


$mailid = $alertrow[14];
$sub = "Call Delegated for Ticket No:".$createdby." ";

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
alert("Delegated Successfully");
window.location='view_alert.php';


</script>
<?
}
else
echo "Error Creating Delegation";
}
?>