<? session_start();
include('config.php');

$select = $_POST['select_product'];
$po_id = $_POST['po_id'];
$po_qty = $_POST['po_qty'];



if(!$po_qty){
    
    foreach($select as $key => $value){
        $sql = mysqli_query($con1,"select * from po_assets where assettrack_id='".$value."'");
        $sql_result = mysqli_fetch_assoc($sql);
        $po_qty[]=$sql_result['qty'];
    }
}


$date = date('Y-m-d H:i:s');

$consignee_name = $_POST["consignee_name"];
$city = $_POST["city"];
$area = $_POST["area"];
$address = $_POST["address"];
$pincode = $_POST['pincode'];

$site_branch = $_POST["site_branch"];
$state = $_POST["state"];

$statesql=mysqli_query($con1,"select * from state where state_id='".$state."'");
$state_result = mysqli_fetch_assoc($statesql);
$state_name = $state_result['state'];

$contact_person_name = $_POST["contact_person_name"];
$contact_person_mobile = $_POST["contact_person_mobile"];
$email_to = $_POST["email_to"];
$DO_no=$_POST['do_no'];
$so_by = $_SESSION['logid'];
$inst_request=$_POST['is_install'];
$buyback = $_POST['buyback'];
$po_custid=$consignee;
$del_type = $_POST['del_type'];
$delivered_to = $_POST['delivered_to'];


$so_trackid = $_GET['id'];

if($so_trackid){
    $sales_sql = "update new_sales_order set branch_id = '".$delivered_to."',del_type='".$del_type."',bb_available='".$buyback."',inst_request='".$inst_request."',user_cont_name='".$contact_person_name."',user_cont_phone='".$contact_person_mobile."',user_mail='".$email_to."',DO_no='".$DO_no."' where so_trackid='".$so_trackid."'";
  //  echo $sales_sql. "</br>";
  mysqli_query($con1,$sales_sql);

 $atm_sql = "update demo_atm set bank_name='".$consignee_name."', area='".$area."', pincode= '".$pincode."', city= '".$city."', branch_id='".$site_branch."',  address='".$address."', DO_no='".$DO_no."', state='".$state_name."' where so_id='".$so_trackid."'";

     mysqli_query($con1,$atm_sql);



}


?>

<script>    
 <?php   if (mysqli_query($con1,$atm_sql)) { ?>
    alert("Sales order Updated successfully !! the sales id for your reference is <? echo $so_trackid;?>");
    <? } ?>
    setTimeout(function() { 
        window.location.href="view_SO.php?id=<? echo $so_trackid; ?>";
    }, 1500);
</script>