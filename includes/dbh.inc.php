<?php

// Parameters for connection //
$serverName = 'localhost';
$uid 	    = 'hioki';
$pwd 	    = '123456Aa%';
$database   = 'goalnavigator';

// Connection //
$conn = new PDO("sqlsrv:server=$serverName;database=$database", $uid, $pwd);
$conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
$conn->setAttribute( PDO::SQLSRV_ATTR_DIRECT_QUERY, true);


if (!$conn){
    die("Connection failed: " . mysqli_connect_error());
}