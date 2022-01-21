<?php
	Header ("Content-Type: application/download\r\n"); 
	Header ("Content-Length: $length\r\n"); 
	Header ("Content-Disposition: attachment; filename=$name\r\n\r\n");
	Header("Content-Transfer-Encoding: binary\r\n"); 
	include ("db.inc");
	$db = new dbObj;
	$db->init();
	$db->query("SELECT att FROM attachment WHERE number = $natt");
	$row = $db->rows();
	echo $row[0];
?>