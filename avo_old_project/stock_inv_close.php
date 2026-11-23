<? include('config.php');

$so_id=$_GET["so_id"];

mysqli_query($con1,"BEGIN");   

$updateso=mysqli_query($con1,"update so_order set status=2 where po_id='".$so_id."'");  
    

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