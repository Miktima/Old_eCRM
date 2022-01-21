<?php
	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
	echo "<html>\n";
	echo "<head>\n";
	echo "<LINK rel=stylesheet href=\"../stl.css\" type=\"text/css\">\n";
	echo "<script language=\"JavaScript\" src=\"../util.js\"></script>\n";
	echo "<title>Outside Services &gt; Replay Message</title>\n";
	echo "</head>\n";
	echo "<body>\n";
	echo "<div id=\"alert\">$alert</div><br><br>\n";
	echo "<form action=\"send.php\" METHOD=\"POST\" enctype=\"multipart/form-data\" name=\"upload\">\n";
	echo "<table bgcolor=\"#c0c0c0\" cellpadding=\"10\" cellspacing=\"0\" width=\"550px\">\n";
	//Subject
	echo "<tr><td>Subject: </td><td colspan=\"3\"><input type=\"Text\" name=\"subject\" value=\"\" size=\"60\"></td></tr>\n";
	echo "<tr>";
	//E-Mail
	echo "<td>Email: </td><td><input type=\"Text\" name=\"uemail\" value=\"\" size=\"20\"></td>";
	//CC field
	echo "<td>CC: </td><td><input type=\"Text\" name=\"cc\" value=\"\" size=\"20\"></td></tr>\n";
	echo "<tr>";
	//Incoming Message
	echo "<td colspan=\"4\">Message";
	echo "<br>\n";
	echo "<textarea name=\"incoming\" title=\"Incoming Message\" rows=\"20\" cols=\"70\">";
	echo "</textarea></td></tr>\n";
	//Attachment field
	echo "<tr><td colspan=\"4\" align=\"center\">Attachment: <input type=\"file\" name=\"userfile1\" size=\"25\"></td></tr>\n";
	echo "<tr><td colspan=\"4\" align=\"center\" valign=\"middle\"><input type=\"Submit\" name=\"btnSend\" value=\"Submit\"></td></tr>\n";
	echo "</table>";
	echo "</form>";
phpinfo();
?>
</body>
</html>
