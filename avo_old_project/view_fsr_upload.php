<?php
include("access.php");

//echo $_SESSION['branch']; 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Inst Report Upload</title>
<link href="style.css" rel="stylesheet" type="text/css" />
<link href="menu.css" rel="stylesheet" type="text/css" />
<link href="popup.css"  rel="stylesheet" type="text/css">
<!--datepicker-->
<link href="datepicker/date_css.css" rel="stylesheet" type="text/css" />
<script src="datepicker/datepick_js.js" type="text/javascript"></script>
<script src="popup.js" type="text/jscript" language="javascript"> </script>
<script>

function runScript(e) {
    if (e.keyCode == 13) {
		searchById('Listing','1','');
       // alert('enter pressed');
        // document.getElementById('button').click();
    }
}

///////////////////////////////search 
function searchById(a,b,perpg) {
//alert(a+" "+b+" "+perpg);
var ppg='';
if(perpg=='')
ppg='10';
else
ppg=document.getElementById(perpg).value;
document.getElementById("search").innerHTML ="<center><img src=loader.gif></center>";
//alert("hi");
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
			 var id=document.getElementById('atmid').value;//alert(id);
			 var fromdt=document.getElementById('fromdt').value;
			 var bank=document.getElementById('bank').value;//alert(area);
			 var todt=document.getElementById('todt').value;
			 var status=document.getElementById('status').value;
			 
			  }
			  
			var url = 'search_fsr_upload.php'; 
	//	alert(url);
		    var pmeters="";
			
			if(a!="Loading"){ 
			 pmeters = 'atmid='+id+'&bank='+bank+'&Page='+b+"&status="+status+'&perpg='+ppg+'&fromdt='+fromdt+'&todt='+todt;
			// alert(pmeters);
			}
			else
			{
				 pmeters = "Page="+b+"&status="+status+'&perpg='+ppg;
		//	alert(pmeters);	 
			}
	HttPRequest.open('POST',url,true);

			HttPRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			HttPRequest.setRequestHeader("Content-length", pmeters.length);
			HttPRequest.setRequestHeader("Connection", "close");
			HttPRequest.send(pmeters);
 
//alert(pmeters); 
			HttPRequest.onreadystatechange = function()
			{
				 if(HttPRequest.readyState == 4) // Return Request
				  {
		var response = HttPRequest.responseText;
 
// alert(response);
				  document.getElementById("search").innerHTML = response;
			  }
		}
  }

</script>
</head>

<body onload="searchById('Listing','1','')">
<center>
<?php include("menubar.php"); ?>

<h2 class="h2color"> Service Report Upload Status</h2>

<table cellpadding="" cellspacing="0" width="100%" >
     <tr> 
     <?php if($_SESSION['branch']=='' or $_SESSION['branch']=='all') { ?>
     <th><select name="branch" id="branch" onchange="searchById('Listing','1','');">
      <option value="">Select Branch</option>
     <?php $brqry=mysqli_query($con1, "select id, name from avo_branch");
     while($brrow=mysqli_fetch_row($brqry)){  ?>
     
      <option value="<?php echo $brrow[0]; ?>"><?php echo $brrow[1]; ?></option>
      <?php } ?>
     </select></th>
    <?php } ?>
    
     <th><select name="status" id="status" onchange="searchById('Listing','1','');">
      <option value="Pending">Pending</option>
      <option value="Completed">Completed</option>
     </select></th>
   
    <th><input type="text" name="atmid" id="atmid" onkeypress="return runScript(event)" placeholder="Site id"/></th><!--4-->
 
    <th ><input type="text" name="bank" id="bank" onkeypress="return runScript(event)" placeholder="Bank"/></th><!--5-->
    
    <th ><input type="text" name="fromdt" id="fromdt" onkeypress="return runScript(event)" readonly="readonly" onclick="displayDatePicker('fromdt');" placeholder="From Date"/> </th ><!--7-->
    <th > <input type="text" name="todt" id="todt" onkeypress="return runScript(event)"  readonly="readonly" onclick="displayDatePicker('todt');" placeholder="To Date"/> </th ><!--8-->
    <th > <input type="button" onclick="searchById('Listing','1','');" value="Search" /></th><!--9-->
  
  </tr>
  
  <tr>
    
  </tr>
</table>
<div id="search">
</div>
</center>
</body>
</html>