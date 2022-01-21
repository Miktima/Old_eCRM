<?php
	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
	echo "<html>\n";
	echo "<head>\n";
	echo "<LINK rel=stylesheet href=\"../stl.css\" type=\"text/css\">\n";
	echo "<script language=\"JavaScript\" src=\"../util.js\"></script>\n";
	echo "<title>Outside Services &gt; Administrative Settings</title>\n";
	echo "</head>\n";
	echo "<body>\n";
	echo "<a href=\"index.php\">back</a>\n";
	echo "<br><br>\n";
	$fdd = 0;
	$fdm = 0;
	$fdy = 0;
	switch ($period)
	{
		case "week":
			$ftstamp = mktime (0,0,0,date("m"), date("d")-7, date("Y"));
			break;
		case "month":
			$ftstamp = mktime (0,0,0,date("m")-1, date("d"), date("Y"));
			break;
		case "3month":
			$ftstamp = mktime (0,0,0,date("m")-3, date("d"), date("Y"));
			break;
		case "6month":
			$ftstamp = mktime (0,0,0,date("m")-6, date("d"), date("Y"));
			break;
	}
	if ($period != "any")
	{
		$fdd = date("d", $ftstamp);
		$fdm = date("m", $ftstamp);
		$fdy = date("Y", $ftstamp);
	}
	$ttstamp = mktime (0,0,0,date("m"), date("d")+1, date("Y"));
	$tdd = date("d", $ttstamp);
	$tdm = date("m", $ttstamp);
	$tdy = date("Y", $ttstamp);
	include ("../db.inc");
	$db = new dbObj;
	$db->init();
	$tcolor = array("#cccccc", "#dddddd");
	// deleted
	if ($admin == "deleted")
	{
		if ($delold == " Remove Old Messages ")
		{
			$lastmonth = mktime (0,0,0,date("m")-3,date("d"),  date("Y"));
			// number of records that will be removed from DB
			$qw = "SELECT count(number) FROM correspondence WHERE correspondence.comments = '**DELETE**' AND correspondence.startd < '".date("Y-m-d", $lastmonth)."' GROUP BY correspondence.comments";
			$db->query($qw);
			$row = $db->rows();
			// list of removed records
			$ndrecords = $row[0];
			echo "Removed messages - $ndrecords<br>\n";
			if ($ndrecords > 0)
			{
				$qw = "SELECT number FROM correspondence WHERE correspondence.comments = '**DELETE**' AND correspondence.startd < '".date("Y-m-d", $lastmonth)."'";
				$db->query($qw);
				$row = $db->rows();
				$qw1 = "DELETE FROM attachment WHERE correspondence IN (";
				$qw2 = "DELETE FROM corrproduct WHERE corr IN (";
				while ($row = $db->rows ())
				{
					$qw1 .= $row[0].", ";
					$qw2 .= $row[0].", ";
				}
				$qw1 = substr($qw1, 0, strlen($qw1)-2);
				$qw1 .= ")";
				$qw2 = substr($qw2, 0, strlen($qw2)-2);
				$qw2 .= ")";
				$db->query($qw1);
				$db->query($qw2);
				$qw = "DELETE FROM correspondence WHERE correspondence.comments = '**DELETE**' AND correspondence.startd < '".date("Y-m-d", $lastmonth)."'";
				$db->query($qw);
			}
			$qw = "SELECT count(number) FROM correspondence WHERE correspondence.comments = '**DELETE**' GROUP BY correspondence.comments";
			$db->query($qw);
			$row = $db->rows();
			echo "All messages marked as 'deleted' - $row[0]";
		}
		else
		{
			$tcnt = 0;
			$qw = "SELECT customer.email, correspondence.subject, correspondence.startd, correspondence.lastd, correspondence.number FROM ((correspondence LEFT JOIN customer ON correspondence.customer = customer.number) LEFT JOIN staff ON correspondence.responsible = staff.number) LEFT JOIN priority ON correspondence.prior = priority.number WHERE correspondence.comments = '**DELETE**' ";
			if ($fdd+$fdm+$fdy > 0){$qw .= "AND correspondence.startd BETWEEN '$fdy-$fdm-$fdd' AND '$tdy-$tdm-$tdd' ";}
			$qw .= "ORDER BY correspondence.startd DESC";
			$db->query($qw);
			echo "<table cellpadding=\"10\" cellspacing=\"0\" width=\"600\">\n";
			echo "<tr>\n";
			echo "<form action=\"delm.php\" METHOD=\"POST\" name=\"delall_form\">\n";
			echo "<input type=\"Hidden\" name=\"admin\" value=\"$admin\">\n";
			echo "<input type=\"Hidden\" name=\"period\" value=\"$period\">\n";
			echo "<th>From</th><th>Subject</th><th>Received</th><th>Deleted</th>\n";
			echo "</tr>";
			if ($db->get_number() > 0)
			{
				while ($row = $db->rows ())
				{
					echo "<tr bgcolor=\"";
					if ($tcnt == 0) {echo $tcolor[$tcnt]; $tcnt = 1;}
					else {echo $tcolor[$tcnt]; $tcnt = 0;}
					echo "\"><td><input type=\"Checkbox\" name=\"n$row[4]\" value=\"on\">&nbsp;<a href=\"delmessage.php?num=$row[4]&admin=$admin&period=$period\">".substr($row[0], 0 , 50);
					if (strlen($row[0]) > 50)
						echo "[...]";
					echo "</a></td><td>".substr($row[1], 0, 100);
					if (strlen($row[1]) > 100)
						echo "[...]";
					echo "</td><td nowrap>".substr($row[2],0,10)."</td><td nowrap>".substr($row[3],0,10)."</td></tr>\n";
				}
			}
			echo "<tr><td colspan=\"4\" align=\"center\"><input type=\"Submit\" name=\"delall\" value=\"Remove from DB\"></td></tr>\n";
			echo "</table></form>";
		}
	}
	//answered
	if ($admin == "answered")
	{
		$tcnt = 0;
		$qw = "SELECT customer.email, correspondence.subject, correspondence.startd, correspondence.lastd, correspondence.number, staff.surname FROM ((correspondence LEFT JOIN customer ON correspondence.customer = customer.number) LEFT JOIN staff ON correspondence.responsible = staff.number) LEFT JOIN priority ON correspondence.prior = priority.number WHERE correspondence.comments <> '**DELETE**' AND correspondence.lastd IS NOT NULL ";
		if ($number > 0) {$qw .= "AND correspondence.number=$number ";} 
		if ($fdd+$fdm+$fdy > 0){$qw .= "AND correspondence.startd BETWEEN '$fdy-$fdm-$fdd' AND '$tdy-$tdm-$tdd' ";}
		$qw .= "ORDER BY correspondence.startd DESC";
		$db->query($qw);
		echo "<table cellpadding=\"10\" cellspacing=\"0\" width=\"80%\">\n";
		echo "<tr>\n";
		echo "<th>From</th><th>Subject</th><th>Received</th><th>Answered</th><th>Responsible</th>\n";
		echo "</tr>";
		if ($db->get_number() > 0)
		{
			while ($row = $db->rows ())
			{
				echo "<tr bgcolor=\"";
				if ($tcnt == 0) {echo $tcolor[$tcnt]; $tcnt = 1;}
				else {echo $tcolor[$tcnt]; $tcnt = 0;}
				echo "\"><td><a href=\"ansmessage.php?num=$row[4]&admin=$admin&period=$period\">".substr($row[0],0,50);
				if (strlen($row[0]) > 50)
					echo "[...]";
				echo "</a></td><td>".substr($row[1],0,100);
				if (strlen($row[1]) > 100)
					echo "[...]";
				echo "</td><td nowrap>".substr($row[2],0,10)."</td><td nowrap>".substr($row[3],0,10)."</td><td>$row[5]</td></tr>";
			}
		}
		echo "</table>";
	}
	// perlustrate
	if ($admin == "perlustrate")
	{
		$tcnt = 0;
		$qw = "SELECT customer.email, correspondence.subject, correspondence.startd, correspondence.lastd, correspondence.number, staff.surname FROM ((correspondence LEFT JOIN customer ON correspondence.customer = customer.number) LEFT JOIN staff ON correspondence.responsible = staff.number) LEFT JOIN priority ON correspondence.prior = priority.number WHERE correspondence.comments <> '**DELETE**' AND correspondence.lastd IS NOT NULL ";
		if ($number > 0) {$qw .= "AND correspondence.number=$number ";} 
		if ($fdd+$fdm+$fdy > 0){$qw .= "AND correspondence.startd BETWEEN '$fdy-$fdm-$fdd' AND '$tdy-$tdm-$tdd' ";}
		$qw .= "ORDER BY correspondence.startd DESC";
		$db->query($qw);
		echo "<table cellpadding=\"10\" cellspacing=\"0\" width=\"80%\">\n";
		echo "<tr>\n";
		echo "<th>From</th><th>Subject</th><th>Received</th><th>Answered</th><th>Responsible</th>\n";
		echo "</tr>";
		if ($db->get_number() > 0)
		{
			while ($row = $db->rows ())
			{
				echo "<tr bgcolor=\"";
				if ($tcnt == 0) {echo $tcolor[$tcnt]; $tcnt = 1;}
				else {echo $tcolor[$tcnt]; $tcnt = 0;}
				echo "\"><td><a href=\"permessage.php?num=$row[4]&admin=$admin&period=$period\">".substr($row[0],0,50);
				if (strlen($row[0]) > 50)
					echo "[...]";
				echo "</a></td><td>".substr($row[1],0,100);
				if (strlen($row[1]) > 100)
					echo "[...]";
				echo "</td><td nowrap>".substr($row[2],0,10)."</td><td nowrap>".substr($row[3],0,10)."</td><td>$row[5]</td></tr>";
			}
		}
		echo "</table>";
	}
	// unanswered
	if ($admin == "unanswered")
	{
		$tcnt = 0;
		$qw = "SELECT customer.email, correspondence.subject, correspondence.startd, correspondence.lastd, correspondence.number, staff.surname FROM ((correspondence LEFT JOIN customer ON correspondence.customer = customer.number) LEFT JOIN staff ON correspondence.responsible = staff.number) LEFT JOIN priority ON correspondence.prior = priority.number WHERE correspondence.comments <> '**DELETE**' AND correspondence.lastd IS NULL ";
		if ($number > 0) {$qw .= "AND correspondence.number=$number ";} 
		if ($fdd+$fdm+$fdy > 0){$qw .= "AND correspondence.startd BETWEEN '$fdy-$fdm-$fdd' AND '$tdy-$tdm-$tdd' ";}
		$qw .= "ORDER BY correspondence.startd DESC";
		$db->query($qw);
		echo "<table cellpadding=\"10\" cellspacing=\"0\" width=\"80%\">\n";
		echo "<tr>\n";
		echo "<th>From</th><th>Subject</th><th>Received</th><th>Responsible</th>\n";
		echo "</tr>";
		if ($db->get_number() > 0)
		{
			while ($row = $db->rows ())
			{
				echo "<tr bgcolor=\"";
				if ($tcnt == 0) {echo $tcolor[$tcnt]; $tcnt = 1;}
				else {echo $tcolor[$tcnt]; $tcnt = 0;}
				echo "\"><td><a href=\"ansmessage.php?num=$row[4]&admin=$admin&period=$period\">".substr($row[0],0,50);
				if (strlen($row[0]) > 50)
					echo "[...]";
				echo "</a></td><td>".substr($row[1],0,100);
				if (strlen($row[1]) > 100)
					echo "[...]";
				echo "</td><td nowrap>".substr($row[2],0,10)."</td><td>$row[5]</td></tr>";
			}
		}
		echo "</table>";
	}
	//settings
	if ($admin == "settings")
	{
		$tcnt = 0;
		echo "<form action=\"set.php\" METHOD=\"POST\" name=\"assign_form\">\n";
		echo "<input type=\"Hidden\" name=\"set\" value=\"$set\">\n";
		echo "<table cellpadding=\"10\" cellspacing=\"0\" width=\"400px\">\n";
		echo "<tr>\n";
		if ($set == "product")
		{
			$qw = "SELECT number, product FROM product";
			echo "<th>Number</th><th>Product</th><th>Delete</th>\n";
		}
		if ($set == "staff")
		{
			$qw = "SELECT number, surname, name FROM staff";
			echo "<th>Number</th><th>Name</th><th>Surname</th><th>Delete</th>\n";
		}
		if ($set == "mboxes")
		{
			$qw = "SELECT id, name, password FROM boxes";
			echo "<th>Number</th><th>Name</th><th>Password</th><th>Delete</th>\n";
		}
		if ($set == "priority")
		{
			$qw = "SELECT number, priority, alert FROM priority";
			echo "<th>Number</th><th>Priority</th><th>Alert period</th><th>Delete</th>\n";
		}
		if ($set == "query")
		{
			$qw = "SELECT number, query FROM query";
			echo "<th>Number</th><th>Message Info</th><th>Delete</th>\n";
		}
		if ($set == "answer")
		{
			$qw = "SELECT number, subject, modified FROM stanswer";
			echo "<th>Number</th><th>Subject</th><th>Modified</th><th>Delete</th>\n";
		}
		$db->query($qw);
		echo "</tr>";
		if ($set == "answer")
		{
			if ($db->get_number() > 0)
			{
				while ($row = $db->rows ())
				{
					echo "<tr bgcolor=\"";
					if ($tcnt == 0) {echo $tcolor[$tcnt]; $tcnt = 1;}
					else {echo $tcolor[$tcnt]; $tcnt = 0;}
					echo "\"><td>$row[0]</td><td><a href=\"editmessage.php?n=$row[0]&set=$set\">$row[1]</a></td><td>$row[2]</td><td align=\"center\"><input type=\"Checkbox\" name=\"$row[0]\" value=\"$row[0]\"></td></tr>";
				}
			}
			echo "<tr><td>Subject:</td><td colspan=\"3\"><input type=\"Text\" name=\"subject\" value=\"\" size=\"60\"></td></tr>";
			echo "<tr><td colspan=\"4\">Letter:<br><textarea name=\"stanswer\" title=\"Standard Answer\" rows=\"20\" cols=\"70\">";
			echo "</textarea></td></tr>";
			echo "<tr><td colspan=\"3\" align=\"left\"><input type=\"submit\" name=\"add\" value=\"Add\"></td>\n";
			echo "<td align=\"right\"><input type=\"submit\" name=\"del\" value=\"Delete Marked\"></td></tr>\n";
		}
		elseif ($set == "priority")
		{
			if ($db->get_number() > 0)
			{
				while ($row = $db->rows ())
				{
					echo "<tr bgcolor=\"";
					if ($tcnt == 0) {echo $tcolor[$tcnt]; $tcnt = 1;}
					else {echo $tcolor[$tcnt]; $tcnt = 0;}
					echo "\"><td>$row[0]</td><td><a href=\"editmessage.php?n=$row[0]&set=$set\">$row[1]</a></td><td>$row[2]</td><td align=\"center\"><input type=\"Checkbox\" name=\"$row[0]\" value=\"$row[0]\"></td></tr>";
				}
			}
			echo "<tr><td>&nbsp;</td><td><input type=\"Text\" name=\"additem\" value=\"\" size=\"10\"></td>";
			echo "<td><input type=\"Text\" name=\"alert\" value=\"\" size=\"3\"></td><td>&nbsp;</td></tr>\n";
			echo "<tr><td colspan=\"3\" align=\"left\"><input type=\"submit\" name=\"add\" value=\"Add\"></td>\n";
			echo "<td align=\"right\"><input type=\"submit\" name=\"del\" value=\"Delete Marked\"></td></tr>\n";
		}
		elseif ($set == "staff")
		{
			if ($db->get_number() > 0)
			{
				while ($row = $db->rows ())
				{
					echo "<tr bgcolor=\"";
					if ($tcnt == 0) {echo $tcolor[$tcnt]; $tcnt = 1;}
					else {echo $tcolor[$tcnt]; $tcnt = 0;}
					echo "\"><td>$row[0]</td><td>$row[2]</td><td><a href=\"editmessage.php?n=$row[0]&set=$set\">$row[1]</a></td><td align=\"center\"><input type=\"Checkbox\" name=\"$row[0]\" value=\"$row[1]\"></td></tr>";
				}
			}
			echo "<tr><td>&nbsp;</td><td><input type=\"Text\" name=\"staffname\" value=\"\" size=\"10\"></td>";
			echo "<td><input type=\"Text\" name=\"additem\" value=\"\" size=\"20\"></td><td>&nbsp;</td></tr>\n";
			echo "<tr><td colspan=\"3\" align=\"left\"><input type=\"submit\" name=\"add\" value=\"Add\"></td>\n";
			echo "<td align=\"right\"><input type=\"submit\" name=\"del\" value=\"Delete Marked\"></td></tr>\n";
		}
		elseif ($set == "mboxes")
		{
			if ($db->get_number() > 0)
			{
				while ($row = $db->rows ())
				{
					echo "<tr bgcolor=\"";
					if ($tcnt == 0) {echo $tcolor[$tcnt]; $tcnt = 1;}
					else {echo $tcolor[$tcnt]; $tcnt = 0;}
					echo "\"><td>$row[0]</td><td><a href=\"editmessage.php?n=$row[0]&set=$set\">$row[1]</a></td><td><input type=\"Password\" name=\"password\" value=\"$row[2]\"</td><td align=\"center\"><input type=\"Checkbox\" name=\"$row[0]\" value=\"$row[1]\"></td></tr>";
				}
			}
			echo "<tr><td>&nbsp;</td><td><input type=\"Text\" name=\"boxname\" value=\"\" size=\"10\"></td>";
			echo "<td><input type=\"Text\" name=\"password\" value=\"\" size=\"20\"></td><td>&nbsp;</td></tr>\n";
			echo "<tr><td colspan=\"3\" align=\"left\"><input type=\"submit\" name=\"add\" value=\"Add\"></td>\n";
			echo "<td align=\"right\"><input type=\"submit\" name=\"del\" value=\"Delete Marked\"></td></tr>\n";
		}
		else
		{
			if ($db->get_number() > 0)
			{
				while ($row = $db->rows ())
				{
					echo "<tr bgcolor=\"";
					if ($tcnt == 0) {echo $tcolor[$tcnt]; $tcnt = 1;}
					else {echo $tcolor[$tcnt]; $tcnt = 0;}
					echo "\"><td>$row[0]</td><td>$row[1]</td><td><input type=\"Checkbox\" name=\"$row[0]\" value=\"$row[1]\"></td></tr>";
				}
			}
			echo "<tr><td><input type=\"Text\" name=\"additem\" value=\"\" size=\"20\"><input type=\"submit\" name=\"add\" value=\"Add\"></td>\n";
			echo "<td><input type=\"submit\" name=\"del\" value=\"Delete Marked\"></td></tr>\n";
		}
		echo "</table></form>";
	}
	echo "</body></html>\n";
?>
