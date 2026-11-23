 <?php
include("config.php");

$date=date('Y-m-d');


//=============== Expiry date from Start date====================

$sitedata= mysqli_query($con1,"select site_ass_id, start_date, valid, atmid from site_assets where exp_date in ('1900-01-01', '2000-01-01')");

while($row=mysqli_fetch_row($sitedata)) {
 
$d1=explode(',',$row[2]);
$exp=date('Y-m-d', strtotime("+$d1[0] months $date"));

mysqli_query($con1,"update site_assets set start_date='".$date."', exp_date='".$exp."', status= '1' where site_ass_id='".$row[0]."'"); 

mysqli_query($con1,"update atm set active='y', expdt='".$exp."' where track_id='".$row[3]."'"); 
  
  

} 


mysqli_close($con1);
mysqli_close($con2);
?>