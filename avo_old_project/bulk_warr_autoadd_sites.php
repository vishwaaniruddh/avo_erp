<?php
include("config.php");

$date=date('Y-m-d');


//====TN Schools ==========
$sitedata= mysqli_query($con1,"select * from tn_schools_1kva_2 where s_no > 131 ");

while($row=mysqli_fetch_row($sitedata)) {

  
$invqry=mysqli_query($con1,"select po_id from so_order where inv_no='".$row[1]."'");

if(mysqli_num_rows($invqry)>0) {
$invrow =mysqli_fetch_row($invqry);
$so_id=$invrow[0];
} else {
 $invqry=mysqli_query($con1,"select po_id from so_order where inv_no='AVOCHN2526SL0468'");  
 $invrow =mysqli_fetch_row($invqry);
$so_id=$invrow[0];
}
  
$saleqry=mysqli_query($con1,"select * from new_sales_order where so_trackid='".$so_id."'" );
$so=mysqli_fetch_row($saleqry);

$poqry=mysqli_query($con1,"select po_no, po_date from purchase_order where id='".$so[1]."'" );
$porow=mysqli_fetch_row($poqry);
$po=$porow[0];
$po_date=$porow[1];

$atm_id=$row[2];
$bank=$row[3];
$area=$row[4];  // if($area=='') { $area='NULL';}
$pin=$row[5]; if($pin=='') { $pin=0;}
$city=$row[6]; if($city=='') { $city = 'NULL';}
$state=$row[7];

$branch=$row[8]; // Branch_id
$address=preg_replace('/[^a-zA-Z0-9_ -]/s',' ',$row[9]);
//=====
$cust_id=101; // Customer Id

$ref_id=$so[7];// Warehouse - atm_id in sales order

$start = $row[14];

for($i = 1; $i <= $row[11]; $i++) {

  
$atmqry=mysqli_query($con1,"select track_id from atm where atm_id='".$atm_id."_".$i."'" );

if(mysqli_num_rows($atmqry) >0) {
 echo "Done <br>"; 
} else {
  
$atm="Insert into atm set atm_id = '".$atm_id."_".$i."', so_id='".$so_id."', cust_id = '".$cust_id."', bank_name = '".$bank."', area='".$area."',city='".$city."', pincode='".$pin."', address = '".$address."',state1 = '".$state."', branch_id = '".$branch."', ref_id ='".$ref_id."', po = '".$po."', podate = '".$po_date."', active = 'Y', site_type= 'Bulk-Direct', start_date='1900-01-01', expdt = '1900-01-01'";
echo $atm."<br>";
//die;
  
$sql=mysqli_query($con1,$atm);
$track_id=mysqli_insert_id($con1);

if($sql) {

   $exp =date('Y-m-d', strtotime("+39 months $start")); 
   
$assetwry="insert into site_assets set cust_id ='".$cust_id."', po='".$po."', atmid='".$track_id."',so_id='".$so_id."', assets_name='UPS', assets_spec='1', valid='39,months', quantity='1', status=1, po_date='".$po_date."', start_date='".$start."', exp_date='".$exp."', atm_trackid='".$track_id."', alert_id=0, type='Bulk'" ;
echo $assetwry."<br>"; 
 // die;
  $insert=mysqli_query($con1,$assetwry);
 //===========Battery=========
$battassetwry="insert into site_assets set cust_id ='".$cust_id."', po='".$po."', atmid='".$track_id."',so_id='".$so_id."', assets_name='Battery', assets_spec='7', valid='39,months', quantity='3', status=1, po_date='".$po_date."', start_date='".$start."', exp_date='".$exp."', atm_trackid='".$track_id."', alert_id=0, type='Bulk'" ;
echo $battassetwry."<br>"; 
 // die;
  $sert=mysqli_query($con1,$battassetwry);
  

} 



}

}
  
}
  
  
  
  
  
  
  
 /*if($sql) {

$assqry=mysqli_query($con1,"select * from new_sales_order_asset where so_trackid='".$so_id."'" );

while($asst=mysqli_fetch_row($assqry)) {

if($asst[3]=='UPS') { $qty=2; }
elseif($asst[3]=='Battery') {$qty=8; } else {$qty=1; }

$assetwry="insert into site_assets set cust_id ='".$cust_id."', po='".$po."', atmid='".$track_id."',so_id='".$so_id."', assets_name='".$asst[3]."', assets_spec='".$asst[4]."', valid='".$asst[6]."', quantity='".$qty."', status=1, po_date='".$po_date."', start_date='".$start."', exp_date='".$exp."', atm_trackid='".$track_id."', alert_id=0, type='Bulk'" ;
echo $assetwry."<br>"; 
 // die;
  $insert=mysqli_query($con1,$assetwry);
 

} 
//=============UPS========
$valid='36,Months';
$spec = 1;
$qty = 1;

//$spec=$row[9]; // 3 kva master & slave
//$qty=$row[10]; // Qty


$assetwry="insert into site_assets set cust_id ='".$cust_id."', po='".$po."', atmid='".$track_id."',so_id='".$so_id."', assets_name='UPS', assets_spec='".$spec."', valid='".$valid."', quantity='".$qty."', status=1, po_date='".$po_date."', start_date='".$start."', exp_date='".$exp."', atm_trackid='".$track_id."', alert_id=0, type='Bulk'" ;
echo $assetwry."<br>";

$insert=mysqli_query($con1,$assetwry);



//=====Battery==============
$bvalid='36,Months';
$bspec=8; // 	
$bqty=3; // Qty

$battry="insert into site_assets set cust_id ='".$cust_id."', po='".$po."', atmid='".$track_id."',so_id=0, assets_name='Battery', assets_spec='".$bspec."', valid='".$bvalid."',quantity='".$bqty."', status=1, po_date='".$po_date."', start_date='".$start."', exp_date='".$exp."', atm_trackid='".$track_id."', alert_id=0, type='Bulk'" ;
$bqry= mysqli_query($con1,$battry);

echo $battry."<br>";
//echo $battry; 

} 

}
*/

?>