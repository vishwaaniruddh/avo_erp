<?php
include("access.php");
include("config.php");
$po_id=$_GET['id']; 
 ### 
?>

<html>
<head>
<title>Invoices</title>
<link href="style.css" rel="stylesheet" type="text/css" />
<link href="datepicker/date_css.css" rel="stylesheet" type="text/css" />
<script src="datepicker/datepick_js.js" type="text/javascript"></script>


<style>

</style>
</head>
<body>
<div align="center" style="padding:15px">


<h3>Invoice Details </h3>
<table id="tab" width="100%" border="1" cellpadding="2" cellspacing="0" style="margin-top:5px;"  >

<tr><td>Description</td><td>No.of Sites</td><td>Invoice Number</td><td>Invoice Date</td><td>Invoice Amount</td><td>Period From</td><td>Period To</td><td>Invoice Copy</td></tr>
<?
$soquery=mysqli_query($con1,"select so_trackid from `new_sales_order` where `po_trackid`='".$po_id."' order by so_trackid ASC");

if(mysqli_num_rows($soquery)>0 ) {
while ($so = mysqli_fetch_row($soquery)) {
$so_id = $so[0];
$invquery=mysqli_query($con1,"select * from `so_order` where `po_id`='".$so_id."'");
$so_order = mysqli_fetch_row($invquery);

$opxquery=mysqli_query($con1,"select consume_qty from `opex_consumption` where `so_id`='".$so_id."'");
$con_qty = mysqli_fetch_row($opxquery);

?>
<tr>
<td>Intial Supply </td>
<td><?php echo $con_qty[0];?></td>
<td><?php echo $so_order[2];?></td> <!--  inv no-->
<td><?php echo $so_order[3];?></td> <!--  inv date-->
<td><?php echo $so_order[4];?></td> <!--  Amount-->
<td></td><td></td>
<td>
<a href="<?php echo $so_order[11]; ?>" target="_blank" ><image src="<?php echo $so_order[11]; ?>" alt="view invoice" width="50" height="50" /></a>
</td>

</tr>
<?php 
}

} else { ?>

<tr><td colspan="8" style="text-align: center;"> No Initial Bill Avlable</td></tr>
<?php } 

$sqlbill="SELECT * FROM opex_bills where po_id= '".$po_id."' ";
//echo $sqlbill;

$billqry=mysqli_query($con1,$sqlbill);
if(mysqli_num_rows($billqry) >0) {
while($bill=mysqli_fetch_row($billqry)){
?>
<tr>
<td>Opex Billing</td> <!--Description -->
<td><?php echo $bill[5];?></td>
<td> <?php echo $bill[2];?></td> <!--Invoice no -->
<td> <?php echo $bill[3];?></td> <!--inv date -->
<td><?php echo $bill[4];?> </td> <!--Amount -->
<td><?php echo $bill[7];?> </td> <!--from -->
<td><?php echo $bill[8];?></td>   <!--to -->
<td>
<a href="<?php echo $bill[6]; ?>" target="_blank" ><image src="<?php echo $bill[6]; ?>" alt="view invoice" width="50" height="50" /></a>
</td>  
    </tr>
<?
}

 } else { ?>
<tr><td style="text-align: center;" colspan="8">No Bills Available</td></tr>
<?
}

?>

</table>
</div>
</body>

<br>
<body>
<center>
<button name="close" id= "close" style="center" onclick="closeWin()">Go Back/ Close Window </button>

<script>
function closeWin() {
  window.close();
}
</script>
</center>
</body>