<?php
	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
	echo "<html>\n";
	echo "<head>\n";
	echo "<LINK rel=stylesheet href=\"stl.css\" type=\"text/css\">\n";
	echo "<script language=\"JavaScript\" src=\"util.js\"></script>\n";
	echo "<title>Outside Services &gt; Replay Message</title>\n";
	echo "</head>\n";
	echo "<body>\n";
	echo "<a href=\"javascript:history.back()\">back</a>\n";
	echo "<h1>message No $num</h1>\n";
	echo "<div id=\"alert\">$alert</div><br><br>\n";
	echo "<form action=\"send.php\" METHOD=\"POST\" enctype=\"multipart/form-data\" name=\"upload\">\n";
	echo "<table bgcolor=\"#c0c0c0\" cellpadding=\"10\" cellspacing=\"0\" width=\"550px\">\n";
	include ("dbmail.def");
	include ("db.inc");
	$db = new dbObj;
	$db->init();
	//the main query
	$db->query("SELECT startd, subject, customer, prior, query, correspondence, comments, toaddress, responsible FROM correspondence WHERE number = $num" );
	echo "<input type=\"Hidden\" name=\"num\" value=\"$num\">";
	$row = $db->rows();
	echo "<tr>";
	//Comments
	echo "<td>Comments:</td><td colspan=\"3\"><input type=\"Text\" name=\"comments\" value=\"$row[6]\" size=\"60\"></td></tr>\n";
	echo "<tr>";
	//Received
	echo "<td>Received:</td><td><strong>$row[0]</strong></td>\n";
	$db->query("SELECT toaddress FROM toaddress WHERE number = $row[7]"); 
	$adr = $db->rows();
	//TO address
	echo "<td>To: </td><td><strong>$adr[0]</strong></td></tr>\n";
	echo "<input type=\"Hidden\" name=\"fromaddress\" value=\"$adr[0]\">";
	echo "<tr>";
	//Subject
	echo "<td>Subject: </td><td colspan=\"3\"><input type=\"Text\" name=\"subject\" value=\"";
	if (!stristr(substr($row[1],0,3),'RE:')) {echo "RE: ";}
	echo "$row[1]\" size=\"60\"></td></tr>\n";
	echo "<tr>";
	$db->query("SELECT number FROM correspondence WHERE customer = $row[2]");
	//number of messages from the customer
	$nentries = $db->get_number();
	$db->query("SELECT email, name, surname, country, firm FROM customer WHERE number = $row[2]");
	$customer = $db->rows();
	echo "<input type=\"Hidden\" name=\"customer\" value=\"$row[2]\">";
	//E-Mail
	echo "<td>Email: </td><td><input type=\"Text\" name=\"uemail\" value=\"$customer[0]\" size=\"20\">";
	//<strong>$customer[0]</strong>
	echo "&nbsp;<input type=\"Submit\" name=\"cngTo\" value=\"Change\"></td>\n";
	//CC field
	echo "<td>CC: </td><td><input type=\"Text\" name=\"cc\" value=\"\" size=\"20\"></td></tr>\n";
	echo "<tr>";
	//Name field
	echo "<td>Name: </td><td><nobr><input type=\"Text\" name=\"name\" value=\"$customer[1]\" size=\"20\">";
	//if number of messages is more than 1, then display link to reports
	if ($nentries > 1) {echo "<a href=\"dist.php?dist=customer&email=".urlencode($customer[0])."&period=any&open=help\" target=\"help\"><img src=\"img/abook.gif\" height=\"24\" width=\"25\" border=\"0\"></a>";}
	include ("db_remote.inc");
	$dbRemote = new dbRemoteObj;
	$dbRemote->init();
	$pur_user = $dbRemote->find('customer', 'email', $customer[0]);
	if ($pur_user != 0) {echo "<a href=\"http://ecrm.paragraph.ru/sales/report/filter.php?filter=email&filter_field=".urlencode($customer[0])."\" target=\"help\"><img src=\"img/money.gif\" height=\"25\" width=\"31\" border=\"0\"></a>\n";}
	echo "</nobr></td>\n";
	//Surname field
	echo "<td>Surname:</td><td><input type=\"Text\" name=\"surname\" value=\"$customer[2]\" size=\"20\"></td></tr>\n";
	echo "<tr>";
	$db = new dbObj;
	$db->init();
	if ($customer[3] != "")
	{
		$db->query("SELECT country FROM country WHERE number = $customer[3]");
		$rcntry = $db->rows(); 
		$cntry = $rcntry[0];
	}
	else
	{
		$cntry = "";
	}
	if ($customer[4] != "")
	{
		$db->query("SELECT firm FROM firm WHERE number = $customer[4]");
		$rfrm = $db->rows();
		$frm = $rfrm[0];
	}
	else
	{
		$frm = "";
	}
	//Firm
	echo "<td>Company:</td><td><input type=\"Text\" name=\"firm\" value=\"$frm\" size=\"20\"></td>\n";
	//Country
	echo "<td>Country:</td><td><select name=\"country\">\n";
	$db->query("SELECT number, country FROM country");
	while ($coun = $db->rows ()) {
		echo "<option value=\"$coun[0]\" ";
		if ($coun[1] == $cntry) echo "selected";
		echo ">$coun[1]";
	}
	echo "</select></td></tr>\n";
	// obsolete string
//	echo "<input type=\"Text\" name=\"country\" value=\"$cntry\" size=\"20\"></td>\n";
	echo "</tr>\n";
	$db->query("SELECT priority FROM priority WHERE number = $row[3]");
	$prior = $db->rows();
	echo "<tr>";
	//Priority
	echo "<td>Priority:</td><td><strong>$prior[0]</strong></td>\n";
	$db->query("SELECT number, query FROM query");
	//Message Type
	echo "<td>Message Type (<a href=\"mestype_help.html\" target=\"help\">?</a>)</td><td><select name=\"query\">\n";
	while ($qur = $db->rows ()) {
		echo "<option value=\"$qur[0]\" ";
		if ($qur[0] == $row[4]) echo "selected";
		echo ">$qur[1]";
	}
	echo "</select></td></tr>\n";
	echo "<tr><td colspan=\"4\">";
	//List of products
	$db->query("SELECT product FROM corrproduct WHERE corr = $num");
	$products = array();
	$i = 0;
	while ($prd = $db->rows ())
	{
		$products[$i] = $prd[0];
		$i++;
	}
	echo "Products:<br>\n";
	echo "<table width=\"100%\">\n";
	$db->query("SELECT number, product FROM product");
	echo "<tr>\n";
	while ($prd = $db->rows ()) {
		$pname = "prod".$prd[0];
		echo "<td><input type=\"Checkbox\" name=\"$pname\" ";
		if (in_array($prd[0], $products)) {echo "checked";}
		echo "><strong>$prd[1]</strong>";
		if (($prd[0]%4) == 0) {echo "</td></tr><tr>";}
		else {echo "</td>";}
	}
	echo "</tr></table></td></tr>\n";
	echo "<tr>";
	//Incoming Message
	echo "<td colspan=\"4\">Message&nbsp;&nbsp;&nbsp;<a href=\"minhtml.php?id=-1&nmsg=$num\" target=\"help\">HTML</a>";
	echo "&nbsp;&nbsp;&nbsp;<a href=\"stanswers.php\" target=\"help\">Standard Letters</a><br>\n";
	echo "<textarea name=\"incoming\" rows=\"20\" cols=\"80\">";
	if (substr(stripslashes($row[5]), 0, 5) == "From:")
	{
		$db->query("SELECT signature FROM staff WHERE number = $row[8]");
		$sign = $db->rows();
		$signature = str_replace("[mail]", substr($adr[0], strpos($adr[0], 60)+1, strrpos($adr[0],62)-strpos($adr[0], 60)-1), $sign[0]);
		echo $signature."\n\n";
	}
	echo stripslashes($row[5]);
	echo "</textarea></td></tr>\n";
	echo "<tr><td><input type=\"Radio\" name=\"tchoise\" value=\"reply\">&nbsp;-&nbsp;Reply</td>";
	echo "<td><input type=\"Radio\" name=\"tchoise\" value=\"reply_nc\">&nbsp;-&nbsp;Reply&nbsp;(not&nbsp;close&nbsp;the&nbsp;ticket)</td>";
	echo "<td><input type=\"Radio\" name=\"tchoise\" value=\"save\">&nbsp;-&nbsp;Save&nbsp;w/o&nbsp;answer</td>";
	echo "<td><input type=\"Radio\" name=\"tchoise\" value=\"close\">&nbsp;-&nbsp;Close&nbsp;the&nbsp;ticket&nbsp;</td></tr>\n";
	if (UseForwardU == "Yes")
		echo "<tr><td colspan=\"2\"><input type=\"Radio\" name=\"tchoise\" value=\"fwd\"> - Forward</td><td colspan=\"2\"><input type=\"Text\" name=\"forward\" value=\"\" size=\"20\"></td></tr>\n";
	echo "<tr><td colspan=\"4\" align=\"center\" valign=\"middle\"><input type=\"Submit\" name=\"btnSend\" value=\"Submit\"></td></tr>\n";
	//Attachment field
	for ($i = 0; $i < NAttFiles; $i++)
		echo "<tr><td><img src=\"img/clip.gif\" width=\"16\" height=\"16\" alt=\"clip".($i+1)."\"></td><td colspan=\"3\" align=\"center\"><input type=\"file\" name=\"userfile".($i+1)."\" size=\"45\"></td></tr>\n";
	echo "</table>";
	echo "</form>";
	//Displaying attachments
	$db->query("SELECT number, name, length, encoding, type FROM attachment WHERE correspondence = $num");
	if ($db->get_number() > 0)
	{
		include ("utils.inc");
		$utils = new utilObj;
		while ($att = $db->rows ()) {
			$icon = "img/".$utils->chooseIcon($att[4]);
			$info = "File:";
			$info .= $att[1];
			$info .= "    Bytes:";
			$info .= $att[2];
			echo "<a href=\"encldb.php?natt=$att[0]&name=$att[1]&length=$att[2]&encoding=$att[3]\"><img alt=\"$info\" width=\"40\" height=\"40\" src=\"$icon\" border=\"0\"></a>\n";		
			echo "&nbsp;&nbsp;&nbsp;";
		}
	}
?>
</body>
</html>
