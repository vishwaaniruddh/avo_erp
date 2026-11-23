<?php
session_start();

include('config.php');
############# must create your db base connection

$strPage = $_REQUEST['Page'];


//var_dump($_POST); die;

$str="";

?><table border="1" cellpadding="2" cellspacing="0" style="margin-top:5px;" class="res"  id="custtable">
<tr><th width="77">Complain ID</th> 
<th width="88">Attended Date</th> 
<th width="77">Name</th>
<th width="72">ATM</th>
<th width="71">Bank</th>
<th width="55">City</th>
<th width="57">Area</th>
<th width="207">Address</th>
<th width="207">State</th>
<th width="75">Problem</th>
<th width="75">Alert Date</th>
<th width="75">Contact Person</th>
<th width="75"> Phone</th>
<th width="75"> Delegated To</th>
<th width="75"> Customer Status</th>
<th width="100">Engineer Last FeedBack</th>

<?php
include("config.php");

$sql.="Select * from alert_progress where Date(responsetime) > '2026-08-01'";

if(isset($_POST['eng']) && $_POST['eng']!='')
{
$eng=$_POST['eng'];
$sql.=" and engg_id ='".$eng."'";
} 
else if(isset($_POST['branch_avo']) && $_POST['branch_avo']!='')

{
$branch=$_POST['branch_avo'];

$brsql=mysqli_query($con1,"Select loginid from area_engg where area ='".$branch."'");
$all_engid = array();

while ($brow=mysqli_fetch_array($brsql)) {
   $all_engid[]= $brow[0];
}
$eng_string = implode(",", $all_engid);

$sql.=" and engg_id in ($eng_string)";
}

if(isset($_POST['fromdt']) && $_POST['fromdt']!='' && isset($_POST['todt']) && $_POST['todt']!='')
{
$fromdt=$_POST['fromdt'];
$todt=$_POST['todt'];
$sql.=" and responsetime Between STR_TO_DATE('$fromdt','%d/%m/%Y') AND STR_TO_DATE('$todt','%d/%m/%Y') + INTERVAL 1 DAY";

}



$table=mysqli_query($con1,$sql);
$count=0;
$Num_Rows = mysqli_num_rows ($table);
 ?>
 <div align="center">Total number of Records :<b><?php echo $Num_Rows; ?></b>
 Records Per Page :<select name="perpg" id="perpg" onchange="searchById('Listing','1','perpg');">

 <?php
 for($i=1;$i<=300;$i++)
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

$qr22=$sql;
$sql.=" order by alert_id DESC LIMIT $Page_Start , $Per_Page";

//echo $sql;
$table=mysqli_query($con1,$sql);

if(mysqli_num_rows($table)>0) {
while($prog= mysqli_fetch_row($table))
{

//===========Alert table=======
$alertqry= mysqli_query($con1,"select * from alert where alert_id='".$prog[1]."'");
$row = mysqli_fetch_row($alertqry);

if($row[21] == 'amc') {
$atm=mysqli_query($con1,"select atmid from Amc where amcid='".$row[2]."'"); 
    $atmrow=mysqli_fetch_row($atm);
     $atm_id = $atmrow[0];
}
else if($row[21] == 'site') {
   // echo "select atm_id from atm where track_id='".$row[2]."'<br>";
	$atm=mysqli_query($con1,"select atm_id from atm where track_id='".$row[2]."'");
	$atmrow=mysqli_fetch_row($atm);
     $atm_id = $atmrow[0]; 
   // echo $atm_id. "<br>";
}
     else { $atm_id = $row[2]; }

 

	$qry=mysqli_query($con1,"select cust_name from customer where cust_id='".$row[1]."'");
	$custrow=mysqli_fetch_row($qry);
	$tab=mysqli_query($con1,"select feedback,standby from eng_feedback where alert_id='".$row[0]."' order by id DESC");
	$row1=mysqli_fetch_row($tab);
	//echo "eng stat".$row[15];
		?>
<tr <?php if($row[26]=='1'){ echo "style='background:#99CC33'"; } if($row[16]=='2'){ echo "style='background:#990000'"; } ?>>
<td width="90" valign="top">&nbsp;<?php echo $row[25]; ?></td>
<td width="77" valign="top">&nbsp;<?php echo date("Y-m-d",strtotime($prog[2])); ?></td>
<td width="77" valign="top">&nbsp;<?php echo $custrow[0]; ?></td>
<td width="72" valign="top">&nbsp;<?php  echo $atm_id; ?></td> <!-- ATM ID-->
<td width="71" valign="top">&nbsp;<?php echo $row[3] ?></td>
<!--<td width="71" valign="top">&nbsp;<?php echo $row[27] ?></td>-->
<td width="55" valign="top">&nbsp;<?php echo $row[6] ?></td>
<td width="57" valign="top">&nbsp;<?php echo $row[4] ?></td>
<td valign="top">&nbsp;<?php echo $row[5] ?></td>
<td width="71" valign="top">&nbsp;<?php echo $row[27] ?></td>
<td width="75" valign="top">&nbsp;<?php echo $row[9];
 ?></td>
<td width="75" valign="top">&nbsp;<?php
if($row[17]=='service' || $row[17]=='new temp' || $row[17]=='new'){ echo date('d/m/Y h:i:s a',strtotime($row[10]));  } else{ if(isset($row[11]) and $row[11]!='0000-00-00') echo date('d/m/Y h:i:s a',strtotime($row[11])); }
?></td>
<td width="75" valign="top">&nbsp;<?php echo $row[12] ?></td>
<td width="75" valign="top">&nbsp;<?php echo $row[13] ?></td>
<td width="75" valign="top">&nbsp;
<?php 
$oldeng=mysqli_query($con1,"select engineer from alert_delegation where alert_id='".$row[0]."'");
$getold=mysqli_fetch_row($oldeng);
$fetchengid=mysqli_query($con1,"Select engg_name,phone_no1 from area_engg where engg_id='".$getold[0]."'");
$getoldname=mysqli_fetch_row($fetchengid);
echo $getoldname[0]."<br>".$getoldname[1];
  ?></td>
  <td width="75" valign="top">&nbsp;
<?php 
if(0 === strpos($row[2], 'temp'))
	echo "PCB";
	else
 if($row[21]=='site'){ echo "Under Warrenty"; }else if($row[21]=='amc'){ echo "AMC"; }else{ echo "PCB"; }
  ?></td>
<td valign="top">&nbsp;<?php if($row1[0]!=''){ ?><a class="update" href="#" onclick="newwin('masteralert.php?id=<?php echo $row[0] ?>','display')" ><?php echo $row1[0]; ?></a><?php }else{ echo "No Update";  } ?></td>

</tr>
<?php
}
?>
</table>

<div class="pagination" style="width:100%;"><font size="4" color="#000">
<!--Total <?php //echo $Num_Rows;?> Record : -->
<?php

}
if($Prev_Page) 
{
	echo " <a href=\"JavaScript:searchById('Listing','$Prev_Page','perpg')\" style=\"text-decoration:none\"><font color=:#005252> << Back</font></a>";
}
/*
for($i=1; $i<=$Num_Pages; $i++){
	if($i != $Page)
	{
		echo " <li><a href=\"JavaScript:searchById('Listing','$i')\">$i</a> </li>";
	}
	else
	{
		echo "<li class='currentpage'><b> $i </b></li>";
	}
}*/
?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php
if($Page!=$Num_Pages)
{
	echo " <a href=\"JavaScript:searchById('Listing','$Next_Page','perpg')\" style=\"text-decoration:none\"><font color=:#005252>Next >></font></a> ";
}

?>
</div>
<form name="frm" method="post" action="exportme.php" target="_new">
<input type="hidden" name="qr" value="<?php echo $qr22; ?>" readonly>
<input type="submit" name="cmdsub" value="Export" >
</form>
 
<div id="bg" class="popup_bg"> </div> 