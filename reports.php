<?php
	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
	echo "<html>\n";
	echo "<head>\n";
	echo "<LINK rel=stylesheet href=\"stl.css\" type=\"text/css\">\n";
	echo "<script language=\"JavaScript\" src=\"util.js\">\n";
	echo "</script>\n";
	echo "<title>Outside Services &gt; Reports</title>\n";
	echo "</head>\n";
	echo "<body leftmargin=\"30\">\n";
	echo "<a href=\"index.php\">back</a>\n";
	echo "<h1>distribution by</h1>\n";
	echo "<form action=\"reports.php\" method=\"post\" name=\"formReport\">\n";
	echo "<input type=\"Radio\" name=\"dist\" value=\"all\" ";
	if ($dist == "all") {echo "checked";}
	echo "> - all<br>\n";
	echo "<input type=\"Radio\" name=\"dist\" value=\"staff\" ";
	if ($dist == "staff") {echo "checked";}
	echo "> - staff<br>\n";
	echo "<input type=\"Radio\" name=\"dist\" value=\"customer\" ";
	if ($dist == "customer") {echo "checked";}
	echo "> - customer<br>\n";
	echo "<input type=\"Radio\" name=\"dist\" value=\"product\" ";
	if ($dist == "product") {echo "checked";}
	echo "> - product<br>\n";
	echo "<input type=\"Radio\" name=\"dist\" value=\"query\" ";
	if ($dist == "query") {echo "checked";}
	echo "> - query type<br>\n";
	echo "<input type=\"Radio\" name=\"dist\" value=\"search\" ";
	if ($dist == "search") {echo "checked";}
	echo "> - search<br>\n";
	echo "<input type=\"Radio\" name=\"dist\" value=\"mails\" ";
	if ($dist == "mails") {echo "checked";}
	echo "> - e-mails<br>\n";
	echo "<input type=\"Radio\" name=\"dist\" value=\"countries\" ";
	if ($dist == "countries") {echo "checked";}
	echo "> - countries<br>\n";
	echo "<input type=\"Radio\" name=\"dist\" value=\"response\" ";
	if ($dist == "response") {echo "checked";}
	echo "> - response time<br>\n";
	echo "<input type=\"Submit\" name=\"typedist\" value=\"Show Fields\">";
	echo "</form>\n";
	if ($dist != "")
	{
		include ("db.inc");
		$db = new dbObj;
		$db->init();
		echo "<form action=\"dist.php\" method=\"post\" name=\"formDist\">\n";
		echo "<input type=\"Hidden\" name=\"dist\" value=\"$dist\">\n";			
		if ($dist == "staff")
		{
			echo "Assigned to: <select name=\"staff\">\n";
			$db->query("Select number, name, surname From staff");
			echo "<option value=\"0\">All";
			while ($row = $db->rows ()) {
				echo "<option value=\"$row[0]\">$row[1] $row[2]";
			}
			echo "</select><br><br>\n";
			echo "<input type=\"Checkbox\" name=\"notrep\" value=\"notrep\"> Only unanswered<br><br>\n";
		}
		if ($dist == "query")
		{
			echo "product: <select name=\"product\">\n";
			$db->query("Select number, product From product");
			echo "<option value=\"0\">All";
			while ($row = $db->rows ()) {
				echo "<option value=\"$row[0]\">$row[1]";
			}
			echo "</select><br><br>\n";
		}
		if ($dist == "customer")
		{
			echo "E-mail: <input type=\"Text\" name=\"email\" value=\"\" size=\"20\">&nbsp;&nbsp;&nbsp;\n";
			echo "Surname: <input type=\"Text\" name=\"surname\" value=\"\" size=\"20\"><br><br>\n";
			echo "<input type=\"Checkbox\" name=\"notrep\" value=\"notrep\"> Only unanswered<br><br>\n";
		}
		if ($dist == "search")
		{
			echo "String in correspondence: <input type=\"Text\" name=\"search\" value=\"\" size=\"50\"><br>\n";
			echo "<input type=\"Radio\" name=\"searchtype\" value=\"ephrase\" checked> - exact phrase &nbsp;&nbsp;&nbsp;";
			echo "<input type=\"Radio\" name=\"searchtype\" value=\"allwords\"> - all words &nbsp;&nbsp;&nbsp;";
			echo "<input type=\"Radio\" name=\"searchtype\" value=\"anywords\"> - any words<br><br>\n";		
		}
//      >>> Specifying direct date 
		echo "From Date (dd/mm/yyyy): ";
		echo "<select name=\"fdd\">";
		echo "<option value=\"0\" selected>--";
		for ($i = 1; $i <= 31; $i++)
		{
			echo "<option value=\"$i\">$i\n";
		} 
		echo "</select>";
		echo "<select name=\"fdm\">";
		echo "<option value=\"0\" selected>--";
		for ($i = 1; $i <= 12; $i++)
		{
			echo "<option value=\"$i\">$i\n";
		} 
		echo "</select>";
		echo "<select name=\"fdy\">";
		echo "<option value=\"0\" selected>----";
		for ($i = 2001; $i <= 2005; $i++)
		{
			echo "<option value=\"$i\">$i\n";
		} 
		echo "</select>";
		echo "&nbsp;&nbsp;&nbsp;&nbsp;";
		echo "To Date (dd/mm/yyyy): ";
		echo "<select name=\"tdd\">";
		echo "<option value=\"0\" selected>--";
		for ($i = 1; $i <= 31; $i++)
		{
			echo "<option value=\"$i\">$i\n";
		} 
		echo "</select>";
		echo "<select name=\"tdm\">";
		echo "<option value=\"0\" selected>--";
		for ($i = 1; $i <= 12; $i++)
		{
			echo "<option value=\"$i\">$i\n";
		} 
		echo "</select>";
		echo "<select name=\"tdy\">";
		echo "<option value=\"0\" selected>----";
		for ($i = 2001; $i <= 2005; $i++)
		{
			echo "<option value=\"$i\">$i\n";
		} 
		echo "</select>";
		echo "<br><br>In Period: <select name=\"period\">\n";
		echo "<option value=\"any\">Anytime\n";
		echo "<option value=\"today\">today\n";
		echo "<option value=\"yesterday\">yesterday\n";
		echo "<option value=\"week\">past 1 week\n";
		echo "<option value=\"month\">past 1 month\n";
		echo "<option value=\"3month\">past 3 month\n";
		echo "<option value=\"6month\">past 6 month\n";
		echo "</select>\n";
		echo "<br><table>\n";
		$db->query("SELECT number, toaddress FROM toaddress");
		echo "<tr>\n";
		$all_addresses = "";
		while ($toadr = $db->rows ()) {
			$adr_name = "adr".$toadr[0];
			$all_addresses .= $adr_name."/";
			echo "<td><input type=\"Checkbox\" name=\"$adr_name\" checked>".$toadr[1];
			if (($toadr[0]%3) == 0) {echo "</td></tr><tr>";}
			else {echo "</td>";}
		}
		echo "</tr>\n";
		echo "<tr><td colspan=\"3\" align=\"center\"><input type=\"Button\" name=\"clearchb\" value=\"Clear All\" onclick=\"cl_checkb('$all_addresses')\"</td></tr></table>\n";
		echo "<br>\n";
		echo "<input type=\"submit\" name=\"action\" value=\"   Submit   \" onClick=\"checkdate ()\">\n";
		echo "</form>\n";
	}
echo "</body></html>\n";
?>
