<?php
$serverName = "tsukasakimysqlserver.database.windows.net";
$connectionInfo = array(
	"Database"=>"mySampleDatabase",
	"UID"=>"azureuser",
	"PWD"=>"123456Aa%");

// Connection //
$conn = sqlsrv_connect($serverName, $connectionInfo);
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}