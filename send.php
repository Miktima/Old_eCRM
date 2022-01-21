<?php
$atts = "";
foreach ($_FILES as $key => $value)
{
	$nerr = 0;
	if ($value['error'] == 0)
		$atts .= $value['name']." (".$value['size'].")   ";
	if ($value['error'] > 0 && $value['error'] < 4)
	{
		$nerr = 1;
		switch ($value['error'])
		{
			case 1:
			case 2:
				$alert = $value['name']." file exceeds the maximal file size";
				break;
			case 3:
				$alert = $value['name']." file was only partially uploaded";
				break;
		}
		break;
	}
}
if ($nerr == 1) 
	Header ("Location: message.php?num=$num&alert=$alert");
elseif (!strstr($cc,"@") && $cc != "") 
{
	$alert = "CC address is not valid!";
	Header ("Location: message.php?num=$num&alert=$alert");
}
elseif (!strstr($forward,"@") && $forward != "") 
{
	$alert = "Forward address is not valid!";
	Header ("Location: message.php?num=$num&alert=$alert");
}
elseif ($cngTo == "Change")
{
	Header ("Location: cngto.php?num=$num&newmail=".urlencode($uemail));
}
elseif ($tchoise == "") 
{
	$alert = "The Submit method is not chosen!";
	Header ("Location: message.php?num=$num&alert=$alert");
}
else
{
	include ("db.inc");
	include ("utils.inc");
	$db = new dbObj;
	$db->init();
	// change user
	$last_cust = $db->maxn('customer', 'number');
	$user = $db->find('customer', 'email', $uemail);
	if ($user == 0)
	{
		$user = $last_cust + 1;
		$db->query("INSERT customer (number, email) VALUES ($user, '$newmail')");
	}
	if ($customer != $user)
	{
		$db->query("UPDATE correspondence SET customer=$user WHERE number=$num");
		$customer = $user;
	}
	// ------
	$utils = new utilObj;
	// Address of one of ParallelGraphics mail boxes
	$from = $fromaddress;
	$content = $incoming;
//	$content = convert_cyr_string($content, "w", "k");
	if ($tchoise == "reply_nc" || $tchoise == "reply" || $tchoise == "fwd")
	{
		include ("mime.inc");
		$db->query("SELECT email FROM customer WHERE number=$customer");
		$em = $db->rows();
		$to = $em[0];
		$i = 0;
		//Create header of email
		if ($cc != "") 
			$headers[$i++] = "CC: $cc";
		$headers[$i++] = "Reply-To: $from";
		$headers[$i++] = "Date: ".date("r");
		$toaddress = $to;
		if ($cc != "") 
			$toaddress .= ", ".$cc;
		//	$bcc = "timofeev@paragraph.ru";
		if ($bcc != "") 
			$toaddress .= ", ".$bcc;
		$subject = stripslashes($subject);
		$content = stripslashes($content);
		if ($tchoise == "reply" || $tchoise == "reply_nc") 
			$mime = new MIME_mail($from, $toaddress, $subject, $content, $headers);
		// If the Froward button is clicked
		if ($tchoise == "fwd") 
		{
			$db->query("SELECT email, notification FROM staff WHERE surname='$NAME'");
			$fromfw = $db->rows();
			$i = 0;
			//Create header of email
			$headers[$i] = "To: $forward";
			if ($cc != "") 
				$headers[++$i] = "CC: $cc";
			$headers[++$i] = "Reply-To: $fromfw[0]";
			$headers[++$i] = "Date: ".date("r");
			$mime = new MIME_mail($fromfw[0], $forward, $subject, $content, $headers);
		}
	//Adding attachments
		foreach ($_FILES as $key => $value)
		{
			if ($value["error"] == 0)
			{
				$fname = get_cfg_var('upload_tmp_dir')."/".$value["name"];
				copy ($value["tmp_name"], $fname);
				if (!$mime->fattach($fname, $value["name"], $value["type"]))
					echo $mime->errstr;
				unlink ($fname);
			}
		}
		if (!($mime->send_mail())) 
		{
			$alert = "Error occurs during sending the e-mail:$mime->errstr";
			$utils->rdirect("message.php?num=$num&alert=$alert");
			return;
		}
	}
	//Create a content
	$content = addslashes($incoming);
	$contentdb = $content."\nAttachment: ".$atts;
	echo $atts;
	$lastd = date("Y-m-d H:i:s");
	//Update the CORRESPONDENCE table
	if ($tchoise == "close") {$contentdb = "-------------The message was closed! --------------\n\n".$content;}
	if ($tchoise == "close" || $tchoise == "reply") {$db->query("Update correspondence Set query=$query, correspondence='$contentdb', lastd='$lastd', comments='$comments' Where number=$num");}
	else {$db->query("Update correspondence Set query=$query, correspondence='$contentdb', comments='$comments' Where number=$num");}
	$nfirm = $db->find('firm', 'firm', $firm);
	//Insert a new firm
	if ($nfirm == 0)
	{
		$last_firm = $db->maxn('firm', 'number');
		$nfirm = $last_firm + 1;
		$db->query("Insert firm (number, firm) Values ($nfirm, '$firm')");
	}
	//Update customer
	$db->query("Update customer Set name='$name', surname='$surname', country=$country, firm=$nfirm Where number=$customer");
	$last_cp = $db->maxn('corrproduct', 'number');
	//Insert list of products
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
	// Checking which products are selected already
	$db->query("SELECT product FROM corrproduct WHERE corr = $num");
	$products_sel = array();
	$i = 0;
	while ($prd_sel = $db->rows ())
	{
		$products_sel[$i] = $prd_sel[0];
		$i++;
	}
	$i = 0;
	$products_del = array();
	// Checking which product is not selected again
	foreach ($products_sel as $value)
	{
		if(!in_array($value,$products))
		{
			$products_del[$i] = $value;
			$i++;
		}
	}
	foreach ($products as $value)
	{
		if (!in_array($value, $products_sel)) 
		{
			$last_cp++;
			$db->query("Insert corrproduct (number, corr, product) Values ($last_cp, $num, $value)");
		}
	}
	foreach ($products_sel as $value)
	{
		if (in_array($value, $products_del))
		{
			$db->query("DELETE FROM corrproduct WHERE corr=$num AND product=$value");
		}
	}
	$utils->rdirect("assigned.php");
}
?>
