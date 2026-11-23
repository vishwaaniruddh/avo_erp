<?php
session_start();
//include("access.php");
include("config.php");

$alert_id= $_POST['alert_id'];
$maxsize='5000';
$size=($_FILES["fsrfile"]["size"] / 1024);



if($size>$maxsize)
{ ?>
<script type="text/javascript">
	alert("File is too large. You can only upload 5MB. Please correct the file size");
	window.location='manual_fsr.php';
	</script> 
    
<?php   }  else {
    
$target_dir = "manual_fsr_25/";    

    
 //==========
$target_file = $target_dir.basename($_FILES['fsrfile']['name']);

if(move_uploaded_file($_FILES['fsrfile']['tmp_name'], $target_file)){		


$inst=mysqli_query($con1,"UPDATE alert set manual_fsr='".$target_file."' where alert_id='".$alert_id."'");	
}

}		


if($inst){
	?> 
	<script type="text/javascript">
	alert("Successfully Uploaded FSR");
	window.close();
	</script> 
	<?php
	} 

else {
    
    echo "</p>";
    echo '<pre>';
    echo 'Here is some more debugging info:';
    print_r($_FILES);
    print "</pre>";
}	
	
	
	?>
	