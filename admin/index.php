<?php
	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
	echo "<html><head>\n";
	echo "<LINK rel=stylesheet href=\"../stl.css\" type=\"text/css\">\n";
	echo "<script language=\"JavaScript\" src=\"../util.js\"></script>\n";
	echo "<title>Outside Services &gt; Administration</title>\n";
	echo "</head>\n";
	echo "<body leftmargin=\"30\">\n";
	echo "[&nbsp;<a href=\"../logout.php\">logout</a>&nbsp;]&nbsp;&nbsp;&nbsp;[&nbsp;<a href=\"../settings.php?sysuser=$NAME\">settings</a>&nbsp;]\n";
	echo "<h1>Show</h1>\n";
	echo "<form action=\"index.php\" method=\"post\" name=\"formAdmin\">\n";
	echo "<input type=\"Radio\" name=\"admin\" value=\"deleted\" ";
	if ($admin == "deleted") {echo "checked";}
	echo "> - deleted<br>\n";
	echo "<input type=\"Radio\" name=\"admin\" value=\"answered\" ";
	if ($admin == "answered") {echo "checked";}
	echo "> - answered<br>\n";
	echo "<input type=\"Radio\" name=\"admin\" value=\"unanswered\" ";
	if ($admin == "unanswered") {echo "checked";}
	echo "> - unanswered<br>\n";
	echo "<input type=\"Radio\" name=\"admin\" value=\"settings\" ";
	if ($admin == "settings") {echo "checked";}
	echo "> - settings<br>\n";
	echo "<input type=\"Radio\" name=\"admin\" value=\"perlustrate\" ";
	if ($admin == "perlustrate") {echo "checked";}
	echo "> - perlustrate<br>\n";
	echo "<input type=\"Submit\" name=\"typeadmin\" value=\"Show Fields\">";
	echo "</form>\n";
	if ($admin != "")
	{
		include ("../db.inc");
		$db = new dbObj;
		$db->init();
		echo "<form action=\"admin.php\" method=\"post\" name=\"formDist\">\n";
		echo "<input type=\"Hidden\" name=\"admin\" value=\"$admin\">\n";
		switch ($admin)
		{
			case "answered":
			case "unanswered":
			case "perlustrate":
				echo "number: <input type=\"Text\" name=\"number\" value=\"\" size=\"10\"><br>\n";
				echo "<br>in period: <select name=\"period\">\n";
				echo "<option value=\"any\">Anytime\n";
				echo "<option value=\"week\">past 1 week\n";
				echo "<option value=\"month\">past 1 month\n";
				echo "<option value=\"3month\">past 3 month\n";
				echo "<option value=\"6month\">past 6 month\n";
				echo "</select>\n";
				echo "<br><br><br>\n";
				break;
			case "deleted":
				echo "<br>in period: <select name=\"period\">\n";
				echo "<option value=\"any\">Anytime\n";
				echo "<option value=\"week\">past 1 week\n";
				echo "<option value=\"month\">past 1 month\n";
				echo "<option value=\"3month\">past 3 month\n";
				echo "<option value=\"6month\">past 6 month\n";
				echo "</select>\n";
				echo "<br><br><input type=\"submit\" name=\"delold\" value=\" Remove Old Messages \">\n";
				$lastmonth = mktime (0,0,0,date("m")-3,date("d"),  date("Y"));
				echo "- It is removed deleted messages received before ".date("d-M-y", $lastmonth)."<br>\n";
				echo "<br><br><br>\n";
				break;
			case "settings":
				echo "<input type=\"Radio\" name=\"set\" value=\"product\"> - product<br>\n";
				echo "<input type=\"Radio\" name=\"set\" value=\"staff\"> - staff<br>\n";
				echo "<input type=\"Radio\" name=\"set\" value=\"priority\"> - priority<br>\n";
				echo "<input type=\"Radio\" name=\"set\" value=\"query\"> - query<br>\n";
				echo "<input type=\"Radio\" name=\"set\" value=\"answer\"> - standard answer<br>\n";
				echo "<input type=\"Radio\" name=\"set\" value=\"mboxes\"> - mailboxes<br>\n";
				break;
		}
		echo "<input type=\"submit\" name=\"action\" value=\"Submit\">&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"reset\" value=\"Clear\" name=\"reset\">\n";
		echo "</form>\n";
	}
	echo "</body></html>\n";
?>
