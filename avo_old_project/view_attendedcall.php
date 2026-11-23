<?php
session_start();
include("access.php");

include("db_connection.php");
$con1 = OpenCon1();


$brmain = isset($_SESSION['branch']) ? $_SESSION['branch'] : 'all';
//echo $brmain;
?>
<!DOCTYPE html
	PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="refresh" content="1200">
	<title>Attended Alerts</title>
	<link href="style.css" rel="stylesheet" type="text/css" />
	<link href="menu.css" rel="stylesheet" type="text/css" />
	<link href="popup.css" rel="stylesheet" type="text/css">
	<!--datepicker-->
	<link href="datepicker/date_css.css" rel="stylesheet" type="text/css" />
	<script src="datepicker/datepick_js.js" type="text/javascript"></script>
	<script src="popup.js" type="text/jscript" language="javascript"> </script>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>


	<script TYPE="text/javascript">
		function popup(mylink, windowname) { if (!window.focus) return true; var href; if (typeof (mylink) == 'string') href = mylink; else href = mylink.href; window.open(href, windowname, 'width=400,height=200,scrollbars=yes'); return false; } 
	</script>


	<script>
	
	function pick_engg(val){

brid=val;
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
    var s=xmlhttp.responseText;
  	//alert(s);
	 document.getElementById('mystate').innerHTML = s;	
    }
  }
   
   //	alert("get_engg_br.php?brid="+brid);    
	xmlhttp.open("GET","get_engg_list.php?brid="+brid,true);
	xmlhttp.send();
}           	
		///////////////////////////////search 
		function searchById(a, b, perpg) {
			var ppg = '';
			if (perpg == '' || !document.getElementById(perpg))
				ppg = '10';
			else
				ppg = document.getElementById(perpg).value;
				
			var searchEl = document.getElementById("search");
			if (searchEl) {
				searchEl.innerHTML = "<center><img src=loader.gif></center>";
			}
			HttPRequest = false;
			if (window.XMLHttpRequest) {
				HttPRequest = new XMLHttpRequest();
				if (HttPRequest.overrideMimeType) {
					HttPRequest.overrideMimeType('text/html');
				}
			} else if (window.ActiveXObject) {
				try {
					HttPRequest = new ActiveXObject("Msxml2.XMLHTTP");
				} catch (e) {
					try {
						HttPRequest = new ActiveXObject("Microsoft.XMLHTTP");
					} catch (e) { }
				}
			}

			if (!HttPRequest) {
				alert('Cannot create XMLHTTP instance');
				return false;
			}
		
			
			var branch_avo = document.getElementById('branch_avo') ? document.getElementById('branch_avo').value : '';

			var fromdt = '', todt = '', eng = '', complaintno = '';

			if (a != "Loading") {
			    var branch_avo = document.getElementById('branch_avo') ? document.getElementById('branch_avo').value : '';
				if (document.getElementById('fromdt')) fromdt = document.getElementById('fromdt').value;
				if (document.getElementById('todt')) todt = document.getElementById('todt').value;
			
				if (document.getElementById('eng')) eng = document.getElementById('eng').value;
			
				if (document.getElementById('branch_avo')) branch_avo = document.getElementById('branch_avo').value;
				if (document.getElementById('complaintno')) complaintno = document.getElementById('complaintno').value;
			}

			var url = 'search_attended_call.php';

			var pmeters = "";
			if (a != "Loading") {
				pmeters = 'Page=' + b + '&perpg=' + ppg + '&fromdt=' + fromdt + '&todt=' + todt + '&eng=' + eng + '&complaintno=' + complaintno + '&branch_avo=' + branch_avo;
			}
			else {
				pmeters = "Page=" + b + '&perpg=' + ppg ;
			}
			HttPRequest.open('POST', url, true);

			HttPRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			HttPRequest.setRequestHeader("Content-length", pmeters.length);
			HttPRequest.setRequestHeader("Connection", "close");
			HttPRequest.send(pmeters);

			HttPRequest.onreadystatechange = function () {
				if (HttPRequest.readyState == 4)
				{
					var response = HttPRequest.responseText;
					var targetEl = document.getElementById("search");
					if (targetEl) {
						targetEl.innerHTML = response;
					}
				}
			}
		}


		function newwin(url, winname, w, h) {
			//alert("hi");
			var left = (screen.width / 2) - (w / 2);
			var top = (screen.height / 2) - (h / 2);
			var targetWin = window.open(url, winname, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=1, resizable=yes, copyhistory=no, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
			//mywindow = window.open(url, winname, "location=400,status=1,scrollbars=1, width=500,height=400,left=350,top=200");

		}

		function newwindow() {
			var myChild = window.open('link.html', '', 'width=,height=,resizable=no');
			myChild.blur();
		}

		function runScript(e) {
			if (e.keyCode == 13) {
				searchById('Listing', '1', '');
			
			}
		}

	</script>


</head>

<body>

	<center>
		<?php
		
		include("menubar.php");

		?>

		<h2 class="h2color">View Attended Calls</h2>

		<div>

			<table cellpadding="" cellspacing="0">

				<tr>

					<th width="75"><input type="text" name="complaintno" id="complaintno"
							onkeypress="return runScript(event)" placeholder="ComplaintNo" /></th>
                 <th width="77">
						<?php
						$selbr = "select * from avo_branch where 1";
						if (isset($_SESSION['branch']) && $_SESSION['branch'] != 'all')
							$selbr .= " and id IN (" . $_SESSION['branch'] . ") ";

						$selbr .= " order by id ASC";
						//echo $selbr;
						$selbr2 = mysqli_query($con1, $selbr)
							?>
						<select name="branch_avo" id="branch_avo" onchange="pick_engg(this.value);">

							<?php if (!isset($_SESSION['branch']) || $_SESSION['branch'] == 'all') { ?>
								<option value="">Branch</option>
							<?php } ?>

							<?php

							while ($branch1 = mysqli_fetch_array($selbr2)) {
								?>
								<option value="<?php echo $branch1[0]; ?>"><?php echo $branch1[1]; ?></option>
								<?php
							}
							?>
						</select>
					</th>

					<th>
					<div id="mystate">
						<?php
						$engq = "select loginid, engg_name from area_engg where status='1'";
						if (isset($_SESSION['branch']) && $_SESSION['branch'] != 'all')
							$engq .= " and area  in(" . $brmain . ")";

						$engq .= " order by engg_name ASC";

						?>
						<select name="eng" id="eng" onchange="searchById('Listing','1','');" style="width:150px">
							<option value="">-select Engineer-</option><?php

							$eng = mysqli_query($con1, $engq);
							while ($engg = mysqli_fetch_array($eng)) {
								?>
								<option value="<?php echo $engg[0]; ?>"><?php echo $engg[1]; ?></option>
								<?php
							}

							?>
						
						</select> </div>
					</th>



					<th width="75"><input type="text" name="fromdt" id="fromdt" onkeypress="return runScript(event)"
							readonly="readonly" onclick="displayDatePicker('fromdt');" placeholder="From Date" /></th>

					<th width="75"><input type="text" name="todt" id="todt" onkeypress="return runScript(event)"
							readonly="readonly" onclick="displayDatePicker('todt');" placeholder="To Date" /></th>
                   <th width="75" rowspan="2"><input type="button" onclick="searchById('Listing','1','');"
							value="Search" /></th>
				

			</table>
		</div>
		<div id="search"></div>

	</center>
</body>

</html>
<?php
CloseCon($con1);
?>