<? include('config.php');
date_default_timezone_set("Asia/Calcutta"); 

$cust=$_GET["cust"];
$atmid=$_GET["atmid"];
$trackid=$_GET["trackid"];
$start_date= date('Y-m-d');




$sql = mysqli_query($con1,"select * from new_sales_order where so_trackid='".$trackid."'");
$sql_result = mysqli_fetch_assoc($sql);


 $inst_request = $sql_result['inst_request'];
 $del_type = $sql_result['del_type'];
 $po_id= $sql_result['po_trackid'];
 //========PO======
 
 $poname=mysqli_query($con1,"select po_no, po_date,contract_type from purchase_order where id='".$po_id."'");
 $po_name= mysqli_fetch_assoc($poname);
 $po_no1=$po_name['po_no'];
 $po_date=$po_name['po_date'];
 $contract_type=$po_name['contract_type'];

if($inst_request=='1' && $del_type =='site_del'){
    
    echo 'new Form and close';
}



if($inst_request=='0' && $del_type =='site_del' || $del_type =='opex' ){
    
    
     $demo_atm_sql = mysqli_query($con1,"select * from demo_atm where so_id='".$trackid."'");
        $demo_atm_sql_result = mysqli_fetch_assoc($demo_atm_sql);
        
        $atm_id = $demo_atm_sql_result['atm_id'];
        $cust_id = $demo_atm_sql_result['cust_id'];
$bank_name = mysqli_real_escape_string($con1,$demo_atm_sql_result['bank_name']);
        $area = mysqli_real_escape_string($con1,$demo_atm_sql_result['area']);
        
$pincode = mysqli_real_escape_string($con1,$demo_atm_sql_result['pincode']);
        $city = mysqli_real_escape_string($con1,$demo_atm_sql_result['city']);
        $branch_id = $demo_atm_sql_result['branch_id'];
$address = mysqli_real_escape_string($con1,$demo_atm_sql_result['address']);
        $state = $demo_atm_sql_result['state'];
        $start_date = date('Y-m-d');
    
    if($area=='') {$area='NULL'; }
    if($city=='') {$city='NULL'; }
    if($pincode=='') {$pincode='NULL'; }
   
    $atm_sql = mysqli_query($con1,"select * from atm where atm_id='".$atm_id."'");
    
    $atm_sql_result = mysqli_fetch_assoc($atm_sql);
mysqli_query($con1,"BEGIN");   
$errors=0;    
    if($atm_sql_result){
         $atm_last_id = $atm_sql_result['track_id'];

           $upquery = "update atm set cust_id='".$cust_id."' ,bank_name='".$bank_name."',area='".$area."',pincode='".$pincode."',city='".$city."',branch_id='".$branch_id."',start_date='".$start_date."',address='".$address."',po='".$po_no1."',podate='".$po_date."',state1='".$state."',so_id='".$trackid."',contract_type='".$contract_type."' where track_id='".$atm_last_id."'";
           $atmqry= mysqli_query($con1,$upquery);
        } else {
        $query = "insert into atm (atm_id,cust_id,bank_name,area,pincode,city,branch_id,start_date,address,po,podate,state1,so_id, site_type,contract_type) values('".$atm_id."','".$cust_id."','".$bank_name."','".$area."','".$pincode."','".$city."','".$branch_id."','".$start_date."','".$address."','".$po_no1."','".$po_date."','".$state."','".$trackid."', 'Non-Install','".$contract_type."')";
        //echo $query;
                
        $atmqry=mysqli_query($con1,$query);
        $atm_last_id = mysqli_insert_id($con1);    
    }
      
    if($atmqry){
           
       $sales_order_asset_sql = mysqli_query($con1,"select * from new_sales_order_asset where so_trackid='".$trackid."'");
        
            while($sales_order_asset_sql_result = mysqli_fetch_assoc($sales_order_asset_sql)){
            
            
            $po = $sales_order_asset_sql_result['po_trackid'];
            $asset_name = $sales_order_asset_sql_result['po_product'];
            $model = $sales_order_asset_sql_result['po_model'];
            $validity = $sales_order_asset_sql_result['po_warr'];
            $quantity = $sales_order_asset_sql_result['po_qty'];
    
   
            mysqli_query($con1,"INSERT INTO site_assets (cust_id, po, assets_name,assets_spec, valid, quantity, atmid, atm_trackid,so_id, start_date, po_date, type, status) VALUES ('".$cust."','".$po_no1."','".$asset_name."','".$model."','".$validity."','".$quantity."','".$atm_last_id."','".$atm_last_id."','".$trackid."','".$start_date."', '".$po_date."', 'Asset_add', '1')");
        }
     
     $updateso=mysqli_query($con1,"update so_order set status=2 ,call_status=2 where po_id='".$trackid."'");
     
   
//=================Asset Expiry insert  ===========        
     $sitedata=mysqli_query($con1,"select site_ass_id, valid, start_date from site_assets where so_id = '".$trackid."'"); 
 while($row=mysqli_fetch_row($sitedata)) {

 $d1=explode(',',$row[1]);
  $exp1=date('Y-m-d', strtotime("+$d1[0] months $start_date"));
  mysqli_query($con1,"update site_assets set exp_date='".$exp1."' where site_ass_id='".$row[0]."'");  
       }

    }  

      } elseif($del_type =='stock_trfr' || $del_type =='ware_del') {


$updateso=mysqli_query($con1,"update so_order set status=2 where po_id='".$trackid."'");  
    
} else {
 
 ?>
 
     <script type="text/javascript">
      alert('Delivery Type Not Proper !!!');
      window.location.href='new_invoices.php';
</script> 
    <? 
}
if($updateso) {
    mysqli_query($con1,"COMMIT");
?>
<script type="text/javascript">
    alert('Successfully Updated the data !!!');
    
    window.location.href='new_invoices.php';
</script>; 

 <?        
} 
else{
    mysqli_query($con1,"ROLLBACK");
       ?>
    
     <script type="text/javascript">
      alert('Some Error Try again !!!');
      window.location.href='new_invoices.php';
</script> 
    
    
    <? 
}

?>