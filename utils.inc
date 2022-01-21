<?php
if($utilObjDefined != 1)
{

  $utilObjDefined = 1;

   //Wrapper class for utilities calls 
  class utilObj
  {
     //Default parameters
		 // Redirect from the addrem page automatically 
    //var $forward =  "off";

     //Empty page with the redirecting function. Style is predefined.
    function rdirect($lhost)
    {
			echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
			echo "<html>\n";
			echo "<head>\n";
			echo "<title>Redirecting ...</title>\n";
			echo "</head>\n";
			echo "<body onLoad=\"javascript:self.location.href='$lhost';\">\n";
			echo "</body>\n";
			echo "</html>\n";
	}
	function getPermission($n, $p)
	{
			$dbutil = new dbObj; 
			$dbutil->init ("bts");
			$rsutil = new recordset;
			$rsutil = $dbutil->exec("SELECT grid FROM users WHERE (username = '$n' AND pass = '$p');");
			$ngr = $rsutil->getVal(0, 0);
			$rsutil = $dbutil->exec("SELECT perm FROM groups WHERE groupid = $ngr;");
			$perm = $rsutil->getVal(0, 0);
			$rsutil->free();
			$dbutil->free(); 
			return $perm;
	}
	function difdate($d1, $d2)
	{
		//number of seconds in a day
		$nsecinday = 86400;
		$ndayinweek = 7;
		//instead of empty dates use the current date 
		if ($d1 == "")
			$dd1 = time();
		else
			$dd1 = strtotime($d1);
		if ($d2 == "")
			$dd2 = time();
		else
			$dd2 = strtotime($d2);
		// do not account Sundays and Saturdays
		if (date("w", $dd1) == 0)
		{
			$dd1 = (int)($dd1/$nsecinday)*$nsecinday + $nsecinday;
		}
		if (date("w", $dd1) == 6)
		{
			$dd1 = (int)($dd1/$nsecinday)*$nsecinday + 2 * $nsecinday;
		}
		$tmpdd1 = $dd1;
		$i = 0;
		while ($tmpdd1 < $dd2)
		{
			if (date("w", $tmpdd1) == 0 || date("w", $tmpdd1) == 6) $i++;
			$tmpdd1 += $nsecinday;
		}
		return sprintf("%01.0f", ((float)($dd2 - $dd1 - ($i * $nsecinday)))/$nsecinday);
//		return (float)($dd2 - $dd1 - ($i * $nsecinday))/$nsecinday;
	}
	// choosing icons for attachment types
	function chooseIcon($type)
	{
		if (stristr($type, "msword") || stristr($type, "richtext"))
			$icon = "doc.gif";
		elseif (stristr($type, "jpeg") || stristr($type, "gif") || stristr($type, "bmp") || stristr($type, "tiff"))
			$icon = "img.gif";
		elseif (stristr($type, "vrml"))
			$icon = "vrml.gif";
		elseif (stristr($type, "zip"))
			$icon = "zip.gif";
		elseif (stristr($type, "pdf"))
			$icon = "pdf.gif";
		else
			$icon = "other.gif";
		return $icon;
	}
  };
}
?>



