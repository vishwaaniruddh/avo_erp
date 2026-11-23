<?php
session_start();


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once 'andi/GCM.php';
include("config.php");

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
  

function clean($string) {
   $string = str_replace(' ', ' ', $string); 
   $string = preg_replace('/[^A-Za-z0-9ĞİŞığşçö\-]/', ' ', $string); 

   return preg_replace('/-+/', '-', $string); 
}


$so_id = $_POST['so_id'];

$cust=$_POST['cust'];
$bank=mysqli_real_escape_string($con1, $_POST['bank']);
$state=$_POST['state_st'];
$branch_id=$_POST['branch_avo'];

$city=mysqli_real_escape_string($con1,$_POST['city']);
$city = ucwords(strtolower($city));
$area=mysqli_real_escape_string($con1,$_POST['area']);
$area = ucwords(strtolower($area));
$add=mysqli_real_escape_string($con1,$_POST['address']);

$add= preg_replace('/[^a-zA-Z0-9_ -]/s',' ',$add);
$add=trim($add);
$add = ucwords(strtolower($add));

$pin=$_POST['pin'];
$adate=$_POST['adate'];

$cname=mysqli_real_escape_string($con1,$_POST['cname']);
$cphone=mysqli_real_escape_string($con1,$_POST['cphone']);
$cemail=mysqli_real_escape_string($con1,$_POST['cemail']);
$ccmail = mysqli_real_escape_string($con1,$_POST['ccemail']);
$cdate = date('Y-m-d H:i:s');
$start_date= date('Y-m-d');
$po=$_POST['po'];
$asset=$_POST['assetsme'];

$atmid = $_POST['ref_id'];
 
$po_qty = $_POST['po_qty'];
$so_order_id = $_POST['so_order_id'];
//==================Add Buyback in problem=================
$soqry = mysqli_query($con1,"select bb_available from new_sales_order where so_trackid ='".$so_id."'");
$bbrow = mysqli_fetch_assoc($soqry);
$bb_avl = $bbrow['bb_available'];

if($bb_avl==0) { $bb_stat = "BuyBack: NO"; }
if($bb_avl==1) { $bb_stat = "BuyBack: Available"; }
if($bb_avl==2) { $bb_stat = "BuyBack: Need to Check "; }

$problem=trim(clean($_POST['prob']));
//if (strlen($problem) > 150)
     //  $prob = substr($prob, 0, 150);
$prob =  mb_strimwidth($problem, 0, 150, "...");

$prob= $bb_stat."\n".$prob;

//echo $prob; die;



if($ccmail=='') {$ccmail='NULL';}


$poqry = mysqli_query($con1,"select * from purchase_order where po_no='".$po."'");

$porow = mysqli_fetch_assoc($poqry);
$poid = $porow['id'];    
$po_date= $porow['po_date']; 
//=================New=========
$contract_type = $porow['contract_type'];


 function string_between_two_string($str, $starting_word, $ending_word) 
{ 
    $subtring_start = strpos($str, $starting_word);
    $subtring_start += strlen($starting_word);
    $size = strpos($str, $ending_word, $subtring_start) - $subtring_start;
    return substr($str, $subtring_start, $size);   
} 
  
function get_cust_id($name,$con1){
    
    global $con;
    
    $sql = mysqli_query($con1,"select * from customer where cust_name='".$name."'");
    
    $sql_result = mysqli_fetch_assoc($sql);
    
    return $sql_result['cust_id'];
}

 $custid = get_cust_id($cust,$con1);

include("config.php");

$demo_atm_sql = mysqli_query($con1,"select * from demo_atm where so_id='".$so_id."'");

$demo_atm_sql_result = mysqli_fetch_assoc($demo_atm_sql);

$atm_id = $demo_atm_sql_result['atm_id'];
$cust_id = $demo_atm_sql_result['cust_id'];
$bank_name = clean($demo_atm_sql_result['bank_name']);
$bank_name = ucwords(strtolower($bank_name));
$area = clean($demo_atm_sql_result['area']);
$area = ucwords(strtolower($area));
$pincode = $demo_atm_sql_result['pincode'];
$city = clean($demo_atm_sql_result['city']);

$address = mysqli_real_escape_string($con1,$demo_atm_sql_result['address']);
$address= preg_replace('/[^a-zA-Z0-9_ -]/s',' ',$address);
$address=trim($address);
$address = ucwords(strtolower($address));

$state = $demo_atm_sql_result['state'];
$sub= mysqli_real_escape_string($con1,$_POST['sub']);
$doc= mysqli_real_escape_string($con1,$_POST['doc']);

if($city=='') { $city='NULL';}
if($pincode=='') { $pincode='NULL';}
if($area=='') { $area='NULL';} 
if($prob=='') { $prob='NULL';}



mysqli_query($con1,"BEGIN");

$errors=0;
$qryatm = mysqli_query($con1,"select * from atm where atm_id='".$atm_id."'");

if(mysqli_num_rows($qryatm)>0){
    
$atmrow=mysqli_fetch_assoc($qryatm);

$track_id=$atmrow['track_id'];

    
$update_atm = "update atm set cust_id='".$custid."', bank_name='".$bank_name."',area='".$area."',pincode='".$pincode."',city='".$city."',branch_id='".$branch_id."',start_date='".$start_date."',address='".$address."',po='".$po."',podate='".$po_date."',state1='".$state."',so_id='".$so_id."', contract_type ='".$contract_type."', site_type='site_del' where track_id='".$track_id."'";
 
    $update=mysqli_query($con1,$update_atm);
    
if(!$update){
  $errors++;
  echo "update atm set cust_id='".$custid."', bank_name='".$bank_name."',area='".$area."',pincode='".$pincode."',city='".$city."',branch_id='".$branch_id."',start_date='".$start_date."',address='".$address."',po='".$po."',podate='".$po_date."',state1='".$state."',so_id='".$so_id."', contract_type='".$contract_type."', site_type='site_del' where track_id='".$track_id."'"."<br>";
 
  }
} else {

   $insert_atm=mysqli_query($con1,"insert into atm (atm_id,cust_id,bank_name,area,pincode,city,branch_id,start_date,address,po, podate,state1,so_id, site_type, active,contract_type) values('".$atm_id."','".$custid."','".$bank_name."','".$area."','".$pincode."','".$city."','".$branch_id."','".$start_date."','".$address."','".$po."','".$po_date."','".$state."','".$so_id."', 'site_del', 'Y', '".$contract_type."')");



    $track_id = mysqli_insert_id($con1);


if(!$insert_atm){
  $errors++;
  $table.="insert into atm (atm_id,cust_id,bank_name,area,pincode,city,branch_id,start_date,address,po, podate,state1,so_id, site_type, active, contract_type) values('".$atm_id."','".$custid."','".$bank_name."','".$area."','".$pincode."','".$city."','".$branch_id."','".$start_date."','".$address."','".$po."','".$po_date."','".$state."','".$so_id."', 'site_del', 'Y','".$contract_type."')";
  }    

}
//==========Alert==========
$qry2=mysqli_query($con1,"select srno from login where username='".$_SESSION['user']."'");
$qry2ro=mysqli_fetch_row($qry2);
$qrr=mysqli_query($con1,"select * from alert where entry_date LIKE ('".date('Y-m-d')."%')");
	$num=mysqli_num_rows($qrr);
	$num2=$num+1;
	if($num2>0 && $num2<=9)
	$num3="0".$num2;
	else
	$num3=$num2;
   
  
   $adate=date('Y-m-d');

$query=mysqli_query($con1,"Insert into alert(`cust_id`,`atm_id`,`bank_name`,`area`,`address`,`city`,`branch_id`, `pincode`, `problem`, `caller_name`, `caller_phone`, `caller_email`, `entry_date`, `alert_date`, `call_status`, `alert_type`, `po`,`state1`, `createdby`,`subject`,`custdoctno`,`assetstatus`, ccmail) Values('".$custid."','".$track_id."','".$bank_name."','".$area."','".$address."','".$city."','".$branch_id."','".$pincode."','".$prob."','".$cname."','".$cphone."','".$cemail."','".$cdate."','".$adate."','1','new','".$po."','".$state."','".$qry2ro[0]."_".date("ymd").$num3."','New Installation','".$doc."','site','".$ccmail."' )");

$last_alert_id = mysqli_insert_id($con1);


if(!$query){
  $errors++;
  $table.="Creating Call Alert is Error";
  }    

//==================New Asset insert======

$sales_order_qry=mysqli_query($con1,"select * from new_sales_order_asset where so_trackid='".$so_id."' ");
$i=0;
while($asset=mysqli_fetch_assoc($sales_order_qry)) {

$product = $asset['po_product'];
$specs = $asset['po_model'];
$qty = $asset['po_qty'];
$valid = $asset['po_warr'];


$assetinst=mysqli_query($con1,"INSERT INTO site_assets (cust_id, po, assets_name,assets_spec, valid, quantity, atmid, type, rate, status, callid,so_id,atm_trackid, start_date, po_date, alert_id) VALUES ('".$custid."', '".$po."', '".$product."','".$specs."', '".$valid."', '".$qty."', '".$track_id."',  'NEW', '0', '1', '0','".$so_id."','".$track_id."','".$start_date."','".$po_date."','".$last_alert_id."')");
$i++ ;

}

if(!$assetinst){
  $errors++;
  }  

if($assetinst){
   
   $atmsql=mysqli_query($con1,"select latitude1,longitude1,address,state1 from atm where track_id='".$track_id."'");
   $rowatm=mysqli_fetch_row($atmsql);
   
   if($rowatm[0] !=0 or $rowatm[0] =='') {
      $latitude = $rowatm[0];
      $longitude = $rowatm[1];
   
   } else {
  //  ######### Error is coming====Need to work############# 
   
    $address2=trim($rowatm[2]);
   $state1 = $rowatm[3];
   
     $address = trim($address2 . ", " .$state1);
    
    if (empty($address)) {
        $errors++;
        }

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
        
    
      $updatelat= mysqli_query($con1,"update atm set latitude1='".$latitude."',longitude1='".$longitude."' where track_id='".$track_id."'");
  } 

 }   //=============== Delegation===========


if($latitude !='' or $latitude !=0) {
	    
 //$radius = 20; // in miles
      $radius = 100*0.621371192; // in km

$lng_min = $longitude - $radius / abs(cos(deg2rad($latitude)) * 69);
    $lng_max = $longitude + $radius / abs(cos(deg2rad($latitude)) * 69);
    $lat_min = $latitude - ($radius / 69);
    $lat_max = $latitude + ($radius / 69);
    
  $qryloc="SELECT *,(6371 * acos( cos( radians($latitude) ) 
              * cos( radians( latitude ) ) 
              * cos( radians( longitude ) - radians($longitude) ) 
              + sin( radians($latitude) ) 
              * sin( radians( latitude ) ) ) ) AS distance FROM area_engg WHERE (longitude BETWEEN $lng_min AND $lng_max) AND (latitude BETWEEN $lat_min and $lat_max) and status=1 ORDER BY distance";
  
  
   $res=mysqli_query($con1,$qryloc);
    $num=mysqli_num_rows($res);
    if($num>0){
        $row=mysqli_fetch_row($res);
        
        $ctime=date("Y-m-d H:i:s");
         $delegate_flag=1;
         $tab=mysqli_query($con1,"update alert set status='Delegated',call_status='1' where alert_id='".$last_alert_id."'");
    
		$tab2=mysqli_query($con1,"Insert into alert_delegation(engineer,atm,alert_id,date,delby) values('".$row[0]."','".$atm_id."','".$last_alert_id."','".$ctime."','".$_SESSION['user']."')");
   
     mysqli_query($con1,"Insert into Delegation_tracking(alertid,del_type,del_date) values('".$last_alert_id."','1','".$ctime."')");
            

}

}
//################### Mail######Need to write ##############

}

if($errors>0) {
  mysqli_query($con1,"ROLLBACK");

die('Failed :<br>'.$table."<br>".mysqli_error($con1));  
} else 
{
$up_so=mysqli_query($con1,"update so_order set status=2, alert_id ='".$last_alert_id."' where id='".$so_order_id."'");

mysqli_query($con1,"COMMIT");
?>
<script type="text/javascript">

alert("Alert created successfully and complaint number is <?php echo $qry2ro[0]."_".date("ymd").$num3; ?>");

 window.location='new_invoices.php';

</script>
<?php } 
?>
