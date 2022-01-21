<?php
	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
	echo "<html>\n";
	echo "<head>\n";
	echo "<LINK rel=stylesheet href=\"stl.css\" type=\"text/css\">\n";
	echo "<script language=\"JavaScript\" src=\"util.js\"></script>\n";
	echo "<title>Outside Services &gt; Query Result</title>\n";
	echo "</head>\n";
	echo "<body>\n";
	//if the help window is opened, then email should be encoded
	if ($open == "help")
	{
		echo "<a href=\"javascript:window.close()\">close</a>\n";
		$email = urldecode($email);
	}
	else
	{
		echo "<a href=\"reports.php\">back</a>\n";
	}
	echo "<br><br>\n";
	if ($fdy == 0) 
	{
		switch ($period)
		{
			case "today":
				$ftstamp = mktime (0,0,0,date("m"), date("d"), date("Y"));
				break;
			case "yesterday":
				$ftstamp = mktime (0,0,0,date("m"), date("d")-1, date("Y"));
				break;
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
			case "any":
				$ftstamp = mktime (0,0,0,1, 1, 1970);
				break;
		}
		$fdd = date("d", $ftstamp);
		$fdm = date("m", $ftstamp);
		$fdy = date("Y", $ftstamp);
		$ttstamp = mktime (0,0,0,date("m"), date("d")+1, date("Y"));
		$tdd = date("d", $ttstamp);
		$tdm = date("m", $ttstamp);
		$tdy = date("Y", $ttstamp);
	}
	// + 1 day becasue the BETWEEN statement is used in the queries
	else
	{
		$ttstamp = mktime (0,0,0,$tdm, $tdd+1, $tdy);
		$tdd = date("d", $ttstamp);
		$tdm = date("m", $ttstamp);
		$tdy = date("Y", $ttstamp);
	}
	include ("utils.inc");
	$util = new utilObj;
	include ("db.inc");
	$db = new dbObj;
	$db->init();
	$tcolor = array("#cccccc", "#dddddd");
	if ($dist == "staff")
	{
		$tcnt = 0;
		$qw = "SELECT customer.email, firm.firm, correspondence.subject, correspondence.startd, correspondence.lastd, staff.surname, correspondence.number FROM ((correspondence LEFT JOIN customer ON correspondence.customer = customer.number) LEFT JOIN staff ON correspondence.responsible = staff.number) LEFT JOIN firm ON customer.firm = firm.number WHERE correspondence.comments <> '**DELETE**' ";
		if ($staff >= 1) 
		{
			$qw .= "AND correspondence.responsible = $staff ";
			$db->query("SELECT surname FROM staff WHERE number = $staff");
			$st = $db->rows();
			$staff_surname = $st[0];
		}
		// check for incoming addresses
		$db->query("SELECT number, toaddress FROM toaddress");
		$numadrs = $db->get_number();
		$qw .= "AND correspondence.toaddress IN (0";
		for ($i = 1; $i <= $numadrs; $i++)
		{
			$pname = ${"adr".$i};
			if ($pname == 'on'){$qw .= ", $i";}
		}
		$qw .= ") ";
		// end of checking for incoming addresses
		if ($notrep != ""){$qw .= "AND correspondence.lastd IS NULL ";}
		if ($fdd+$fdm+$fdy > 0){$qw .= "AND correspondence.startd BETWEEN '$fdy-$fdm-$fdd' AND '$tdy-$tdm-$tdd' ";}
		$qww = $qw;
		$qw .= "ORDER BY correspondence.startd DESC";
		$qww .= "AND correspondence.correspondence LIKE '%-------------The message was closed! --------------%'";
		$db->query($qww);
		$num_closed = $db->get_number();
		$db->query($qw);
		echo "<h1>Assigned to ";
		if ($staff == 0)
			echo "all";
		else
			echo $staff_surname;
		echo ">>> \n";
		if ($fdd+$fdm+$fdy > 0) 
			echo "From $fdd/$fdm/$fdy to $tdd/$tdm/$tdy >>> \n";
		if ($notrep != "")
			echo "only unanswered >>>\n";
		echo "Number of records: ".$db->get_number()."\n";
		echo " (closed $num_closed)</h1><br>\n";
		echo "<table cellpadding=\"10\" cellspacing=\"0\" width=\"80%\">\n";
		echo "<tr>\n";
		echo "<th>From</th><th>Company</th><th>Subject</th><th>Received</th><th>Answered</th><th>Assigned</th>\n";
		echo "</tr>";
		if ($db->get_number() > 0)
		{
			while ($row = $db->rows ())
			{
				$dif = $util->difdate($row[3], $row[4]);
				echo "<tr bgcolor=\"";
				if ($tcnt == 0) {echo $tcolor[$tcnt]; $tcnt = 1;}
				else {echo $tcolor[$tcnt]; $tcnt = 0;}
				echo "\"><td><a href=\"infomessage.php?num=$row[6]\">$row[0]</a></td><td>$row[1]</td><td>$row[2]</td><td>";
				echo substr($row[3],8,2)."/".substr($row[3],5,2)."/".substr($row[3],2,2)."</td><td>";
				if ($row[4] != "")
					echo substr($row[4],8,2)."/".substr($row[4],5,2)."/".substr($row[4],2,2)." ($dif)";
				else
					echo "<span id=\"alert\">($dif)</span>";
				echo "</td><td>$row[5]</td></tr>";
			}
		}
		echo "</table>";
	}

	if ($dist == "customer")
	{
		$tcnt = 0;
		$qw = "SELECT customer.email, firm.firm, correspondence.subject, correspondence.startd, correspondence.lastd, staff.surname, correspondence.number FROM ((correspondence LEFT JOIN customer ON correspondence.customer = customer.number) LEFT JOIN staff ON correspondence.responsible = staff.number) LEFT JOIN firm ON customer.firm = firm.number WHERE correspondence.comments <> '**DELETE**' ";
		if ($email != ""){$qw .= "AND customer.email LIKE '%$email%' ";}
		if ($notrep != ""){$qw .= "AND correspondence.lastd IS NULL ";}
		// check for incoming addresses
		if ($open != "help")
		{
			$db->query("SELECT number, toaddress FROM toaddress");
			$numadrs = $db->get_number();
			$qw .= "AND correspondence.toaddress IN (0";
			for ($i = 1; $i <= $numadrs; $i++)
			{
				$pname = ${"adr".$i};
				if ($pname == 'on'){$qw .= ", $i";}
			}
			$qw .= ") ";
		}
		// end of checking for incoming addresses
		if ($surname != ""){$qw .= "AND customer.surname = '$surname' ";}
		if ($fdd+$fdm+$fdy > 0){$qw .= "AND correspondence.startd BETWEEN '$fdy-$fdm-$fdd' AND '$tdy-$tdm-$tdd' ";}
		$qw .= "ORDER BY correspondence.startd DESC";
		$db->query($qw);
		echo "<h1>Messages from";
		if ($email != "")
			echo " * $email *";
		if ($surname != "")
			echo " $surname";
		if ($email != "" && $surname != "")
			echo " all";
		echo ">>> \n";
		if ($fdd+$fdm+$fdy > 0) 
			echo "From $fdd/$fdm/$fdy to $tdd/$tdm/$tdy >>> \n";
		if ($notrep != "")
			echo "only unanswered >>>\n";
		echo "Number of records: ".$db->get_number()."</h1><br><br>\n";
		echo "<table cellpadding=\"10\" cellspacing=\"0\" width=\"80%\">\n";
		echo "<tr>\n";
		echo "<th>From</th><th>Company</th><th>Subject</th><th>Received</th><th>Answered</th><th>Assigned</th>\n";
		echo "</tr>";
		if ($db->get_number() > 0)
		{
			while ($row = $db->rows ())
			{
				$dif = $util->difdate($row[3], $row[4]);
				echo "<tr bgcolor=\"";
				if ($tcnt == 0) {echo $tcolor[$tcnt]; $tcnt = 1;}
				else {echo $tcolor[$tcnt]; $tcnt = 0;}
				echo "\"><td><a href=\"infomessage.php?num=$row[6]\">$row[0]</a></td><td>$row[1]</td><td>$row[2]</td><td>";
				echo substr($row[3],8,2)."/".substr($row[3],5,2)."/".substr($row[3],2,2)."</td><td>";
				if ($row[4] != "")
					echo substr($row[4],8,2)."/".substr($row[4],5,2)."/".substr($row[4],2,2)." ($dif)";
				else
					echo "<span id=\"alert\">($dif)</span>";
				echo "</td><td>$row[5]</td></tr>";
			}
		}
		echo "</table>";
	}

	if ($dist == "search")
	{
		$tcnt = 0;
		$qw = "SELECT customer.email, firm.firm, correspondence.subject, correspondence.startd, correspondence.lastd, staff.surname, correspondence.number FROM ((correspondence LEFT JOIN customer ON correspondence.customer = customer.number) LEFT JOIN staff ON correspondence.responsible = staff.number) LEFT JOIN firm ON customer.firm = firm.number WHERE correspondence.comments <> '**DELETE**' ";
		if ($search != "" && $searchtype == 'ephrase'){$qw .= "AND (correspondence.correspondence LIKE '%$search%' OR correspondence.origcorr LIKE '%$search%' OR correspondence.subject LIKE '%$search%') ";}
		if ($search != "" && ($searchtype == 'allwords' || $searchtype == 'anywords')){
			$search_array = explode(" ", $search);
			$qw .= "AND ((correspondence.correspondence LIKE '%".str_replace("+", " ", $search_array[0])."%' OR correspondence.origcorr LIKE '%".str_replace("+", " ", $search_array[0])."%' OR correspondence.subject LIKE '%".str_replace("+", " ", $search_array[0])."%') ";
			for ($i = 1; $i < count($search_array); $i++)
			{
				if ($searchtype == 'allwords') {$qw .= "AND ";}
				else {$qw .= "OR ";}
				$qw .= "(correspondence.correspondence LIKE '%".str_replace("+", " ", $search_array[$i])."%' OR correspondence.origcorr LIKE '%".str_replace("+", " ", $search_array[$i])."%' OR correspondence.subject LIKE '%".str_replace("+", " ", $search_array[$i])."%') ";
			}
			$qw .= ") ";
		}
		// check for incoming addresses
		$db->query("SELECT number, toaddress FROM toaddress");
		$numadrs = $db->get_number();
		$qw .= "AND correspondence.toaddress IN (0";
		for ($i = 1; $i <= $numadrs; $i++)
		{
			$pname = ${"adr".$i};
			if ($pname == 'on'){$qw .= ", $i";}
		}
		$qw .= ") ";
		// end of checking for incoming addresses
		if ($notrep != ""){$qw .= "AND correspondence.lastd IS NULL ";}
		if ($fdd+$fdm+$fdy > 0){$qw .= "AND correspondence.startd BETWEEN '$fdy-$fdm-$fdd' AND '$tdy-$tdm-$tdd' ";}
		$qw .= "ORDER BY correspondence.startd DESC";
		$db->query($qw);
		echo "<h1>Search '$search'>>> Mode: ";
		switch ($searchtype)
		{
			case "ephrase":
				echo "exact phrase";
				break;
			case "allwords":
				echo "all words";
				break;
			case "anywords":
				echo "any words";
				break;
		}
		echo " >>>";
		if ($fdd+$fdm+$fdy > 0) 
			echo "From $fdd/$fdm/$fdy to $tdd/$tdm/$tdy >>> \n";
		if ($notrep != "")
			echo "only unanswered >>>\n";
		echo "Number of records: ".$db->get_number()."</h1><br><br>\n";
		echo "<table cellpadding=\"10\" cellspacing=\"0\" width=\"80%\">\n";
		echo "<tr>\n";
		echo "<th>From</th><th>Company</th><th>Subject</th><th>Received</th><th>Answered</th><th>Assigned</th>\n";
		echo "</tr>";
		if ($db->get_number() > 0)
		{
			while ($row = $db->rows ())
			{
				$dif = $util->difdate($row[3], $row[4]);
				echo "<tr bgcolor=\"";
				if ($tcnt == 0) {echo $tcolor[$tcnt]; $tcnt = 1;}
				else {echo $tcolor[$tcnt]; $tcnt = 0;}
				echo "\"><td><a href=\"infomessage.php?num=$row[6]\">$row[0]</a></td><td>$row[1]</td><td>$row[2]</td><td>";
				echo substr($row[3],8,2)."/".substr($row[3],5,2)."/".substr($row[3],2,2)."</td><td>";
				if ($row[4] != "")
					echo substr($row[4],8,2)."/".substr($row[4],5,2)."/".substr($row[4],2,2)." ($dif)";
				else
					echo "<span id=\"alert\">($dif)</span>";
				echo "</td><td>$row[5]</td></tr>";
			}
		}
		echo "</table>";
	}

	if ($dist == "product")
	{
?>
		<script language="JavaScript">
		function chooseprd(prd)
		{
			document.frmDist.product.value = prd;
			document.frmDist.submit(); 
		}
		</script>
<?php
		echo "<h1>Distribution by products";
		if ($fdd+$fdm+$fdy > 0) 
			echo ">>> From $fdd/$fdm/$fdy to $tdd/$tdm/$tdy\n";
		echo "</h1><br><br>\n";
		$width = 400; //the maximal widht of bar in pixeles
		$h = 16;
		$tcnt = 0;
		$qw = "SELECT product.product, count(product.product) FROM (corrproduct LEFT JOIN correspondence ON corrproduct.corr = correspondence.number) LEFT JOIN product ON corrproduct.product = product.number WHERE correspondence.comments <> '**DELETE**' ";
		// check for incoming addresses
		$db->query("SELECT number, toaddress FROM toaddress");
		$numadrs = $db->get_number();
		$qw .= "AND correspondence.toaddress IN (0";
		for ($i = 1; $i <= $numadrs; $i++)
		{
			$pname = ${"adr".$i};
			if ($pname == 'on'){$qw .= ", $i";}
		}
		$qw .= ") ";
		// end of checking for incoming addresses
		if ($fdd+$fdm+$fdy > 0){$qw .= "AND correspondence.startd BETWEEN '$fdy-$fdm-$fdd' AND '$tdy-$tdm-$tdd' ";}
		$qw .= "GROUP BY product.product";
		$db->query($qw);
		$array_pr=Array();
		if ($db->get_number() > 0)
		{
			while ($row = $db->rows ())
			{
				$array_pr[$row[0]]=$row[1];
			}
		}
		$k = $width/max($array_pr);
		$s = array_sum($array_pr);
		echo "<table>\n";
		foreach($array_pr as $key => $value)
		{
			$ukey = urlencode($key);
			echo "<tr><td><a href=\"javascript:chooseprd('$ukey')\">$key</a></td>\n";
			$img_w = $value*$k;
			$part = $value*100.0/$s;
			echo "<td><img alt=\"$key\" src=\"img/dist.gif\" width=\"$img_w\" height=\"$h\">&nbsp;";
			echo sprintf ("%.1f", $part);
			echo "&nbsp;%&nbsp;($value)</td></tr>\n";
		}
		echo "</table>\n";
		echo "<form action=\"dist.php\" method=\"post\" name=\"frmDist\">\n";
		echo "<input type=\"Hidden\" name=\"fdd\" value=\"$fdd\">\n";
		echo "<input type=\"Hidden\" name=\"fdm\" value=\"$fdm\">\n";
		echo "<input type=\"Hidden\" name=\"fdy\" value=\"$fdy\">\n";
		echo "<input type=\"Hidden\" name=\"tdd\" value=\"$tdd\">\n";
		echo "<input type=\"Hidden\" name=\"tdm\" value=\"$tdm\">\n";
		echo "<input type=\"Hidden\" name=\"tdy\" value=\"$tdy\">\n";
		echo "<input type=\"Hidden\" name=\"dist\" value=\"product\">\n";
		for ($i = 1; $i <= $numadrs; $i++)
		{
			$pname = ${"adr".$i};
			echo "<input type=\"Hidden\" name=\"adr$i\" value=\"$pname\">";
		}
		echo "<input type=\"Hidden\" name=\"product\" value=\"\">\n";
		echo "</form>\n";
		if($product != "")
		{
			echo "<br><br><br>";
			$pr = urldecode($product);
			$npr = $db->find('product', 'product', $pr);
			echo "<h1>$pr</h1><br><br>";
			$qw = "SELECT customer.email, firm.firm, correspondence.subject, correspondence.startd, correspondence.lastd, staff.surname, correspondence.number FROM (((correspondence LEFT JOIN customer ON correspondence.customer = customer.number) LEFT JOIN corrproduct ON correspondence.number = corrproduct.corr) LEFT JOIN firm ON customer.firm = firm.number) LEFT JOIN staff ON correspondence.responsible = staff.number WHERE correspondence.comments <> '**DELETE**' AND corrproduct.product=$npr "; 
			// check for incoming addresses
			$db->query("SELECT number, toaddress FROM toaddress");
			$numadrs = $db->get_number();
			$qw .= "AND correspondence.toaddress IN (0";
			for ($i = 1; $i <= $numadrs; $i++)
			{
				$pname = ${"adr".$i};
				if ($pname == 'on'){$qw .= ", $i";}
			}
			$qw .= ") ";
			// end of checking for incoming addresses
			if ($fdd+$fdm+$fdy > 0){$qw .= "AND correspondence.startd BETWEEN '$fdy-$fdm-$fdd' AND '$tdy-$tdm-$tdd' ";}
			$qw .= "ORDER BY correspondence.startd DESC";
			$db->query($qw);
			echo "<table cellpadding=\"10\" cellspacing=\"0\" width=\"80%\">\n";
			echo "<tr>\n";
			echo "<th>From</th><th>Company</th><th>Subject</th><th>Received</th><th>Answered</th><th>Assigned</th>\n";
			echo "</tr>";
			if ($db->get_number() > 0)
			{
				while ($row = $db->rows ())
				{
					$dif = $util->difdate($row[3], $row[4]);
					echo "<tr bgcolor=\"";
					if ($tcnt == 0) {echo $tcolor[$tcnt]; $tcnt = 1;}
					else {echo $tcolor[$tcnt]; $tcnt = 0;}
					echo "\"><td><a href=\"infomessage.php?num=$row[6]\">$row[0]</a></td><td>$row[1]</td><td>$row[2]</td><td>";
					echo substr($row[3],8,2)."/".substr($row[3],5,2)."/".substr($row[3],2,2)."</td><td>";
					if ($row[4] != "")
						echo substr($row[4],8,2)."/".substr($row[4],5,2)."/".substr($row[4],2,2)." ($dif)";
					else
						echo "<span id=\"alert\">($dif)</span>";
					echo "</td><td>$row[5]</td></tr>";
				}
			}
			echo "</table>";
		}
	}

	if ($dist == "query")
	{
?>
		<script language="JavaScript">
		function choosequr(qur)
		{
			document.frmDist.query.value = qur;
			document.frmDist.submit(); 
		}
		</script>
<?php
		$db->query("SELECT product FROM product WHERE number = $product");
		$prd = $db->rows();
		echo "<h1>Distribution by query types";
		if ($fdd+$fdm+$fdy > 0) 
			echo ">>> From $fdd/$fdm/$fdy to $tdd/$tdm/$tdy";
		if ($products != "all")
			echo ">>> $prd[0]";
		echo "</h1><br><br>\n";
		$width = 400; //the maximal widht of bar in pixeles
		$h = 16;
		$tcnt = 0;
		$qw = "SELECT query.query, count(query.query) FROM (correspondence LEFT JOIN query ON correspondence.query = query.number) LEFT JOIN corrproduct ON corrproduct.corr=correspondence.number WHERE correspondence.comments <> '**DELETE**' ";
		// check for incoming addresses
		$db->query("SELECT number, toaddress FROM toaddress");
		$numadrs = $db->get_number();
		$qw .= "AND correspondence.toaddress IN (0";
		for ($i = 1; $i <= $numadrs; $i++)
		{
			$pname = ${"adr".$i};
			if ($pname == 'on'){$qw .= ", $i";}
		}
		$qw .= ") ";
		// end of checking for incoming addresses
		if ($fdd+$fdm+$fdy > 0){$qw .= "AND correspondence.startd BETWEEN '$fdy-$fdm-$fdd' AND '$tdy-$tdm-$tdd' ";}
		if ($product > 0) $qw .= "AND corrproduct.product = $product ";
		$qw .= "GROUP BY query.query";
		// echo $qw."<br>\n";
		$db->query($qw);
		$array_pr=Array();
		if ($db->get_number() > 0)
		{
			while ($row = $db->rows ())
			{
				if ($row[0] != "") $array_pr[$row[0]]=$row[1];
			}
		}
		$k = $width/max($array_pr);
		$s = array_sum($array_pr);
		echo "<table>\n";
		foreach($array_pr as $key => $value)
		{
			$ukey = urlencode($key);
			echo "<tr><td><a href=\"javascript:choosequr('$ukey')\">$key</a></td>\n";
			$img_w = $value*$k;
			$part = $value*100.0/$s;
			echo "<td><img alt=\"$key\" src=\"img/dist.gif\" width=\"$img_w\" height=\"$h\">&nbsp;";
			echo sprintf ("%.1f", $part);
			echo "&nbsp;%&nbsp;($value)</td></tr>\n";
		} 
		echo "</table>\n";
		echo "<form action=\"dist.php\" method=\"post\" name=\"frmDist\">\n";
		echo "<input type=\"Hidden\" name=\"fdd\" value=\"$fdd\">\n";
		echo "<input type=\"Hidden\" name=\"fdm\" value=\"$fdm\">\n";
		echo "<input type=\"Hidden\" name=\"fdy\" value=\"$fdy\">\n";
		echo "<input type=\"Hidden\" name=\"tdd\" value=\"$tdd\">\n";
		echo "<input type=\"Hidden\" name=\"tdm\" value=\"$tdm\">\n";
		echo "<input type=\"Hidden\" name=\"tdy\" value=\"$tdy\">\n";
		echo "<input type=\"Hidden\" name=\"dist\" value=\"query\">\n";
		echo "<input type=\"Hidden\" name=\"product\" value=\"$product\">\n";
		for ($i = 1; $i <= $numadrs; $i++)
		{
			$pname = ${"adr".$i};
			echo "<input type=\"Hidden\" name=\"adr$i\" value=\"$pname\">";
		}
		echo "<input type=\"Hidden\" name=\"query\" value=\"\">\n";
		echo "</form>\n";
		if($query != "")
		{
			echo "<br><br><br>";
			$pr = urldecode($query);
			$npr = $db->find('query', 'query', $pr);
			echo "<h1>$pr</h1><br><br>";
			$qw = "SELECT customer.email, customer.surname, correspondence.startd, correspondence.lastd, correspondence.number, correspondence.subject FROM (((correspondence LEFT JOIN customer ON correspondence.customer = customer.number) LEFT JOIN query ON correspondence.query = query.number) LEFT JOIN priority ON correspondence.prior = priority.number) LEFT JOIN corrproduct ON corrproduct.corr=correspondence.number WHERE correspondence.comments <> '**DELETE**' AND query.number=$npr "; 
			// check for incoming addresses
			$db->query("SELECT number, toaddress FROM toaddress");
			$numadrs = $db->get_number();
			$qw .= "AND correspondence.toaddress IN (0";
			for ($i = 1; $i <= $numadrs; $i++)
			{
				$pname = ${"adr".$i};
				if ($pname == 'on'){$qw .= ", $i";}
			}
			$qw .= ") ";
			// end of checking for incoming addresses
			if ($product > 0) $qw .= "AND corrproduct.product = $product ";
			if ($fdd+$fdm+$fdy > 0){$qw .= "AND correspondence.startd BETWEEN '$fdy-$fdm-$fdd' AND '$tdy-$tdm-$tdd'";}
			$db->query($qw);
			echo "<table cellpadding=\"10\" cellspacing=\"0\" width=\"80%\">\n";
			echo "<tr>\n";
			echo "<th>From</th><th>Surname</th><th>Subject</th><th>Received</th><th>Answered</th>\n";
			echo "</tr>";
			if ($db->get_number() > 0)
			{
				while ($row = $db->rows ())
				{
					echo "<tr bgcolor=\"";
					if ($tcnt == 0) {echo $tcolor[$tcnt]; $tcnt = 1;}
					else {echo $tcolor[$tcnt]; $tcnt = 0;}
					$dif = $util->difdate($row[2], $row[3]);
					echo "\"><td><a href=\"infomessage.php?num=$row[4]\">$row[0]</a></td><td>$row[1]</td><td>$row[5]</td><td>";
					echo substr($row[2],8,2)."/".substr($row[2],5,2)."/".substr($row[2],2,2)."</td><td>";
					if ($row[3] != "")
						echo substr($row[3],8,2)."/".substr($row[3],5,2)."/".substr($row[3],2,2)." ($dif)";
					else
						echo "<span id=\"alert\">($dif)</span>";
					echo "</td></tr>";
				}
			}
			echo "</table>";
		}
	}

	if ($dist == "all")
	{
		$tcnt = 0;
		$qw = "SELECT customer.email, correspondence.subject, correspondence.startd, staff.surname, correspondence.lastd, correspondence.number, firm.firm FROM ((correspondence LEFT JOIN customer ON correspondence.customer = customer.number) LEFT JOIN staff ON correspondence.responsible = staff.number) LEFT JOIN firm ON customer.firm = firm.number WHERE correspondence.comments <> '**DELETE**' ";
		// check for incoming addresses
		$db->query("SELECT number, toaddress FROM toaddress");
		$numadrs = $db->get_number();
		$qw .= "AND correspondence.toaddress IN (0";
		for ($i = 1; $i <= $numadrs; $i++)
		{
			$pname = ${"adr".$i};
			if ($pname == 'on'){$qw .= ", $i";}
		}
		$qw .= ") ";
		// end of checking for incoming addresses
		if ($fdd+$fdm+$fdy > 0){$qw .= "AND correspondence.startd BETWEEN '$fdy-$fdm-$fdd' AND '$tdy-$tdm-$tdd' ";}
		$qw .= "ORDER BY correspondence.startd DESC";
		$db->query($qw);
		echo "<h1>All messages >>> \n";
		if ($fdd+$fdm+$fdy > 0) 
			echo "From $fdd/$fdm/$fdy to $tdd/$tdm/$tdy >>> \n";
		echo "Number of records: ".$db->get_number()."</h1><br>\n";
		echo "<table cellpadding=\"10\" cellspacing=\"0\" width=\"80%\">\n";
		echo "<tr>\n";
		echo "<th>From</th><th>Company</th><th>Subject</th><th>Received</th><th>Answered</th><th>Assigned</th>\n";
		echo "</tr>";
		if ($db->get_number() > 0)
		{
			while ($row = $db->rows ())
			{
				$dif = $util->difdate($row[2], $row[4]);
				echo "<tr bgcolor=\"";
				if ($tcnt == 0) {echo $tcolor[$tcnt]; $tcnt = 1;}
				else {echo $tcolor[$tcnt]; $tcnt = 0;}
				echo "\"><td><a href=\"infomessage.php?num=$row[5]\">$row[0]</a></td><td>$row[6]</td><td>$row[1]</td><td>";
				echo substr($row[2],8,2)."/".substr($row[2],5,2)."/".substr($row[2],2,2)."</td><td>";
				if ($row[4] != "") 
					echo substr($row[4],8,2)."/".substr($row[4],5,2)."/".substr($row[4],2,2)." ($dif)\n";
				else
					echo "<span id=\"alert\">($dif)</span>";
				echo "</td><td>$row[3]</td></tr>";
			}
		}
		echo "</table>";
	}

	if ($dist == "mails")
	{
		$width = 400; //the maximal widht of bar in pixeles
		$h = 16;
		$tcnt = 0;
		// check for incoming addresses
		$db->query("SELECT number, toaddress FROM toaddress");
		$numadrs = $db->get_number();
		$qw = " AND correspondence.toaddress IN (0";
		for ($i = 1; $i <= $numadrs; $i++)
		{
			$pname = ${"adr".$i};
			if ($pname == 'on'){$qw .= ", $i";}
		}
		$qw .= ")";
		// end of checking for incoming addresses
		$db->query("SELECT * FROM correspondence WHERE correspondence.startd BETWEEN '$fdy-$fdm-$fdd' AND '$tdy-$tdm-$tdd'".$qw);
		$total = $db->get_number();
		$db->query("SELECT * FROM correspondence WHERE correspondence.lastd IS NULL AND correspondence.comments NOT LIKE '%**DELETE**%' AND correspondence.startd BETWEEN '$fdy-$fdm-$fdd' AND '$tdy-$tdm-$tdd' ".$qw);
		$unansw = ($db->get_number());
		$db->query("SELECT * FROM correspondence WHERE correspondence.lastd IS NOT NULL AND correspondence.startd BETWEEN '$fdy-$fdm-$fdd' AND '$tdy-$tdm-$tdd' ".$qw);
		$closed = $db->get_number();
		$db->query("SELECT * FROM correspondence WHERE correspondence.comments LIKE '%**DELETE**%' AND correspondence.startd BETWEEN '$fdy-$fdm-$fdd' AND '$tdy-$tdm-$tdd' ".$qw);
		$delmes = $db->get_number();
		$db->query("SELECT * FROM correspondence WHERE correspondence.correspondence LIKE '%-------------The message was closed! --------------%' AND correspondence.startd BETWEEN '$fdy-$fdm-$fdd' AND '$tdy-$tdm-$tdd' ".$qw);
		$closedna = $db->get_number();
		$repld = ($closed - $closedna);
		$db->query("SELECT * FROM correspondence WHERE correspondence.comments LIKE '%**DELETE**%' AND correspondence.correspondence LIKE 'Reason for deleting: spam%' AND correspondence.startd BETWEEN '$fdy-$fdm-$fdd' AND '$tdy-$tdm-$tdd' ".$qw);
		$delmes_spam = $db->get_number();
		$db->query("SELECT * FROM correspondence WHERE correspondence.comments LIKE '%**DELETE**%' AND correspondence.correspondence LIKE 'Reason for deleting: virus%' AND correspondence.startd BETWEEN '$fdy-$fdm-$fdd' AND '$tdy-$tdm-$tdd' ".$qw);
		$delmes_virus = $db->get_number();
		$db->query("SELECT * FROM correspondence WHERE correspondence.comments LIKE '%**DELETE**%' AND correspondence.correspondence LIKE 'Reason for deleting: duplicate%' AND correspondence.startd BETWEEN '$fdy-$fdm-$fdd' AND '$tdy-$tdm-$tdd' ".$qw);
		$delmes_dup = $db->get_number();
		$db->query("SELECT * FROM correspondence WHERE correspondence.comments LIKE '%**DELETE**%' AND correspondence.correspondence LIKE 'Reason for deleting: notdeliv%' AND correspondence.startd BETWEEN '$fdy-$fdm-$fdd' AND '$tdy-$tdm-$tdd' ".$qw);
		$delmes_notdeliv = $db->get_number();
		$delmes_other = ($delmes - $delmes_spam - $delmes_virus - $delmes_dup - $delmes_notdeliv);
		$k = $width/max($unansw, $repld, $closedna, $delmes);
		echo "<h1>Total: $total";
		if ($fdd+$fdm+$fdy > 0) 
			echo " >>> From $fdd/$fdm/$fdy to $tdd/$tdm/$tdy <br><br>\n";
		echo "<table>\n";
		echo "<tr><td>Unanswered</td>";
		$img_w = $unansw*$k;
		$part = $unansw*100.0/$total;
		echo "<td><img alt=\"Unanswered\" src=\"img/dist.gif\" width=\"$img_w\" height=\"$h\">&nbsp;";
		echo sprintf ("%.1f", $part);
		echo "&nbsp;% ($unansw)</td></tr>\n";
		echo "<tr><td>Replyed</td>";
		$img_w = $repld*$k;
		$part = $repld*100.0/$total;
		echo "<td><img alt=\"Replyed\" src=\"img/dist.gif\" width=\"$img_w\" height=\"$h\">&nbsp;";
		echo sprintf ("%.1f", $part);
		echo "&nbsp;%($repld)</td></tr>\n";
		echo "<tr><td>Closed</td>";
		$img_w = $closedna*$k;
		$part = $closedna*100.0/$total;
		echo "<td><img alt=\"Closed-unanswered\" src=\"img/dist.gif\" width=\"$img_w\" height=\"$h\">&nbsp;";
		echo sprintf ("%.1f", $part);
		echo "&nbsp;%($closedna)</td></tr>\n";
		echo "<tr><td>Deleted</td>";
		$img_w = $delmes*$k;
		$part = $delmes*100.0/$total;
		echo "<td><img alt=\"Deleted\" src=\"img/dist.gif\" width=\"$img_w\" height=\"$h\">&nbsp;";
		echo sprintf ("%.1f", $part);
		echo "&nbsp;%($delmes)</td></tr>\n";
		echo "</table><br><br><br>\n";
		echo "Reason for Deleting:<br><br>\n";
		$k = $width/max($delmes_spam, $delmes_virus, $delmes_dup, $delmes_other, $delmes_notdeliv);
		echo "<table>\n";
		echo "<tr><td>Spam</td>";
		$img_w = $delmes_spam*$k;
		$part = $delmes_spam*100.0/$delmes;
		echo "<td><img alt=\"Spam\" src=\"img/dist.gif\" width=\"$img_w\" height=\"$h\">&nbsp;";
		echo sprintf ("%.1f", $part);
		echo "&nbsp;%($delmes_spam)</td></tr>\n";
		echo "<tr><td>Virus</td>";
		$img_w = $delmes_virus*$k;
		$part = $delmes_virus*100.0/$delmes;
		echo "<td><img alt=\"Virus\" src=\"img/dist.gif\" width=\"$img_w\" height=\"$h\">&nbsp;";
		echo sprintf ("%.1f", $part);
		echo "&nbsp;%($delmes_virus)</td></tr>\n";
		echo "<tr><td>Duplicate</td>";
		$img_w = $delmes_dup*$k;
		$part = $delmes_dup*100.0/$delmes;
		echo "<td><img alt=\"Duplicate\" src=\"img/dist.gif\" width=\"$img_w\" height=\"$h\">&nbsp;";
		echo sprintf ("%.1f", $part);
		echo "&nbsp;%($delmes_dup)</td></tr>\n";
		echo "<tr><td>Other</td>";
		$img_w = $delmes_other*$k;
		$part = $delmes_other*100.0/$delmes;
		echo "<td><img alt=\"Other\" src=\"img/dist.gif\" width=\"$img_w\" height=\"$h\">&nbsp;";
		echo sprintf ("%.1f", $part);
		echo "&nbsp;%($delmes_other)</td></tr>\n";
		echo "<tr><td>Not Delivered</td>";
		$img_w = $delmes_notdeliv*$k;
		$part = $delmes_notdeliv*100.0/$delmes;
		echo "<td><img alt=\"Not Delivered\" src=\"img/dist.gif\" width=\"$img_w\" height=\"$h\">&nbsp;";
		echo sprintf ("%.1f", $part);
		echo "&nbsp;%($delmes_notdeliv)</td></tr>\n";
		echo "</table><br><br>\n";
	}

	if ($dist == "countries")
	{
		$width = 400; //the maximal widht of bar in pixeles
		$h = 16;
		$tcnt = 0;
		$qw = "SELECT country.country FROM (correspondence LEFT JOIN customer ON correspondence.customer = customer.number) LEFT JOIN country ON customer.country = country.number WHERE correspondence.comments <> '**DELETE**' ";
		// check for incoming addresses
		$db->query("SELECT number, toaddress FROM toaddress");
		$numadrs = $db->get_number();
		$qw .= "AND correspondence.toaddress IN (0";
		for ($i = 1; $i <= $numadrs; $i++)
		{
			$pname = ${"adr".$i};
			if ($pname == 'on'){$qw .= ", $i";}
		}
		$qw .= ") ";
		// end of checking for incoming addresses
		if ($fdd+$fdm+$fdy > 0){$qw .= "AND correspondence.startd BETWEEN '$fdy-$fdm-$fdd' AND '$tdy-$tdm-$tdd' ";}
		$db->query($qw);
		$total = $db->get_number();
		$qw = "SELECT country.country, count(country.country) AS ncountry, sum(country.country) AS scountry FROM (correspondence LEFT JOIN customer ON correspondence.customer = customer.number) LEFT JOIN country ON customer.country = country.number WHERE correspondence.comments <> '**DELETE**' ";
		// check for incoming addresses
		$db->query("SELECT number, toaddress FROM toaddress");
		$numadrs = $db->get_number();
		$qw .= "AND correspondence.toaddress IN (0";
		for ($i = 1; $i <= $numadrs; $i++)
		{
			$pname = ${"adr".$i};
			if ($pname == 'on'){$qw .= ", $i";}
		}
		$qw .= ") ";
		// end of checking for incoming addresses
		if ($fdd+$fdm+$fdy > 0){$qw .= "AND correspondence.startd BETWEEN '$fdy-$fdm-$fdd' AND '$tdy-$tdm-$tdd' ";}
		
		$qw .= "GROUP BY country.country ";
		$qw .= "ORDER BY ncountry DESC LIMIT 10";
		$db->query($qw);
		$iflag = 0;
		echo "<table>\n";
		if ($db->get_number() > 0)
		{
			while ($row = $db->rows ())
			{
				if ($iflag == 0)
				{
					$nunknown = $row[1];
					$iflag = 1;
				}
				else
				{
					if($iflag == 1)
					{
						$k = $width/$row[1];
						$iflag = 2;
					}
					$img_w = $row[1]*$k;
					$part = $row[1]*100.0/$total;
					echo "<tr><td>$row[0]</td>\n";
					echo "<td><img alt=\"$key\" src=\"img/dist.gif\" width=\"$img_w\" height=\"$h\">&nbsp;";
					echo sprintf ("%.1f", $part);
					echo "&nbsp;%&nbsp;($row[1])</td></tr>\n";
				}
			}
		}
		echo "</table>\n";
		echo "<h1>Total: $total >>> Unknown: $nunknown";
		if ($fdd+$fdm+$fdy > 0) 
			echo " >>> From $fdd/$fdm/$fdy to $tdd/$tdm/$tdy <br><br>\n";
	}
	
	if ($dist == "response")
	{
?>
		<script language="JavaScript">
		function choosestf(stf)
		{
			document.frmDist.sstaff.value = stf;
			document.frmDist.submit(); 
		}
		</script>
<?php
		echo "<h1>";
		if ($fdd+$fdm+$fdy > 0) 
			echo "From $fdd/$fdm/$fdy to $tdd/$tdm/$tdy\n";
		else
			echo "Whole period";
		if (!empty($sstaff)) {echo ">>> Distribution for $sstaff\n";}
		echo "</h1><br><br>\n";
		$width = 400; //the maximal widht of bar in pixeles
		$h = 16;
		$tcnt = 0;
		if (empty($sstaff)) {$db->query("SELECT number, surname FROM staff");}
		else {$db->query("SELECT number, surname FROM staff WHERE surname='$sstaff'");}
		$staff_array = $db->get_array();
		$staff_number = $staff_array[0];
		$staff_surname = $staff_array[1];
		$ar_response = array();
		$d_response = array();
		foreach($staff_number as $key=>$value)
		{
			$qw = "SELECT correspondence.startd, correspondence.lastd FROM correspondence WHERE correspondence.comments <> '**DELETE**' AND correspondence.responsible=$value ";
			// check for incoming addresses
			$db->query("SELECT number, toaddress FROM toaddress");
			$numadrs = $db->get_number();
			$qw .= "AND correspondence.toaddress IN (0";
			for ($i = 1; $i <= $numadrs; $i++)
			{
				$pname = ${"adr".$i};
				if ($pname == 'on'){$qw .= ", $i";}
			}
			$qw .= ") ";
			// end of checking for incoming addresses
			if ($fdd+$fdm+$fdy > 0){$qw .= "AND correspondence.startd BETWEEN '$fdy-$fdm-$fdd' AND '$tdy-$tdm-$tdd' ";}
			$db->query($qw);
			$diff = 0;
			if ($db->get_number() > 0)
			{
				$query_array = $db->get_array();
				$query_startd = $query_array[0];
				$query_lastd = $query_array[1];
				for ($i = 0; $i < count($query_startd); $i++)
				{
					$dif = $util->difdate($query_startd[$i], $query_lastd[$i]);
					$d_response[$dif]++;
					$diff += $dif;
				}
				$ar_response[$staff_surname[$key]] = $diff/count($query_startd);
			}
		}
		echo "<form action=\"dist.php\" method=\"post\" name=\"frmDist\">\n";
		echo "<input type=\"Hidden\" name=\"fdd\" value=\"$fdd\">\n";
		echo "<input type=\"Hidden\" name=\"fdm\" value=\"$fdm\">\n";
		echo "<input type=\"Hidden\" name=\"fdy\" value=\"$fdy\">\n";
		echo "<input type=\"Hidden\" name=\"tdd\" value=\"$tdd\">\n";
		echo "<input type=\"Hidden\" name=\"tdm\" value=\"$tdm\">\n";
		echo "<input type=\"Hidden\" name=\"tdy\" value=\"$tdy\">\n";
		echo "<input type=\"Hidden\" name=\"dist\" value=\"response\">\n";
		for ($i = 1; $i <= $numadrs; $i++)
		{
			$pname = ${"adr".$i};
			echo "<input type=\"Hidden\" name=\"adr$i\" value=\"$pname\">";
		}
		echo "<input type=\"Hidden\" name=\"sstaff\" value=\"\">\n";
		echo "</form>\n";
		if(empty($sstaff))
		{
			$k = $width/max($ar_response);
			echo "<table>\n";
			foreach ($ar_response as $key=>$value)
			{
				$img_w = $value*$k;
				$ukey = urlencode($key);
				echo "<tr><td><a href=\"javascript:choosestf('$ukey')\">$key</a></td>\n";
				echo "<td><img alt=\"$key\" src=\"img/dist.gif\" width=\"$img_w\" height=\"$h\">&nbsp;";
				echo sprintf ("%.1f", $value);
				echo "</td></tr>\n";
			}
			echo "</table><br><br>\n";
		}
		echo "<table>\n";
		echo "<tr><td>Period</td><td>&nbsp;</td></tr>\n";
		ksort($d_response);
		$k = $width/max($d_response);
		foreach ($d_response as $key=>$value)
		{
			$img_w = $value*$k;
			echo "<tr><td>$key</td>\n";
			echo "<td><img alt=\"$key\" src=\"img/dist.gif\" width=\"$img_w\" height=\"$h\">&nbsp;";
			echo sprintf ("%.1f", $value);
			echo "</td></tr>\n";
		}
		echo "</table><br><br>\n";
	}
	echo "</body></html>\n";
?>
