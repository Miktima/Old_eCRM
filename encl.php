<?php
	Header ("Content-Type: application/download\r\n"); 
	Header ("Content-Length: $length\r\n"); 
	Header ("Content-Disposition: attachment; filename=$name\r\n\r\n");
	Header("Content-Transfer-Encoding: binary\r\n"); 
	include ("dbmail.def");
	include ("db.inc");
	$db = new dbObj;
	$db->init();
	$db->query("SELECT id, name, password FROM boxes WHERE id = $id");
	$row = $db->rows();
	$mbox = imap_open (MailServer, $row[1], $row[2]);
	switch ($encoding)
	{
		case "base64":
			echo imap_base64(imap_fetchbody($mbox, $nmsg, $part_no));
			break;
		case "qprint":
			echo imap_qprint(imap_fetchbody($mbox, $nmsg, $part_no));
			break;
		default:
			echo imap_fetchbody($mbox, $nmsg, $part_no);
	}
?>