<?php
$serverName = "goalnavigator.database.windows.net";
$connectionInfo = array(
	"Database"=>"goalnavigator",
	"UID"=>"hioki",
	"PWD"=>"123456Aa%");

// Connection //
$conn = sqlsrv_connect($serverName, $connectionInfo);
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
else{
    echo("Success");
}
