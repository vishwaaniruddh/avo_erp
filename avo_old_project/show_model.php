<?php
$product=$_GET['product'];
include('config.php');

$qry=mysqli_query($con1,"select * from assets_make where asset_id='".$product."' ");
while($stroo=mysqli_fetch_array($qry))
{
?>
<td> <?php echo $stroo[2]; } ?> </td>
