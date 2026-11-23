<?php session_start(); 
 include('config.php');
include("access.php");

$strPage = $_REQUEST['Page'];

$sql="SELECT * from so_order where inv_date >'2026-03-08'";


if($_POST['status']=="pending")
{
$sql.=" and (pod_img is NULL or pod_img='')";

} 
elseif($_POST['status']=="completed")
{
$sql.=" and (pod_img is NOT NULL and pod_img !='old') ";    
}

if(isset($_POST['invno']) && $_POST['invno']!='')
{
$invno = $_POST['invno'];
$sql.=" and inv_no ='".$invno."'";

}

if(isset($_POST['invdate']) && $_POST['invdate']!='')
{
$date = str_replace('/', '-', $_POST['invdate']);
$invdate= date('Y-m-d', strtotime($date));

$sql.=" and inv_date ='".$invdate."'";

}

//echo $sql;

//==========search by customer id======================================
if(isset($_POST['cid']) && $_POST['cid']!='')
{
$cid=$_POST['cid']; 
$sql.=" and customer_vertical ='".$cid."'";

}

//==========search by Branch===========================================
if(isset($_POST['state']) && $_POST['state']!='')
{
$state=$_POST['state'];
//$sql.=" and state1 LIKE '%".$state."%'";
$sql.=" and avo_branch ='".$state."'";
}
//echo $sql;

//==========search by Date======================================================
if(isset($_POST['sdate']) && $_POST['sdate']!='' && isset($_POST['edate']) && $_POST['edate']!='')
{
$fromdt=$_REQUEST['sdate'];
$todt=$_REQUEST['edate'];

$sql.=" and inv_date Between STR_TO_DATE('$fromdt','%d/%m/%Y') AND STR_TO_DATE('$todt','%d/%m/%Y')"; //   + INTERVAL 1 DAY";

}

$sqlr=$sql;

//echo $sql;
//die;

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
$sql.=" order by po_id DESC LIMIT $Page_Start , $Per_Page";

//echo $sql;
//die;

$table=mysqli_query($con1,$sql);

?>
<table border="1" cellpadding="2" cellspacing="0" style="margin-top:5px;"  id="custtable"> 
<tr>
                <th width="2%">S.No</th>
                <th width="5%">Customer Vertical</th>
                <th width="5%">Invoice No.</th>
                <th width="5%">Invoice Date</th>
                <th width="5%">Branch</th>
                <th width="5%">Site/Sol/ATM Id</th>
                <th width="7%">End User Name</th>
                
                <th width="10%">Address</th>
                <th width="5%">Courier Name</th>
                <th width="5%">Courier Docket No</th>
                <th width="5%">Delivery Date</th>
                <th width="5%">Status</th>
               
                
                
               </tr>

<?php
$i=1;

if(mysqli_num_rows($table)>0) {
while($row= mysqli_fetch_assoc($table))
{ 

$id = $row['id']; 
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
<!---Address  -->
<td width="70"><?php echo $atm[2]; ?></td>
<td width="70"><?php echo $row['courier']; ?></td>
<td width="70"><?php echo $row['docketno']; ?></td>
<?php if($row['del_date']=='1990-01-01') { $deldate=''; } 
else {$deldate = $row['del_date']; } ?>
<td width="70"><?php echo $deldate; ?></td>

<td>
 <?php if($row['pod_img'] == NULL){  ?> 
<button><a href="javascript:void(0);" class="btn btn-primary" onclick="window.open('pod_upload.php?id=<? echo $id;?>','upload','width=700px,height=500,left=300,top=100')" class="update">Upload</a></button>

<? } else { ?>
<a href="<?php echo $row['pod_img']; ?>" target="_blank" ><image src="<?php echo $row['pod_img']; ?>" alt="view POD" width="50" height="50" /></a> <br><br> 

<a href="javascript:void(0);" class="btn btn-primary" onclick="window.open('pod_upload.php?id=<? echo $id;?>','upload','width=700px,height=500,left=300,top=100')" class="update"><font color="Red">Edit</font></a>
<?php }  ?>
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

<form name="frm" method="post" action="export_pod_status.php" target="_new">
<input type="hidden" name="qr" value="<?php echo $sqlr; ?>" readonly>
<input type="submit" name="cmdsub" value="Export" > <span>Max Records-2000</span>
</form>
