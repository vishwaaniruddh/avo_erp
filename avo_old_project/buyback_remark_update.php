<?php
session_start();
include("config.php");

var_dump($_POST);

//die;

$so_id=$_POST['so_id'];
$var_update=$_POST['reason'];
$user=$_SESSION['user'];

//echo "insert into buyback_update(so_trackid,remarks,update_by,datetime ) values ('".$so_id."','".$var_update."' ,'".$user."','".date('Y-m-d H:i:s')."')";
//die;

$qryinsert=mysqli_query($con1,"insert into buyback_update(so_trackid,remarks,update_by,datetime ) values ('".$so_id."','".$var_update."' ,'".$user."','".date('Y-m-d H:i:s')."')");

if($qryinsert) {

?>
<script type="text/javascript">
alert("Data Updated !!");

window.close(); 
</script>

<?php } else {
?>
<script type="text/javascript">
alert("error!!");
window.close(); 
</script>
		
<?php
}
?>



