<?php
include ("../utils.inc");
include ("../db.inc");
$db = new dbObj;
$db->init();
$utils = new utilObj;

if ($set == "answer")
{
	if ($save == "Save")
	{
		$moddate = date("Y-m-d");
		$db->query("UPDATE stanswer SET subject='$subject', text='$stanswer', modified='$moddate' WHERE number = $n");
	}
	$utils->rdirect("admin.php?admin=settings&set=$set");
}
if ($set == "priority")
{
	if ($save == "Save")
	{
		$db->query("UPDATE priority SET priority='$additem', alert=$alert  WHERE number = $n");
	}
	$utils->rdirect("admin.php?admin=settings&set=$set");
}
if ($set == "staff")
{
	if ($save == "Save")
	{
		$db->query("UPDATE staff SET name='$staffname', surname=$additem  WHERE number = $n");
	}
	if (!empty($newpass1))
	{
		if($newpass1 != $newpass2) {$utils->rdirect("editmessage.php?alert=1&n=$n&set=$set");}
		else
		{
			$np = md5($newpass1);
			$db->query("UPDATE staff SET passwd='$np' WHERE number=$n");
			$utils->rdirect("admin.php?admin=settings&set=$set");
		} 
	}
	$utils->rdirect("admin.php?admin=settings&set=$set");
}
if ($set == "mboxes")
{
	if ($arflag == "on") {$arflag = 1;}
	else {$arflag = 0;}
	$db->query("UPDATE boxes SET name='$boxname', password='$password', arflag=$arflag, subject='$subject', autoreply='$autoreply' WHERE id = $n");
	$utils->rdirect("admin.php?admin=settings&set=$set");
}
?>
