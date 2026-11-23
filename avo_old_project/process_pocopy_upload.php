<?php
session_start();
include('config.php');

$po_id= $_POST['po_id'];


	$target_dir = "po_copy/";

$target_file = $target_dir . $_FILES['pofile']['name']; 

        $uploadOk = 1;
//echo $po_id."---".$target_file; die;

    if(move_uploaded_file($_FILES["pofile"]["tmp_name"], $target_file)){   

//echo "UPDATE purchase_order set po_file='".$target_file."' where id='".$po_id."'"; die;

$qry=mysqli_query($con1,"UPDATE purchase_order set po_file='".$target_file."' where id='".$po_id."'");
          }
   
   	if($qry)
{

	echo '<script>
	alert("PO Copy uploaded successfully.");
	</script>';
	}
 	else{
        echo 'Error Occurred. Go Back and try again.';
	
 	} 
		
	?>