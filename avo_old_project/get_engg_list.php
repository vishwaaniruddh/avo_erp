<?php
include("config.php");
$brid=$_GET['brid'];
?>

<select name="eng" id="eng" >
<option value="">--Select--</option>
<?php
$state=mysqli_query($con1,"select loginid, engg_name from area_engg where area='".$brid."' and status=1 and deleted=0 order by engg_name ASC");

while($name=mysqli_fetch_array($state))
{

?>
<option value="<?php echo $name[0]; ?>"><?php echo $name[1]; ?></option>
<?php
}
?>
</select>
