<?php
include 'config.php';

$brid = $_GET['brid'];

?>
<select name="payment_term" id="payment_term" required>
<?php
$sql = mysqli_query($con1,"select * from po_payment_terms where contract_type='" . $brid . "' ");

while($stro=mysqli_fetch_array($sql))
{
?>
<option value="<?php echo $stro[2]; ?>"><?php echo $stro[2]; ?></option>
<?php
}
?>
</select>
