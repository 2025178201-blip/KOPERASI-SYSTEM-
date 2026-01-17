<?php
/* PHP & Oracle DB connection file */

$user = "system";      // Oracle username
$pass = "oracle";      // Oracle password

// Use IP + port + service name
$host = "localhost:1522/freepdb1";

$dbconn = oci_connect($user, $pass, $host);

if (!$dbconn) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
} else {
    
}
?>
