<?php
class MP_Log
{
	const noMP_Log	= 123456789;

	function __construct($name, $args = array())
	{
		$this->errors 	= array (	1 	=> 'E_ERROR', 
							2 	=> 'E_WARNING', 
							4 	=> 'E_PARSE', 
							8 	=> 'E_NOTICE', 
							16 	=> 'E_CORE_ERROR', 
							32 	=> 'E_CORE_WARNING', 
							64 	=> 'E_COMPILE_ERROR', 
							128 	=> 'E_COMPILE_WARNING', 
							256 	=> 'E_USER_ERROR', 
							512 	=> 'E_USER_WARNING * ', 
							1024 	=> 'E_USER_NOTICE', 
							2048 	=> 'E_STRICT', 
							4096 	=> 'E_RECOVERABLE_ERROR', 
							8191 	=> 'E_ALL' ); 


		$defaults = array(	'path'		=> MP_ABSPATH,
						'force'		=> false,
						'option_name'	=> 'general',
		);
		$r = wp_parse_args( $args, $defaults );
		extract( $r );

		$this->name 	= $name;
		$this->path 	= $path . 'tmp';
		$this->option_name= $option_name;

		global $wpdb;
		$this->ftmplt	= 'MP_Log' . '_' . $wpdb->blogid . '_' . $this->name . '_';

		$this->file 	= $this->path . '/' . $this->ftmplt . gmdate('Ymd', current_time('timestamp')) . '.txt';

		$logs = get_option(MailPress::option_name_logs);
		$this->log_options = (isset($logs[$this->option_name])) ? $logs[$this->option_name] : MailPress::$default_option_logs;

		$this->level 	= (isset($this->log_options['level']))    ? (int) $this->log_options['level'] 	: self::noMP_Log ;
		$this->levels	= array (	1 	=> 1, 
							2 	=> 2, 
							4 	=> 4, 
							8 	=> 8, 
							16 	=> 16, 
							32 	=> 32, 
							64 	=> 64, 
							128 	=> 128, 
							256 	=> 256, 
							512 	=> 512, 
							1024 	=> 1024, 
							2048 	=> 2048, 
							4096 	=> 4096, 
							8191 	=> 8191 );
		if ($force) 
		{
			foreach ($this->levels as $k => $v) $this->levels[$k] = 0;
			$this->level = 0;
		}
		if (!is_dir($this->path)) $this->level = self::noMP_Log ;
		if (self::noMP_Log == $this->level) return;
		if ( 0  != $this->level) set_error_handler(array($this, 'logError'), $this->level);

		$this->start($force);
	}

	function start($force = false)
	{
		$plugin_version = ' **** (' . MP_Version . ')';

		$page = '';
		$page = $_SERVER['REQUEST_URI'];

		$this->data = "\n";

		if ($force) 
			$this->log (" **** Start logging **** {$this->name} *** log forced$plugin_version");
		elseif (!empty($page))
			$this->log (" **** Start logging **** {$this->name} *** level : {$this->level}$plugin_version **** $page");
		else
			$this->log (" **** Start logging **** {$this->name} *** level : {$this->level}$plugin_version");

// purge log
		$this->dopurge();

		ob_start();
	}

	function restart()
	{
		$this->stop();
		$this->data = "";
		ob_start();
	}

	function log($x, $level=0)
	{
		if (stripos($x, 'simplepie') == true) return;
		if (strpos($x, ' WP_Http') == true)   return;

		if (self::noMP_Log    == $this->level) return;
		if ($level <= $this->level) $this->data .= date_i18n('Y-m-d H:i:s u') . " -- " . $x . "\n";
	}

	function logError($error_level, $error_message, $error_file, $error_line, $error_context=false)
	{ 
		if (strpos($error_message, 'Please use the instanceof operator') == true) return;
		$this->log ("PHP [" . $this->errors[$error_level] . "] $error_level : $error_message in $error_file at line $error_line ", $error_level);
	}

	function stop()
	{
			if (self::noMP_Log == $this->level) return;
			if (0   != $this->level) restore_error_handler();

			$log = (ob_get_length()) ? ob_get_contents() : '';
		if (ob_get_length()) ob_end_clean();
		if (!empty($log)) $this->log($log);

		$this->fh = fopen($this->file , 'a+');
		fputs($this->fh, $this->data); 
		fclose($this->fh); 
	}

	function end($y = true)
	{
			if (self::noMP_Log == $this->level) return;
			if (0   != $this->level) restore_error_handler();

			$log = (ob_get_length()) ? ob_get_contents() : '';
		if (ob_get_length()) ob_end_clean();
		if (!empty($log)) $this->log($log);

		$y = ($y) ? "TRUE" : "FALSE";

		$this->log (" **** End   logging **** {$this->name} *** level : $this->level **** status : $y ");

		$this->fh = fopen($this->file , 'a+');
		fputs($this->fh, $this->data); 
		fclose($this->fh); 

// mem'ries ...
		$xs = array( 	'this->data', 'this->errors', 'this->name', 'this->path', 'this->plug', 'this->ftmplt', 'this->level', 'this->levels', 'this->lastpurge', 'this->lognbr');
		foreach ($xs as $x) if (isset($$x)) unset($$x);
	}

	function dopurge()
	{
		$now = date_i18n('Ymd', current_time('timestamp'));
		$this->lastpurge= (isset($this->log_options['lastpurge'])) ? $this->log_options['lastpurge'] 		: $now;
		$this->lognbr 	= (isset($this->log_options['lognbr']))    ? (int) $this->log_options['lognbr'] 	: 1;

		if ($now == $this->lastpurge) return;

		$this->log_options['lastpurge'] = $now;
		$this->log_options['lognbr']    = $this->lognbr;

		$logs = get_option(MailPress::option_name_logs);
		$logs[$this->option_name] = $this->log_options;
		update_option (MailPress::option_name_logs, $logs);


		$xs = array();
		$l = opendir($this->path);
		if ($l) 
		{
			while ( ($file = readdir($l)) !== false ) if ( preg_match('#' . $this->ftmplt . '[0-9]#', $file) ) $xs[] = $file;
			@closedir($l);
		}

		sort($xs);
		$y = count($xs) - $this->lognbr + 1;

		while ($y > 0)
		{
			@unlink($file = $this->path . '/' . array_shift($xs));
			$this->log (" **** Purged log file **** " . $file);
			$y--;
		}
	}
}