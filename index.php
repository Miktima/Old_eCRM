<?php
	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
	echo "<html><head>\n";
	echo "<LINK rel=stylesheet href=\"stl.css\" type=\"text/css\">\n";
	echo "<script language=\"JavaScript\" src=\"util.js\"></script>\n";
	echo "<title>Outside Services &gt; Login</title>\n";
	echo "</head>\n";
	echo "<body>\n";
	echo "<br><br>";
	if ($nologin == 1) {echo "<div id=\"alert\" align=\"center\">Login or password is incorrect. Try again</div>\n";}
	echo "<br><br><br><br><br><br>\n";
	echo "<div align=\"center\">\n";
	echo "<form action=\"checkps.php\" METHOD=\"POST\" name=\"formLogin\">\n";
	echo "<br>\n";
	echo "<p>enter login and password<br><br>\n";
	echo "<input type=\"Text\" name=\"login\"><br><br>\n";
	echo "<input type=\"Password\" name=\"password\"><br>\n";
	echo "<br><br>\n";
	echo "<input type=submit name=submit value=Submit>&nbsp;&nbsp;<input type=reset name=reset value=Reset>\n";
	echo "</form>\n";
	echo "<br><br><br>\n";
	echo "<a href=\"reports.php\">reports</a><br><br>\n";
	echo "<a href=\"stanswers.php\">standard answers</a><br><br>\n";
	echo "<a href=\"..\sales\index.php\">sales db</a>\n";
	echo "</div>\n";
	echo "</body>\n";
	echo "</html>\n";
?>
