<?php
include("config.php");

// Select ATM records where latitude is not available
$sql = "SELECT amcid, address, city, state
        FROM Amc
        WHERE active='Y'
          AND latitude1 = 0.0 order by amcid ASC";
          
        //  AND track_id >'383500'";
//echo $sql; die;

$result = mysqli_query($con1, $sql);

if (!$result) {
    die("Database Error: " . mysqli_error($con1));
}

while ($row = mysqli_fetch_assoc($result)) {
 
    $id      = $row['amcid'];
  
    $address = trim($row['address'] . ", " . $row['city'] . ", " . $row['state']);

    if (empty($address)) {
        echo "Track ID: $id - Address is empty.<br>";
        continue;
    }

    // Google Geocoding API URL
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" .
            urlencode($address) .
            "&key=AIzaSyCBE1Xgn2mQmGOtUevIuFYw6443BkKCjbI";

    // Fetch API response using cURL
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo "Track ID: $id - cURL Error: " . curl_error($ch) . "<br>";
        curl_close($ch);
        continue;
    }

    curl_close($ch);

    $output = json_decode($response, true);

    if (!$output) {
        echo "Track ID: $id - Invalid JSON response.<br>";
        continue;
    }

    if ($output['status'] == "OK") {

        $latitude  = $output['results'][0]['geometry']['location']['lat'];
        $longitude = $output['results'][0]['geometry']['location']['lng'];

        $update = "UPDATE Amc
                   SET latitude1 = '$latitude',
                       longitude1 = '$longitude'
                   WHERE amcid = '$id'";

        if (mysqli_query($con1, $update)) {
            echo "Track ID: $id - Updated Successfully (Lat: $latitude, Lng: $longitude)<br>";
        } else {
            echo "Track ID: $id - Database Update Failed: " . mysqli_error($con1) . "<br>";
        }

    } else {

        echo "Track ID: $id - Geocoding Failed (" . $output['status'] . ")<br>";

        if (!empty($output['error_message'])) {
            echo "Error: " . $output['error_message'] . "<br>";
        }
    }

    // Small delay to avoid hitting API rate limits
    usleep(100000); // 0.1 second
}

mysqli_free_result($result);
mysqli_close($con1);

echo "<br><b>Process Completed.</b>";
?>