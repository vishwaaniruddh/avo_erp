<?php

// error_reporting(0);
error_reporting(E_ALL);
date_default_timezone_set('Asia/Kolkata');

function OpenCon1()
 {
 $dbhost = "localhost";
 $dbuser = "avoservice_avoserviceAvo";
 $dbpass = "Myaccounts123*";
 $db = "avoservice_avoserviService";
 $conn = new mysqli($dbhost, $dbuser, $dbpass,$db) or die("Connect failed: %s\n". $conn -> error);
 
 return $conn;
 }

function OpenCon2()
 {
 $dbhost = "localhost";
 $dbuser = "avoservice_aniruddh";
 $dbpass = "Aniruddh2024!#";
 $db = "avoservice_avoserviCrm";
 $conn = new mysqli($dbhost, $dbuser, $dbpass,$db) or die("Connect failed: %s\n". $conn -> error);
 
 return $conn;
 }

function CloseCon($conn)
 {
 $conn -> close();
 }

?>