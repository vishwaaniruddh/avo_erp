<?php 
session_start();
include('config.php');
include('vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;


$target_dir = 'PHPExcel';
$file_name = $_FILES["userfile"]["name"];
$file_tmp = $_FILES["userfile"]["tmp_name"];
$file = $target_dir . '/' . $file_name;


move_uploaded_file($file_tmp, $file);

try {
    $spreadsheet = IOFactory::load($file);
} catch (Exception $e) {
    die('Error loading file "' . pathinfo($file, PATHINFO_BASENAME) . '": ' . $e->getMessage());
}

$sheet = $spreadsheet->getActiveSheet();
$highestRow = $sheet->getHighestRow();
$highestColumn = $sheet->getHighestColumn();

  if($highestRow > 1000){ echo "You Can't Upload more than 1000 sites at a time";
  die;}
 
   for ($row = 1; $row <= $highestRow; $row++) { 
    $rowData[] = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, 
                                    null, true, false);
  }

    $row = $row-2;

$cdate=date("Y-m-d");
$contents='';
$contents.="S.No \t SIte Id\t Expiry Date\t Status\t ";


for($i = 1; $i<=$row; $i++){
      
$sno =  $rowData[$i][0][0];
        $site_id = $rowData[$i][0][1];
        $site_id = trim($site_id);

	$expiry=$rowData[$i][0][2];

  $UNIX_DATE = ($expiry - 25569) * 86400;
	if($UNIX_DATE>0){
 $expiry=gmdate("Y-m-d",$UNIX_DATE);
	
    } else {
	 $expiry=$cdate; 
	}
  
  
  
$atmqry=mysqli_query($con1,"select amcid from `Amc` where `atmid`='".$site_id."'");
if(mysqli_num_rows($atmqry)>0) {
  
$result=mysqli_query($con1,"update Amc set amc_ex_date='".$expiry."', active='N' where atmid='".$site_id."'");

  $contents.="\n ".$sno." \t ".$site_id." \t ".$expiry." \t Updated Successfully";
	 
	}  else {
  $contents.="\n ".$sno." \t ".$site_id." \t ".$expiry." \t Id Not Available";
}

}



 //======================print $contents;

$contents = strip_tags($contents); // remove html and php tags etc. str_replace(',', '\,', $row[faqdesk_answer_short])
  header("Content-Disposition: attachment; filename=rejectedsites.xls");
  header("Content-Type: application/vnd.ms-excel");
  print $contents;
  //echo "<br>";
//echo "<script type='text/javascript'>window.location='newsite.php';</script>";

