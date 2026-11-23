<?php
include("access.php");
?>
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="refresh" content="1200" >
<title>AVOUPS-<?php echo $_SESSION['user']; ?></title>
<link href="style.css" rel="stylesheet" type="text/css" />
<link href="menu.css" rel="stylesheet" type="text/css" />
<link href="popup.css"  rel="stylesheet" type="text/css">
<!--datepicker-->
<link href="datepicker/date_css.css" rel="stylesheet" type="text/css" />
<script src="datepicker/datepick_js.js" type="text/javascript"></script>
<script src="popup.js" type="text/jscript" language="javascript"> </script>


<script type="text/javascript">


///////////////////////////////search 
function searchById(a,b,perpg) {
//alert(a+" "+b+" "+perpg);
var ppg='';
if(perpg=='')
ppg='25';
else
ppg=document.getElementById(perpg).value;

//alert(ppg);
document.getElementById("search").innerHTML ="<center><img src=../loader.gif></center>";

		  HttPRequest = false;
		  if (window.XMLHttpRequest) { // Mozilla, Safari,...
			 HttPRequest = new XMLHttpRequest();
			 if (HttPRequest.overrideMimeType) {
				HttPRequest.overrideMimeType('text/html');
			 }
		  } else if (window.ActiveXObject) { // IE
			 try {
				HttPRequest = new ActiveXObject("Msxml2.XMLHTTP");
			 } catch (e) {
				try {
				   HttPRequest = new ActiveXObject("Microsoft.XMLHTTP");
				} catch (e) {}
			 }
		  } 
 
		  if (!HttPRequest) {
			 alert('Cannot create XMLHTTP instance');
			 return false;
		  }
		
			 
			 var status=document.getElementById('status').value;
			 
			 if(a!="Loading"){
			var cid=document.getElementById('cid').value;
			 var status=document.getElementById('status').value;//alert(city);
			 var invno=document.getElementById('invno').value;//alert(invno);
			 var invdate=document.getElementById('invdate').value;//alert(invdate);
			 var state=document.getElementById('state').value; //alert(state);
			 var sdate=document.getElementById('sdate').value;//alert(sdate);
			 var edate=document.getElementById('edate').value;//alert(edate);
			 
			 }
			var url = 'search_pod_upload_status.php';
		var pmeters="";
		//	alert(url);
			if(a!="Loading"){ 
 			pmeters = 'status='+status+'&cid='+cid+'&state='+state+'&invno='+invno+'&invdate='+invdate+'&Page='+b+'&perpg='+ppg+'&sdate='+sdate+'&edate='+edate;
			}
			else
			{
			pmeters = 'Page='+b+'&perpg='+ppg+'&status='+status;	
			}
			//alert(pmeters);
			HttPRequest.open('POST',url,true);

			HttPRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			HttPRequest.setRequestHeader("Content-length", pmeters.length);
			HttPRequest.setRequestHeader("Connection", "close");
			HttPRequest.send(pmeters);
 
//alert("gg"); 
			HttPRequest.onreadystatechange = function()
			{
 
				 if(HttPRequest.readyState == 4) // Return Request
				  {
		var response = HttPRequest.responseText;
 
 //alert(response);
				  document.getElementById("search").innerHTML = response;
			  }
		}
  }
 
function newwin(url,winname,w,h)
{
//alert("hi");
var left = (screen.width/2)-(w/2);
var top = (screen.height/2)-(h/2);

 }
 

</script>

</head>

<body>
<center>

<?php  if($_SESSION['designation']==5){
            include("AccountManager/menubar.php");
        } else{
          include("menubar.php");  
        } ?>

<h2 class="h2color">Proof of Delivery Upload status</h2>
<h4 class="h6color">Effect from 09-03-2026</h4>

<div>

<table  border="0" cellpadding="0" cellspacing="0">

<tr><th>Upload Status</th><th>Invoice No</th><th>Invoice Date</th><th>Customer Vertical</th><th>Branch</th><th>From Bill Date</th><th>To Bill date</th><th>Search</th></tr>

<tr>

<th width="75">

<select name="status" id="status" >
<option value="pending">-Pending-</option>
<option value="completed">-Completed-</option>
</select>
</th>



<th><input type="text" name="invno" id="invno"/></th>
<th width="75">
<input type="text" name="invdate" id="invdate"  readonly="readonly" onclick="displayDatePicker('invdate')"; placeholder="Inv Date"/></th>


 <?  
    $client="select cust_id,cust_name from customer where 1";
    if($_SESSION['designation']==5){
   
    $cust=mysqli_query($con1,"select client from clienthandle where logid= (select srno from login where username='".$_SESSION['user']."')");
    $cc=array();
    while($custr=mysqli_fetch_array($cust))
    $cc[]=$custr[0];
    
    $ccl=implode(",",$cc);
    $ccl=str_replace(",","','",$ccl);
    $ccl="'".$ccl."'";
    $client.=" and cust_name in($ccl)";
    
    }
    $client.=" order by cust_name ASC";
    //echo $client;
    ?>
   
    <th>
         <select name="cid" id="cid" onchange="searchById('Listing','1','');"> 
   <?php if($_SESSION['designation']!=5){ ?>
   
    <option value="">Select Client</option> <?php } ?>
<?php
$cl=mysqli_query($con1,$client);
while($clro=mysqli_fetch_row($cl))
{
?>
<option value="<?php echo $clro[0]; ?>"><?php echo $clro[1]; ?></option>
<?php
}
?></select></th>

<th width="75">

<select name="state" id="state" >
<option value="">--Select Branch--</option>
<?php 
$sql=mysqli_query($con1,"select * from avo_branch");
while($sql1=mysqli_fetch_row($sql)){

?>
<option value="<?php echo $sql1[0];?>" ><?php echo $sql1[1];?></option>
<?php } ?>
</select>
</th>


<th width="75">
<input type="text" name="sdate" id="sdate"  readonly="readonly" onclick="displayDatePicker('sdate')"; placeholder="From Bill Date"/></th>


<th width="75"><input type="text" name="edate" id="edate"  readonly="readonly" onclick="displayDatePicker('edate')"; placeholder="To Bill Date"/></th>
<th><input type="button" onclick="searchById('Listing','1','');" value="Search" /></th>
</tr>
</table>
</div>

<div id="search"> </div>

</center>
</body>
</html>