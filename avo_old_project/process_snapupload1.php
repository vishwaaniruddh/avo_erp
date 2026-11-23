<?php
session_start();
include("config.php");

// Image compression function
function compressImage($source_image, $compress_image, $mime)
{
    if ($mime == 'image/jpeg') {
        $source = imagecreatefromjpeg($source_image);
        imagejpeg($source, $compress_image, 75);
    } elseif ($mime == 'image/png') {
        $source = imagecreatefrompng($source_image);
        imagepng($source, $compress_image, 6);
    } elseif ($mime == 'image/gif') {
        $source = imagecreatefromgif($source_image);
        imagegif($source, $compress_image); // No quality param
    }
    return $compress_image;
}

// Receive POST data
$alert_id = isset($_POST['alert_id']) ? mysqli_real_escape_string($con1, $_POST['alert_id']) : '';
$cnt = isset($_POST['cnt']) ? (int)$_POST['cnt'] : 0;

// Validate file upload
if (!isset($_FILES['userfile']) || $_FILES['userfile']['error'] !== UPLOAD_ERR_OK) {
    echo "<script>alert('Upload error.'); window.close();</script>";
    exit;
}

$maxsizeKB = 10000;
$fileSizeKB = $_FILES['userfile']['size'] / 1024;
if ($fileSizeKB > $maxsizeKB) {
    echo "<script>alert('File too large. Max allowed size is {$maxsizeKB} KB.'); window.location = 'snaps_new.php';</script>";
    exit;
}

// Setup directory
$target_dir = "inst_snaps_290725/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0755, true);
}

// Sanitize and rename file
$originalName = $_FILES['userfile']['name'];
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

if (!in_array($extension, $allowed_extensions)) {
    echo "<script>alert('Invalid file type. Only JPG, PNG, and GIF allowed.'); window.close();</script>";
    exit;
}

$newFileName = uniqid('img_', true) . '.' . $extension;
$target_file = $target_dir . $newFileName;
$temp_file = $_FILES['userfile']['tmp_name'];

$image_info = getimagesize($temp_file);
$mime_type = $image_info['mime'] ?? '';

compressImage($temp_file, $target_file, $mime_type);

$inst = false;
$date = date('Y-m-d H:i:s');

// Check if alert_id exists in snap_inst
$q_check = "SELECT alert_id FROM snap_inst WHERE alert_id = '$alert_id'";
$result = mysqli_query($con1, $q_check);
$exists = mysqli_num_rows($result) > 0;

// Insert or update
if (!$exists && $cnt === 1) {
    $q_insert = "INSERT INTO snap_inst (alert_id, full_product, created_at) VALUES ('$alert_id', '$target_file', '$date')";
    $inst = mysqli_query($con1, $q_insert);

    if ($inst) {
        mysqli_query($con1, "UPDATE alert SET snap_file = 'Done' WHERE alert_id = '$alert_id'");
    }
} elseif ($exists) {
    $field_map = [
        2 => 'front_panel',
        3 => 'buyback',
        4 => 'input_volt',
        5 => 'output_volt',
        6 => 'earth_volt'
    ];

    if (array_key_exists($cnt, $field_map)) {
        $field = $field_map[$cnt];
        $q_update = "UPDATE snap_inst SET $field = '$target_file' WHERE alert_id = '$alert_id'";
        $inst = mysqli_query($con1, $q_update);
    }
}

// Final result & redirection
if ($inst) {
    $cnt += 1;
    if ($cnt <= 6) {
        echo "<script>alert('Successfully uploaded snap. Please continue.'); window.location = 'add_snaps.php?id={$alert_id}&cnt={$cnt}';</script>";
    } else {
        echo "<script>alert('All uploads complete.'); window.close();</script>";
    }
} else {
    echo "<script>alert('Some error occurred. Please try again.'); window.close();</script>";
}
?>
