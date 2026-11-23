<?php
include("access.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>pod_upload</title>
<link href="style.css" rel="stylesheet" type="text/css" />
<link href="menu.css" rel="stylesheet" type="text/css" />

<!--validation-->
<script>
//////////////////////////////site type function

function validate(form){
 with(form)
 {

if(podfile.value.length < 1)
{
    alert("You Forgot to select the file to Export");
     return false;
}
 }
 return true;
 }
 

</script>

</head>

<body>
<center>
<?php 
include("config.php"); ?>

<h2>Upload POD</h2>
<div id="header">

<form action="process_pod_upload.php" method="post" enctype="multipart/form-data" onSubmit="return validate(this)" name="form">
<table>
<input type="hidden" name="id" value="<?php echo $_GET['id']; ?>" id="id" />
<tr>
<td width="216" height="35"><b>Select *PDF File to Export:</b></td>
<td width="521"><input type="file" name="images" value="" id="images" /></td>
</tr>


<tr>
<td height="35"  colspan="2"><input type="submit" value="submit" class="readbutton" /></td>
</tr>
</table>

</form>

</div>

</center>
</body>
</html>