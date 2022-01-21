<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<title>Transfer Contry from one DB to another</title>
</head>

<body>
<?php
mysql_connect ( "", "php_ecrm", "vgy7ujm" );
mysql_select_db ("ecrm");
$result = mysql_query("SELECT country FROM country ORDER BY country");
$country = array();
while ($row = mysql_fetch_row ($result))
{
	$country[] = $row[0];
}
mysql_connect ( "", "tima", "tima" );
mysql_select_db ("ecrm");
$rt = mysql_query("SELECT product FROM product ORDER BY product");
while ($r = mysql_fetch_row ($rt))
{
	echo $r[0]."<br>\n";
}
?>
</body>
</html>
