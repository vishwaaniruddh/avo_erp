<?php
//include("access.php");
include('config.php');


$sql = mysqli_query($con1,"select * from technosysy_reco_portal");

while($reslt=mysqli_fetch_row($sql)) {

$assql = mysqli_query($con1, "select * from site_assets where atmid='".$reslt[1]."' and assets_name='UPS'");

if(mysqli_num_rows($assql)>0) {
$ast=mysqli_fetch_row($assql);
    
echo "update site_assets set valid='36,Months', status=1, start_date ='".$reslt[3]."',exp_date = '".$reslt[4]."'  WHERE site_ass_id ='".$ast[0]."' and assets_name='UPS' </br>" ;
//die;
$astsql=mysqli_query($con1,"update site_assets set valid='36,Months', status=1, start_date ='".$reslt[3]."',exp_date = '".$reslt[4]."'  WHERE site_ass_id ='".$ast[0]."' and assets_name='UPS'");
} else {

echo "insert into site_assets set cust_id='19', po='TSSPl/PO/2022-23/194', assets_name='UPS',assets_spec=4, valid='36,Months',quantity=1, atmid='".$reslt[1]."',serialno=1,type='NEW', rate=1,status=1, callid=0,so_id='123',atm_trackid='".$reslt[1]."',entry_dt='2025-06-30 00:00:00, start_date ='".$reslt[3]."',exp_date = '".$reslt[4]."',po_date='2022-11-22' </br>";
//die; 
$astsql=mysqli_query($con1,"insert into site_assets set cust_id='19', po='TSSPl/PO/2022-23/194', assets_name='UPS',assets_spec=4, valid='36,Months',quantity=1, atmid='".$reslt[1]."',serialno=1,type='NEW', rate=1,status=1, callid=0,so_id='123',atm_trackid='".$reslt[1]."',entry_dt='2025-06-30 00:00:00', start_date ='".$reslt[3]."',exp_date = '".$reslt[4]."',po_date='2022-11-22'");
}

echo "update atm set active='y' WHERE track_id ='".$reslt[1]."' </br>";

$astl=mysqli_query($con1,"update atm set active='y' WHERE track_id ='".$reslt[1]."'");

}

