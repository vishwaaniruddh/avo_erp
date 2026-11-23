<?php
include("access.php");
include('config.php');


$sql = mysqli_query($con1,"select * from bilaspur_smartcity ");
$i=0;

while($reslt=mysqli_fetch_row($sql)) {
$i++;

$astsql=mysqli_query($con1,"select track_id from atm where atm_id='".$reslt[1]."'");

$row=mysqli_fetch_row($astsql);

echo $i."update site_assets set status=1, exp_date='2028-12-03' where atmid='".$row[0]."' </br>"; 

$asst=mysqli_query($con1,"update site_assets set status=1, exp_date='2028-12-03' where atmid='".$row[0]."'");

echo $i."update atm set active='Y' where track_id='".$row[0]."' </br>";

$asst=mysqli_query($con1,"update atm set active='Y' where track_id='".$row[0]."'");

}


