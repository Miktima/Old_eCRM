<?php
include ("../utils.inc");
include ("../db.inc");
$db = new dbObj;
$db->init();
$utils = new utilObj;

if ($set == "product")
{
	if ($add == "Add")
	{
		$last = $db->maxn('product', 'number');
		$last++;
		$db->query("INSERT product (number, product) VALUES ($last, '$additem')");
	}
	if ($del == "Delete Marked")
	{
		$db->query("SELECT number, product FROM product");
		$delpr = Array();
		$i = 0;
		while ($row = $db->rows ())
		{
			if (${$row[0]} == $row[1])
			{
				$delpr[$i]=$row[0];
				$i++;
			}
		}
		$qw1 = "DELETE FROM product WHERE ";
		$qw2 = "DELETE FROM corrproduct WHERE ";
		for($j=0; $j<$i; $j++)
		{
			if ($j > 0)
			{
				$qw1 .= "OR ";
				$qw2 .= "OR ";
			}
			$qw1 .= "number=$delpr[$j] ";
			$qw2 .= "product=$delpr[$j] ";
		}
		if ($i > 0)
		{
			$db->query($qw1);
			$db->query($qw2);
		}
	}
	$utils->rdirect("admin.php?admin=settings&set=product");
}

if ($set == "staff")
{
	if ($add == "Add")
	{
		$last = $db->maxn('staff', 'number');
		$last++;
		$passwd = md5(strtolower($additem));
		$db->query("INSERT staff (number, name, surname, passwd) VALUES ($last, '$staffname', '$additem', '$passwd')");
	}
	if ($del == "Delete Marked")
	{
		$db->query("SELECT number, surname FROM staff");
		$delpr = Array();
		$i = 0;
		while ($row = $db->rows ())
		{
			if (${$row[0]} == $row[1])
			{
				$delpr[$i]=$row[0];
				$i++;
			}
		}
		$qw1 = "DELETE FROM staff WHERE ";
		for($j=0; $j<$i; $j++)
		{
			if ($j > 0)
			{
				$qw1 .= "OR ";
			}
			$qw1 .= "number=$delpr[$j] ";
		}
		if ($i > 0)
		{
			$db->query($qw1);
		}
	}
	$utils->rdirect("admin.php?admin=settings&set=staff");
}

if ($set == "mboxes")
{
	if ($add == "Add")
	{
		$last = $db->maxn('boxes', 'id');
		$last++;
		$db->query("INSERT boxes (id, name, password) VALUES ($last, '$boxname', '$password')");
	}
	if ($del == "Delete Marked")
	{
		$db->query("SELECT id, name FROM boxes");
		$delpr = Array();
		$i = 0;
		while ($row = $db->rows ())
		{
			if (${$row[0]} == $row[1])
			{
				$delpr[$i]=$row[0];
				$i++;
			}
		}
		$qw1 = "DELETE FROM boxes WHERE ";
		for($j=0; $j<$i; $j++)
		{
			if ($j > 0)
			{
				$qw1 .= "OR ";
			}
			$qw1 .= "id=$delpr[$j] ";
		}
		if ($i > 0)
		{
			$db->query($qw1);
		}
	}
	$utils->rdirect("admin.php?admin=settings&set=$set");
}

if ($set == "priority")
{
	if ($add == "Add")
	{
		$last = $db->maxn('priority', 'number');
		$last++;
		$db->query("INSERT priority (number, priority, alert) VALUES ($last,  '$additem', $alert)");
	}
	if ($del == "Delete Marked")
	{
		$db->query("SELECT number, priority FROM priority");
		$delpr = Array();
		$i = 0;
		while ($row = $db->rows ())
		{
			if (${$row[0]} == $row[1])
			{
				$delpr[$i]=$row[0];
				$i++;
			}
		}
		$qw1 = "DELETE FROM priority WHERE ";
		for($j=0; $j<$i; $j++)
		{
			if ($j > 0)
			{
				$qw1 .= "OR ";
			}
			$qw1 .= "number=$delpr[$j] ";
		}
		if ($i > 0)
		{
			$db->query($qw1);
		}
	}
	$utils->rdirect("admin.php?admin=settings&set=priority");
}

if ($set == "query")
{
	if ($add == "Add")
	{
		$last = $db->maxn('query', 'number');
		$last++;
		$db->query("INSERT query (number, query) VALUES ($last,  '$additem')");
	}
	if ($del == "Delete Marked")
	{
		$db->query("SELECT number, query FROM query");
		$delpr = Array();
		$i = 0;
		while ($row = $db->rows ())
		{
			if (${$row[0]} == $row[1])
			{
				$delpr[$i]=$row[0];
				$i++;
			}
		}
		$qw1 = "DELETE FROM query WHERE ";
		for($j=0; $j<$i; $j++)
		{
			if ($j > 0)
			{
				$qw1 .= "OR ";
			}
			$qw1 .= "number=$delpr[$j] ";
		}
		if ($i > 0)
		{
			$db->query($qw1);
		}
	}
	$utils->rdirect("admin.php?admin=settings&set=query");
}

if ($set == "answer")
{
	if ($add == "Add")
	{
		$last = $db->maxn('stanswer', 'number');
		$last++;
		$moddate = date("Y-m-d");
		$db->query("INSERT stanswer (number, subject, text, modified) VALUES ($last,  '$subject', '$stanswer', '$moddate')");
	}
	if ($del == "Delete Marked")
	{
		$db->query("SELECT number FROM stanswer");
		$delpr = Array();
		$i = 0;
		while ($row = $db->rows ())
		{
			if (${$row[0]} == $row[0])
			{
				$delpr[$i]=$row[0];
				$i++;
			}
		}
		$qw1 = "DELETE FROM stanswer WHERE ";
		for($j=0; $j<$i; $j++)
		{
			if ($j > 0)
			{
				$qw1 .= "OR ";
			}
			$qw1 .= "number=$delpr[$j] ";
		}
		if ($i > 0)
		{
			$db->query($qw1);
		}
	}
	$utils->rdirect("admin.php?admin=settings&set=$set");
}
?>
