
<?php
session_start(); 

include('db_connection.php');
$con1 = OpenCon1();

############# must create your db base connection

$strPage = $_REQUEST['Page'];

$cur_date =date('Y-m-d');

?>

<table width="150%" border="1" cellpadding="2" cellspacing="0" style="margin-top:5px;"  >
<tr>
<th width="10%">Contract Type</th> 
<th width="5%">Customer</th>
<th width="5%">PO No</th>
<th width="5%">PO Description</th>
<th width="5%">P.O._Date</th>
<th width="5%">P.O.File</th>
<th width="5%">Buyer</th>
<th width="10%">Buyer Address</th>
<th width="5%">Delivery Type</th>
<th width="7%">Products_Ordered_as_per_PO</th>
<th width="5%">Contract Term</th>
<th width="5%">Billing term in Opex</th>
<th width="5%">PO Site Count</th>
<th width="5%">Supplied Sites</th> 
<th width="5%">Last Billed Sites</th>
<th width="5%">Last Inv. No.</th> 
<th width="4%">Last Inv. Date</th>
<th width="4%">Last Inv. Amount</th>
              
<th width="4%">Bill Due From </th> 
<th width="6%">Status </th> 
 <th width="4%">Show Bill Details</th> 
<th width="6%">Actions </th> 

</tr>
<?php

$sql="Select * from purchase_order where contract_type = '".$_POST['contract']."'and no_sites !='0'";
if(isset($_POST['cid']) && $_POST['cid'] !='')
{
$sql.=" and cust_id = '".$_POST['cid']."'";
}

if(isset($_POST['po_no']) && $_POST['po_no'] !='')
{
$sql.=" and po_no = '".$_POST['po_no']."'";
}

//======================================Search Call Date wise
if(isset($_POST['fromdt']) && $_POST['fromdt']!='' && isset($_POST['todt']) && $_POST['todt']!=''){
		$fromdt=$_POST['fromdt'];
		$todt=$_POST['todt'];

$fromdt=date('Y-m-d',strtotime(str_replace('/','-',$_REQUEST['fromdt'])));
$todt=date('Y-m-d',strtotime(str_replace('/','-',$_REQUEST['todt'])));
$sql.=" and po_time between '".$fromdt." 00:00:00' and '".$todt." 23:59:59'";

}

$countsql=str_replace("Select *","Select count(*) as count",$sql);
$countrow=mysqli_query($con1,$countsql);

$rows=mysqli_fetch_assoc($countrow);
$Num_Rows=$rows['count'];
$count=0;
//$Num_Rows = mysqli_num_rows($table);
 ?>
 <div align="center">Total number of Records :<b><?php echo $Num_Rows; ?></b>
 Records Per Page :<select name="perpg" id="perpg" onchange="searchById('Listing','1','perpg');">

 <?php
 for($i=1;$i<=$Num_Rows;$i++)
 {
 if($i%10==0)
 {
 ?>
 <option value="<?php echo $i; ?>" <?php if(isset($_POST['perpg']) && $_POST['perpg']==$i){?>  selected="selected" <?php } ?>><?php echo $i."/page"; ?></option>
 <?php
 }
 }
 
 ?>
 <option value="<?php echo $Num_Rows; ?>"><?php echo "All"; ?></option>
 </select> </div>
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

// echo $sql;

$qr22=$sql;
$sql.=" order by id DESC LIMIT $Page_Start , $Per_Page";

//echo $sql;

$table=mysqli_query($con1,$sql);

if(mysqli_num_rows($table)>0) {

while($row= mysqli_fetch_row($table))
{

$po_id=$row[8];

//echo date('Y-m-d', strtotime('+1 Month',$row[5]));

//===============Cutsomer===========
	$qry=mysqli_query($con1,"select cust_name from customer where cust_id='".$row[2]."'");
	$custrow=mysqli_fetch_row($qry);

		?>
<tr> 

<td  valign="top"><?php echo $row[6]; ?></td>
<td  valign="top">&nbsp;<?php echo $custrow[0]; ?></td>
<td  valign="top">&nbsp;<?php echo $row[0];  ?></td>
<td  valign="top">&nbsp;<?php echo $row[3];  ?></td>
<td width="71" valign="top">&nbsp;<?php echo $row[5] ?></td>

<td>
<?php if($row[25] !='') { ?>
<a href="<?php echo $row[25]; ?>" target="_blank" ><image src="<?php echo $row[25]; ?>" alt="PO Copy" width="50" height="50" /></a>
<?php } else {  echo "No File Found"; }
?>
</td> 
<?
$sql=mysqli_query($con1,"select * from buyer where buyer_ID='".$row[13]."'");
    $sql_result = mysqli_fetch_assoc($sql);
    $buyer_name = $sql_result['buyer_name']; ?>

<td width="71" valign="top">&nbsp;<?php echo $buyer_name ?></td> 
<td width="71" valign="top">&nbsp;<?php echo $row[9] ?></td>
    
<td width="71" valign="top">&nbsp;<?php echo $row[16] ?></td>

<!------ PO Assets --------------->
<td style="width:70%">
<?
$i=0;
$selasset=mysqli_query($con1,"select * from po_assets where po_trackid='".$row[8]."'");
   while($sql_res = mysqli_fetch_assoc($selasset)) {
$i++;
$sqlasset=mysqli_query($con1,"select * from assets_specification where ass_spc_id ='".$sql_res['specs']."'");  
$spec = mysqli_fetch_row($sqlasset);
   
 echo $i.") ".$spec[2]." # qty:".$sql_res['qty']."</br>";     
   }
?>    
</td>
<td width="71" valign="top">&nbsp;<?php echo $row[18] ?></td><!-- contractterms -->
<td width="100" valign="top"><?php echo "Every ".$row[4]." Months" ?></td> <!-- Bill term-->
<td> &nbsp;<?php echo $row[1] ?></td> <!-- Total Sites -->

<?php
//echo "select consume_qty from `opex_consumption` where `po_id`='".$po_id."'";
//========================Qty Consumed===========
$consquery=mysqli_query($con1,"select consume_qty from `opex_consumption` where `po_id`='".$po_id."'");
if(mysqli_num_rows($consquery) >0){
$cons_qty=array();
while($consrow=mysqli_fetch_row($consquery)) {
  $cons_qty[]=$consrow[0]; 
}

$consumed=array_sum($cons_qty);
}
?>
<td><?php echo $consumed;?></td> <!--  Consumed Qty-->

<?php
//============Last Bills ===== 
//  first check opex bills if not SO bills===
$billsquery=mysqli_query($con1,"select * from `opex_bills` where `po_id`='".$po_id."'order by id DESC limit 1");
if(mysqli_num_rows($billsquery) >0){
$bill=mysqli_fetch_row($billsquery);

//echo "Opex Bills"; 
?>
<td><?php echo $bill[5];?></td> <!--  No of sites-->
<td><?php echo $bill[2];?></td> <!--  inv no-->
<td><?php echo $bill[3];?></td>  <!--  inv date-->
<td><?php echo $bill[4];?></td>  <!--  Inv Amount-->
<?

$due_date=date('Y-m-d', strtotime("+$row[4] months $bill[8]"));
?>
<td><?php echo $due_date;?></td>
<?php 
} else { 
//##########  else check So order####################
//echo "SO Bills";
$soquery=mysqli_query($con1,"select so_trackid from `new_sales_order` where `po_trackid`='".$po_id."' order by so_trackid DESC limit 1");

if(mysqli_num_rows($soquery)>0 ) {
$so = mysqli_fetch_row($soquery);
$so_id = $so[0];
$invquery=mysqli_query($con1,"select * from `so_order` where `po_id`='".$so_id."'");
if(mysqli_num_rows($invquery)>0) {
$so_order = mysqli_fetch_row($invquery);

$opxquery=mysqli_query($con1,"select consume_qty from `opex_consumption` where `so_id`='".$so_id."'");
$opxcont = mysqli_fetch_row($opxquery);
?>

<td><?php echo $opxcont[0];?></td>
<td><?php echo $so_order[2];?></td> <!--  inv no-->
<td><?php echo $so_order[3];?></td> <!--  inv date-->
<td><?php echo $so_order[4];?></td> <!--  Amount-->
<?
//-########  Calculation for due date##############
if($row[6] == 'Opex/Rental') {
$due_date=date('Y-m-d', strtotime("+1 Day", $bill[8]));

} else if($row[6] == 'Deferred_Capex'){
  
if($row[18]=='1 Year Capex + 6 Years OPEX') {
 $due_date = date('Y-m-d', strtotime("+12 month", strtotime($so_order[3])));  

} 
elseif($row[18]=='2 Years Capex + 3 Years OPEX') {
$due_date = date('Y-m-d', strtotime("+24 month", strtotime($so_order[3])));

} else if($row[18]=='3 Years Capex + 2 Years OPEX'){
$due_date = date('Y-m-d', strtotime("+36 month", strtotime($so_order[3])));

} else if($row[18]=='5 Years Capex + 2 Years OPEX'){
$due_date = date('Y-m-d', strtotime("+60 month", strtotime($so_order[3])));

} else if($row[18]=='5 Years Capex + 3 Years OPEX'){
$due_date = date('Y-m-d', strtotime("+1 month", strtotime($so_order[3])));
}    
///====== OPEX Then  Capex  =============== 
 else if($row[18]=='3 Years OPEX Then +2 Years Capex'){
$due_date = date('Y-m-d', strtotime("+1 month", strtotime($so_order[3])));
}
 else if($row[18]=='2 Years OPEX Then +3 Years Capex'){
$due_date = date('Y-m-d', strtotime("+1 month", strtotime($so_order[3])));
}
 else if($row[18]=='5 Years OPEX Then +3 Years Capex'){
$due_date = date('Y-m-d', strtotime("+1 month", strtotime($so_order[3])));
}
else if($row[18]=='5 Years OPEX Then +2 Years Capex'){
$due_date = date('Y-m-d', strtotime("+1 month", strtotime($so_order[3])));
}

else { $due_date = "NOt Found"; }   


?>
<td><?php echo $due_date ;?></td>

<?php    
}

}else { ?>
<td>Not Billed yet</td>
<td>Not Billed yet</td>
<td>Not Billed yet</td>
<td>Not Billed yet</td>
<td>Not Billed yet</td>

<? } 
}else { ?>

<td>No SO</td>
<td>No SO</td>
<td>No SO</td>
<td>No SO</td>
<td>No SO</td>
<?php 
}
    
}

if(strtotime($due_date) >= strtotime($cur_date)){
   $status="No Due"; 
} 
elseif(strtotime($due_date) <= strtotime($cur_date)){
   $status="Bill Pending"; 
}
?>
<td><?php echo $status;?></td>

<td>
<button><a href="javascript:void(0);" class="btn btn-primary" onclick="window.open('show_opex_invoice.php?id=<? echo $po_id;?>','view updates','width=600px,height=600,left=350,top=75')" class="update">Show Bills</a></button>
</td>

<td>
<a href="javascript:void(0);" class="btn btn-primary" style="color:yellow;" onclick="window.open('opex_inv_upload.php?id=<? echo $po_id;?>','view updates','width=600px,height=600,left=350,top=75')" class="update">Generate Bill</a>
</td>

</tr>
<?php
} 
}
?>
</table>
<div class="pagination" style="width:100%;"><font size="4" color="#000">
<!--Total <?php //echo $Num_Rows;?> Record : -->
<?php


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
</div>
<div><form name="frm" method="post" action="exportme.php" target="_new">
<input type="hidden" name="qr" value="<?php echo $qr22; ?>" readonly>
<input type="submit" name="cmdsub" value="Export" > <span>(Export Minimum Records)</span>
</form>
</div> 
</html>

<?php  CloseCon($con1); ?>