<?php
	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
	echo "<html>\n";
	echo "<head>\n";
	echo "<LINK rel=stylesheet href=\"../stl.css\" type=\"text/css\">\n";
	echo "<script language=\"JavaScript\" src=\"../util.js\"></script>\n";
	echo "<title>Outside Services &gt; Edit Message</title>\n";
	echo "</head>\n";
	echo "<body>\n";
	echo "<a href=\"javascript:history.back()\">back</a>\n";
	echo "<br><br>\n";
	include ("../db.inc");
	$db = new dbObj;
	$db->init();
	echo "<form action=\"edit.php\" METHOD=\"POST\" name=\"edit_form\">\n";
	if ($set == "answer")
	{
		$db->query("SELECT number, subject, text FROM stanswer WHERE number = $n");
		$row = $db->rows();
		echo "<input type=\"Hidden\" name=\"n\" value=\"$n\">\n";
		echo "<input type=\"Hidden\" name=\"set\" value=\"$set\">\n";
		echo "<input type=\"Text\" name=\"subject\" value=\"$row[1]\" size=\"50\"><br>\n";
		echo "<textarea name=\"stanswer\" title=\"Standard Answer\" rows=\"20\" cols=\"70\">";
		echo $row[2];
		echo "</textarea><br><br><br>\n";
		echo "<input type=\"submit\" name=\"save\" value=\"Save\">";
//		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"Reset\" name=\"Clear\" value=\"Clear\"><br>\n";
	}
	if ($set == "priority")
	{
		$db->query("SELECT number, priority, alert FROM priority WHERE number = $n");
		$row = $db->rows();
		echo "<input type=\"Hidden\" name=\"n\" value=\"$n\">\n";
		echo "<input type=\"Hidden\" name=\"set\" value=\"$set\">\n";
		echo "Priority:&nbsp;<input type=\"Text\" name=\"additem\" value=\"$row[1]\" size=\"10\">\n";
		echo "&nbsp;&nbsp;&nbsp;";
		echo "Alert Period:&nbsp;<input type=\"Text\" name=\"alert\" value=\"$row[2]\" size=\"3\">\n";
		echo "<input type=\"submit\" name=\"save\" value=\"Save\">";
//		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"Reset\" name=\"Clear\" value=\"Clear\"><br>\n";
	}
	if ($set == "staff")
	{
		$db->query("SELECT number, name, surname FROM staff WHERE number = $n");
		$row = $db->rows();
		if ($alert == 1) {echo "<div id=\"alert\">The passwords are not match</div>";}
		echo "<input type=\"Hidden\" name=\"n\" value=\"$n\">\n";
		echo "<input type=\"Hidden\" name=\"set\" value=\"$set\">\n";
		echo "Name:&nbsp;<input type=\"Text\" name=\"staffname\" value=\"$row[1]\" size=\"20\">\n";
		echo "&nbsp;&nbsp;&nbsp;";
		echo "Surname:&nbsp;<input type=\"Text\" name=\"surname\" value=\"$row[2]\" size=\"20\">\n";
		echo "<br><br>";
		echo "Password:<br><input type=\"Password\" name=\"newpass1\" value=\"\" size=\"20\"><br>\n";
		echo "<input type=\"Password\" name=\"newpass2\" value=\"\" size=\"20\"><br><br>\n";
		echo "<input type=\"submit\" name=\"save\" value=\"Save\">";
//		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"Reset\" name=\"Clear\" value=\"Clear\"><br>\n";
	}
	if ($set == "mboxes")
	{
		$db->query("SELECT id, name, password, autoreply, arflag, subject FROM boxes WHERE id = $n");
		$row = $db->rows();
		echo "<input type=\"Hidden\" name=\"n\" value=\"$n\">\n";
		echo "<input type=\"Hidden\" name=\"set\" value=\"$set\">\n";
		echo "Mailbox:&nbsp;<input type=\"Text\" name=\"boxname\" value=\"$row[1]\" size=\"20\">\n";
		echo "&nbsp;&nbsp;&nbsp;";
		echo "Password:&nbsp;<input type=\"Text\" name=\"password\" value=\"$row[2]\" size=\"20\">\n";
		echo "&nbsp;&nbsp;&nbsp;";
		echo "Use autoreply:&nbsp;<input type=\"Checkbox\" name=\"arflag\" ";
		if ($row[4] == 1) {echo "checked";}
		echo ">\n";
		echo "<br><br>";
		echo "Subject:&nbsp;<input type=\"Text\" name=\"subject\" value=\"$row[5]\" size=\"80\"><br><br>\n";
		echo "Autoreply:<br><textarea title=\"Autoreply\" rows=\"10\" cols=\"70\" name=\"autoreply\">";
		echo $row[3];
		echo "</textarea><br>\n";
		echo "<input type=\"submit\" name=\"save\" value=\"Save\">";
//		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"Reset\" name=\"Clear\" value=\"Clear\"><br>\n";
	}
	echo "</form></body></html>\n";
?>
