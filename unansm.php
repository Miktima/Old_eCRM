<?php
include ("utils.inc");
include ("db.inc");
$db = new dbObj;
$db->init();
$utils = new utilObj;
$db->query("UPDATE correspondence SET comments='$comments', lastd=NULL, prior=$priority, responsible=$staff WHERE number=$num");
$utils->rdirect("unansmessage.php?num=$num")
?>
