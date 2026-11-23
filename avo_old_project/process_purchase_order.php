<?php session_start();
include("functions.php");
date_default_timezone_set("Asia/Calcutta"); 

//include("Whatsapp_delegation/delegation_fun.php");

$userid = $_SESSION['logid'];
$branchid = $_SESSION['branchid'];
    $id = $_GET['id'];

//var_dump($_POST); die;

function get_customer_address($id){
     global $con1;
    
    $sql = mysqli_query($con1,"select * from buyer where buyer_ID='".$id."'");
    
    $sql_result=mysqli_fetch_assoc($sql);
    
    return $sql_result['buyer_address'];
}

function get_gst($id){
     global $con1;
    
    $sql = mysqli_query($con1,"select * from buyer where buyer_ID='".$id."'");
    
    $sql_result=mysqli_fetch_assoc($sql);
    
    return $sql_result['buyer_gst'];
}


function get_asset_name($id){
     global $con1;
    
    $sql = mysqli_query($con1,"select * from assets where assets_id='".$id."'");
    $sql_result = mysqli_fetch_assoc($sql);
    
    return $sql_result['assets_name'];
}
$date = date('Y-m-d h:i:s');

$only_date = date('Y-m-d');


if(isset($_GET['action']) && $_GET['action']=='delete'){
     global $con1;
    
    $id = $_GET['id'];
    $sql=mysqli_query($con1,"update purchase_order set po_status = 9 where id ='".$id."' ");
    if($sql){
        echo '<script>alert("Record deleted successfully");window.location.href="view_purchase_order.php"</script>';
    } 
} else {
   
$raiseby = $_SESSION['logid'];

//echo '<pre>';print_r($_POST);echo '</pre>';

$buyer_id = $_POST['buyerID'];
$customer_vertical = $_POST['customer_vertical']; 
$sales_exe_name = $_POST['sales_exe_name'];
//==================instead of======
//$modeOfSale = $_POST['modeOfSale']; 
$del_type = $_POST['del_type'];

$po_number = $_POST['po_number'];
$po_date = $_POST['po_date']; 
//============================================
$contract_type = $_POST['contract_type']; 


$warr_type = $_POST['warr_type'];
$product = $_POST['product'];

$make = $_POST['make'];
$model = $_POST['model'];
// $capacity = $_POST['capacity']; 
$quantity = $_POST['quantity'];
$basic_Price = $_POST['price'];

$warranty = $_POST['warranty'];
$delivery_mode = $_POST['delivery_mode']; 
$other_charges = $_POST['other_charges'];
$payment_term = $_POST['payment_term'];
$tat = $_POST['tat'];
$remarks =$_POST['remarks'];

//$whatsno=$_POST['whatsno'];

//======================= OAN
/*$qrr=mysqli_query($con1,"select * from purchase_order where po_time LIKE ('".date('Y-m-d')."%')");
	$num=mysqli_num_rows($qrr);
	$num2=$num+1;
	if($num2>0 && $num2<=9)
	$num3="0".$num2;
	else
	$num3=$num2; */

//====================

if($_GET['action']=='edit' && $_GET['id']>0){

function get_assets_name($id){
    global $con1;
    
    $sql = mysqli_query($con1,"select * from assets where assets_id='".$id."'");
    $sql_result = mysqli_fetch_assoc($sql);
    
    return $sql_result['assets_name'];
}

$i=0;
    $assets_sql = mysqli_query($con1,"select * from po_assets where po_trackid='".$id."'");
    while($assets_sql_result = mysqli_fetch_assoc($assets_sql)){
        
        $assets_name = get_assets_name($product[$i]);
        $assettrack_id = $assets_sql_result['assettrack_id'];
        
        mysqli_query($con1,"update po_assets set assets_name = '".$assets_name."', specs='".$model[$i]."',qty='".$quantity[$i]."',warranty='".$warranty[$i]."',rate='".$basic_Price[$i]."' where assettrack_id='".$assettrack_id."'");
        
        $i++;
    }


     $sql=mysqli_query($con1,"update purchase_order set cust_id='".$customer_vertical."',buyer_id='".$buyer_id."' ,po_raiseby= '".$raiseby."',del_type='".$del_type."',po_tat='".$tat."',po_payment='".$payment_term."',salesperson='".$sales_exe_name."',po_remarks='".$remarks."',warr_type='".$warr_type."',delivery_mode='".$delivery_mode."', other_charge='".$other_charges."' where id='".$id."' ");
     
         ?>
         
         
         <script>
            alert("PO Succefully updated");
            window.location.href="view_purchase_order.php"
            
            </script>';
<? 
} 
else {
    
 mysqli_query($con1,"BEGIN");   
   $errors=0;

    $sql_insert = mysqli_query($con1,"insert into  purchase_order (po_no,cust_id,buyer_id,po_raiseby,del_type,po_tat,po_payment,salesperson,po_remarks,po_status,warr_type,other_charge,delivery_mode,buyeraddress,gst,po_time,po_date,branch_id,created_by, contract_type,po_file)  values('".$po_number."','".$customer_vertical."','".$buyer_id."','".$raiseby."','".$del_type."','".$tat."','".$payment_term."','".$sales_exe_name."','".$remarks."','1','".$warr_type."','".$other_charges."','".$delivery_mode."','".get_customer_address($buyer_id)."','".get_gst($buyer_id)."','".$date."','".$po_date."','".$branchid."','".$userid."','".$contract_type."','')") ;
    

    $insert_id = mysqli_insert_id($con1);
    
 if(!$sql_insert) {
     $errors++;
     $reason="PO Table insert error";
 }  
    
    $product_count = count($_POST['product']);

    for($i=0;$i<$product_count;$i++){
//echo "insert into  po_assets (po_no,assets_name,qty,warranty,rate,po_trackid,po_product,specs) values('".$po_number."','".get_asset_name($product[$i])."','".$quantity[$i]."','".$warranty[$i]."','".$basic_Price[$i]."','".$insert_id."','".$product[$i]."','".$model[$i]."') </br>" ;

        $sql_asset = mysqli_query($con1,"insert into  po_assets (po_no,assets_name,qty,warranty,rate,po_trackid,po_product,specs) values('".$po_number."','".get_asset_name($product[$i])."','".$quantity[$i]."','".$warranty[$i]."','".$basic_Price[$i]."','".$insert_id."','".$product[$i]."','".$model[$i]."')") ;
    }    

 if(!$sql_asset) {
     $errors++;
     $reason="PO Assets Not Inserted";
 }  

}
}
if($errors>0)
{
    mysqli_query($con1,"ROLLBACK");
die('Failed Reason:<br>'.$reason."<br>".mysqli_error($con1));
}
 else 
{
    mysqli_query($con1,"COMMIT");
?>
<script>
    alert("PO Added successfully !! the OAN No: <? echo $insert_id;?>");
    
    setTimeout(function() { 
       
      window.location.href="view_purchase_order.php";
    
    }, 1500);
    
</script>

<?php }  ?>