<?php include('config.php');

$id=$_POST['id'];
$sub=$_POST['sub'];
$date = str_replace('/', '-', $_POST['sub']);
$del_date= date('Y-m-d', strtotime($date));

$sel=mysqli_query($con1,"select inv_date from so_order where id='".$id."'");
$row=mysqli_fetch_row($sel);
$inv_date=$row[0];
if($inv_date > $del_date) {
 echo '0';   
}
else {

$qry="update so_order set del_date=STR_TO_DATE('".$sub."','%d/%m/%Y') where id='".$id."'";


if(mysqli_query($con1,$qry)){
    
    echo '1';
}
else{
    echo '0';
}

}

?>