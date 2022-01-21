<?php
if($dbObjDefined != 1)
{
	$dbObjDefined = 1;
   //Wrapper class for database calls 
	class dbObj
	{
		//Connection handle to database 
    	var $conn;
     	//Default connection parameters 
		var $host =  "192.168.1.14";
		var $user =  "php_ecrm";
		var $password =  "vgy7ujm";
		var $port =  "3306";
		var $dbname =  "ecrm";
		var $result;

     	//Open initial connection.
		function init()
		{
			$this->conn = mysql_connect ( $this->host, $this->user, $this->password ); 
			mysql_select_db ($this->dbname);
    	}

     	//Send SQL to database connection.   
    	function query($SQL)
    	{
			$this->result = mysql_query($SQL);
		} 

		//Close connection to database 
		function close()
		{
			mysql_close($this->conn);
		}
		
		// Get number of rows for SELECT query
		function get_number()
		{
			return mysql_num_rows($this->result);
		}
		// Get number of fields for SELECT query
		function get_fields_number()
		{
			return mysql_num_fields($this->result);
		}
		// Get result of query
		function rows()
		{
			return mysql_fetch_row ($this->result);
		}
		// Get maximum number of the indexed field
		function maxn($table, $field)
		{
			$this->query("SELECT MAX($field) FROM $table");
			$n = mysql_result ($this->result, 0);
			if ($n == "") {return 0;}
			else {return $n;}
		}
		//Looking for index for searching value
		function find($table, $field, $value)
		{
			$this->query("SELECT number FROM $table WHERE $field = '$value'");
			if (mysql_num_rows($this->result) == 0) {return 0;}
			else {return mysql_result ($this->result, 0);}						
		}
		//Looking for a part of the field
		function sfind($table, $field, $value)
		{
			$this->query("SELECT number FROM $table WHERE $field LIKE '%$value%'");
			if (mysql_num_rows($this->result) == 0) {return 0;}
			else {return mysql_result ($this->result, 0);}						
		}
		//Get result of a select query as array
		//Each field is specified as array
		function get_array()
		{
			$j=0;
			while ($row = $this->rows()) {
				for($i = 0; $i < $this->get_fields_number(); $i++)
				{
					$val = "arr".$i;
					${$val}[$j] = $row[$i];
				}
				$j++;
			}
			for($i = 0; $i < $this->get_fields_number(); $i++)
			{
				$val = "arr".$i;
				$array[$i] = ${$val};
			}
			return $array;
		}
	};
}
?>
