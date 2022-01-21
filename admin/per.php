<?php
include ("../utils.inc");
include ("../db.inc");
$db = new dbObj;
$db->init();
$utils = new utilObj;
$c = addslashes($correspondence);
$o = addslashes($origcorr);
$db->query("UPDATE correspondence SET correspondence='$c', origcorr='$o' WHERE number=$num");
echo mysql_error();
$db->query("SELECT number FROM attachment WHERE correspondence = $num");
if ($db->get_number() > 0)
{
	$corflag = false;
	$qw = "DELETE FROM attachment WHERE number IN (";
	while ($row = $db->rows ())
	{
		if (${"del".$row[0]} == "on")
		{
			$qw .= $row[0].", ";
			$corflag = true;  //if any attachment for deleting occur
		}
	}
	$qw = substr($qw, 0, strlen($qw)-2);
	$qw .= ")";
	if ($corflag) 
		$db->query($qw);
}
$utils->rdirect("admin.php?admin=$admin&period=$period");
?>
