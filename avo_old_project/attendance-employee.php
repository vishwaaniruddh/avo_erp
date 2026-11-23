<?php 

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

	session_start();	
	include('includes/dbconnect.php');

	if(!isset($_SESSION['username'])){
	header('location: index.php');
	}
	
	
 ?>

<?php
$date = date_default_timezone_set('Asia/Kolkata');
$datemonthyeartime = date("F j, Y, g:i a T");
$indiacurrentdate = date("F j, Y");
$indiacurrenttime = date("g:i a");
?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
        <meta name="description" content=" Payroll Software">
		<meta name="keywords" content="admin, estimates, business, corporate, creative, management, minimal, modern, accounts, invoice, html5, responsive, CRM, Projects">
        <meta name="author" content="Payroll System">
        <meta name="robots" content="noindex, nofollow">
        <title>Attendance - Payroll System</title>
		
		<!-- Favicon -->
        <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png">
		
		<!-- Bootstrap CSS -->
        <link rel="stylesheet" href="assets/css/bootstrap.min.css">
		
		<!-- Fontawesome CSS -->
        <link rel="stylesheet" href="assets/css/font-awesome.min.css">
		
		<!-- Lineawesome CSS -->
		<link rel="stylesheet" href="assets/css/line-awesome.min.css">
		
		<!-- Select2 CSS -->
		<link rel="stylesheet" href="assets/css/select2.min.css">
		
		<!-- Datetimepicker CSS -->
		<link rel="stylesheet" href="assets/css/bootstrap-datetimepicker.min.css">
		
		<!-- Main Stylesheet -->
        <link rel="stylesheet" href="assets/css/style.css">
		
	<style>
		
		#page-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5); /* Semi-transparent black */
        z-index: 9999; /* Ensure it stays on top */
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-align: center;
        }
        
        /* Simple CSS Spinner */
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin-bottom: 10px;
        }
        
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

	</style>
		

    </head>
    

<body onload="myFunction()">


<!-- Main Wrapper -->
        <div class="main-wrapper">
		
			<!-- Header -->
            <?php include_once("includes/header.php");?>
			<!-- /Header -->
			
			<!-- Sidebar -->
            <?php include_once("includes/sidebar.php");?>
			<!-- /Sidebar -->
			
			<!-- Page Wrapper -->
            <div class="page-wrapper">
			
				<!-- Page Content -->
                <div class="content container-fluid">

				
					<!-- Page Header -->
					<div class="page-header">
						<div class="row">
							<div class="col-sm-12">
								<h3 class="page-title">Attendance</h3>
								<ul class="breadcrumb">
									<li class="breadcrumb-item"><a href="index.html">Dashboard</a></li>
									<li class="breadcrumb-item active">Attendance</li>
								</ul>
							</div>
							
						</div>
					</div>
					<!-- /Page Header -->


					
					<div class="row">
						<div class="col-md-4">
							<div class="card punch-status">
								<div class="card-body">



  
  <?php
    $user_name = $_SESSION['username'];
    $duplicate = mysqli_query($conn,"SELECT * FROM employees WHERE UserName='$user_name'");
    $stmt = $conn->prepare("SELECT * FROM employees WHERE UserName = ?");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $emp_id = $row['id'];
            $emp_name = $row['emp_name'];
            $emp_picture = $row['Picture'];
            $com_id = $row['com_id'];
            $com_name = $row['com_name'];
            $att_location = $row['att_location'];
            $shift_duty = $row['shift_name'];
            $emp_ipaddress = "0";
        }
    }
    $stmt->close();
    
    $stmt = $conn->prepare("SELECT com_latitude, com_longitude FROM clients WHERE id = ?");
    $stmt->bind_param("i", $com_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row4 = $result->fetch_assoc();
        $com_latitude = $row4['com_latitude'];
        $com_longitude = $row4['com_longitude'];
    }
    $stmt->close();

    
  //Add Intime to employees from shift master
    $stmt = $conn->prepare("SELECT * FROM shift_master WHERE shift_name = ?");
    $stmt->bind_param("s", $shift_duty);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row3 = $result->fetch_assoc();
        $shift_intime = $row3['in_time'];
        $shift_outtime = $row3['out_time'];
        $lgrace_time = $row3['grace_time'];
    } else {
        $shift_intime = '10:00:00';
        $shift_outtime = '19:00:00';
        $lgrace_time = '10:00:00';
    }
    $stmt->close();
	
	// Office In Time Calculation
    $orunning_time1 = strtotime($shift_intime);
    $orunning_time2 = date('H:i:s', $orunning_time1);
    $orunning_time3 = date("Y/m/d");
    $orunning_time = date('Y-m-d H:i:s', strtotime("$orunning_time3 $orunning_time2"));
    
    // Office Out Time Calculation
    $orunning_outtime1 = strtotime($shift_outtime);
    $orunning_outtime2 = date('H:i:s', $orunning_outtime1);
    $orunning_outtime3 = date("Y/m/d");
    $orunning_outtime = date('Y-m-d H:i:s', strtotime("$orunning_outtime3 $orunning_outtime2"));
    
   
  ?>
  
  
  <?php
    $datetime = date_create()->format('Y-m-d');
    $stmt = $conn->prepare("SELECT status, total_time FROM inout_attendence WHERE emp_id=? AND d_date=? AND com_id=?");
    $stmt->bind_param("isi", $emp_id, $datetime, $com_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row1 = $result->fetch_assoc();
        $pending_status = $row1['status'];
        $total_time = $row1['total_time'];
    } else {
        $pending_status = "";
        $total_time = 0;
    }
    $stmt->close();
  ?>
  

										<input type="hidden" name="status" id="status" value="<?php echo $pending_status;?>">
										<input type="hidden" name="orunning_time" id="orunning_time" value="<?php echo $orunning_time;?>">
										<input type="hidden" name="orunning_outtime" id="orunning_outtime" value="<?php echo $orunning_outtime;?>">
										<input type="hidden" name="user_id" id="user_id" value="<?php echo $emp_id;?>">
										<input type="hidden" name="user_name" id="user_name" value="<?php echo $emp_name;?>">
										<input type="hidden" name="com_id" id="com_id" value="<?php echo $com_id;?>">
										<input type="hidden" name="com_name" id="com_name" value="<?php echo $com_name;?>">
										<input type="hidden" name="emp_shiftduty" id="emp_shiftduty" value="<?php echo $shift_duty ;?>">
										<input type="hidden" name="att_location" id="att_location" value="<?php echo $att_location;?>">
										<input type="hidden" name="emp_ipaddress" id="emp_ipaddress" value="<?php echo $emp_ipaddress;?>">
										<input type="hidden" name="com_latitude" id="com_latitude" value="<?php echo $com_latitude;?>">
										<input type="hidden" name="com_longitude" id="com_longitude" value="<?php echo $com_longitude;?>">
										
									<h5 class="card-title">Timesheet <small class="text-muted"><?php echo $indiacurrentdate; ?></small></h5>
									<div class="punch-det">
										<h6>Punch In at</h6>
										<p><?php echo $indiacurrentdate; ?></p>
									</div>
									<div class="punch-info">										
										<input style="text-align:center;" class="punch-hours" type="text" name="running_time" id="mytime" readonly>										
									</div>
									
									<div class="punch-btn-section">
									    <label for="submit" id = "GFG" class="btn btn-primary punch-btn">Punch In</label>
								        <input type="file" name="submit" id="submit" value="Punch In" style="display: none; accept="image/*" capture="camera"">
										<video id="video" width="320" height="240" autoplay hidden></video>
                                        <canvas id="canvas" width="320" height="240" hidden></canvas>
                                        <button id="attendanceBtn" hidden>Mark Attendance</button>
                                        <!-- The Overlay -->
                                        <div id="page-overlay" style="display: none;">
                                            <div class="loader-content">
                                                <div class="spinner"></div>
                                                <p>Processing, please wait...</p>
                                            </div>
                                        </div>
									</div>
									
									
									<div class="statistics">
										<div class="row">
											<div class="col-md-6 col-6 text-center">
												<div class="stats-box">
													<p>Break</p>
													<h6>1.00 hrs</h6>													
												</div>
											</div>
											<div class="col-md-6 col-6 text-center">
												<div class="stats-box">
													<p>Tiffin</p>
													<h6>15 Mins</h6>
												</div>
											</div>
										</div>
									</div>
								</div>
								</form>
							</div>
						</div>
						
						<div class="col-md-4">
							<div class="card recent-activity">
								<div class="card-body">


								<?php
								
        							$stmt = $conn->prepare("SELECT * FROM inout_attendence WHERE emp_id=? AND d_date=?");
                                    $stmt->bind_param("is", $emp_id, $datetime);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $in_time = $row['intime'];
                                            $in_time_cal = $row['intime_mandatory'];
                                            $newin_time = date('h:i A', strtotime($in_time));
                                    
                                            $out_time = $row['outtime'];
                                            $newout_time = $out_time ? date('h:i A', strtotime($out_time)) : "";
                                    
                                            $total_hours = $row['total_time'];
                                            $pdays = $row['present_days'];
                                    
                                            if ($pdays == 'P') $present_days = 'Present';
                                            elseif ($pdays == 'L') $present_days = 'Late';
                                            elseif ($pdays == 'A') $present_days = 'Absent';
                                            else $present_days = '';
                                        }
                                    } else {
                                        $newin_time = "";
                                        $newout_time = "";
                                        $total_hours = "";
                                        $present_days = "";
                                        $in_time_cal = "";
                                    }
                                    $stmt->close();

									?>

									<h5 class="card-title">Today Activity</h5>
									<ul class="res-activity-list">
										<li>
											<p class="mb-0">Punch In at</p>
											<p class="res-activity-time">
												<i class="fa fa-clock-o"></i>
												<p><?php echo $newin_time;?></p>
												<input type="hidden" name="intime" id="intime" value="<?php echo $in_time_cal;?>">
												
											</p>
										</li>
										<li>
											<p class="mb-0">Punch Out at</p>
											<p class="res-activity-time">
												<i class="fa fa-clock-o"></i>
												<p><?php echo $newout_time;?></p>
											</p>
										</li>
										
									
										<li>
											<p class="mb-0">Status</p>
											<p class="res-activity-time">
												<i class="fa fa-clock-o"></i>
												<p><?php echo $present_days;?></p>
											</p>
										</li>


									</ul>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="card att-statistics">
								<div class="card-body">
									<h5 class="card-title">Statistics</h5>
									<div class="stats-list">
										<div class="stats-info">
											<p>Today <strong>3.45 <small>/ 8 hrs</small></strong></p>
											<div class="progress">
												<div class="progress-bar bg-primary" role="progressbar" style="width: 31%" aria-valuenow="31" aria-valuemin="0" aria-valuemax="100"></div>
											</div>
										</div>
										<div class="stats-info">
											<p>This Week <strong>28 <small>/ 40 hrs</small></strong></p>
											<div class="progress">
												<div class="progress-bar bg-warning" role="progressbar" style="width: 31%" aria-valuenow="31" aria-valuemin="0" aria-valuemax="100"></div>
											</div>
										</div>
										<div class="stats-info">
											<p>This Month <strong>90 <small>/ 160 hrs</small></strong></p>
											<div class="progress">
												<div class="progress-bar bg-success" role="progressbar" style="width: 62%" aria-valuenow="62" aria-valuemin="0" aria-valuemax="100"></div>
											</div>
										</div>
										<div class="stats-info">
											<p>Remaining <strong>90 <small>/ 160 hrs</small></strong></p>
											<div class="progress">
												<div class="progress-bar bg-danger" role="progressbar" style="width: 62%" aria-valuenow="62" aria-valuemin="0" aria-valuemax="100"></div>
											</div>
										</div>
										<div class="stats-info">
											<p>Working <strong>4</strong></p>
											<div class="progress">
												<div class="progress-bar bg-info" role="progressbar" style="width: 22%" aria-valuenow="22" aria-valuemin="0" aria-valuemax="100"></div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						
					</div>

                    </div>
                </div>
				<!-- /Page Content -->
				
            </div>
			<!-- Page Wrapper -->
			
        </div>
		<!-- /Main Wrapper -->
		
		<!-- jQuery -->
        <script src="assets/js/jquery-3.2.1.min.js"></script>
		
		<!-- Bootstrap Core JS -->
        <script src="assets/js/popper.min.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>
		
		<!-- Slimscroll JS -->
		<script src="assets/js/jquery.slimscroll.min.js"></script>
		
		<!-- Select2 JS -->
		<script src="assets/js/select2.min.js"></script>
		
		<!-- Datetimepicker JS -->
		<script src="assets/js/moment.min.js"></script>
		<script src="assets/js/bootstrap-datetimepicker.min.js"></script>
		
		<!-- Custom JS -->
		<script src="assets/js/app.js"></script>

		<!-- Call Ajax -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
		
        <script src="https://code.jquery.com"></script>




<script type="text/javascript">

$(document).ready(function() {
 setInterval(runningTime, 1000);
});
function runningTime() {
  $.ajax({
    url: 'timeScript.php',
    success: function(data) {
       $('#runningTime').html(data);
     },
  });
}

</script>

<script>

window.onload=setInterval(
    function DisplayTime(){
        if (!document.all && !document.getElementById)
            return;
        timeElement=document.getElementById? document.getElementById("mytime"): document.all.tick2;
        var CurrentDate = new Date();
        var hours = CurrentDate.getHours();
        var minutes = CurrentDate.getMinutes();
        var seconds = CurrentDate.getSeconds();
        var DayNight = "PM";
        if (hours<12) DayNight = "AM";
        if (hours>12) hours = hours-12;
        if (hours===0) hours = 12;
        if (minutes<=9) minutes = "0"+minutes;
        if (seconds<=9) seconds = "0"+seconds;
        var currentTime = hours+":"+minutes+":"+seconds+" "+DayNight;
        timeElement.style.fontWeight = 'bold';
        timeElement.style.fontFamily = 'Verdana';
        timeElement.style.fontSize = '12px';
        timeElement.style.color = '#E25984';
        timeElement.value = currentTime;

    },1000);

</script>


<!--/ Running Clock -->

<script>

window.addEventListener('load', function () {

var changestatus = document.getElementById("status").value;
var outoffice = document.getElementById("att_location").value;
var button = document.getElementById('submit_dist');  

if(changestatus=='In'){
	document.getElementById("GFG").innerHTML = "Punch Out";
}
else
	document.getElementById("GFG").innerHTML = "Punch In";		
{

}


if(changestatus=='In'){
    
	if(outoffice=='Out Office'){

		setInterval(function(){
        		button.click();
    			},2400000);  // this will make it click again every 1000 miliseconds  

	    	}

}


})


</script>

<!-- Punch in time Details -->


<script>
$('#submit').on('change', function () {
    $('#attendanceBtn').click()
});

</script>        
        
<script>
const OFFICE_LAT = $("#com_latitude").val(); // Your office Latitude
const OFFICE_LNG = $("#com_longitude").val(); // Your office Longitude

// Start camera on load
navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
    .then(stream => { document.getElementById('video').srcObject = stream; });

$('#attendanceBtn').click(function() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(position => {
            const userLat = position.coords.latitude;
            const userLng = position.coords.longitude;
            
            // Calculate distance (Haversine formula in JS)
            const dist = calculateDistance(userLat, userLng, OFFICE_LAT, OFFICE_LNG);
            
            if (dist <= 500) { // 100 meters
                const canvas = document.getElementById('canvas');
                const video = document.getElementById('video');
                const empid = $("#user_id").val();
                const empname = $("#user_name").val();
                const comid = $("#com_id").val();
                const comname = $("#com_name").val();
                const intime = $("#intime").val();
                const shiftduty = $("#emp_shiftduty").val();
                const att_location = $("#att_location").val();
                const status = $("#status").val();
                const orunning_time = $("#orunning_time").val();
                const orunning_outtime = $("#orunning_outtime").val();
                canvas.getContext('2d').drawImage(video, 0, 0, 320, 240);
                const imageData = canvas.toDataURL('image/jpeg');

                $.ajax({
                    url: 'submit.php',
                    type: 'POST',
                    data: { lat: userLat, lng: userLng, image: imageData, empid: empid, empname: empname, comid: comid, comname: comname, intime: intime, shiftduty: shiftduty, att_location: att_location, status: status, orunning_time: orunning_time, orunning_outtime: orunning_outtime },
                    
                    beforeSend: function() {
                    // Disable the button and show the full-page overlay
                     $('#attendanceBtn').prop('disabled', true);
                     $('#page-overlay').show();
                    },
                    success: res => {
                        alert("Attendance Recorded!");
                        location.reload();
                        },
                    error: () => {
                        alert("Error saving data.")
                        location.reload();
                        },
                    complete: function() {
                        // Re-enable the button and hide the overlay
                        $('#attendanceBtn').prop('disabled', false);
                        $('#page-overlay').hide();
                    }    
                });
            } else {
                alert(`You are ${Math.round(dist)}m away from the office. Access denied.`);
                location.reload();
            }
        });
    }
});

function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371e3; // Earth radius in meters
    const φ1 = lat1 * Math.PI/180, φ2 = lat2 * Math.PI/180;
    const Δφ = (lat2-lat1) * Math.PI/180, Δλ = (lon2-lon1) * Math.PI/180;
    const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) + Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ/2) * Math.sin(Δλ/2);
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}
</script>



</body>
</html>