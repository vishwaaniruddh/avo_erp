<?php
include('config.php');
############# must create your db base connection
//array(4) { ["Page"]=> string(1) "1" ["perpg"]=> string(2) "25" ["product"]=> string(1) "2" ["model"]=> string(2) "12" }

$strPage = $_REQUEST['Page'];
$Per_Page =$_POST['perpg'];   // Records Per Page

$product=$_POST['product'];
$model=$_POST['model'];

$sql= "SELECT * FROM  `assets_specification` where status=1 ";

if(isset($_POST['product']) && $_POST['product']!='')
{
$sql.=" and assets_id ='".$product."'";
}	

if(isset($_POST['model']) && $_POST['model']!='')
{
$sql.=" and make_id ='".$model."'";
}	
//echo $sql;
$table=mysqli_query($con1,$sql);

$Num_Rows = mysqli_num_rows ($table);
 
########### pagins
?>
 <div align="center"><b>Total Records: <?php echo $Num_Rows; ?></b>&nbsp;&nbsp;&nbsp;
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
$qry22=$sql;
$sql.=" order by name DESC LIMIT $Page_Start , $Per_Page";
//echo $sql;
$table=mysqli_query($con1,$sql);
$count=0;
$i=1;
?>
<table  border="1" cellpadding="2" cellspacing="0" style="margin-top:5px;border:1px #fff solid" class="se">
<tr>
<th>Sr. No.</th>
<th>Assets Name</th>
<th>Specification</th>
<th>Company Name</th>
<th>Edit</th>
</tr>
<?php
while($row= mysqli_fetch_row($table))
{

//echo $row[2]; die;

$count=$count+1;
$qry1=mysqli_query($con1,"select * from assets where assets_id='$row[1]'");
$row1=mysqli_fetch_row($qry1);
?>
<tr class="<?php if($count%2==0){ echo "res1"; } else{ echo "res2"; }  ?>">
<td><?php echo $i++; ?></td>
<td><?php echo $row1[1];?></td>
<td><?php echo $row[2];?></td>
<td><?php echo $row[3];?></td>
<td><a href="edit_assetspec.php?id=<?php echo $row[0];?>" >Edit</a></td>
</tr>
<?php } ?>
</table></div>
</center>