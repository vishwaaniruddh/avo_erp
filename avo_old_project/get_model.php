<?php
$product=$_GET['product'];
include('config.php');

?>

<select name="model" id="model" >
<option value="">--Select--</option>
<?php
$qry=mysqli_query($con1,"select * from assets_make where asset_id='".$product."' ");
while($stroo=mysqli_fetch_array($qry))
{
?>
<option value="<?php echo $stroo[0]; ?>"><?php echo $stroo[2]; ?></option>
<?php
}
?>
</select>
