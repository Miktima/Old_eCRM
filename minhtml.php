<?php
	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
	echo "<html>\n";
	echo "<head>\n";
	echo "<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=windows-1251\">\n";
	echo "<LINK rel=stylesheet href=\"stl.css\" type=\"text/css\">\n";
	echo "<script language=\"JavaScript\" src=\"util.js\"></script>\n";
	echo "<title>Outside Services &gt; Incoming Message in HTML</title>\n";
	echo "</head>\n";
	echo "<body>\n";
	echo "<a href=\"javascript:window.close()\">close</a><br><br><br>\n";
	echo "<h1>Message</h1><br>";
	if ($id >= 0)
	{
		include ("dbmail.def");
		include ("imap.inc");
		$imap = new imapObj;
		include ("db.inc");
		$db = new dbObj;
		$db->init();
		$db->query("SELECT id, name, password FROM boxes WHERE id = $id");
		$row = $db->rows();
		$mbox = imap_open (MailServer, $row[1], $row[2]);
		//The functions from www.php.net
		$msg = imap_header($mbox, $nmsg);
		//Incoming Message
		$from_o = $msg->from[0];
		$hcor = "From: ".$imap->imap_conv($from_o->personal)." &lt;".$from_o->mailbox."@".$from_o->host."&gt;<br>";
		$hcor .= "Sent: ".date("Y-m-d H:i:s", $msg->udate)."<br>";
		$to_add = $msg->to;
		$hcor .= "To: ";
		if(is_array($to_add))
		{
			foreach($to_add as $vadd)
			{
				$hcor .= $imap->imap_conv($vadd->personal)." &lt;".$vadd->mailbox."@".$vadd->host."&gt; ";
			}
		}
		$hcor .= "<br>";
		$cc_add = $msg->cc;
		$hcor .= "CC: ";
		if(is_array($cc_add))
		{
			foreach($cc_add as $cadd)
			{
				$hcor .= $imap->imap_conv($cadd->personal)." &lt;".$cadd->mailbox."@".$cadd->host."&gt; ";
			}
		}
		$hcor .= "<br>";
		$hcor .= "Subject: ".$imap->imap_conv($msg->subject)."<br>";
		$correspondence = $imap->get_message($mbox, $nmsg, "TEXT/PLAIN");
		if ($correspondence == "") {$correspondence =$imap->get_message($mbox, $nmsg, "TEXT/HTML");}
		echo $hcor."<br><br><br>".$correspondence;
	}
	if ($id == -1)
	{
		include ("db.inc");
		$db = new dbObj;
		$db->init();
		//the main query
		$db->query("SELECT correspondence FROM correspondence WHERE number = $nmsg" );
		$row = $db->rows();
		echo "<br><br><br>";
		if (stristr($row[0],"<br>"))
			echo $row[0];
		else
			echo nl2br($row[0]);
	}
	echo "</body></html>";
?>
