<?php
session_start();
include("access.php");

include("db_connection.php");
$con1 = OpenCon1();


$brmain=$_SESSION['branch'];
//echo $brmain;
?>
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="refresh" content="1200" >
<title>Call Alerts</title>
<link href="style.css" rel="stylesheet" type="text/css" />
<link href="menu.css" rel="stylesheet" type="text/css" />
<link href="popup.css"  rel="stylesheet" type="text/css">
<!--datepicker-->
<link href="datepicker/date_css.css" rel="stylesheet" type="text/css" />
<script src="datepicker/datepick_js.js" type="text/javascript"></script>
<script src="popup.js" type="text/jscript" language="javascript"> </script>

<!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous"> -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script> 


<script TYPE="text/javascript"> 
function popup(mylink, windowname) { if (! window.focus)return true; var href; if (typeof(mylink) == 'string') href=mylink; else href=mylink.href; window.open(href, windowname, 'width=400,height=200,scrollbars=yes'); return false; } 
</script>


<script>

function confirm_generate(id,atm,cid,type)

{

 	if (confirm("Are you sure you want to Generate call ?"))
	{
		document.location="service_priority.php?id="+id+"&type="+type+"&atm="+atm+"&cid="+cid;
		
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
		
			 if(a!="Loading"){
			 	var id=document.getElementById('atmid').value;//alert(id);
			 	var area=document.getElementById('area').value;//alert(cid);
			 	var bank=document.getElementById('bank').value;//alert(bank);
			   	var complaintno=document.getElementById('complaintno').value;
			  }

			var url = 'view_search_call.php'; 
	
		    var pmeters="";
			if(a!="Loading"){ 
			 pmeters = 'atmid='+id+'&area='+area+'&bank='+bank+'&Page='+b+'&perpg='+ppg+'&complaintno='+complaintno;
		
			}
			else
			{
				 pmeters = "&Page="+b+'&perpg='+ppg;
			}
		//	alert(pmeters);
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
var targetWin = window.open (url, winname, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=1, resizable=yes, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);
  //mywindow = window.open(url, winname, "location=400,status=1,scrollbars=1, width=500,height=400,left=350,top=200");
 
 }
 
 function newwindow()  
{  
    var myChild= window.open('link.html','','width=,height=,resizable=no');  
    myChild.blur();
} 
 
 function runScript(e) {
    if (e.keyCode == 13) {
		searchById('Listing','1','');
       // alert('enter pressed');
        // document.getElementById('button').click();
    }
}
//===========Reopen Call============

function reopen_fun(Reopen_call){

if(confirm("Are you sure you want to Reopen this call ?"))
  {
    
    var xmlhttp;
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
//	 alert(xmlhttp.responseText);	
	 res=xmlhttp.responseText;
	 
	// alert(res);
        
       	if(res=="1"){
	alert("Call is Reopen successfull");
	}else{
	
	alert("Please try again...");
	}
    }
  }
  xmlhttp.open("GET","Reopen_call.php?id="+Reopen_call,true);
xmlhttp.send();
    
  }
}



</script>

  
</head>

<body>
<center>
<?php 
include("menubar.php");  
 ?>

<h2 class="h2color">Search Alerts</h2>

<div >

<table cellpadding="" cellspacing="0" >

  <tr>
  
    <th><input type="text" name="complaintno" id="complaintno" onkeypress="return runScript(event)" placeholder="Call Ticket No"/></th>
    <th width="75"><input type="text" name="atmid" id="atmid" onkeypress="return runScript(event)" placeholder="Site/Sol/ATM I'd"/></th>
    <th width="75"><input type="text" name="bank" id="bank" onkeypress="return runScript(event)" placeholder="End User"/></th>
    <th width="75"><input type="text" name="area" id="area" onkeypress="return runScript(event)" placeholder="Address"/></th>
    
    
    <th width="75" rowspan="2"><input type="button" onclick="searchById('Listing','1','');" value="Search" /></th>
  </tr>
  
</table>
</div>
<div id="search"></div>

</center>
</body>
</html>
<?php
 CloseCon($con1);
?>