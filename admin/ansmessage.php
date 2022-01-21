<?php
	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
	echo "<html>\n";
	echo "<head>\n";
	echo "<LINK rel=stylesheet href=\"../stl.css\" type=\"text/css\">\n";
	echo "<script language=\"JavaScript\" src=\"../util.js\"></script>\n";
	echo "<title>Outside Services &gt; Message</title>\n";
	echo "</head>\n";
	echo "<body>\n";
	echo "<a href=\"javascript:history.back()\">back</a>\n";
	echo "<h1>message No $num</h1>\n";
	echo "<form action=\"ansm.php\" METHOD=\"POST\" name=\"assign_form\">\n";
	echo "<input type=\"Hidden\" name=\"num\" value=\"$num\">\n";
	echo "<input type=\"Hidden\" name=\"admin\" value=\"$admin\">\n";
	echo "<input type=\"Hidden\" name=\"period\" value=\"$period\">\n";
	echo "<table bgcolor=\"#c0c0c0\" cellpadding=\"10\" cellspacing=\"0\" width=\"550px\">\n";
	include ("../db.inc");
	$db = new dbObj;
	$db->init();
	$db->query("SELECT startd, subject, customer, prior, query, correspondence, comments, toaddress, query, origcorr FROM correspondence WHERE number = $num" );
	$row = $db->rows();
	echo "<tr>";
	echo "<td colspan=\"2\">Comments: <input type=\"Text\" name=\"comments\" value=\"$row[6]\" size=\"50\"></td></tr>\n";
	echo "<tr>";
	echo "<td>Received: <strong>$row[0]</strong></td>\n";
	$db->query("SELECT toaddress FROM toaddress WHERE number = $row[7]"); 
	$adr = $db->rows();
	echo "<td>To: <strong>$adr[0]</strong></td></tr>\n";
	echo "<tr>";
	echo "<td colspan=\"2\">Subject: <strong>$row[1]</strong></td></tr>\n";
	echo "<tr>";
	$db->query("SELECT email, name, surname, country, firm FROM customer WHERE number = $row[2]");
	$customer = $db->rows();
	echo "<td>Email: <strong>$customer[0]</strong></td>\n";
	echo "<td>&nbsp;</td></tr>\n";
	echo "<tr>";
	echo "<td>Name: <strong>$customer[1]</strong></td>\n";
	echo "<td>Surname: <strong>$customer[2]</strong></td></tr>\n";
	echo "<tr>";
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
	echo "<td>Company: <strong>$frm</strong></td>\n";
	echo "<td>Country: <strong>$cntry</strong></td>\n";
	echo "</tr>\n";
	if ($row[3] != "")
	{
		$db->query("SELECT priority FROM priority WHERE number = $row[3]");
		$pr = $db->rows();
		$prior = $pr[0];
	}
	else
	{
		$prior = "";
	}
	echo "<tr>";
	echo "<td>Priority: <strong>$prior</strong></td>\n";
	if ($row[8] != "")
	{
		$db->query("SELECT query FROM query WHERE number = $row[8]");
		$mt = $db->rows();
		$mtype = $mt[0];
	}
	else
	{
		$mtype = "";
	}
	echo "<td>Message Type :<strong>$mtype</strong></td></tr>\n";
	echo "<tr><td colspan=\"2\">";
	echo "Products: ";
	$db->query("SELECT product FROM corrproduct WHERE corr = $num");
	$products = array();
	$i = 0;
	while ($prd = $db->rows ())
	{
		$products[$i] = $prd[0];
		$i++;
	}
	foreach ($products as $value)
	{
		$db->query("SELECT product FROM product WHERE number = $value");
		$name_prd = $db->rows();
		echo "<strong>$name_prd[0]</strong> &nbsp;&nbsp;&nbsp;";
	}
	echo "<tr>";
	echo "<td colspan=\"2\">Answer<br><textarea title=\"Answer\" rows=\"20\" cols=\"70\" name=\"answer\">\n";
	echo stripslashes($row[5]);
	echo "\n\n--- Original Message from DB ---\n\n".stripslashes($row[9]);
	echo "</textarea></td>\n";
	echo "</tr>";
	echo "<tr><td>Assign to: <select name=\"staff\">\n";
	//Names from the staff table
	$db->query("Select number, name, surname From staff");
	while ($row = $db->rows ()) {
		echo "<option value=\"$row[0]\">$row[1] $row[2]";
	}
	echo "</select></td><td>";
	$db->query("Select number, priority From priority");
	echo "Priority <select name=\"priority\">\n";
	//Priority. The Normal priority is default
	while ($row = $db->rows ()) {
		echo "<option value=\"$row[0]\"";
		if ($row[1] == "Normal") {echo " selected";}
		echo ">$row[1]";
	}
	echo "</select>\n";
	echo "<input type=\"Submit\" value=\"Assign\" name=\"Assign\">\n";
	echo "</td></tr>\n";
	echo "</table></form>";
	//Displaying attachments
	include ("../utils.inc");
	$utils = new utilObj;
	$db->query("SELECT number, name, length, encoding, type FROM attachment WHERE correspondence = $num");
	if ($db->get_number() > 0)
	{
		while ($att = $db->rows ()) {
			$icon = "../img/".$utils->chooseIcon($att[4]);
			$info = "File:";
			$info .= $att[1];
			$info .= "    Bytes:";
			$info .= $att[2];
			echo "<a href=\"../encldb.php?natt=$att[0]&name=$att[1]&length=$att[2]&encoding=$att[3]\"><img alt=\"$info\" width=\"40\" height=\"40\" src=\"$icon\" border=\"0\"></a>\n";		
			echo "&nbsp;&nbsp;&nbsp;";
		}
	}
	echo "</body></html>\n";
?>
