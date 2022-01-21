<?php
if($imapObjDefined != 1)
{

  $imapObjDefined = 1;

	// Defining a class where informatin about an attachement is saved
	class fileAtt // class of parameters of attached file
	{
		var $type; // mime type of the attached file
		var $att_name; //name of the attached file
		var $size; //size of the attached file
		var $encoding; // encoding if the attached file
		var $part_no; // number of part
	}
   //Wrapper class for imap calls 
  class imapObj
  {
		//getting part of e-mail
		function isAtach ($mbox, $nmail)
		{
			$struct = imap_fetchstructure($mbox, $nmail);
			$isattach = 0;
			if (is_array($struct->parts))
			{
				while(list($index, $sub_structure) = each($struct->parts)) 
				{
					// if disposition is attachemnt
					if (stristr($sub_structure->disposition, "attachment")){$isattach = 1; break; }
					// if mime type is not text
					if ($sub_structure->type != 0 ){$isattach = 1; break;}
				}
			}
			return $isattach;
		}
		function cyr_conv($str)
		{
			$newstr = "";
			$elements=imap_mime_header_decode($str);
			for($i=0; $i<count($elements); $i++) 
			{
				switch ($elements[$i]->charset)
				{
					case "koi8-r":
//						$newstr .= convert_cyr_string($elements[$i]->text, "k", "w");
						$newstr .= $elements[$i]->text;
						break;
					case "KOI8-R":
//						$newstr .= convert_cyr_string($elements[$i]->text, "k", "w");
						$newstr .= $elements[$i]->text;
						break;
					case "windows-1251":
						$newstr .= $elements[$i]->text;
						break;
					default:
						$newstr .= $elements[$i]->text;
				}
			}
			return $newstr;
		}
		function imap_conv($str)
		{
			$newstr = "";
			$elements=imap_mime_header_decode($str);
			for($i=0; $i<count($elements); $i++) 
			{
				$newstr .= $elements[$i]->text;
			}
			return $newstr;
		}
		// ********** get mime type **************
		function get_mime_type(&$structure) 
		{ 
			$primary_mime_type = array("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER"); 
			// checking for MIME type
			if($structure->subtype) 
			{
				return $primary_mime_type[(int) $structure->type] . '/' . $structure->subtype; 
			} 
			return "TEXT/PLAIN"; 
		}
		// ********** get message ***************   
		function get_message($stream, $msg_number, $mime_type, $structure = false, $part_number = false) 
		{
			if(!$structure) 
			{
				$structure = imap_fetchstructure($stream, $msg_number); 
			}
			if($structure) 
			{ 
				if($mime_type == $this->get_mime_type($structure))
				// if the MIME type of the structure and required one are the same 
				{ 
					if(!$part_number) 
					{
						// if the part number is not defined, then it is once 
						$part_number = "1"; 
					} 
					$text = imap_fetchbody($stream, $msg_number, $part_number);
					//defining encoding type and returning text
					if($structure->encoding == 3) 
					{ 
//						return convert_cyr_string(imap_base64($text), "k", "w"); 
						return imap_base64($text); 
					} 
					else if($structure->encoding == 4) 
					{ 
//						return convert_cyr_string(imap_qprint($text), "k", "w"); 
						return imap_qprint($text); 
					} 
					else 
					{ 
//						return convert_cyr_string($text, "k", "w"); 
						return $text; 
					} 
				} 
				if($structure->type == 1) // multipart 
				{
					while(list($index, $sub_structure) = each($structure->parts)) 
					{ 
						if($part_number) 
						{
							$prefix = $part_number . '.'; 
						} 
						$data = $this->get_message($stream, $msg_number, $mime_type, $sub_structure, $prefix . ($index + 1));
						if($data)
						{
							return $data;
						}
					}
				} 
			} 
			return false; 
		}
		// ********** get mime encoding *************
		function get_mime_encoding($de_part)
		{
			switch ($de_part->encoding)
			{
				case ENCBASE64:			$mime_encoding = "base64"; break;
				case ENCQUOTEDPRINTABLE:	$mime_encoding = "qprint"; break;
				case ENCOTHER:			$mime_encoding = "other"; break;
				default:			$mime_encoding = "other";
			}
			return $mime_encoding;
		}
		// ********** Getting atachments **********
		function get_attachments ($mbox, $nmsg)
		{ 
			$fileAtt = new fileAtt;
			$attachments = array();
			$structure = imap_fetchstructure($mbox, $nmsg);
			if (is_array($structure->parts))
			{
				$isattach = 0;
				while(list($index, $sub_structure) = each($structure->parts)) 
				{
//					if ($sub_structure->type != 0)
					if (stristr($sub_structure->disposition, "attachment") || ($sub_structure->type != 0))
					{
						$att_name = "unknown";
						$isattach = 1;
						foreach ($sub_structure->parameters as $param)
						{
							if ($param->attribute == "name" || $param->attribute == "NAME")
								$att_name = $param->value;
						}
						//if a name of the attachment is not specifing in a paramatares array,
						// look this for in the disposition->filename
						if ($att_name == "unknown")
						{
							if (is_array($sub_structure->dparameters))
							{
								foreach ($sub_structure->dparameters as $param)
								{
									if (stristr($param->attribute, "filename"))
									{
										$att_name = $param->value;
									}
								}
							}
						}
						$fileAtt->type = $sub_structure->subtype;
						$fileAtt->att_name = $att_name;
						$fileAtt->size = $sub_structure->bytes;
						$fileAtt->encoding = $this->get_mime_encoding($sub_structure);
						$fileAtt->part_no = $index+1;
						if ($att_name != "unknown") 
						{
							$attachments[] = $fileAtt;
						}
					}
				}
			}
			if ($isattach == 1) 
				return $attachments;
			else
				return 0;
		}
	};
}
?>



