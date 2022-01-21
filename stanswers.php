<?php
	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
	echo "<html>\n";
	echo "<head>\n";
	echo "<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=windows-1251\">\n";
	echo "<LINK rel=stylesheet href=\"stl.css\" type=\"text/css\">\n";
	echo "<script language=\"JavaScript\" src=\"util.js\"></script>\n";
	echo "<title>Outside Services &gt; Standard Letters</title>\n";
	echo "</head>\n";
	echo "<body>\n";
	include ("db.inc");
	$tcolor = array("#cccccc", "#dddddd");
	$db = new dbObj;
	$db->init();
	if (empty($n))
	{
		echo "<a href=\"javascript:window.close()\">close</a><br><br><br>\n";
		$tcnt = 0;
		$db->query("SELECT number, subject, modified FROM stanswer");
		echo "<table cellpadding=\"10\" cellspacing=\"0\" width=\"400px\">\n";
		echo "<tr><th>Subject</th><th>Modified</th></tr>\n";
		if ($db->get_number() > 0)
		{
			while ($row = $db->rows ())
			{
				echo "<tr bgcolor=\"";
				if ($tcnt == 0) {echo $tcolor[$tcnt]; $tcnt = 1;}
				else {echo $tcolor[$tcnt]; $tcnt = 0;}
				echo "\"><td><a href=\"stanswers.php?n=$row[0]\">$row[1]</td><td>$row[2]</td></tr>";
			}
		}
	}
	else
	{
		echo "<a href=\"stanswers.php\">back</a><br><br><br>\n";
		$db->query("SELECT subject, text FROM stanswer WHERE number=$n");
		$row = $db->rows();
		echo "<strong>$row[0]</strong><br><br>\n";
		echo "<textarea name=\"stanswer\" title=\"Standard Letter\" rows=\"20\" cols=\"70\">";
		echo $row[1];
		echo "</textarea>\n";
	}
	echo "</body></html>";
?>
