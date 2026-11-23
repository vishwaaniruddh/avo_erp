<?php
session_start();
include 'config.php';
include("access.php");

$cdate=date("Y-m-d");

$fix=50;
$Branch=$_POST['Branch'];
$Department=$_POST['Department'];
$Employee_name=$_POST['Employee_name'];
$Employee_code=$_POST['Employee_code'];
$from=$_POST['from'];
$to=$_POST['to'];


if(isset($_POST['from']) && $_POST['from'] =='') {$from = $cdate;}
if(isset($_POST['to']) && $_POST['from'] =='') {$to = $cdate;}




$brch=mysqli_query($con1,"select name from avo_branch where id='".$_SESSION['branch']."'");
$brname=mysqli_fetch_array($brch);
//echo $_SESSION['branch'];

if($_SESSION['branch']=="all"){
$abc="select * from employee where status='0'" ;
//echo $abc; 
}else{

$abc="select * from employee where status='0'and branch='".$brname[0]."'" ;
//echo $abc;
}


$strPage=$_POST['Page'];


?>
<?php

if($Branch!=""){
$abc.=" and branch like '%".$Branch."%'";
// echo $abc;
}

if($Department!=""){
$abc.=" and department='".$Department."'";
 
}

if($Employee_name!=""){
$abc.=" and empName='".$Employee_name."'";
//echo $abc;
}

if($Employee_code!=""){
$abc.=" and empcode='".$Employee_code."'";
}


//echo $abc;

    $result=mysqli_query($con1,$abc);
   
     $Num_Rows=mysqli_num_rows($result);

//echo $abc;
//die;

$Per_Page =$_POST['perpg'];   // Records Per Page

$Page = $strPage;

if($strPage=="")
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

$abc.=" LIMIT $Page_Start , $Per_Page";


	
$qrys=mysqli_query($con1,$abc);

	$count=mysqli_num_rows($qrys);

$sr=1;
	if($Page=="1" or $Page=="")
	{
	$sr="1";
	}else
	{
	  $sr=($fix* $Page)-$fix;
	  
	   $sr=$sr+1;
	}


  
   ?> 
   
<html>

<style>
.space{margin-left:80px;}
.addcolor{font-size:20px; color:#C60000; font-family:"Trebuchet MS", Arial, Helvetica, sans-serif;}
</style>

<body>
<Form>
<center>



<div align="center">Total number of Records :<b><?php echo $Num_Rows; ?></b>

<!--=================================================-->
 Records Per Page :<select name="perpg" id="perpg" onchange="a('1','perpg');">

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

  <table border="1" style="margin-top:10px"  width="100%">
<?php   
$sday1="select distinct(date) from Attendance where date between '".$from."' and '".$to ."'";
$rday1=mysqli_query($con1,$sday1);

?>
  <tr>
      
        <th>Sr</th>
    <!--<th>id</th>-->
    <th>EmployeeName</th>
    <!--<th>Surname</th>-->
    <th>empcode</th>
       <th>department</th>
     <th>branch</th>
<?php while($row4=mysqli_fetch_array($rday1))
{?>
 <th><?php echo date("d-m", strtotime($row4[0]) );;?></th>
 <?php }?>

  </tr>

  <?php 
   while($row = mysqli_fetch_array($qrys)) {
  
   ?>
  
 <tr style="background-color:#cfe8c7">
   <td><?php echo $sr;?></td>
    <td><?php echo $row["EmployeeName"];?></td>
   <td><?php echo $row["empcode"];?></td>
    <td><?php echo $row["department"];?></td>
    <td><?php echo $row["branch"];?></td>
   <?php
   $sday2="select distinct(date)  from Attendance where date between '".$from ."' and '".$to ."'";
	$rday2=mysqli_query($con1,$sday2);
     while($row2=mysqli_fetch_array($rday2)){
  
  $dataa="SELECT attendance  FROM Attendance WHERE date ='".$row2[0]."' and emp_id='".$row["id"]."'"; 

    $dataa1=mysqli_query($con1,$dataa);
   // echo $dataa;
   $fatchdata=mysqli_fetch_array($dataa1);?>
    <td> <? echo $fatchdata[0];?></td>
 <?php }?>
   </tr>	
  <?php
$sr++;
  ?>
<?php 
}
?>

</table>

 <?php 
 
if($Prev_Page) 
{
	echo " <center><a href=\"JavaScript:a('$Prev_Page','perpg')\"> << Back></center></a> ";
}

if($Page!=$Num_Pages)
{
	echo " <center><a href=\"JavaScript:a('$Next_Page','perpg')\">Next >></center></a> ";
}

?>
</form>


</body>
</html>







