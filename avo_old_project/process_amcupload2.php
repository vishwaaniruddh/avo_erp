<?php
session_start();
include('config.php');
include('vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\IOFactory;

function clean($string) {
    $string = preg_replace('/[^A-Za-z0-9\-]/', ' ', $string);
    return preg_replace('/-+/', '-', $string);
}

// Validate required fields
if (!isset($_POST['cust'], $_POST['po2'], $_POST['servicetype'], $_POST['po_id'], $_FILES['images'])) {
    die('Missing required fields or file');
}

$cust = $_POST['cust'];
$po2 = $_POST['po2'];
$service = $_POST['servicetype'];
$po_id = $_POST['po_id'];

$target_dir = 'PHPExcel';
$file_name = basename($_FILES["images"]["name"]);
$file_tmp = $_FILES["images"]["tmp_name"];
$file = $target_dir . '/' . $file_name;

if (!move_uploaded_file($file_tmp, $file)) {
    die('File upload failed.');
}

// Load Excel
try {
    $spreadsheet = IOFactory::load($file);
} catch (Exception $e) {
    die('Error loading file: ' . $e->getMessage());
}

$sheet = $spreadsheet->getActiveSheet();
$highestRow = $sheet->getHighestRow();
$highestColumn = $sheet->getHighestColumn();

if ($highestRow > 5000) {
    die("You can't upload more than 5000 sites at a time.");
}

$rowData = [];
for ($row = 1; $row <= $highestRow; $row++) {
    $rowData[] = $sheet->rangeToArray("A$row:$highestColumn$row", null, true, false);
}

$rowCount = count($rowData) - 1;

$contents = "S.No\tATM Id\t End User\t Area\t Pincode\t City\t State\t Branch\t Address\t Amc Start Date\t Product\tQty\tAMC Expiry Date\tStatus";

//echo $contents;
//die;

for ($i = 1; $i <= $rowCount; $i++) {
    $error = '';
    $errcnt = 0;

    list(
        $s_no, $site_id, $enduser, $area, $pin,
        $city, $state, $branch, $add, $start,
        $product, $qty, $exp
    ) = $rowData[$i][0];



    $site_id = trim($site_id);
    $add = clean($add);
    $city = ucwords(strtolower($city));
    $area = ucwords(strtolower($area));
    $enduser = ucwords(strtolower($enduser));
    $state = ucwords(strtolower($state));
    $add = ucwords(strtolower($add));
    
    
    //$start_date = $start;
   // $exp_date = $exp;
    
//echo "Hellllooooooooo".$contents; 
    $start_date = '';
    $UNIX_DATE = ($start - 25569) * 86400;
    if ($UNIX_DATE > 0) {
        $start_date = gmdate("Y-m-d", $UNIX_DATE);
    } else {
        $error .= "AMC start date format wrong; ";
        $errcnt++;
    } 

    $exp_date = '';
    $UNIX_DATE2 = ($exp - 25569) * 86400;
    if ($UNIX_DATE2 > 0) {
        $exp_date = gmdate("Y-m-d", $UNIX_DATE2);
    } else {
        $error .= "AMC Expiry date format wrong; ";
        $errcnt++;
    } 

    $br_id = '';
    $branch_qry = mysqli_query($con1, "SELECT id FROM avo_branch WHERE name='" . $branch . "'");
    if ($branch_qry && mysqli_num_rows($branch_qry) > 0) {
        $br_row = mysqli_fetch_row($branch_qry);
        $br_id = $br_row[0];
    }

    if (empty($site_id)) {
        $error .= "Site ID not found; ";
        $errcnt++;
    } elseif (empty($br_id)) {
        $error .= "Branch name not matching; ";
        $errcnt++;
    } elseif (empty($start)) {
        $error .= "AMC start date is missing; ";
        $errcnt++;
    } elseif (empty($exp)) {
        $error .= "Expiry date missing; ";
        $errcnt++;
    } else {
        $sqlhis = mysqli_query($con1, "SELECT amc_st_date FROM Amc WHERE atmid='" . mysqli_real_escape_string($con1, $site_id) . "'");
        if ($sqlhis && mysqli_num_rows($sqlhis) > 0) {
            $exist = mysqli_fetch_row($sqlhis);
            if ($exist[0] == $start_date) {
                $errcnt++;
                $error .= "AMC start date is already as per portal; ";
            }
        }
    }

    if ($errcnt > 0) {
        $status = trim($error);
    } else {
        $check = mysqli_query($con1, "SELECT amcid FROM Amc WHERE atmid='" . mysqli_real_escape_string($con1, $site_id) . "'");
        if ($check && mysqli_num_rows($check) > 0) {
            $existidrow = mysqli_fetch_row($check);
            $amc_id = $existidrow[0];


            mysqli_query($con1, "UPDATE Amc SET refid ='$po_id',po='$po2', amc_st_date='$start_date', amc_ex_date='$exp_date', bankname='$enduser', city='$city', state='$state', address='" . addslashes($add) . "', cid='$cust', branch='$br_id', po='$po2', active='Y' WHERE amcid='$amc_id'");
            $status = "AMC Updated";
        } else {

            mysqli_query($con1, "INSERT INTO Amc(refid, po, cid, atmid, bankname, area, pincode, city, address, state, amc_st_date, amc_ex_date, branch, active, ups, nou) VALUES ('$po_id', '$po2', '$cust', '$site_id', '$enduser', '" . trim($area) . "', '$pin', '" . trim($city) . "', '" . addslashes($add) . "', '" . trim($state) . "', '$start_date', '$exp_date', '$br_id', 'Y', '$product', '$qty')");
            $status = "AMC Inserted";
        }
    }

    // Add row to contents
    $contents .= "\n$s_no\t$site_id\t$enduser\t$area\t$pin\t$city\t$state\t$branch\t$add\t$start_date\t$product\t$qty\t$exp_date\t$status";
}

// Update PO upload date
if (!empty($po_id)) {
    $date = date('Y-m-d');
    mysqli_query($con1, "UPDATE amc_po_new SET upload_date='$date', status=2 WHERE po_id='$po_id'");
}

// Output
$contents = strip_tags($contents);
header("Content-Disposition: attachment; filename=AMC_upload.xls");
header("Content-Type: application/vnd.ms-excel");
echo $contents;
exit;
