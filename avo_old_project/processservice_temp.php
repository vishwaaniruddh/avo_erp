<?php session_start();
include('config.php');

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
  



$datetime = date('Y-m-d h:i:s');

//=============Function for distnace ====================================

function distance($lat1, $lon1, $lat2, $lon2, $unit) {
  if (($lat1 == $lat2) && ($lon1 == $lon2)) {
    return 0;
  }
  else {
    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($unit);

    if ($unit == "K") {
      return ($miles * 1.609344);
    } else if ($unit == "N") {
      return ($miles * 0.8684);
    } else {
      return $miles;
    }
  }
}


    $dt=date("Y-m-d H:i:s");
	$qrr=mysqli_query($con1,"select * from alert where entry_date LIKE ('".date('Y-m-d')."%')");
	$num=mysqli_num_rows($qrr);
	$num2=$num+1;
	if($num2>0 && $num2<=9)
	$num3="0".$num2;
	else
	$num3=$num2;
	

	
    $wnatsno=$_POST['whatsno'];
    
$createdby=$_SESSION['logid'];
	$createdby=$createdby."_".date("ymd").$num3;

	$chksql=mysqli_query($con1,"select alert_id from alert where createdby='".$createdby."'");
	if(mysqli_num_rows($chksql)>0){
	    echo "This docket no is already given";
	} else 
	{
      

$adate=date('Y-m-d');

$add=mysqli_real_escape_string($con1,$_POST['add']);
$prob= mysqli_real_escape_string($con1,$_POST['prob']);
$alert_type =$_POST['alert_type'];
$asset_type = $_POST['type'];


  $alertsql =  "INSERT INTO `alert` (`alert_id`, `cust_id`,`atm_id`, `bank_name`, `area`, `address`, `city`, `branch_id`, `pincode`, `problem`, `entry_date`, `alert_date`, `caller_name`, `caller_phone`, `caller_email`, `status`, `call_status`, `alert_type`, `standby`, `po`, `assetstatus`, `appby`, `appref`,`state1`,`createdby`,`subject`,`custdoctno`,`ccmail`,`whatsapp`) 
    VALUES (NULL, '".$_POST['cust']."','".$_POST['ref']."' , '".$_POST['bank']."', '".$_POST['area']."', '".$add."', '".$_POST['city']."', '".$_POST['branch']."', '".$_POST['pin']."', '".$prob."', '".$dt."', '".$adate."', '".$_POST['cname']."', '".$_POST['cphone']."', '".$_POST['cemail']."', 'Pending', 'Pending', '$alert_type', '', '".$_POST['po']."','".$asset_type."', '".$_POST['appby']."', '".$_POST['how']."','".$_POST['state']."','".$createdby."','".$_POST['sub']."','".$_POST['docket']."','".$ccm."' ,'".$wnatsno."')" ; 
	

	$sql = mysqli_query($con1,$alertsql);
	
	//$alert_id=$con1->insert_id;
     $alert_id=mysqli_insert_id($con1);


$atm_id=$_POST['ref'];
 $cutoff_date=date('Y-m-d 00:00:00', strtotime('-120 days'));

$last="select alert_id, entry_date from alert where atm_id='$atm_id' and entry_date >'$cutoff_date' and entry_date < NOW() and call_status !='Rejected' order by alert_id DESC limit 2";

$sql2=mysqli_query($con1,$last);


if(mysqli_num_rows($sql2) > 0) {
 $rowre=mysqli_fetch_row($sql2);
 $repet=mysqli_query($con1,"update alert set repeat_callid='".$rowre[0]."' where alert_id='".$alert_id."'");
}  
//########################

	if($sql){
$sql_qry=mysqli_query($con1,"select * from alert where alert_id = '".$alert_id."'");
$row1=mysqli_fetch_row($sql_qry);

$atm_qry=mysqli_query($con1,"select atm_id from atm where track_id= '".$row1[2]."'");
if(mysqli_num_rows($atm_qry)>0) {
 $row2=mysqli_fetch_row($atm_qry);
 $site_id = $row2[0];
}
else {
    $atm_qry=mysqli_query($con1,"select atmid from Amc where amcid= '".$row1[2]."'");
if(mysqli_num_rows($atm_qry)>0) {
 $row2=mysqli_fetch_row($atm_qry);
 $site_id = $row2[0];
} else {  $site_id = $row1[2] ;}
}


$mailid = $row1[14];
$sub = "Call Logged Successfully and Ticket No:".$createdby." has been generated";

if(!empty($mailid)){

$emailBody="<html>
<head>
<title>Switching AVO Electro Power Limited</title>
</head>
<body>
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
		<td>".$row1[3]."</td>
		<td>".$row1[6]."</td>
		<td>".$row1[5]."</td>
		<td>".$row1[27]."</td>
	</tr>";	
	
$emailBody.="</table><br><br><font color='blue'>Switching <font color='red'>AVO</font> Electro Power Limited</font> 
			<br><br><font color='blue'> Raised By:</font> <font color='red'>".$_SESSION['user']."</font> </body></html>";

//$recipientEmails = extract_email_address($row1[32]);
$newmail = new PHPMailer(true);
try{

$newmail->isSMTP();
$newmail->Host = 'mail.avoservice.in';
$newmail->SMTPAuth = true;
$newmail->Username = 'avoups@avoservice.in';
$newmail->Password = 'DeeBee@12345';
$newmail->SMTPSecure = 'tls';
$newmail->Port = 587;

$newmail->setFrom('avoups@avoservice.in','Helpdesk');

$newmail->addAddress($mailid);
//$newmail->addAddress('boopathy@avoups.com');

if($row1[32] !=''){
$recipientEmails = extract_email_address($row1[32]);
foreach ($recipientEmails as $email) {
$newmail->addCC($email);
}
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

}catch(Exception $e){
echo "Mailer Error: ".$newmail->ErrorInfo;
}

}


	echo "<center><br><br><br>Call logged successfully, Docket no. is ".$createdby."<br><br> <a href=service1.php >click here</a> to log another call</center>";
	}
	else
	{
	echo "error , Please try again."."<a href=service1.php >click here</a>";
	}
	
	
    }// else close
	?>