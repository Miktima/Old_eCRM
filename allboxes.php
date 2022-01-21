<?php
	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
	echo "<html>\n";
	echo "<head>\n";
	echo "<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=windows-1251\">\n";
	echo "<LINK rel=stylesheet href=\"stl.css\" type=\"text/css\">\n";
	echo "<script language=\"JavaScript\" src=\"util.js\"></script>\n";
	echo "<title>Outside Services &gt; Boxes</title>\n";
	echo "</head>\n";
	echo "<body>\n";
	include("dbmail.def");
	include ("utils.inc");
	$util = new utilObj;
	include ("db.inc");
	$db = new dbObj;
	$db->init();
	//Checking system logins
	$db->query("SELECT id, name, password FROM boxes");
	echo "[&nbsp;<a href=\"logout.php\">logout</a>&nbsp;]&nbsp;&nbsp;&nbsp;[&nbsp;<a href=\"settings.php?sysuser=$NAME\">settings</a>&nbsp;]<br><br>\n";
	echo "<table width=\"400\">\n";
	echo "<tr><th>Box</th><th>Total</th><th>New</th></tr>\n";
	$j = 0;
	while ($row = $db->rows ()) {
		if ($j%2 == 0) {echo "<tr bgcolor=\"#dddddd\">";}
		else {echo "<tr bgcolor=\"#cccccc\">";}
		//Try to open IMAP stream
		$mbox = imap_open (MailServer, $row[1], $row[2]);
		echo "<td><a href=\"boxes.php?id=$row[0]\">$row[1]</a></td>\n";
		$ntotal = 0;
		$nnew = 0;
		$nummsg = imap_num_msg ($mbox);
		if ($nummsg < 25)
		{
			for( $i=0 ; $i < $nummsg ; $i++ )
			{ 
				$msg = imap_header($mbox, $i+1);
				$ntotal++;
				if ($msg->Unseen == "U" || $msg->Recent == "N") {$nnew++;}
			}
		}
		else
		{
			$ntotal = ">25";  // Do not count email, if number more than 25
		}
		echo "<td>$ntotal</td><td>$nnew</td></tr>\n";
		$j++;
	}
	echo "</table><br><br>\n";
	if (empty($alert)) {echo "<a href=\"allboxes.php?alert=all\">Show all unanswered messages</a>\n";}
	else {echo "<a href=\"allboxes.php\">Show delayed unanswered messages</a>\n";}
	echo "<br><br><div id=\"alert\">Unanswered messages (";
	if (empty($alert)) {echo "delayed according to the priority";}
	else {echo "all";}
	echo ")</div>";
	$db->query("SELECT number, email FROM staff ORDER BY number");
	$staff = $db->get_array();
	$tcolor = array("#cccccc", "#dddddd");
	$tcnt = 0;
	$db->query("CREATE TEMPORARY TABLE corr_table (number INT, subject VARCHAR(200), startd DATETIME, lastd DATETIME, alert SMALLINT, customer INT, responsible INT)");
//	echo "error - >".mysql_error()."<br>\n";
	$qw = "INSERT INTO corr_table (number, subject, startd, lastd, alert, customer, responsible) ";
	$qw .= "SELECT correspondence.number, correspondence.subject, correspondence.startd, correspondence.lastd, ";
	$qw .= "priority.alert, correspondence.customer, correspondence.responsible FROM correspondence ";
	$qw .= "LEFT JOIN priority ON correspondence.prior = priority.number WHERE (correspondence.lastd IS NULL) ";
	$qw .= "AND (correspondence.comments <> '**DELETE**') AND (correspondence.responsible > 0)";
	$db->query($qw);	
//	echo "error - >".mysql_error()."<br>\n";
	$qw = "SELECT customer.email, firm.firm, corr_table.subject, corr_table.startd, corr_table.lastd, staff.surname, corr_table.number, staff.number, corr_table.alert FROM ((corr_table LEFT JOIN customer ON corr_table.customer = customer.number) LEFT JOIN staff ON corr_table.responsible = staff.number) LEFT JOIN firm ON customer.firm = firm.number ";
	// end of checking for incoming addresses
	$qw .= "ORDER BY corr_table.startd DESC";
	$db->query($qw);
//	echo "error - >".mysql_error()."<br>\n";
	echo "<table cellpadding=\"10\" cellspacing=\"0\" width=\"80%\">\n";
	echo "<tr>\n";
	echo "<th>From</th><th>Company</th><th>Subject</th><th>Received</th><th>Answered</th><th>Assigned</th>\n";
	echo "</tr>";
	if ($db->get_number() > 0)
	{
		while ($row = $db->rows ())
		{
			$dif = $util->difdate($row[3], $row[4]);
			if (empty($alert)) {$delay = $row[8];}
			else {$delay = -100;}
			if ($dif > $delay)
			{
				$staff_email = $staff[1][$row[7]-1];
				echo "<tr bgcolor=\"";
				if ($tcnt == 0) {echo $tcolor[$tcnt]; $tcnt = 1;}
				else {echo $tcolor[$tcnt]; $tcnt = 0;}
				echo "\"><td><a href=\"unansmessage.php?num=$row[6]\" target=\"assign\">$row[0]</a></td><td>$row[1]</td><td>$row[2]</td><td>$row[3]</td><td>$row[4] <div id=\"alert\">($dif)</div></td><td><a href=\"mailto:$staff_email\">$row[5]</a></td></tr>";
			}
		}
	}
	$db->query("DROP TABLE IF EXISTS corr_table");
	echo "</table>";
	echo "</html></body>\n";
?>
