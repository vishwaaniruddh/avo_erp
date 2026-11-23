<?php include('config.php');
$sqlme=$_POST['qr'];

$sqlme=$sqlme." limit 0, 1500";;

function clean($string) {
   $string = str_replace(' ', ' ', $string); 
   $string = preg_replace('/[^A-Za-z0-9ĞİŞığşçö\-]/', ' ', $string); 

   return preg_replace('/-+/', '-', $string); 
}

function po_data($parameter,$po_id){
    global $con1;
    $sql = mysqli_query($con1,"select $parameter from purchase_order where id='".$po_id."'");
    
    $sql_result = mysqli_fetch_assoc($sql);
    $result = $sql_result[$parameter];
    
    return $result;
    
}

function get_branch($parameter,$id){
    global $con1;
    $sql = mysqli_query($con1,"select $parameter from avo_branch where id='".$id."'");
    $sql_result = mysqli_fetch_assoc($sql);
    $result = $sql_result[$parameter];
    return $result;
    
}


function atm_data($parameter,$so_id){
    global $con1;
    $sql = mysqli_query($con1,"select $parameter from demo_atm where so_id='".$so_id."'");
     $sql_result = mysqli_fetch_assoc($sql);
    $result = $sql_result[$parameter];
    return $result;
    
}
function get_cust_vertical_name($id){
    
    global $con1;
    
    $sql=mysqli_query($con1,"select * from customer where cust_id='".$id."'");
    
    $sql_result = mysqli_fetch_assoc($sql);
    
    $cust_name = $sql_result['cust_name'];
    
    return $cust_name;
}

function buyer_data($parameter,$buyer_id){
    global $con1;
    $sql = mysqli_query($con1,"select $parameter from buyer where buyer_ID='".$buyer_id."'");
    
    $sql_result = mysqli_fetch_assoc($sql);
    
    $result = $sql_result[$parameter];
        return $result;
    }
    
function last_update($parameter,$so_id){
    global $con1;
    $sql = mysqli_query($con1,"select $parameter from SO_Update where so_id='".$so_id."' and remarks_type='1' ORDER BY updt_id DESC LIMIT 1");
   
    $sql_result = mysqli_fetch_assoc($sql);
    
    $result = $sql_result[$parameter];
    
    return $result;
    }

    
function create_data($parameter,$create){
    global $con1;
    $sql = mysqli_query($con1,"select $parameter from login where srno='".$create."'");
    
    $sql_result = mysqli_fetch_assoc($sql);
    
    $result = $sql_result[$parameter];
        return $result;
    }
    
$table=mysqli_query($con1,$sqlme);

$contents='';
 $contents.="Sr no \t PO No \t PO Date  \t SO Date/time \t DO No. \t  Vertical/ Customer \t  Buyer Name/Address \t Buyer GST No. \t Delivery Type \t Delivery Scope\t End User/Consignee \t Site/Sol/ATM Id \t Billing Branch\t Delivery Branch\t Delivery/Consignee Address \t City \t State \t Pincode \t Sales Person \t Contact Person \t Contact No. \t SO Status\t So Raised By \t SO Last Remark\t Assets\t";


$i=1;


while($sql_result = mysqli_fetch_assoc($table)){
    

    $po_id = $sql_result['po_trackid'];
    $so_id = $sql_result['so_trackid'];
    $atm_id = $sql_result['atm_id'];
    $consignee = atm_data('bank_name',$so_id);
    $address = atm_data('address',$so_id);
    $customer_vertical = $sql_result['po_custid'];
    $buyer_id = $sql_result['buyerid'];
    $create = $sql_result['so_by'];
    
    
    $bill_branch = $sql_result['branch_id'];
    $del_branch = atm_data('branch_id',$so_id);
    
    //echo $so_id ;
   
  If ($sql_result['status'] == '1'){
      $status1 = "Pending";
} elseif ($sql_result['status'] == '2'){
      $status1 = "Closed";
} elseif ($sql_result['status'] == 'h'){
      $status1 = "On Hold";
} else $status1 = "Cancelled";  




    $contents.="\n".$i."\t";
    $contents.= po_data('po_no',$po_id)."\t";
    $contents.= po_data('po_date',$po_id)."\t";
    $contents.= $sql_result['so_time']."\t";
    $contents.= clean($sql_result['DO_no'])."\t";
    $contents.= get_cust_vertical_name($customer_vertical)."\t";
    $contents.= clean(buyer_data('buyer_name',$buyer_id)).'<br>'.trim(buyer_data('buyer_address',$buyer_id))."\t";
    $contents.= po_data('gst',$po_id)."\t";
    $contents.= $sql_result['del_type']."\t";
    
    $contents.= po_data('delivery_mode',$po_id)."\t";
    
    $contents.= $consignee."\t";
    $contents.= $sql_result['atm_id']."\t";
    
    
    $contents.= get_branch('name',$bill_branch)."\t";
    $contents.= get_branch('name',$del_branch)."\t";
 
    $contents.= clean($address)."\t";
    $contents.= atm_data('city',$so_id)."\t";
    $contents.= atm_data('state',$so_id)."\t";
    $contents.= atm_data('pincode',$so_id)."\t";
    
    //====================Sales Exec==
    
    $person_id=po_data('salesperson',$po_id);
    
    $executive_qry = mysqli_query($con2,"SELECT exe_name FROM salesteam where exe_id='".$person_id."'");
    $executive_name = mysqli_fetch_assoc($executive_qry);
    $name = $executive_name['exe_name'];
    $contents.= $name."\t";
   
    $contents.= $sql_result['user_cont_name']."\t";
    $contents.= $sql_result['user_cont_phone']."\t";
    $contents.= $status1."\t";
  //  $contents.= $sql_result['status']."\t";
    $contents.= create_data('username', $create)."\t";
    $contents.= clean(last_update('Remarks_update',$so_id))."\t";
 
 
 $tab=mysqli_query($con1,"SELECT * from new_sales_order_asset where so_trackid ='".$so_id."'");


$ii=1;
while($asset=mysqli_fetch_array($tab)){

$qrytest=mysqli_query($con1,"select name from assets_specification where ass_spc_id='".$asset[4]."'");	
$spc=mysqli_fetch_row($qrytest);

//$prod=$asset[3].', '.$spc[0].', '.$asset[5].', '.$asset[7];
$prod=$asset[3].', '.$spc[0].', '.$asset[5].', Rs:'.$asset[7]."Warr:".$asset[6];

$contents.=$prod."\t";

$ii++;
}

 
    $i++; 
}




$contents = strip_tags($contents); 


   header("Content-Disposition: attachment; filename=mis.xls");
  header("Content-Type: application/vnd.ms-excel");
  print $contents;
  
 
?>