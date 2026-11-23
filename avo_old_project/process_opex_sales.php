<?php
session_start();
	include('config.php');
	include("access.php");


$error=0;
// var_dump($_POST);
// die;

 	$po_id= $_POST['po_id'];
 	$invno=$_POST['invno'];
	$invval=$_POST['invval'];
	$bill_sites=$_POST['bill_sites'];

	$user=$_SESSION['user'];
	$entry=date('Y-m-d H:i:s');

$daten = str_replace('/', '-', $_POST['date']);
$date= date('Y-m-d', strtotime($daten));

$start1 = str_replace('/', '-', $_POST['start']);
$start= date('Y-m-d', strtotime($start1));

$exp1 = str_replace('/', '-', $_POST['expiry']);
$expiry= date('Y-m-d', strtotime($exp1));

	$target_dir = "opex_invoices/";
      $target_file = $target_dir . $po_id . $_FILES['invfile']['name']; 
      //  echo "file:".$target_file ;

        $uploadOk = 1;
//======
echo "insert into opex_bills(po_id,inv_no,inv_date,inv_value,bill_start,bill_end,created_by,time, billed_sites) values('".$po_id."','".$invno."','".$date."','".$invval."','".$start."','".$expiry."','".$user."','".$entry."', '".$bill_sites."' )";

//die;

   $qry=mysqli_query($con1,"insert into opex_bills(po_id,inv_no,inv_date,inv_value,bill_start,bill_end,created_by,time, billed_sites) values('".$po_id."','".$invno."','".$date."','".$invval."','".$start."','".$expiry."','".$user."','".$entry."', '".$bill_sites."' )");
          
     $lid=mysqli_insert_id($con1);  
     
      
         if(move_uploaded_file($_FILES["invfile"]["tmp_name"], $target_file)){          
          mysqli_query($con1,"UPDATE opex_bills set inv_file='".$target_file."'where id='".$lid."'");
          }
    
	if(!$qry)
{
$error++;
}

if($error=="0")
{
	echo '<script>
	alert("Bill Added successfully.");
	window.opener.location.reload();
	self.close();

		</script>';

	}
 	else{
        echo 'Error Occurred. Please <a href="opex_inv_upload.php?id='.$po_id.'" >GO BACK</a> and try again.';
	
 	}
	echo '</center>';
	?>