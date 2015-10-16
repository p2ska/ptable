<?php

// andmebaasi klass

class P_DATABASE {
	var $error = 0,
		$error_msg = "",
		$rows = 0,
		$insert_id = 0,
		$result,
		$query,
		$connection;

	function p_database($host = DB_HOST, $db = DB_NAME, $user = DB_USER, $pass = DB_PASS, $charset = DB_CHARSET, $collation = DB_COLLATION) {
		if (!$this->connection = @mysql_connect($host, $user, $pass, true))
			die("Connection to database server has failed.<br/>". @mysql_error($this->connection));

		if (!@mysql_select_db($db, $this->connection))
			die("Database not found.<br/>". @mysql_error($this->connection));

		@mysql_query("set names '". $charset. "' collate '". $collation. "'");
	}

	function switch_db($db)	{
		if (!@mysql_select_db($db, $this->connection))
			die("Database not found.<br>". @mysql_error($this->connection));

		$this->query("set names '". $charset. "' collate '". $collation. "'");
	}

	function query($query, $values = false) {
		$this->rows = $this->error = $param_count = 0;
		$this->error_msg = "";
		$param = array();
		$using = false;
		
		if ($this->result)
			@mysql_free_result($this->result);

		$this->query = "prepare prep_query from '". $query. "'";

		if (!$this->result = @mysql_query($this->query, $this->connection))
			return $this->error();

		if ($values) {
			foreach ($values as $value) {
				$this->query = "set @param". $param_count. " = '". mysql_real_escape_string($value). "'";
				$param[] = "@param". $param_count;

				if (!$this->result = @mysql_query($this->query, $this->connection))
					return $this->error();

				$param_count++;
			}
			
			$using = " using ". implode(", ", $param);
		}

		$this->query = "execute prep_query". $using;

		$this->result = @mysql_query($this->query, $this->connection);
		$this->rows = @mysql_num_rows($this->result);
		$this->insert_id = @mysql_insert_id($this->connection);
		
		return $this->result;
	}

	function error() {
		$this->error = @mysql_errno($this->connection);
		$this->error_msg = @mysql_error($this->connection). " FAILED QUERY_STR=". $this->query;
		
		return false;
	}

	function get_obj() {
		return @mysql_fetch_object($this->result);
	}

	function get_all() {
		$all = array();

		while ($obj = @mysql_fetch_object($this->result))
			if ($obj)
				$all[] = $obj;

		return $all;
	}
	
	function write_log($subject) {
		$this->query("insert into log (id, user, subject, date) values (?, ?, ?, ?)", array("", USER_IP, $subject, date(DB_DATEFORMAT)));
	}

	function free() {
		@mysql_free_result($this->result);
	}

    function close() {
		@mysql_close($this->connection);
	}
}

?>
