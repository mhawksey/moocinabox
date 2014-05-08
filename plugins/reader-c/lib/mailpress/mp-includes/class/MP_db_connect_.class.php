<?php
abstract class MP_db_connect_
{
	function mysql_disconnect($x = '0')
	{
		global $wpdb;
		if (isset($this->trace)) $this->trace->log("MAILPRESS [NOTICE] - Disconnecting from " . DB_NAME . " ($x)");
		mysql_close($wpdb->dbh);
		if (isset($this->trace)) $this->trace->log("MAILPRESS [NOTICE] - Disconnected ($x)");
	}

	function mysql_connect($x = '0')
	{
		global $wpdb;
		if (isset($this->trace)) $this->trace->log("MAILPRESS [NOTICE] - Connecting to " . DB_NAME . " ($x)");

		$wpdb->__construct(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);

		if (isset($this->trace)) $this->trace->log("MAILPRESS [NOTICE] - Connected ($x)");
	}
}