<? include('config.php');

$tkt = $_POST['tkt_no'];
$branch= $_POST['branch'];

$sql = mysqli_query($con1,"select alert_id from alert where createdby='".$tkt."' and branch_id='".$branch."'");
$sql_result=mysqli_fetch_assoc($sql);

//$delsql = mysqli_query($con1,"select engineer from alert_delegation where alert_id='".$sql_result['alert_id']."'");
//$result=mysqli_fetch_assoc($sql);

//$engsql = mysqli_query($con1,"select engg_name from area_engg where engg_id='".$result['engineer']."'");
//$enggrow=mysqli_fetch_assoc($sql);
//$engg=$enggrow[];
//==================

if($sql_result){
 echo 1; 

}
else{
    echo 0;
}

?>