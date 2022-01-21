<?php
	include ("dbmail.def");
	include ("db.inc");
	include ("imap.inc");
	include ("utils.inc");
	$db = new dbObj;
	$imap = new imapObj;
	$utils = new utilObj;
	$db->init();
	$db->query("SELECT id, name, password FROM boxes WHERE id = $id");
	$row = $db->rows();
	$namebox = $row[1];
	$mbox = imap_open (MailServer, $row[1], $row[2]);
	// chaging destination email
	if ($operation == "change")
	{
		include ("db.inc");
		include ("utils.inc");
		$db = new dbObj;
		$utils = new utilObj;
		$db->init();
//		$db->query("LOCK TABLES customer WRITE;");
		$last_cust = $db->maxn('customer', 'number');
		$user = $db->find('customer', 'email', $uemail);
		if ($user == 0)
		{
			$firm = $db->find('firm', 'firm', '');
			$user = $last_cust + 1;
			$db->query("INSERT customer (number, email, firm, country) VALUES ($user, '$uemail', $firm, 0)");
		}
//		$db->query("UNLOCK TABLES;");
		$utils->rdirect("text.php?id=$id&nmsg=$nmsg&user=$user");
	}
	else
	{
		include "mime.inc";
		$msg = imap_header($mbox, $nmsg);
		$from_o = $msg->from[0];
		$date = date("Y-m-d H:i:s", $msg->udate);
//		$db->query("LOCK TABLES customer WRITE, firm WRITE, attachment WRITE, correspondence WRITE;");
		if ($operation == "delete")
		{
			$delreason = "Reason for deleting: ".$delreason;
		}
		if ($operation == "assign" || $operation == "delete")
		{
			$nfirm = $db->find('firm', 'firm', $firm);
			//Insert a new firm
			if ($nfirm == 0)
			{
				$last_firm = $db->maxn('firm', 'number');
				$nfirm = $last_firm + 1;
				$db->query("Insert firm (number, firm) Values ($nfirm, '$firm')");
			}
			//getting the last customer number
			$last_cust = $db->maxn('customer', 'number');
			//finding the current customer
			$user = $db->find('customer', 'email', $uemail);
			//insert a new customer information if he is not available in the DB
			if ($user == 0)
			{
				$user = $last_cust + 1;
				$db->query("INSERT customer (number, email, name, surname, country, firm) VALUES ($user, '$uemail', '$name', '$surname', $country, $nfirm)");
			}
			else
			{
				$db->query("Update customer Set name='$name', surname='$surname', country=$country, firm=$nfirm Where number=$user");
			}
		}
		//clear email address if it is available in the spam and virus table
		if ($operation == "assign" || $operation == "forward")
		{
			if ($db->find("spamadr", "address", $uemail) != 0)
			{
				$db->query("DELETE FROM spamadr WHERE address='$uemail'");
			}
		}
		//inserting email addresses to the spam and virus table when the corresponding reasons are available
		if ($operation == "delete")
		{
			if (substr($delreason, 21, 4) == "spam" || substr($delreason, 21, 5) == "virus") 
			{
				if ($db->find("spamadr", "address", $uemail) == 0) {$db->query("INSERT spamadr (address) VALUES ('$uemail')");}
			}
		}
		if ($operation == "assign" || $operation == "delete")
		{
			//Keepping the original email (it may be differnet from the main)
			//getting the last customer number
			$last_cust = $db->maxn('customer', 'number');
			//finding the current customer
			$user_orig = $db->find('customer', 'email', $from_o->mailbox."@".$from_o->host);
			//insert a new customer information if he is not available in the DB
			if ($user_orig == 0)
			{
				$user_orig = $last_cust + 1;
				$email = $from_o->mailbox."@".$from_o->host;
				$db->query("INSERT customer (number, email) VALUES ($user_orig, '$email')");
			}
			//getting the last correspondence number
			$last_cor = $db->maxn('correspondence', 'number');
			$last_cor++;
		}
		$corr = addslashes ($imap->get_message($mbox, $nmsg, "TEXT/PLAIN"));
		if ($corr == "") {$corr = addslashes ($imap->get_message($mbox, $nmsg, "TEXT/HTML"));}
		//create From string
		$hcor = "From: ".$imap->imap_conv($from_o->personal)." <".$from_o->mailbox."@".$from_o->host.">\n";
		$hcor .= "Sent: ".date("Y-m-d H:i:s", $msg->udate)."\n";
		$to_add = $msg->to;
		$hcor .= "To: ";
		if(is_array($to_add))
		{
			foreach($to_add as $vadd)
			{
				$hcor .= $imap->imap_conv($vadd->personal)." <".$vadd->mailbox."@".$vadd->host."> ";
			}
		}
		$hcor .= "\n";
		$cc_add = $msg->cc;
		$hcor .= "CC: ";
		if(is_array($cc_add))
		{
			foreach($cc_add as $cadd)
			{
				$hcor .= $imap->imap_conv($cadd->personal)." <".$cadd->mailbox."@".$cadd->host."> ";
			}
		}
		$hcor .= "\n";
		$hcor .= "Subject: ".$imap->imap_conv($msg->subject)."\n";
		switch ($operation)
		{
			case "delete":
				$correspondence = $delreason."\n\n";
			case "assign":
				$correspondence .= addslashes(htmlspecialchars($hcor))."\n\n".$corr;
				break;
			case "forward":
				$correspondence = $hcor."\n\n".stripslashes($corr);
				break;
		}
		//End of creating From string
		$subject = addslashes ($imap->imap_conv($msg->subject));
		$defbox = $namebox;
		if ($namebox == "info2") {$defbox = "info";}
		if ($namebox == "feedback2") {$defbox = "feedback";}
		if ($namebox == "fboutline") {$defbox = "outline";}
		$toaddress = $db->sfind('toaddress', 'toaddress', $defbox);
		if ($operation == "forward")
		{
			$db->query("SELECT number, toaddress FROM toaddress WHERE number = $toaddress");
			$row = $db->rows();
			$toaddress = $row[1];
			//Create header of email
			$adrto = $forward;
			$headers[0] = "Reply-To: $toaddress";
			$headers[1] = "Date: ".date("r");
			$mime = new MIME_mail($toaddress, $adrto, "FW: ".$subject, $correspondence, $headers);
		}
		if ($operation == "assign" || $operation == "delete")
		{
			if ($operation == "delete") 
			{
				$comments = "**DELETE**";
				$assign = 'NULL';
			}
			$db->query("INSERT correspondence (number, customer, startd, responsible, correspondence, prior, subject, comments, toaddress, query, origcorr, origcust) VALUES ($last_cor, $user, '$date', $assign, '$correspondence', $priority, '$subject', '$comments', $toaddress, $query, '$correspondence', $user_orig)");
			echo mysql_error();
			//Insert list of products
			$last_cp = $db->maxn('corrproduct', 'number');
			$db->query("SELECT number, product FROM product");
			$products = array();
			$i = 0;
			while ($prd = $db->rows ())
			{
				$pname = ${"prod".$prd[0]};
				if ($pname == 'on')
				{
					$products[$i] = $prd[0];
					$i++;
				}
			}
			foreach ($products as $value)
			{
				$last_cp++;
				$db->query("Insert corrproduct (number, corr, product) Values ($last_cp, $last_cor, $value)");
			}
		}
		if ($operation == "assign")
		{
			//Notification about assigned message
			$db->query("SELECT email, notification FROM staff WHERE number=$assign");
			$notif_row = $db->rows ();
			if ($notif_row[1] == 1)
			{
				$headers[0] = "Reply-To: ecrm@parallelgraphics.com";
				$headers[1] = "Date: ".date("r");
				$mime = new MIME_mail("ecrm@parallelgraphics.com", $notif_row[0], "Assigned: ". stripslashes($subject), "The message was assigned to you:\n". stripslashes($hcor)."\n\n". stripslashes($corr), $headers);
				$mime->send_mail();
			}
		}
		$aaa = $imap->get_attachments($mbox, $nmsg);
		if ($aaa != 0)
		{
			$last_att = $db->maxn('attachment', 'number');
			include ("utils.inc");
			$utils = new utilObj;
			foreach ($aaa as $value)
			{
				$last_att++;
				switch ($value->encoding)
				{
					case "base64":
						$att = imap_base64(imap_fetchbody($mbox, $nmsg, $value->part_no));
						break;
					case "qprint":
						$att = imap_qprint(imap_fetchbody($mbox, $nmsg, $value->part_no));
						break;
					default:
						$att = imap_fetchbody($mbox, $nmsg, $value->part_no);
				}
				switch ($operation)
				{
					case "assign":
						$att = addslashes($att);
						$db->query("INSERT attachment (number, correspondence, att, name, length, encoding, type) VALUES ($last_att, $last_cor, '$att', '".$value->att_name."', ".$value->size.", '".$value->encoding."', '".$value->type."')");
						break;
					case "forward":
						$mime->attach($att, $value->att_name, $value->type, "base64", "attachment; filename=\"".$value->att_name."\"");
						break;
					case "delete":
						$att = addslashes($att);
						$db->query("INSERT attachment (number, correspondence, att, name, length, encoding, type) VALUES ($last_att, $last_cor, '$att', '".$value->att_name."', ".$value->size.", '".$value->encoding."', '".$value->type."')");
						break;
				}
			}
		}
		if ($operation == "forward")
		{
			$mime->send_mail(); // Forward email
		}
		if ($operation == "assign")
		{
			//Autoreply
			if (!empty($autoreply))
			{
				if ($autoreply == "on")
				{
					$db->query("SELECT subject, autoreply, arflag FROM boxes WHERE id = $id");
					$row = $db->rows();
					$db->query("SELECT toaddress FROM toaddress WHERE number = $toaddress");
					$aradr = $db->rows();
					$toaddress = substr($aradr[0], strpos($aradr[0], 60)+1, strrpos($aradr[0],62)-strpos($aradr[0], 60)-1);
					//Create header of email
					$headers[0] = "Reply-To: $aradr[0]";
					$headers[1] = "Date: ".date("r");
					$mime = new MIME_mail($toaddress, $from_o->mailbox."@".$from_o->host, $row[0], $row[1], $headers);
					$mime->send_mail();
				}
			}
		}
//		$db->query("UNLOCK TABLES;");
		//moving the mail into the Done mailbox
		imap_mail_move($mbox, "$nmsg", DoneBox);
		imap_expunge($mbox);
		// redirecting to appropriate page
		$totalemails = imap_num_msg ($mbox);
		if ($totalemails == 0)
			$utils->rdirect("boxes.php?id=$id");
		if ($nmsg > $totalemails)
			$nmsg = $totalemails;
		$utils->rdirect("text.php?id=$id&nmsg=$nmsg");
	}
?>
