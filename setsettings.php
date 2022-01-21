<?php
include ("db.inc");
include ("utils.inc");
$db = new dbObj;
$utils = new utilObj;
$db->init();
if (empty($sysuser))
{
	if ($note == "on") {$note = 1;}
	else {$note = 0;}
	$db->query("UPDATE staff SET email='$email', notification = $note, signature='$signature' WHERE surname='$NAME'");
	if (!empty($oldpass))
	{
		if($newpass1 != $newpass2) {$utils->rdirect("settings.php?alert=1");}
		else
		{
			$db->query("SELECT surname, passwd FROM staff WHERE surname='$NAME'");
			$row=$db->rows();
			if (md5($oldpass) != $row[1])
			{
				$utils->rdirect("settings.php?alert=2");
			}
			else
			{
				$np = md5($newpass1);
				$db->query("UPDATE staff SET passwd='$np' WHERE surname='$NAME'");
				$utils->rdirect("assigned.php");
			}
		} 
	}
	$utils->rdirect("assigned.php");
}
else
{
	if($newpass1 != $newpass2) {$utils->rdirect("settings.php?sysuser=$sysuser&alert=1");}
	else
	{
		$db->query("SELECT id, login, password FROM syspass WHERE login='$NAME'");
		$row=$db->rows();
		if (md5($oldpass) != $row[2])
		{
			$utils->rdirect("settings.php?sysuser=$sysuser&alert=2");
		}
		else
		{
			$np = md5($newpass1);
			$db->query("UPDATE syspass SET password='$np' WHERE login='$NAME'");
			$utils->rdirect("settings.php?sysuser=$sysuser");
		}
	} 
}
?>
