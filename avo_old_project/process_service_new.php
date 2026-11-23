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


//########################

$delegate_flag=0;

$atm_id=$_POST['ref'];
 $cutoff_date=date('Y-m-d 00:00:00', strtotime('-30 days'));

$last="select alert_id, entry_date from alert where atm_id='$atm_id' and entry_date >'$cutoff_date' and entry_date < NOW() and call_status !='Rejected' order by alert_id DESC limit 2";

$sql2=mysqli_query($con1,$last);


if(mysqli_num_rows($sql2) > 0) {
 $rowre=mysqli_fetch_row($sql2);
 $repet=mysqli_query($con1,"update alert set repeat_callid='".$rowre[0]."' where alert_id='".$alert_id."'");
} 

     // ================GPS delegation==========
if($_POST['type']=='site') {
	$at=mysqli_query($con1,"select atm_id,latitude1,longitude1,address,city,state1 from atm where track_id='".$_POST['ref']."'");
}	elseif($_POST['type']=='amc') {
	$at=mysqli_query($con1,"select atmid,latitude1,longitude1,address,city,state from Amc where amcid='".$_POST['ref']."'");
}	
	$atro=mysqli_fetch_row($at);
	$atm_id=$atro[0];

	
	if($atro[1]==0.0)
	{
        $address=trim($atro[3].','.$atro[4].','.$atro[5]);
      
      // Google Geocoding API URL
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" .
            urlencode($address) .
            "&key=AIzaSyCBE1Xgn2mQmGOtUevIuFYw6443BkKCjbI";

    // Fetch API response using cURL
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    curl_close($ch);

    $output = json_decode($response, true);

    if ($output['status'] == "OK") {

        $latitude  = $output['results'][0]['geometry']['location']['lat'];
        $longitude = $output['results'][0]['geometry']['location']['lng'];

        if($_POST['type']=='site')
	           mysqli_query($con1,"update atm set latitude1='".$latitude."',longitude1='".$longitude."' where track_id='".$_POST['ref']."'");
	    elseif($_POST['type']=='amc')
	           mysqli_query($con1,"update Amc set latitude1='".$latitude."', longitude1='".$longitude."' where amcid='".$_POST['ref']."'");
        } 
	}
    else
    {
        $latitude=$atro[1]; 
        $longitude=$atro[2];
    }  
    

if($latitude !=0) {
   //$radius = 20; // in miles
      $radius = 100*0.621371192; // in km

    $lng_min = $longitude - $radius / abs(cos(deg2rad($latitude)) * 69);
    $lng_max = $longitude + $radius / abs(cos(deg2rad($latitude)) * 69);
    $lat_min = $latitude - ($radius / 69);
    $lat_max = $latitude + ($radius / 69);
    
    $qry="SELECT *,(6371 * acos( cos( radians($latitude) ) 
              * cos( radians( latitude ) ) 
              * cos( radians( longitude ) - radians($longitude) ) 
              + sin( radians($latitude) ) 
              * sin( radians( latitude ) ) ) ) AS distance FROM area_engg WHERE (longitude BETWEEN $lng_min AND $lng_max) AND (latitude BETWEEN $lat_min and $lat_max) and status=1 ORDER BY distance";
 
    $res=mysqli_query($con1,$qry);
    $num=mysqli_num_rows($res);
    if($num>0){
        $row=mysqli_fetch_row($res);
 
         $ctime=date("Y-m-d H:i:s");
         //$etdt=date("Y-m-d H:i:s", strtotime($ctime." + 4 hours"));
         $delegate_flag=1;
         $tab=mysqli_query($con1,"update alert set status='Delegated',call_status='1' where alert_id='".$req."'");

        if($tab){
	
		$tab2=mysqli_query($con1,"Insert into alert_delegation(engineer,atm,alert_id,date,delby) values('".$row[0]."','".$_POST['ref']."','".$req."','".$ctime."','".$_SESSION['user']."')");
                }
                
                
        if($tab2){
              echo "Successfully Delegated"; 
     mysqli_query($con1,"Insert into Delegation_tracking(alertid,del_type,del_date) values('".$req."',1,'".$ctime."')");

} 
}

}

if($delegate_flag==0)
 {
   // ###############Delegation History

       $delqry=mysqli_query($con1,"SELECT engineer,count(*) as cnt  FROM `alert_delegation` WHERE `alert_id` in(select `alert_id` from alert where atm_id='".$_POST['ref']."' and assetstatus='".$_POST['type']."') group by engineer order by cnt desc");
      
      $bidqry=mysqli_query($con1,"select branch_id from alert where alert_id='".$req."'");
      $bidrow=mysqli_fetch_row($bidqry);
      $branch_id=$bidrow[0];
     
       while($delrow=mysqli_fetch_row($delqry))
       {
        $enqry=mysqli_query($con1,"select * from area_engg where engg_id='".$delrow[0]."' and area='".$branch_id."' and status=1"); 
        if(mysqli_num_rows($enqry)>0)
        {  // delegate
         $ctime=date("Y-m-d H:i:s");
         $etdt=date("Y-m-d H:i:s", strtotime($ctime." + 4 hours"));
         $delegate_flag=1;
         $tab=mysqli_query($con1,"update alert set status='Delegated',call_status='1',eta='".$etdt."' where alert_id='".$req."'");
       
		$tab2=mysqli_query($con1,"Insert into alert_delegation(engineer,atm,alert_id,date,delby) values('".$delrow[0]."','".$_POST['ref']."','".$req."','".$ctime."','".$_SESSION['user']."')");
           
              echo "Successfully Delegated"; 
               
                
                mysqli_query($con1,"Insert into Delegation_tracking(alertid,del_type,del_date) values('".$req."',2,'".$ctime."')");
        }         

    }
    
    
}




//####################### Delegation end####

	if($sql){

	echo "<center><br><br><br>Call logged successfully, Docket no. is ".$createdby."<br><br> <a href=service1.php >click here</a> to log another call</center>";
	}
	else
	{
	echo "error , Please try again."."<a href=service1.php >click here</a>";
	}
	
	
    }// else close
	?>