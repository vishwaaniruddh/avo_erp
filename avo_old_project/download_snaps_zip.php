<?php
include('config.php'); // DB connection

if (!isset($_GET['alert_id'])) {
    die("Missing alert_id");
}

$alert_id = intval($_GET['alert_id']);


$sql = mysqli_query($con1, "SELECT atm_id FROM alert WHERE alert_id = '$alert_id'");
$alertrow = mysqli_fetch_assoc($sql);
$track_id = $alertrow['atm_id'];

$sqlatm = mysqli_query($con1, "SELECT atm_id FROM atm WHERE track_id = '$track_id'");
$atmrow = mysqli_fetch_assoc($sqlatm);
$atm_id=$atmrow['atm_id'];


// Fetch image paths from snap_inst table
$query = mysqli_query($con1, "SELECT * FROM snap_inst WHERE alert_id = '$alert_id'");
$row = mysqli_fetch_assoc($query);


if (!$row) {
    die("No snaps found for Site ID $atm_id");
}

// Collect all file paths
$files = array_filter([
    $row['full_product'], $row['front_panel'], $row['buyback'],
    $row['input_volt'], $row['output_volt'], $row['earth_volt'],
]);

//$zipname = "snaps_alert_{$alert_id}.zip";
$zipname = "snaps_{$atm_id}.zip";


$zip = new ZipArchive();
$tempZipPath = sys_get_temp_dir() . '/' . $zipname;

if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Cannot open <$zipname>");
}

$filesAdded = 0;
$tempFiles = []; // Store temp file paths for cleanup

foreach ($files as $filePath) {
    if (!$filePath) continue;

    // Check if path is a URL or relative
    if (filter_var($filePath, FILTER_VALIDATE_URL)) {
        // It's a URL, download to temp
        $tempFile = tempnam(sys_get_temp_dir(), 'img_');
        if (@file_put_contents($tempFile, file_get_contents($filePath))) {
            $zip->addFile($tempFile, basename($filePath));
            $tempFiles[] = $tempFile;
            $filesAdded++;
        }
    } else {
        // Assume relative local path
        $fullPath = realpath(__DIR__ . '/' . $filePath);
        if ($fullPath && file_exists($fullPath)) {
            $zip->addFile($fullPath, basename($filePath));
            $filesAdded++;
        }
    }
}

$zip->close();

if ($filesAdded === 0) {
    unlink($tempZipPath);
    die("No valid image files found for ATM ID $atm_id.");
}

// Send ZIP for download
header('Content-Type: application/zip');
header("Content-Disposition: attachment; filename=\"$zipname\"");
header('Content-Length: ' . filesize($tempZipPath));

ob_clean(); flush(); // Prevent output corruption
readfile($tempZipPath);
unlink($tempZipPath);

// Delete temp files if downloaded from URL
foreach ($tempFiles as $file) {
    if (file_exists($file)) {
        unlink($file);
    }
}

exit;
?>
