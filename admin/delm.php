<?php
include ("../utils.inc");
include ("../db.inc");
$db = new dbObj;
$db->init();
$utils = new utilObj;

if ($delete == "Remove from DB") 
{
	$db->query("DELETE FROM correspondence Where number=$num");
	$db->query("DELETE FROM attachment Where correspondence=$num");
	$db->query("DELETE FROM corrproduct Where corr=$num");
}
if ($Assign == "Assign") 
{
	$db->query("UPDATE correspondence SET comments=NULL, lastd=NULL, prior=$priority, responsible=$staff WHERE number=$num");
}
if ($delall == "Remove from DB") 
{
	$dbrem = new dbObj;
	$dbrem->init();
	$qw = "SELECT correspondence.number FROM correspondence WHERE correspondence.comments = '**DELETE**' ";
	$db->query($qw);
	if ($db->get_number() > 0)
	{
		while ($row = $db->rows ())
		{
			if(${"n".$row[0]} == "on")
			{
				$num = $row[0];
				$dbrem->query("DELETE FROM correspondence Where number=$num");
				$dbrem->query("DELETE FROM attachment Where correspondence=$num");
				$dbrem->query("DELETE FROM corrproduct Where corr=$num");
			}
		}
	}
}
$utils->rdirect("admin.php?admin=$admin&period=$period")
?>
