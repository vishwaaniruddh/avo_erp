<?php
session_start();
	include('config.php');

 	$id=$_POST['id'];


$target_dir = "pod_copy/";

$target_file = $target_dir . $_FILES['images']['name']; 

        $uploadOk = 1;

         if($_FILES['images']['size']<2000000) {         
          if (move_uploaded_file($_FILES["images"]["tmp_name"], $target_file)) {
                      
     $qry= mysqli_query($con1,"update so_order set pod_img='".$target_file."' where id='".$id."'");
       
       } } 
      else { echo " File size must be less than 2MB" ;}

                               	echo '<center><br><br><br>';
	if($qry){
	echo 'POD uploaded successfully.';
	}
	else
        echo 'Error Occurred. File size May be Big. Please <a href="pod_upload.php?id='.$id.'" >GO BACK</a> and try again.';
	echo '</center>';
 
 echo '<script> window.setTimeout("window.close()", 1000); </script>';
 
?>