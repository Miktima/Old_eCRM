<?php
//	echo "<form action=\"test.php\" METHOD=\"POST\" name=\"formLogin\"  enctype=\"multipart/form-data\">\n";
	echo "<form action=\"test.php\" METHOD=\"POST\" name=\"formLogin\">\n";
	echo "<br>\n";
	echo "<p>enter login and password<br><br>\n";
	echo "<input type=\"Hidden\" name=\"num\" value=\"3\">";
	echo "<input type=\"Text\" name=\"login\"><br><br>\n";
	echo "<input type=\"Password\" name=\"password\"><br>\n";
	echo "<br><br>\n";
	echo "<input type=submit name=submit value=Submit>&nbsp;&nbsp;<input type=reset name=reset value=Reset>\n";
	echo "</form>\n";
?>
