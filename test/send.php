<?php
if (!strstr($cc,"@") && $cc != "") 
{
	$alert = "CC address is not valid!";
	Header ("Location: message.php?num=$num&alert=$alert");
}
if (!strstr($forward,"@") && $forward != "") 
{
	$alert = "Forward address is not valid!";
	Header ("Location: message.php?num=$num&alert=$alert");
}
if ($HTTP_POST_FILES['userfile1']['size'] > 2097152)
{
	$alert = "The attached file is too large - ";
	$alert .= $HTTP_POST_FILES['userfile1']['size'];
	$alert .= ". The maximal size is 2 M bytes";
	Header ("Location: message.php?num=$num&alert=$alert");
}
else
{
	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
	echo "<html>\n";
	echo "<head>\n";
  	echo "<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=windows-1251\">\n";
	echo "<LINK rel=stylesheet href=\"stl.css\" type=\"text/css\">\n";
	echo "<script language=\"JavaScript\" src=\"util.js\"></script>\n";
	echo "<title>Outside Services &gt; Send</title>\n";
	echo "</head>";
	echo "<body>";
	// Address of one of ParallelGraphics mail boxes
	$from = "Mikhail Timofeev <timofeev@parallelgraphics.com>";
	//Create a content
	$content = addslashes($incoming);
	$lastd = date("Y-m-d H:i:s");
	$content = stripslashes($content);
	include "mime.inc";
	$to = $uemail;
	$i = 0;
	//Create header of email
//	$headers[$i] = "To: $to";
	if ($cc != "") {$headers[$i++] = "CC: $cc";}
	$headers[$i++] = "Reply-To: $from";
	$headers[$i++] = "Return-Path: timofeev@parallelgraphics.com";
	$headers[$i++] = "Date: ".date("r");
	$toaddress = $to;
	if ($cc != "") {$toaddress .= ", ".$cc;}
//	$bcc = "timofeev@paragraph.ru";
//	$bcc = "shamraev@parallelgraphics.com";
	if ($bcc != "") {$toaddress .= ", ".$bcc;}
	$subject = stripslashes($subject);
//	if (!stristr(substr($subject,0,3),'RE:')) {$subject ="RE: ".$subject;}
	$mime = new MIME_mail($from, $toaddress, $subject, $content, $headers);
	//Adding attachments
	foreach ($HTTP_POST_FILES as $key => $value)
	{
		if ($value["tmp_name"] != "")
		{
			
			$fname = get_cfg_var('upload_tmp_dir')."/".$value["name"];
			copy ($value["tmp_name"], $fname);
			if (!$mime->fattach($fname, $value["name"], $value["type"]))
			{
				echo $mime->errstr;
			}
			unlink ($fname);
		}
	}
	include ("../utils.inc");
	$utils = new utilObj;
	//Redirect only after sucsessfully sending!
	if ($mime->send_mail()) {$utils->rdirect("message.php");}
	else
	{
		echo "Error occurs during sending the e-mail:$mime->errstr<br><br>";
	}
	echo "</body></html>";
}
?>
