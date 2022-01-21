<?php
	include ("dbmail.def");
	include ("imap.inc");
	$imap = new imapObj;
	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
	echo "<html>\n";
	echo "<head>\n";
	echo "<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=windows-1251\">\n";
	echo "<LINK rel=stylesheet href=\"stl.css\" type=\"text/css\">\n";
	echo "<script language=\"JavaScript\" src=\"util.js\"></script>\n";
	echo "<title>Outside Services &gt; Incoming Message</title>\n";
?>
	<script language="JavaScript">
	function delchange() // runs when a message is deleted
	{
		if(assign_form.delreason.value == "no")
		{
			alert ("a reason is not specified!")
		}
		else
		{
			assign_form.operation.value = "delete";
			assign_form.submit();
		}
	}
	function assignchange() //runs when a message is assigned
	{
		if(assign_form.assign.value == "no")
		{
			alert ("a person is not specified!")
		}
		else
		{
			assign_form.operation.value = "assign";
			assign_form.submit();
		}
	}
	function p_forward() //runs when a message is forwarded
	{
		if (assign_form.forward.value == "")
		{
			alert("The forward address is empty");
		}
		else
		{
			assign_form.operation.value = "forward";
			assign_form.submit();
		}
	}
	function change() //runs when a destination email is changed
	{
		assign_form.operation.value = "change";
		assign_form.submit();
	}
	</script>
<?php
	echo "</head>\n";
	echo "<body>\n";
	echo "<a href=\"boxes.php?id=$id\">list of messages</a>\n";
	include ("db.inc");
	$db = new dbObj;
	$db->init();
	$db->query("SELECT id, name, password, arflag FROM boxes WHERE id = $id");
	$row = $db->rows();
	$mbox = imap_open (MailServer, $row[1], $row[2]);
	$arflag = $row[3];
	echo "<form action=\"assign.php\" METHOD=\"POST\" name=\"assign_form\">\n";
	echo "<table width=\"600\">\n";
	echo "<tr>\n";
	/*
	//previous message
	$pnmsg = $nmsg;
	if ($pnmsg > 1)
		$pnmsg--;
	echo "<td><a href=\"text.php?id=$id&nmsg=$pnmsg\"><img alt=\"previous\" border=\"0\" height=\"32\" width=\"32\" src=\"img/larrow.gif\"></a></td>\n";
	// next message
	$nnmsg = $nmsg;
	if ($nnmsg < imap_num_msg ($mbox))
		$nnmsg++;
	echo "<td><a href=\"text.php?id=$id&nmsg=$nnmsg\"><img alt=\"next\" border=\"0\" height=\"32\" width=\"32\" src=\"img/rarrow.gif\"></a></td>\n";
	*/
	//remove message
	echo "<td><a href=\"javascript:delchange()\"><img alt=\"remove\" border=\"0\" height=\"32\" width=\"32\" src=\"img/trash.gif\"></a>\n";
	echo "<select name=\"delreason\">\n";
	echo "<option value=\"no\">-------\n";
	echo "<option value=\"spam\">Spam\n";
	echo "<option value=\"virus\">Virus\n";
	echo "<option value=\"duplicate\">Duplicate\n";
	echo "<option value=\"notdeliv\">Not delivered\n";
	echo "<option value=\"outofoffice\">Out of office\n";
	echo "<option value=\"other\">Other\n";
	echo "</select>\n";
	echo "</td>\n";
	//assign message
	echo "<td align=\"right\"><a href=\"javascript:assignchange()\"><img alt=\"assign\" border=\"0\" height=\"32\" width=\"32\" src=\"img/man.gif\"></a>\n";
	echo "<select name=\"assign\">\n";
	echo "<option value=\"no\">-------------------\n";
	//Names from the staff table
	$db->query("SELECT number, name, surname FROM staff ORDER BY surname");
	while ($row = $db->rows ()) {
		echo "<option value=\"$row[0]\">$row[1] $row[2]\n";
	}
	echo "</select></td>\n";
	if ($arflag == 1)
		echo "<td align=\"right\"><input type=\"Checkbox\" name=\"autoreply\" checked value=\"on\"> - autoreply</td>";
	echo "</tr>\n";
	echo "</table>\n";
	echo "<input type=\"Hidden\" name=\"nmsg\" value=\"$nmsg\">\n"; //Strore number of the message
	echo "<input type=\"Hidden\" name=\"id\" value=\"$id\">\n"; //Strore number of the message
	echo "<input type=\"Hidden\" name=\"operation\" value=\"\">\n"; //name of an operation
	//The functions from www.php.net
	$msg = imap_header($mbox, $nmsg); // retrieving header of a message with number $nmsg
	echo "<table bgcolor=\"#c0c0c0\" cellpadding=\"10\" cellspacing=\"0\" width=\"600px\">\n";
	//Incoming Message
	echo "<tr><td colspan=\"4\">Message&nbsp;&nbsp;&nbsp;<a href=\"minhtml.php?id=$id&nmsg=$nmsg\" target=\"help\">HTML</a><br>";
	echo "<textarea name=\"incoming\" rows=\"20\" cols=\"70\">";
	$from_o = $msg->from[0];
	$useremail = $from_o->mailbox."@".$from_o->host;
	$hcor = "From: ".$imap->imap_conv($from_o->personal)." <$useremail>\n";
	$hcor .= "Sent: ".date("Y-m-d H:i:s", $msg->udate)."\n";
	$to_add = $msg->to;
	$hcor .= "To: ";
	if(is_array($to_add))
	{
		foreach($to_add as $vadd)
		{
			$hcor .= $vadd->personal." <".$vadd->mailbox."@".$vadd->host."> ";
		}
	}
	$hcor .= "\n";
	$cc_add = $msg->cc;
	$hcor .= "CC: ";
	if(is_array($cc_add))
	{
		foreach($cc_add as $cadd)
		{
			$hcor .= $cadd->personal." <".$cadd->mailbox."@".$cadd->host."> ";
		}
	}
	$hcor .= "\n";
	$hcor .= "Subject: ".$imap->imap_conv($msg->subject)."\n";
	$correspondence = $imap->get_message($mbox, $nmsg, "TEXT/PLAIN");
	if ($correspondence == "") {$correspondence =$imap->get_message($mbox, $nmsg, "TEXT/HTML");}
	if (strstr($correspondence, "<form") && strstr($correspondence, "<table"))
		$correspondence = "A form is available in this email. Use HTML link to view it.";
//	$correspondence = str_replace("</form>","<!--</form>//-->",$correspondence);
	echo $hcor."\n\n".$correspondence;
	echo "</textarea></td></tr>\n";
	echo "<tr>";
	echo "<td>Received:</td><td><strong>".date("Y-m-d H:i:s", $msg->udate)."</strong></td>\n";
	$to_add = $msg->to;
	$toaddress = "";
	if(is_array($to_add))
	{
		foreach($to_add as $vadd)
		{
			$toaddress .= $imap->imap_conv($vadd->personal)." &lt;".$vadd->mailbox."@".$vadd->host."&gt; ";
		}
	}
	echo "<td align=\"right\">To:</td><td><strong>$toaddress</strong></td></tr>\n";
	echo "<tr>";
	//Subject
	echo "<td>Subject:</td><td colspan=\"3\"><strong>".$imap->imap_conv($msg->subject)."</strong></td></tr>\n";
	if (empty($user))
	{
		$user = $db->find('customer', 'email', $useremail);
	}
	echo "<input type=\"Hidden\" name=\"user\" value=\"$user\">"; 
	if ($user != 0)
	{
		$db->query("SELECT name, surname, email, country, firm FROM customer WHERE number=$user");
		$row = $db->rows();
		$user_name = $row[0];
		$user_surname = $row[1];
		$user_email = $row[2];
		$user_country = $row[3];
		$user_firm = $row[4];
		if ($user_country != "")
		{
			$db->query("SELECT country FROM country WHERE number=$user_country");
			$v = $db->rows();
			$user_country = $v[0];
		}
		if ($user_firm != "")
		{
			$db->query("SELECT firm FROM firm WHERE number=$user_firm");
			$v = $db->rows();
			$user_firm = $v[0];
		}
	}
	else
	{
		$user_name = "";
		$user_surname = "";
		$user_email = $useremail;
		$user_country = "";
		$user_firm = "";
	}
	echo "<tr>";
	//Email
	echo "<td>Email:</td><td><input type=\"Text\" name=\"uemail\" value=\"$user_email\" size=\"20\">";
	echo "&nbsp;<input type=\"Button\" name=\"cngTo\" value=\"Change\" onclick=\"change()\">";
	echo "</td>\n";
	if(is_array($cc_add))
	{
		foreach($cc_add as $cadd)
		{
			$ccaddress .= $cadd->personal." &lt;".$cadd->mailbox."@".$cadd->host."&gt; ";
		}
	}
	echo "<td align=\"right\">CC:</td><td>$ccaddress</td></tr>\n";
	echo "<tr>";
	//Name field
	echo "<td>Name:</td><td><nobr><input type=\"Text\" name=\"name\" value=\"$user_name\" size=\"20\">";
	// if user's email is available in DB
	if ($user != 0) {echo "<a href=\"dist.php?dist=customer&email=".urlencode($user_email)."&period=any&open=help\" target=\"help\"><img src=\"img/abook.gif\" height=\"24\" width=\"25\" border=\"0\"></a>\n";}
	include ("db_remote.inc");
	$dbRemote = new dbRemoteObj;
	$dbRemote->init();
	$pur_user = $dbRemote->find('customer', 'email', $useremail);
	if ($pur_user != 0) {echo "<a href=\"http://ecrm.paragraph.ru/sales/report/filter.php?filter=email&filter_field=".urlencode($user_email)."\" target=\"help\"><img src=\"img/money.gif\" height=\"25\" width=\"31\" border=\"0\"></a>\n";}
	echo "</nobr></td>\n";
	//Surname field
	echo "<td align=\"right\">Surname:</td><td><input type=\"Text\" name=\"surname\" value=\"$user_surname\" size=\"20\"></td></tr>\n";
	echo "<tr>";
	//Firm
	echo "<td>Company:</td><td><input type=\"Text\" name=\"firm\" value=\"$user_firm\" size=\"20\"></td>\n";
	//Country
	echo "<td align=\"right\">Country:</td><td>";
	$db = new dbObj;
	$db->init();
	$db->query("Select number, country From country");
	echo "<select name=\"country\">\n";
	//Country. User's country is default
	while ($row = $db->rows ()) {
		echo "<option value=\"$row[0]\"";
		if ($row[1] == $user_country) {echo " selected";}
		echo ">$row[1]\n";
	}
	echo "</select>\n";
	echo "</td>\n";
	//obsolete string
//	echo "<input type=\"Text\" name=\"country\" value=\"$user_country\" size=\"20\"></td>\n";
	echo "</tr>\n";
	$db->query("SELECT priority FROM priority");
	$prior = $db->rows();
	echo "<tr>";
	//Priority
	echo "<td>Priority:</td><td>";
	$db->query("Select number, priority From priority");
	echo "<select name=\"priority\">\n";
	//Priority. The Normal priority is default
	while ($row = $db->rows ()) {
		echo "<option value=\"$row[0]\"";
		if ($row[1] == "Normal") {echo " selected";}
		echo ">$row[1]\n";
	}
	echo "</select>\n";
	echo "</td>\n";
	$db->query("SELECT number, query FROM query");
	//Message Type
	echo "<td align=\"right\">Message Type (<a href=\"mestype_help.html\" target=\"help\">?</a>)</td><td><select name=\"query\">\n";
	while ($qur = $db->rows ()) {
		echo "<option value=\"$qur[0]\" ";
		if (stristr("Query",$qur[1])) {echo "selected";}
		echo ">$qur[1]\n";
	}
	echo "</select></td></tr>\n";
	echo "<tr><td colspan=\"4\">";
	//List of products
	echo "Products:<br>\n";
	echo "<table>\n";
	$db->query("SELECT number, product FROM product");
	echo "<tr>\n";
	while ($prd = $db->rows ()) {
		$pname = "prod".$prd[0];
		echo "<td><input type=\"Checkbox\" name=\"$pname\"><strong>$prd[1]</strong>";
		if (($prd[0]%4) == 0) {echo "</td></tr><tr>";}
		else {echo "</td>";}
	}
	echo "</tr></table></td>\n";
	echo "</tr>";
	//Comments
	echo "<tr><td>Comments:</td><td colspan=\"3\"><input type=\"Text\" name=\"comments\" value=\"\" size=\"60\"></td></tr>\n";
	//Forward
	echo "<tr><td colspan=\"4\" align=\"center\">\n";
	if (UseForward == 'Yes')
	{
		echo "<input type=\"Text\" name=\"forward\" value=\"\" size=\"20\">&nbsp;&nbsp;&nbsp;\n";
		echo "<input type=\"Button\" value=\"Forward\" name=\"btnForward\" onclick=\"p_forward()\">\n";
	}
	else
		echo "&nbsp;";
	echo "</td></tr>\n";
	echo "</table></form>\n<br><br>\n";
/*
	$structure = imap_fetchstructure($mbox, $nmsg);
	//Getting atachments
	if (is_array($structure->parts))
	{
		$isattach = 0;
		while(list($index, $sub_structure) = each($structure->parts)) 
		{
//			if ($sub_structure->type > 2 || $sub_structure->type == "")
			if (stristr($sub_structure->disposition, "attachment"))
			{
				$isattach = 1;
				$icon = "img/";
				switch ($sub_structure->subtype)
				{
					case "MSWORD":
						$icon .= "doc.gif";
						break;
					case "RICHTEXT":
						$icon .= "doc.gif";
						break;
					case "JPEG":
						$icon .= "img.gif";
						break;
					case "GIF":
						$icon .= "img.gif";
						break;
					case "VRML":
						$icon .= "vrml.gif";
						break;
					default:
						$icon .= "other.gif";
				}
				$att_name = "unknown";
				foreach ($sub_structure->parameters as $param)
				{
					if ($param->attribute == "name" || $param->attribute == "NAME")
					{
						$att_name = $param->value;
						$att_name = urlencode($att_name);
					}
				}
				//if a name of the attachment is not specifing in a paramatares array,
				// look this for in the disposition->filename
				if ($att_name == "unknown")
				{
					foreach ($sub_structure->dparameters as $param)
					{
						if ($param->attribute == "filename" || $param->attribute == "FILENAME")
						{
							$att_name = $param->value;
							$att_name = urlencode($att_name);
						}
					}
				}
				$info = "File:";
				$info .= $att_name;
				$info .= "    Bytes:";
				$info .= $sub_structure->bytes;
				$enc = $imap->get_mime_encoding($sub_structure);
				$part_no=$index+1;
				if ($att_name != "unknown") {
//					echo "<a href=\"encl.php?id=$id&nmsg=$nmsg&part_no=$part_no&name=$att_name&length=$sub_structure->bytes&encoding=$enc\"><img alt=\"$info\" width=\"40\" height=\"40\" src=\"$icon\" border=\"0\"></a>\n";
//					echo "&nbsp;&nbsp;&nbsp;";
				}
			}
		}
*/
		$aaa = $imap->get_attachments($mbox, $nmsg);
		if ($aaa != 0)
		{
			include ("utils.inc");
			$utils = new utilObj;
			foreach ($aaa as $value)
			{
				$info = "File:";
				$info .= $value->att_name;
				$info .= "    Bytes:";
				$info .= $value->size;
				echo "<a href=\"encl.php?id=$id&nmsg=$nmsg&part_no=".$value->part_no."&name=".urlencode($value->att_name)."&length=".$value->size."&encoding=".$value->encoding."\"><img alt=\"$info\" width=\"40\" height=\"40\" src=\"";
				echo "img/".$utils->chooseIcon($value->type);
				echo "\" border=\"0\"></a>\n";
				echo "&nbsp;&nbsp;&nbsp;";
			}
		}
//		if (stristr($HTTP_USER_AGENT,"MSIE 5.5") && $isattach == 1)
//		{
//			echo "<br><br>To download an attachment, you need right-click and choose SAVE TARGET AS ...\n";
//		}
//	}
	echo "</body></html>";
?>
