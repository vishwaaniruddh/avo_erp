<html>
<head>
<title>Upload Purchase Order</title>
<link href="style.css" rel="stylesheet" type="text/css" />
<link href="datepicker/date_css.css" rel="stylesheet" type="text/css" />
<script src="datepicker/datepick_js.js" type="text/javascript"></script>

<script>

function validate1(form1){
 with(form1)
 {


}

 return true;
 }
</script>

<style>

</style>
</head>
<body>
<form name="form" action="process_pocopy_upload.php" method="post" enctype="multipart/form-data" onSubmit="return validate1(this)">

<div align="center" style="padding:10px">
    
<input type="hidden" name="po_id" value="<?php echo $_GET['poid']; ?>" >
<h3>Upload PO Copy</h3>
<?php
 include('config.php'); 
   $sql= mysqli_query($con1,"select po_no from purchase_order where id='".$_GET['poid']."'");
    $sql_result = mysqli_fetch_assoc($sql);
?>    

<table id="tab">
  
<tr>
  <td>PO Number:   </td>
  
 <td> <?php echo $sql_result['po_no'] ; ?>
  </td>
</tr>
<tr><td>&nbsp;</td><td>&nbsp;</td></tr>

<tr>
  <td> Upload PO File:</td>
  
  <td>
  <input type="file" name="pofile" id="pofile" required />
  </td>
</tr>

<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
<tr>
  <td colspan="2" align="center">
  <input type="submit" name="subs" value="submit" />
  </td>
</tr>

</table>
</div>
</form>
</body>