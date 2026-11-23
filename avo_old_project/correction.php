<?php
//include("access.php");
include('config.php');


$contents='';
$contents="S.No\t shared Inv No\t so_id\t portal Inv No\t Inv Date\t Assets \t ";

$sql = mysqli_query($con1,"select * from so_required_saepl");

$cnt=0;

while($reslt=mysqli_fetch_row($sql)) {
$cnt++;

//echo "select * from so_order where inv_no ='".$reslt[1]."'"; die;
$contents.="\n".$cnt;
$contents.="\t".$reslt[1];


$astl=mysqli_query($con1,"select * from so_order where inv_no like'%".$reslt[1]."%'");
if(mysqli_num_rows($astl) >0) {
 
$so_order=mysqli_fetch_row($astl);

$astsql=mysqli_query($con1,"select * from new_sales_order_asset WHERE so_trackid ='".$so_order[1]."'");

$contents.="\t".$so_order[1];
$contents.="\t".$so_order[2];
$contents.="\t".$so_order[3];

$as_det="";

while($detailme=mysqli_fetch_row($astsql))
{
$as_det="";

$qry3me=mysqli_query($con1,"select name from assets_specification where ass_spc_id='$detailme[4]'");
$spec=mysqli_fetch_row($qry3me);

$as_det= $detailme[3]."( Spec: ".$spec[0]." - Qty:".$detailme[5]." - Rate:".$detailme[7].")";

$contents.="\t".str_replace("\n","  ",preg_replace('/\s+/', ' ',$as_det));

}

} else { $contents.="\t Not Matching"; }

}
$contents = strip_tags($contents); // remove html and php tags etc. str_replace(',', '\,', $row[faqdesk_answer_short])
  header("Content-Disposition: attachment; filename=so_asset.xls");
  header("Content-Type: application/vnd.ms-excel");
  print $contents;
  
  ?>


