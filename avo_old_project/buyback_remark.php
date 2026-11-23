<?php include("access.php");
include("config.php");

$so_id=$_GET['id'];


 ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Add SO status</title>
<link href="style.css" rel="stylesheet" type="text/css" />

<!--datepicker-->
<link href="datepicker/date_css.css" rel="stylesheet" type="text/css" />
<script src="datepicker/datepick_js.js" type="text/javascript"></script>
<!--validation-->
<script>


function getXMLHttp()

{

  var xmlHttp

 //alert("hi1");

  try

  {

    //Firefox, Opera 8.0+, Safari
 xmlHttp = new XMLHttpRequest();
  }

  catch(e)
  {
    //Internet Explorer
    try
    {
      xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
    }
   catch(e)
    {
      try
      {
        xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
      }
      catch(e)
      {
        alert("Your browser does not support AJAX!")
       return false;
      }
   }
 }
  return xmlHttp;
}


function DDL_reason(){

var ddlReson=document.getElementById('ddl_reson').value;
 
document.getElementById('reason').innerHTML=ddlReson;

}


</script>
</head>



<body>


<center>
 
<?php // include("menubar.php");

 ?>

<table border="1" width="70%">
<thead>
<tr><th colspan="3" align="center"> <h2 style="text-align:center">Previous Update</h2> </th> </tr>
<tr>
<th>Updates</th>
<th>Date / Time</th>

</tr>
</thead>

<tbody>
<!--========PREVIOUS UPDATE DATA SHOW HERE ============================-->

<?php
   
	
	$qryfirst="SELECT * FROM `buyback_update` WHERE so_trackid='".$so_id."' order by datetime desc ";
	
	$tab=mysqli_query($con1,$qryfirst);
	   while( $rowup=mysqli_fetch_array($tab)){
	
 ?>
<tr>
<td><?php echo $rowup[2];?></td>
<td><?php echo date('d/m/Y h:i:s a',strtotime($rowup[4])); ?></td>
</tr>
<?php } ?>

<tr><th colspan="3" align="center"><h2>New Update</h2></th></tr>

<?php

?>
<div id="header">
<form action="buyback_remark_update.php" method="post" name="form">
<table>
    
<input type="hidden" name="so_id" value="<?php echo $so_id;?>" />

<tr>
<td height="35">Reason: </td>
<td>
  <textarea rows="4" cols="28" id="reason" name="reason" value=""></textarea></td></tr>
<tr>
<td height="35" colspan="2" align="center">

<input type="submit" value="submit" class="readbutton" />
</table>
 </div>
<form>
</center>
</body>
</html>