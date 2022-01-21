<?php 
if ($MODE!=="test") 
{
	//Is cookies enabled or not? 
	SetCookie("COOKIE", "VALUE");
	SetCookie("NAME", $login); 
	SetCookie("PASS", $password); 
	Header("Location: ".$_SERVER["SCRIPT_NAME"]."?MODE=test"); 
	exit;
}
else if ($COOKIE=="VALUE") 
{
	include ("utils.inc");
	$utils = new utilObj;
	include ("db.inc");
	$db = new dbObj;
	$db->init();
	//Checking system logins
	$db->query("SELECT login, password FROM syspass");
	while ($row = $db->rows ()) {
		if (strstr($row[0], $NAME))
		{
			if ($row[1] == md5($PASS))
			{
				switch ($NAME) 
				{
					case "manager":
						$utils->rdirect("allboxes.php");
						break;
					case "admin":
						$utils->rdirect("admin/index.php");
						break;
				}
			}
			else {
				$utils->rdirect("index.php?nologin=1");
			}
		}
	}
	//Checking users logins
	$db->query("SELECT surname, passwd FROM staff");
	while ($row = $db->rows ()) {
		if (stristr($row[0], $NAME))
		{
			if ($row[1] == md5($PASS))
			{
				$utils->rdirect("assigned.php");
			}
			else {
				$utils->rdirect("index.php?nologin=1");
			}
		}
	}
	$utils->rdirect("index.php?nologin=1");
}
else
{ 
	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
	echo "<html>\n";
	echo "<head>\n";
    echo "<LINK rel=\"stylesheet\" href=\"stl.css\" type=\"text/css\">\n";
	echo "<title>Outside Services</title>\n";
	echo "</head>\n";
	echo "<body>\n";
	echo "<h1 align=\"center\">Outside Services</h1><br><br>\n";
	echo "<div align=\"center\">\n";
	echo "Your browser does not allow per-session cookies<br>Please enable the cookies and try again<br><br>\n";
	echo "<a href=\"index.php\">Back</a>\n";
	echo "</div>\n";
	echo "</body>\n";
	echo "</html>\n";
}
?>