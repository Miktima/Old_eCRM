<?php
	include "../imap.inc";
	$source = "=?EUC-KR?B?w9bH/MH4?=";
	
	$imap = new imapObj;
	$text = $imap->imap_conv($source);
	echo $text;
	echo phpinfo();
?>
