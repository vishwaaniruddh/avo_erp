<?php
date_default_timezone_set("Asia/Calcutta");   // India time (GMT+5:30)

// $username = 'aniruddh@sarmicrosystems.in';
// $password = 'AVav@@2023';
// $emailServer = 'mail.sarmicrosystems.in';

//$username = 'avoups@avoservice.in';
//$password = 'DeeBee@12345';
//$emailServer = 'mail.avoservice.in';

 $username = 'calllog@avoups.com';
 $password = 'DeeBee@12345';

// $username = 'rajeshrungta719@gmail.com';
// $password = 'prrtjohqqsiwuwix'; // Use App Password or regular password
 $emailServer = 'imap.gmail.com'; // Correct hostname for Gmail IMAP

$inbox = imap_open("{{$emailServer}:993/imap/ssl}INBOX", $username, $password);

if ($inbox) {
    $numMessages = imap_num_msg($inbox);
    echo "Number of messages: $numMessages";
} else {
    echo "Failed to connect to the IMAP server: " . imap_last_error();
}

?>