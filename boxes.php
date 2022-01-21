<?php
	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
	echo "<html>\n";
	echo "<head>\n";
	echo "<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=windows-1251\">\n";
	echo "<LINK rel=stylesheet href=\"stl.css\" type=\"text/css\">\n";
	echo "<script language=\"JavaScript\" src=\"util.js\"></script>\n";
?>
	<script language="JavaScript">
	function chkChoice() // runs when a message is deleted
	{
		if(document.remspam.delchoises.value == "no")
		{
			alert ("a reason is not specified!");
		}
		else
		{
			remspam.submit();
		}
	}
	</script>
<?php
	echo "<title>Outside Services &gt; Inbox</title>\n";
	echo "</head>\n";
	echo "<body>\n";
	include ("dbmail.def");
	include ("imap.inc");
	//Try to open IMAP stream
	include ("db.inc");
	$db = new dbObj;
	$db->init();
	$db->query("SELECT id, name, password FROM boxes WHERE id = $id");
	$row = $db->rows();
	$mbox = @imap_open (MailServer, $row[1], $row[2]);
	if (!$mbox)
	{
		//Error: Stream is not opened
		echo "<br><br><br><h1 align=\"center\">ERROR.<br>Login or Password is incorrect</h1>\n";
		echo "<br><br>";
		echo "<div align=\"center\"><a href=\"index.php\">Try again</a></div>\n";
		echo "</html></body>";	
	}
	else
	{
		echo "<a href=\"allboxes.php\">back</a>";
		$imap = new imapObj;
		echo "<form action=\"remspam.php\" method=\"post\" name=\"remspam\">";
		echo "<table width=\"800\">\n";
		echo "<tr><th>&nbsp;</th><th>From</th><th>Subject</th><th>Received</th>";
		if (UseGroupRemoving == "Yes")
			echo "<th>Remove</th>";
		echo "</tr>\n";
		$ntotal = 0;
		$nnew = 0;
		$nummsg = imap_num_msg ($mbox);
		for( $i=0 ; $i < $nummsg ; $i++ )
		{ 
			$msg = imap_header($mbox, $i+1);
			$ntotal++;
			if ($i%2 == 0) {echo "<tr bgcolor=\"#dddddd\"";}
			else {echo "<tr bgcolor=\"#cccccc\"";}
			if ($msg->Unseen == "U" || $msg->Recent == "N") {echo " id=\"new\""; $nnew++;} // new message
			echo ">";
			echo "<td>";
			//Check for attachment
			if ($imap->isAtach($mbox, $i+1) == 1) {echo "<img alt=\"attachment\" src=\"img/clip.gif\" width=\"16\" height=\"16\">";}
			else {echo "&nbsp;";}
			echo "</td>\n";
			echo "<td>";
			$nmsg = $i + 1;
			echo "<a href=\"text.php?id=$id&nmsg=$nmsg\">";
			//get address
//			echo "&nbsp;".$imap->cyr_conv($msg->fromaddress);
			echo "&nbsp;".$imap->imap_conv($msg->fromaddress);
			echo "</a>";
			echo "</td>\n";
			echo "<td";
			$from_o = $msg->from[0];
			if ($db->find("spamadr", "address", $from_o->mailbox."@".$from_o->host) != 0) {echo " class=\"spam\"";}	//is availbale in spam and virus table 
			echo ">";
			//get subject
			echo "&nbsp;".$imap->cyr_conv($msg->subject); 
			echo "</td>\n";
			echo "<td>";
			//get date
			echo date("Y-m-d H:i:s", $msg->udate);
			echo "</td>";
			if (UseGroupRemoving == 'Yes')
				echo "<td><input type=\"Checkbox\" name=\"remmsg$i\" value=\"remove\"></td>\n";
			echo "</tr>\n";
		}
		echo "</table>\n";
		echo "<br>";
		echo "<script language=\"JavaScript\">\n";
		echo "function selall()\n";
		echo "{\n";
		echo "	for(var i=0 ; i < $nummsg ; i++ )\n";
		echo "	{\n";
		echo "		eval(\"document.remspam.remmsg\"+i+\".checked = true;\");\n";
		echo "	}\n";
		echo "}\n";
		echo "</script>\n";
		echo "<input type=\"Hidden\" name=\"id\" value=\"$id\">\n";
		echo "<table width=\"600\" border=\"0\">\n";
		echo "<tr><td>new messages:&nbsp;<strong>$nnew</strong></td>\n";
		echo "<td>total messages:&nbsp;<strong>$ntotal</strong></td></tr>\n";
		// Removing a group of messages
		echo "<tr><td>";
		if (UseSpamRemoving == 'Yes')
			echo "<input type=\"Submit\" value=\"&nbsp;Delete Highlighted Messages&nbsp;\" name=\"btnRemspam\">";
		else 
			echo "&nbsp;";
		echo "</td><td>";
		if (UseGroupRemoving == 'Yes')
			echo "<input type=\"Button\" value=\"&nbsp;Delete Selected Messages&nbsp;\" name=\"btnRemselect\" OnClick=\"chkChoice()\">";
		else
			echo "&nbsp;";
		echo "</td></tr>\n";
		echo "<tr><td>";
		if(UseSelectAll == 'Yes')
			echo "Select all: <input type=\"Checkbox\" value=\"&nbsp;Select All&nbsp;\" name=\"btnSelall\" OnChange=\"selall()\">";
		else
			echo "&nbsp;";
		echo "</td>";
		// reasons for removing
		echo "<td>";
		if (UseGroupRemoving == 'Yes')
		{
			echo "Reason for removing:&nbsp;<select name=\"delchoises\">\n";
			echo "<option value=\"no\">-------\n";
			echo "<option value=\"spam\">Spam\n";
			echo "<option value=\"virus\">Virus\n";
			echo "<option value=\"duplicate\">Duplicate\n";
			echo "<option value=\"notdeliv\">Not delivered\n";
			echo "<option value=\"outofoffice\">Out of office\n";
			echo "<option value=\"other\">Other\n";
			echo "</select>";
		}
		else
			echo "&nbsp;";
		echo "</td></tr>\n";
		echo "</table>\n";
		echo "</form>";
	}
	echo "</html></body>\n";
?>
