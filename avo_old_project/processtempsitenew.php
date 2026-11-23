<?php
include("access.php");
include("config.php");
include("Whatsapp_delegation/delegation_fun.php");
include_once 'andi/GCM.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

function extract_email_address($string) {
    $emails = [];
    foreach(preg_split('/\s/', $string) as $token) {
        $email = filter_var(filter_var($token, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
        if ($email !== false) {
            $emails[] = $email;
        }
    }
    return $emails;
}

function generate_ticket_no($con1, $user) {
    $qry2 = mysqli_query($con1, "SELECT srno FROM login WHERE username='$user'");
    if (!$qry2) {
        die("Error in generate_ticket_no: " . mysqli_error($con1));
    }
    $qry2ro = mysqli_fetch_row($qry2);
    $qrr = mysqli_query($con1, "SELECT * FROM alert WHERE entry_date LIKE '".date('Y-m-d')."%'");
    if (!$qrr) {
        die("Error in generate_ticket_no: " . mysqli_error($con1));
    }
    $num = mysqli_num_rows($qrr) + 1;
    $num3 = $num > 9 ? $num : "0".$num;
    return $qry2ro[0] . "_" . date("ymd") . $num3;
}

$bank = mysqli_real_escape_string($con1, $_POST['bank']);
$cust = mysqli_real_escape_string($con1, $_POST['cust']);
$branch = mysqli_real_escape_string($con1, $_POST['branch_avo']);
$area = mysqli_real_escape_string($con1, $_POST['area']);
$city = mysqli_real_escape_string($con1, $_POST['city']);
$address = mysqli_real_escape_string($con1, $_POST['address']);
$atmid = mysqli_real_escape_string($con1, $_POST['atmid']);
$po = mysqli_real_escape_string($con1, $_POST['po']);
$pincode = mysqli_real_escape_string($con1, $_POST['pincode']);
$state = mysqli_real_escape_string($con1, $_POST['state']);
$type = mysqli_real_escape_string($con1, $_POST['type']);
$type_call = mysqli_real_escape_string($con1, $_POST['type_call']);
$cname = mysqli_real_escape_string($con1, $_POST['cname']);
$doc = mysqli_real_escape_string($con1, $_POST['doc']);
$sub = mysqli_real_escape_string($con1, $_POST['sub']);
$prob = mysqli_real_escape_string($con1, $_POST['prob']);
$cphone = mysqli_real_escape_string($con1, $_POST['cphone']);
$cemail = mysqli_real_escape_string($con1, $_POST['cemail']);
$reason = mysqli_real_escape_string($con1, $_POST['how']);
$approved = mysqli_real_escape_string($con1, $_POST['appby']);
$whatsno = mysqli_real_escape_string($con1, $_POST['whatsno']) ?: 'NULL';
$ccm = implode(",", extract_email_address($_POST['ccemail']));
$alert_date = date('Y-m-d');
$createdby = generate_ticket_no($con1, $_SESSION['user']);

// Determine alert type and status
$alert_type = 'new temp';
if ($type_call === 'service') {
    $alert_type = 'new temp';
} elseif ($type_call === 'pm' || $type_call === 'dere' || $type_call === 'w2pcb') {
    $alert_type = 'temp_' . $type_call;
}

$alert_query = "INSERT INTO alert_testing (cust_id, atm_id, bank_name, area, address, city, branch_id, pincode, problem, caller_name, caller_phone, caller_email, alert_date, call_status, alert_type, entry_date, po, state1, createdby, subject, custdoctno, appby, appref, ccmail, whatsapp) 
VALUES ('$cust', 'temp_$atmid', '$bank', '$area', '$address', '$city', '$branch', '$pincode', '$prob', '$cname', '$cphone', '$cemail', '$alert_date', 'Pending', '$alert_type', NOW(), '$po', '$state', '$createdby', '$sub', '$type', '$approved', '$reason', '$ccm', '$whatsno')";

if (mysqli_query($con1, $alert_query)) {
    $id = mysqli_insert_id($con1);
    $temp_query = "INSERT INTO tempsites_testing (custid, po, atmid, bankname, area, pincode, city, state, address, ref_id, type, state1, trackerid) 
    VALUES ('$cust', '$po', 'temp_$atmid', '$bank', '$area', '$pincode', '$city', '$branch', '$address', '0', '$type', '$state', '$id')";

    if (mysqli_query($con1, $temp_query)) {
        // Fetch alert details
        $alertqry = mysqli_query($con1, "SELECT * FROM alert WHERE alert_id='$id'");
        $alertr = mysqli_fetch_row($alertqry);

        // Determine call type
        $calltp = '';
        switch ($alertr[17]) {
            case 'new':
                $calltp = "New Installation";
                break;
            case 'service':
            case 'new temp':
                $calltp = "Service Call";
                break;
            case 'pm':
            case 'temp_pm':
                $calltp = "PM Call";
                break;
            case 'dere':
            case 'temp_dere':
                $calltp = "De-Re Installation";
                break;
        }

        // WhatsApp notification
        $MassageNew = "[T]*Switching AVO Electro Power Ltd*";
        $Massage3 = "*ATM Id:* $atmid";
        $Massage4 = "*Ticket No:* $alertr[25]";
        $Massage5 = "*End User:* $alertr[3]";
        $Massage6 = "*Address:* $alertr[5]";
        $Massage7 = "*Type Of Call:* $calltp";
        $Massage8 = "*Problem Reported:* $alertr[9]";
        $cmobile = $alertr[13];
        $gmobile = $alertr[45];
        $whats_no = "$cmobile,$gmobile";
        $cmessage = "*Call is Logged with Us !!*";
        $cmmessage = "*Engineer Will be deligated shortly*";
        $allMessage = "$MassageNew\n$cmessage\n$cmmessage\n$Massage3\n$Massage4\n$Massage5\n$Massage6\n$Massage7\n$Massage8";
        SendWhatmsg($whats_no, $allMessage);

        // Email notification
        $tbl = "
            <html>
            <head><title>Switching AVO Electro Power Limited</title></head>
            <body>
                <p>New Call Logged from <font color='blue'>Switching <font color='red'>AVO</font> Electro Power Limited</font></p>
                <table border='1' width='700px'>
                    <tr>
                        <th>COMPLAINT ID</th><th>ATM ID</th><th>BANK</th><th>State</th><th>City</th><th>Address</th><th>ISSUE</th><th>STATUS</th>
                    </tr>
                    <tr>
                        <td>{$alertr[25]}</td>
                        <td>$atmid</td>
                        <td>{$alertr[3]}</td>
                        <td>{$alertr[27]}</td>
                        <td>{$alertr[6]}</td>
                        <td>{$alertr[5]}</td>
                        <td>{$alertr[9]}</td>
                        <td><b>Pending</b></td>
                    </tr>
                </table>
                <br><br>
                <font color='blue'>Switching <font color='red'>AVO</font> Electro Power Limited</font>
            </body>
            </html>";
        $to = $alertr[14];
        $subject = "{$alertr[25]} <Switching AVO Electro Power Limited>";
        $headers = "From: <HelpDesk@avoservice.in>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        $headers .= "Cc: $ccm\r\n";
        mail($to, $subject, $tbl, $headers);

        echo "<script type='text/javascript'>
                alert('Alert created successfully. Complain ID is : $createdby');
                window.location='newtempsite.php';
              </script>";
    } else {
        echo "Failed to INSERT INTO tempsites_testing: " . mysqli_error($con1);
    }
} else {
    echo "Failed to INSERT INTO alert_testing: " . mysqli_error($con1);
}

?>
