<?php
include ("db.inc");
include ("utils.inc");
$db = new dbObj;
$utils = new utilObj;
$db->init();
$last_cust = $db->maxn('customer', 'number');
$user = $db->find('customer', 'email', $newmail);
if ($user == 0)
{
	$user = $last_cust + 1;
	$db->query("INSERT customer (number, email) VALUES ($user, '$newmail')");
}
//$db->query("UPDATE correspondence SET customer=$user, comments='$comments' WHERE number=$num");
$db->query("UPDATE correspondence SET customer=$user WHERE number=$num");
$utils->rdirect("message.php?num=$num");
?>
