<?php
	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
	echo "<html>\n";
	echo "<head>\n";
	echo "<LINK rel=stylesheet href=\"stl.css\" type=\"text/css\">\n";
	echo "<script language=\"JavaScript\" src=\"util.js\"></script>\n";
	echo "<title>Outside Services &gt; Settings</title>\n";
	echo "</head>\n";
	echo "<body>\n";
	if (empty($sysuser)) {echo "[&nbsp;<a href=\"assigned.php\">back</a>&nbsp;]<br><br>\n";}
	else
	{
		if ($sysuser == "manager") {echo "[&nbsp;<a href=\"allboxes.php\">back</a>&nbsp;]<br><br>\n";}
		if ($sysuser == "admin") {echo "[&nbsp;<a href=\"admin/index.php\">back</a>&nbsp;]<br><br>\n";}
	}
	echo "<h1>settings for ";
	echo strtoupper($NAME);
	echo "</h1>\n";
	$tcolor = array("#dddddd", "#cccccc");
	$tcnt = 0;
	include ("db.inc");
	$db = new dbObj;
	$db->init();
	if ($alert == 1) {echo "<div id=\"alert\">The new passwords are not match</div>";}
	if ($alert == 2) {echo "<div id=\"alert\">The old password is wrong</div>";}
	echo "<form action=\"setsettings.php\" METHOD=\"POST\" name=\"formSettings\">\n";
	echo "<table cellpadding=\"10\" cellspacing=\"0\" width=\"500\">\n";
	if (empty($sysuser))
	{
		$db->query("SELECT email, notification, signature FROM staff WHERE surname='$NAME'");
		$row = $db->rows();
		echo "<tr bgcolor=\"$tcolor[0]\">\n";
		echo "<td>Email notification</td>\n";
		echo "<td><input type=\"Text\" name=\"email\" value=\"$row[0]\" size=\"30\"></td>";
		echo "<td><input type=\"Checkbox\" name=\"note\" ";
		if ($row[1] == 1) {echo "checked";}
		echo "></td></tr>";
		echo "<tr bgcolor=\"$tcolor[1]\"><td>Signature</td>";
		echo "<td><textarea name=\"signature\" title=\"Signature\" rows=\"7\" cols=\"30\">";
		echo $row[2];
		echo "</textarea></td>\n";
		echo "<td>[mail] is changed to the incoming mailbox</td></tr>";
		echo "<tr bgcolor=\"$tcolor[0]\">\n";
		echo "<td>Old password</td>\n";
		echo "<td><input type=\"Password\" name=\"oldpass\" value=\"\" size=\"30\"></td><td>&nbsp;</td></tr>\n";
		echo "<tr bgcolor=\"$tcolor[1]\">\n";
		echo "<td>New password</td>\n";
		echo "<td><input type=\"Password\" name=\"newpass1\" value=\"\" size=\"30\"></td><td>&nbsp;</td></tr>\n";
		echo "<tr bgcolor=\"$tcolor[1]\">\n";
		echo "<td>&nbsp;</td>\n";
		echo "<td><input type=\"Password\" name=\"newpass2\" value=\"\" size=\"30\"></td><td>&nbsp;</td></tr>\n";
		echo "</tr>";
		echo "<tr>";
		echo "<td><input type=submit name=submit value=Submit></td><td>&nbsp;</td><td><input type=reset name=reset value=Reset></td>\n";
		echo "</tr>";
	}
	else
	{
		echo "<input type=\"Hidden\" name=\"sysuser\" value=\"$sysuser\">\n";
		echo "<tr bgcolor=\"$tcolor[0]\">\n";
		echo "<td>Old password</td>\n";
		echo "<td><input type=\"Password\" name=\"oldpass\" value=\"\" size=\"30\"></td></tr>\n";
		echo "<tr bgcolor=\"$tcolor[1]\">\n";
		echo "<td>New password</td>\n";
		echo "<td><input type=\"Password\" name=\"newpass1\" value=\"\" size=\"30\"></td></tr>\n";
		echo "<tr bgcolor=\"$tcolor[1]\">\n";
		echo "<td>&nbsp;</td>\n";
		echo "<td><input type=\"Password\" name=\"newpass2\" value=\"\" size=\"30\"></td></tr>\n";
		echo "<tr><td><input type=\"Submit\" name=\"chpass\" value=\"Change Password\"></td>";
		echo "<td align=\"right\"><input type=\"Reset\" name=\"reset\" value=\"Reset\"></td>\n";
		echo "</tr>";
	}
	echo "</table>\n";
	echo "</body></html>\n";
?>
