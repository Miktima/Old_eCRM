<?php
	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
	echo "<html>\n";
	echo "<head>\n";
	echo "<LINK rel=stylesheet href=\"stl.css\" type=\"text/css\">\n";
	echo "<script language=\"JavaScript\" src=\"util.js\"></script>\n";
	echo "<title>Outside Services &gt; Assigned messages</title>\n";
	echo "</head>\n";
	echo "<body>\n";
	echo "[&nbsp;<a href=\"logout.php\">logout</a>&nbsp;]&nbsp;&nbsp;&nbsp;[&nbsp;<a href=\"settings.php\">settings</a>&nbsp;]<br><br>\n";
	echo "<h1>assigned to ";
	echo strtoupper($NAME);
	echo "</h1>\n";
	echo "<table cellpadding=\"10\" cellspacing=\"0\" width=\"80%\">\n";
	echo "<tr>\n";
	echo "<th>From</th><th>Subject</th><th>Received</th><th>Priority</th>\n";
	echo "</tr>\n";
	$tcolor = array("#dddddd", "#cccccc");
	$tcnt = 0;
	include ("db.inc");
	$db = new dbObj;
	$db->init();
	$db->query("CREATE TEMPORARY TABLE corr_table (number INT, subject VARCHAR(200), startd DATETIME, customer INT, responsible INT, prior INT)");
//	echo "time - >".time()."<br>\n";
	$qw = "INSERT INTO corr_table (number, subject, startd, customer, responsible, prior) ";
	$qw .= "SELECT correspondence.number, correspondence.subject, correspondence.startd, ";
	$qw .= "correspondence.customer, correspondence.responsible, prior FROM correspondence ";
	$qw .= "WHERE (correspondence.lastd IS NULL) AND (responsible > 0)";
	$db->query($qw);	
//	echo "time - >".time()."<br>\n";
	$db->query("SELECT customer.email, corr_table.subject, corr_table.startd, priority.priority, corr_table.number FROM ((corr_table LEFT JOIN customer ON corr_table.customer = customer.number) LEFT JOIN staff ON corr_table.responsible = staff.number) LEFT JOIN priority ON corr_table.prior = priority.number WHERE staff.surname = '$NAME' ORDER BY corr_table.startd");
	while ($row = $db->rows ()) {
		echo "<tr bgcolor=\"";
		if ($tcnt == 0) {echo $tcolor[$tcnt]; $tcnt = 1;}
		else {echo $tcolor[$tcnt]; $tcnt = 0;}
		echo "\"><td><a href=\"message.php?num=$row[4]\">$row[0]</a></td><td>$row[1]</td><td>$row[2]</td><td>$row[3]</td></tr>";
	}
//	echo "time - >".time()."<br>\n";
//	echo "error - >".mysql_error()."<br>\n";
	$db->query("DROP TABLE IF EXISTS corr_table");
	echo "</body></html>\n";
?>
