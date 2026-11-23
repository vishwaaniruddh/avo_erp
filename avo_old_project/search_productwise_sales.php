<?php session_start(); 
 include('config.php');
include("access.php");
 //var_dump($_SESSION);
 
function get_spec($spc_id){
    
    global $con1;
  // echo "select name from assets_specification where ass_spc_id='".$spc_id."'";
    $sql = mysqli_query($con1,"select name from assets_specification where ass_spc_id='".$spc_id."'");
    $sql_result = mysqli_fetch_assoc($sql);
    
    return $sql_result['name'];
} 


$strPage = $_REQUEST['Page'];

$sql="SELECT a.*,b.* FROM so_order a, new_sales_order_asset b, new_sales_order c WHERE a.po_id = b.so_trackid and a.po_id = c.so_trackid and c.del_type != 'stock_trfr' AND a.status=2";

if(isset($_POST['product']) && $_POST['product']!='') {
 $product=$_POST['product'];
 
$asstqr=mysqli_query($con1,"Select assets_name from assets where assets_id = '".$product."'");
$astrow=mysqli_fetch_row($asstqr);
 $sql.=" and b.po_product='".$astrow[0]."'";   
    
}


if(isset($_POST['specs']) && $_POST['specs']!='')
{
$specs=$_POST['specs'];
//if($specs !='20kva')
$sql.=" and b.po_model='".$specs."'";
//else  // 20 / 25kvA //30 kVA //40kVA /50kVA /60 kVA/80 kVA /100kVA
//$sql.=" and b.po_model in(30,121,150,156,219,313,360,498,743,815,816,839,840,841,842,843,844,845,846,847,879,914,941,961,962,963,964,965,966,967,968,969,970,971,1037,1038,1039,1058,1059,1060,1061,1062,1063,1064,1065,1066,1067,1068,1069,1070,1071,1072,1274,79,318,1073,1074,1075,1076,1260,1273, 33,119,178,212,218,233,266,353,381,400,441,468,487,509,512,513,860,861,862,915,974,975,976,1086,1087,1088,1089,1090,1091,1092,1093,1094,1276, 45,149,315,320,417,544,875,876,877,878,988,989,1103,1104,1105,1106,1107 )";
}

//==========search by customer id======================================
if(isset($_POST['cid']) && $_POST['cid']!='')
{
$cid=$_POST['cid']; 
$sql.=" and a.customer_vertical ='".$cid."'";

}

//==========search by Branch===========================================
if(isset($_POST['state']) && $_POST['state']!='')
{
$state=$_POST['state'];
//$sql.=" and state1 LIKE '%".$state."%'";
$sql.=" and a.avo_branch ='".$state."'";
}
//echo $sql;

//==========search by Date======================================================
if(isset($_POST['sdate']) && $_POST['sdate']!='' && isset($_POST['edate']) && $_POST['edate']!='')
{
$fromdt=$_REQUEST['sdate'];
$todt=$_REQUEST['edate'];

$sql.=" and a.inv_date Between STR_TO_DATE('$fromdt','%d/%m/%Y') AND STR_TO_DATE('$todt','%d/%m/%Y')"; //   + INTERVAL 1 DAY";

}



$sqlr=$sql;

$table=mysqli_query($con1,$sql);

$Num_Rows = mysqli_num_rows ($table);
 
?>
 <div align="center">Total number of Records :<b><?php echo $Num_Rows; ?></b>
 Records Per Page :<select name="perpg" id="perpg" onchange="searchById('Listing','1','perpg');">

 
 <?php
 for($i=1;$i<=$Num_Rows;$i++)
 {
 if($i%25==0)
 {
 ?>
 <option value="<?php echo $i; ?>" <?php if(isset($_POST['perpg']) && $_POST['perpg']==$i){?>  selected="selected" <?php } ?>><?php echo $i."/page"; ?></option>
 <?php
 }
 }
 
 ?>
 <option value="<?php echo $Num_Rows; ?>"><?php echo "All"; ?></option>
 </select>
 
 </div>
 <?php
########### pagins
//echo $_POST['perpg'];
$Per_Page =$_POST['perpg'];   // Records Per Page
 
$Page = $strPage;
if(!$strPage)
{
	$Page=1;
}
 
$Prev_Page = $Page-1;
$Next_Page = $Page+1;


$Page_Start = (($Per_Page*$Page)-$Per_Page);
if($Num_Rows<=$Per_Page)
{
	$Num_Pages =1;
}
else if(($Num_Rows % $Per_Page)==0)
{
	$Num_Pages =($Num_Rows/$Per_Page) ;
}
else
{
	$Num_Pages =($Num_Rows/$Per_Page)+1;
	$Num_Pages = (int)$Num_Pages;
}
$sql.=" order by a.po_id DESC LIMIT $Page_Start , $Per_Page";

echo $sql;
//die;

$table=mysqli_query($con1,$sql);

?>
<table border="1" cellpadding="2" cellspacing="0" style="margin-top:5px;"  id="custtable"> 
<tr>
                <th width="5%">S.No</th>
                <th width="5%">Customer Vertical</th>
                <th width="5%">Invoice No.</th>
                <th width="5%">Invoice Date</th>
                <th width="5%">Branch</th>
                <th width="5%">Site/Sol/ATM Id</th>
                <th width="5%">End User Name</th>
                <th width="5%">City</th>
                <th width="5%">Address</th>
                <th width="5%">Product Details</th>
                <th width="5%">All products in this Invoice</th>
                
                
               </tr>

<?php
$i=1;

if(mysqli_num_rows($table)>0) {
while($row= mysqli_fetch_assoc($table))
{ 


$so_id=$row['po_id'];
$cid= $row['customer_vertical'];
$br=  $row['avo_branch'];
$atmqry= mysqli_query($con1,"SELECT bank_name,city, address FROM demo_atm WHERE so_id='".$so_id."'");
$atm=mysqli_fetch_row($atmqry);


?>

<div align="center">
<tr>
<td width="77"><?php echo $i++; ?></td>
<!-- Cust -->

<td width="77">
 <?   $custname=mysqli_query($con1,"select `cust_name` from `customer` where `cust_id`='".$cid."'");
$custname1=mysqli_fetch_row($custname);
echo $custname1[0];  ?></td>
     
<td width="5%"><? echo $row['inv_no'];?></td> 
<td width="5%"><? echo $row['inv_date'];?></td> 
<!-- Branch -->
<td width="5%">
<?php 
$branch=mysqli_query($con1,"select name from avo_branch where id='".$br."'");
$branch1=mysqli_fetch_row($branch);
echo $branch1[0]; ?></td>

<!---atm id -->
<td width="95"><?php echo $row['atm_id']; ?></td>
<!---bank -->

<td width="95"><?php echo $atm[0]; ?></td>
<!--City -->

<td width="50"><?php echo $atm[1]; ?></td>
<!---Address  -->
<td width="70"><?php echo $atm[2]; ?></td>

<!---Product  -->
<td width="70"><?php echo $row['po_product']." - ".get_spec($row['po_model'])." || Warr :".$row['po_warr']." || Qty :".$row['po_qty']." || Rate:".$row['po_rate']."</br>"; ?></td>

<? $assetqry=mysqli_query($con1,"select * from new_sales_order_asset where so_trackid='".$so_id."'");
?>

<td width="100"> 
<?
while($detailme=mysqli_fetch_row($assetqry))
{ 
echo $detailme[3].' Cap:' .get_spec($detailme[4]).' || Qty:'.$detailme[5].') || Warr:'.$detailme[6].' || Rate:'.$detailme[7]."</br>";
} 
?>
</td>

</tr></div>

<?php } ?> 

</table>
<div class="pagination" style="width:100%;"><font size="4" color="#000">
<!--Total <?php //echo $Num_Rows;?> Record : -->
<?php

} 


if($Prev_Page) 
{
	echo " <a href=\"JavaScript:searchById('Listing','$Prev_Page','perpg')\" style=\"text-decoration:none\"><font color=:#005252> << Back</font></a>";
}

?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php
if($Page!=$Num_Pages)
{
	echo " <a href=\"JavaScript:searchById('Listing','$Next_Page','perpg')\" style=\"text-decoration:none\"><font color=:#005252>Next >></font></a> ";
}
?>

<form name="frm" method="post" action="export_producwise_sales.php" target="_new">
<input type="hidden" name="qr" value="<?php echo $sqlr; ?>" readonly>
<input type="submit" name="cmdsub" value="Export" > <span>Max Records-2000</span>
</form>
