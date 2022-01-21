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
	$nummsg = imap_num_msg ($mbox);
//	$db->query("LOCK TABLES customer WRITE, firm WRITE, attachment WRITE, correspondence WRITE;");
	if (!empty($btnRemspam))
	{
		for( $i=0 ; $i < $nummsg ; $i++ )
		{ 
			$msg = imap_header($mbox, $i+1);
			$from_o = $msg->from[0];
			if ($db->find("spamadr", "address", $from_o->mailbox."@".$from_o->host) != 0) 
			{
				imap_mail_move($mbox, $i+1, DoneBox);
			}
		}
	}
	if (!empty($delchoises))
	{
		for( $i=0 ; $i < $nummsg ; $i++ )
		{ 
			if (${"remmsg".$i} == "remove") 
			{
				$msg = imap_header($mbox, $i+1);
				$from_o = $msg->from[0];
				$uemail = $from_o->mailbox."@".$from_o->host;
				$date = date("Y-m-d H:i:s", $msg->udate);
				//add email to spam table
				if ($delchoises == "spam" || $delchoises == "virus") 
				{
					if ($db->find("spamadr", "address", $uemail) == 0) {$db->query("INSERT spamadr (address) VALUES ('$uemail')");}
				}
				$delreason = "Reason for deleting: ".$delchoises;
				
				$nfirm = $db->find('firm', 'firm', '');
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
				$corr = addslashes ($imap->get_message($mbox, $i+1, "TEXT/PLAIN"));
				if ($corr == "") {$corr = addslashes ($imap->get_message($mbox, $i+1, "TEXT/HTML"));}
				//create From string
				$hcor = "From: ".$from_o->personal." <".$from_o->mailbox."@".$from_o->host.">\n";
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
				$correspondence = $delreason."\n\n".addslashes(htmlspecialchars($hcor))."\n\n".$corr;
				//End of creating From string
				$subject = addslashes($imap->imap_conv($msg->subject));
				$defbox = $namebox;
				if ($namebox == "info2") {$defbox = "info";}
				if ($namebox == "feedback2") {$defbox = "feedback";}
				if ($namebox == "fboutline") {$defbox = "outline";}
				$toaddress = $db->sfind('toaddress', 'toaddress', $defbox);
				$db->query("INSERT correspondence (number, customer, startd, correspondence, subject, comments, toaddress, origcorr, origcust) VALUES ($last_cor, $user, '$date', '$correspondence', '$subject', '**DELETE**', $toaddress, '$correspondence', $user_orig)");
				$aaa = $imap->get_attachments($mbox, $i+1);
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
								$att = imap_base64(imap_fetchbody($mbox, $i+1, $value->part_no));
								break;
							case "qprint":
								$att = imap_qprint(imap_fetchbody($mbox, $i+1, $value->part_no));
								break;
							default:
								$att = imap_fetchbody($mbox, $i+1, $value->part_no);
						}
						$att = addslashes($att);
						$db->query("INSERT attachment (number, correspondence, att, name, length, encoding, type) VALUES ($last_att, $last_cor, '$att', '".$value->att_name."', ".$value->size.", '".$value->encoding."', '".$value->type."')");
					}
				}
				imap_mail_move($mbox, $i+1, DoneBox);
			}
		}
	}
//	$db->query("UNLOCK TABLES;");
	imap_expunge($mbox);
	$utils->rdirect("boxes.php?id=$id");
?>
