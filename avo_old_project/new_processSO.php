<?php
session_start();
	include('config.php');

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


function get_data($parameter,$soid){
    global $con1;

    $sql = mysqli_query($con1,"select $parameter from new_sales_order where so_trackid='".$soid."'");

    $sql_result = mysqli_fetch_assoc($sql);
    
    return $sql_result[$parameter];
    
}



$error=0;

	$sid=$_POST['sid'];
$invno=$_POST['invno'];

$invlen = strlen($invno);	

if($invlen <15 || $invlen >16) {
 
 echo '<script>
	alert("Enter 15 or 16 Digit Invoice Number without Space.");
	window.location.href="sales_ordernew.php";
	</script>';   
    
}
	
	$customer_vertical = get_data('po_custid',$sid);
	$atm_id = get_data('atm_id',$sid);
	$avo_branch = get_data('branch_id',$sid);

 	//echo "cust=".$cust."<br>";
 	$invno=$_POST['invno'];
	$date1=$_POST['date1'];

	$invval=$_POST['invval'];
	$del_mode=$_POST['del_mode'];
	$cname=$_POST['cname'];
	$dno=$_POST['dno'];
	$crn=$_POST['crn'];
	


	$crnamt=$_POST['crnamt'];
	//	$crnfile=$_POST['crnfile'];
	

	if($cname==''){$crndate='NULL';}
	if($dno==''){$dno='NULL';}
	if($crn==''){$crn='NULL';}
	

	$target_dir = "invoices_25/";
	$target_dir1 = "creditnotes_25/";

if($_POST['date2']!="")
{
$datdist = str_replace('/', '-', $_POST['date2']);
$date2= date('Y-m-d', strtotime($datdist));
} else {
$date2="1990-01-01";    
}		

if($_POST['estdate']!="")
{
$datest = str_replace('/', '-', $_POST['estdate']);
$estdate= date('Y-m-d', strtotime($datest));
} else {
$estdate="1990-01-01";    
}

if($_POST['crndate']!="")
{
$datcrn = str_replace('/', '-', $_POST['deldt']);
$crndate= date('Y-m-d', strtotime($datcrn));
} else {
$crndate="1990-01-01";    
}

if($_POST['deldt']!="")
{
$daten = str_replace('/', '-', $_POST['deldt']);
$deldt= date('Y-m-d', strtotime($daten));
} else {  
$deldt="1990-01-01";    
}

        $target_file = $target_dir . $_FILES['invfile']['name']; 
        
        //echo "file:".$target_file ;

         $target_file1 = $target_dir1 . $_FILES['crnfile']['name'];
        //echo "file:".$target_file1 ; 
        
        $uploadOk = 1;


      $qry=mysqli_query($con1,"insert into so_order(po_id,inv_no,inv_date,inv_value,courier,docketno,est_date,dis_date,crn_no,crn_date,crn_amount,status,inv_img,customer_vertical,atm_id,avo_branch, del_mode) values('".$sid."','".$invno."',STR_TO_DATE('".$date1."','%d/%m/%Y'),'".$invval."','".$cname."','".$dno."','".$estdate."','".$date2."','".$crn."','".$crndate."','".$crnamt."','1','".$target_file."','".$customer_vertical."','".$atm_id."','".$avo_branch."', '".$del_mode."')");
 

        $check = getimagesize($_FILES['invfile']['tmp_name']); 
//echo "image:".$check;


     $lid=mysqli_insert_id($con1);  
      
         if(move_uploaded_file($_FILES["invfile"]["tmp_name"], $target_file)){   
          mysqli_query($con1,"UPDATE so_order set inv_img='".$target_file."',inv_img_time='".date("Y-m-d H:i:s")."' where id='".$lid."'");
          }
             //$check1 = getimagesize($_FILES['crnfile']['tmp_name']); //echo "image:".$check;
         if(move_uploaded_file($_FILES["crnfile"]["tmp_name"], $target_file1)){
    mysqli_query($con1,"UPDATE so_order set crn_img='".$target_file1."' where id='".$lid."'");
                              
                                }           
	if($qry)
{
  
      mysqli_query($con1,"UPDATE new_sales_order set status = '2' where so_trackid ='".$sid."'");
 
 //################### Mail####################
$sql_qry=mysqli_query($con1,"select * from new_sales_order where so_trackid =".$sid."");
$sales_row=mysqli_fetch_row($sql_qry);

$sql_demo = mysqli_query($con1,"select * from demo_atm where so_id =".$sid."");
$demo_atm=mysqli_fetch_row($sql_demo); 

$mailid = $sales_row[11];
$sub = "Invoice Generated! Against Site/Sol Id: ".$sales_row[7]." ";

if(!empty($mailid)){
$emailBody="<html>
<head>
<title>Switching AVO Electro Power Limited</title>
</head>
<body>
<table border='1' width='700px'>
<tr>
	
	<th>Site/Sol ID</th>
	<th>Invoice Number</th>
	<th>Invoice Date</th>
	<th>End User</th>
	<th>Address</th>
	<th>State</th>
</tr>";
$emailBody.="<tr>
		<td>".$sales_row[7]."</td>
		<td>".$invno."</td>
		<td>".$date1."</td>
		<td>".$demo_atm[6]."</td>
		<td>".$demo_atm[11]."</td>
		<td>".$demo_atm[13]."</td>
	</tr>";	
	
$emailBody.="</table><br><br><font color='blue'>Switching <font color='red'>AVO</font> Electro Power Limited</font> 
			<br><br><font color='blue'> Invoiced By:</font> <font color='red'>".$_SESSION['user']."</font> </body></html>";


//$recipientEmails = extract_email_address($alertrow[32]);

$newmail = new PHPMailer(true);
try{

$newmail->isSMTP();
$newmail->Host = 'mail.avoservice.in';
$newmail->SMTPAuth = true;
$newmail->Username = 'avoups@avoservice.in';
$newmail->Password = 'DeeBee@12345';
$newmail->SMTPSecure = 'tls';
$newmail->Port = 587;

$newmail->setFrom('avoups@avoservice.in','e-Accounts');

$newmail->addAddress($mailid);
//$newmail->addAddress('boopathy@avoups.com');

if($alertrow[32] !=''){
$recipientEmails = extract_email_address($alertrow[32]);
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
//echo "Mail Sent";

}catch(Exception $e){
echo "Mailer Error: ".$newmail->ErrorInfo;
}
}

 
 
 
	echo '<script>
	alert("Invoice generated successfully.");
	window.location.href="sales_ordernew.php";
	</script>';
	}
 	else{
        echo 'Error Occurred. Please <a href="sales_ordernew.php" >GO BACK</a> and try again.';
	
 	}
		
	?>