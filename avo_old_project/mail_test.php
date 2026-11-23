<? session_start();
include('config.php');
include("access.php");


function extract_email_address($string) {
            $emails = [];
           //  foreach (preg_split('/\s/', $string) as $token)
          //  preg_split("/[\s,]+/"
            foreach (preg_split("/[\s,]+/", $string) as $token) {
                $email = filter_var(filter_var($token, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
                if ($email !== false) {
                    $emails[] = $email;
                }
            }
            return $emails;
        }


$tbl="<html>
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
	<th>AVO Branch</th>
	<th>DO No</th>
	<th>Asset Details</th>
	
</tr>";
$tbl.="<tr>
		<td>Site ID: DB</td>
		<td>Switching AVO</td>
		<td>Switching AVO</td>
		<td>Chennai</td>
		<td>Kovvur, Chennai</td>
		<td>Tamilnadu</td>
		<td>Nil</td>
	
		
		
	</tr>";	
	
	$to1 = 'boopathy@avoups.com,service3@avoups.com';

	$to1 =extract_email_address($to1);

//echo '<pre>'; print_r($to); echo '</pre>';
//die;

$to=implode(',',$to1);
//echo $to;

/*foreach ($to1 as $email) {
//$cc->addCC($email);
$cc=$email;
} */
//var_dump($cc);
//die;

	$cc='bhoopathy_vsp@yahoo.co.in';
	$subject = "Sales Order for site id:-ABCD has been generated";
	
	$tbl.="</table><br><br><font color='blue'>Switching <font color='red'>AVO</font> Electro Power Limited</font> 
			<br><br><font color='blue'>SO Raised By:</font> <font color='red'>".$_SESSION['user']."</font> </body></html>";
	$headers = "From:<e_testing@avoservice.in>\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	$headers .= "Cc: ".$cc. "\r\n";
	$message=$tbl;
	
	$mailqry=mail($to, $subject, $message, $headers);
	if($mailqry){
	    
	    echo "Mail Sent";
	} else {
	    echo "Error";
	} 

	?>