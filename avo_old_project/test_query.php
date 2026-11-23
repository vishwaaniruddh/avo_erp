<?php
include ("config.php");
$id = 778138;
$type = 'amc';
$qry = "";

$stat = 0;

// ======= Repeat calls Block==============================
$tmb = date('Y-m-d 00:00:00', strtotime('-15 days'));
$ly = date('Y-m-d 00:00:00', strtotime('-1 year'));

    $qry = "select b.atmid,a.address,a.state1,a.entry_date,a.problem,a.call_status,a.alert_id,a.status, a.alert_type,a.close_date from alert a,Amc b where a.atm_id='$id' and a.entry_date >'$ly' and a.atm_id=b.amcid order by alert_id DESC limit 6 ";

echo $qry; die;

//$sql = mysqli_query($con1,$qry);
//echo $row[0].", ".$row[1]."<br>";

?>

