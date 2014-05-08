<?php
abstract class MP_options_
{
	function __construct()
	{
   		// Load all options so that they can do what they have to do.
		$root = MP_ABSPATH . 'mp-includes/class/options/' . $this->path;
		$dir  = @opendir($root);
		if ($dir) while ( ($file = readdir($dir)) !== false ) if ($file[0] != '.') $this->load($root, $file);
		@closedir($dir);

		do_action('MailPress_load_' . ucfirst(str_replace('/', '_', $this->path)));
	}

	function load($root, $file)
	{
		if (isset($this->deep))
		{
			if (is_dir("$root/$file"))
			{
				$root .= "/$file";
				$dir  = @opendir($root);
				if ($dir) while (($file = readdir($dir)) !== false) if ($file[0] != '.') $this->load_file("$root/$file");
				@closedir($dir);
				return;
			}
		}
		elseif ( isset($this->includes) && !isset($this->includes[substr($file, 0, -4)]) ) return;

		$this->load_file("$root/$file");
	}

	function load_file($file)
	{
		if (substr($file, -4) != '.php') return;
		require_once($file);
	}
}