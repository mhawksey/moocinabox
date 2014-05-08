<?php
abstract class MP_tracking_xml_
{
	function __construct($title)
	{
		$this->title = $title;
		$this->img_path = site_url() . '/' . MP_PATH . "mp-admin/images/{$this->id}";
    
		$xml = new SimpleXMLElement(file_get_contents(MP_ABSPATH . "mp-admin/xml/{$this->id}s.xml"));
		$this->xml = $xml->{$this->id};

		add_filter("MailPress_tracking_{$this->folder}_register",		array($this, 'register'),	8, 1);
		add_filter("MailPress_tracking_{$this->folder}_{$this->id}_get",array($this, 'get'), 	8, 1);
	}

	function register($items)
	{
		$items[$this->id] = $this->title;
		return $items;
	}

	function get($string)
	{
		$item = new stdClass();
		$count = 0;

		foreach ($this->xml as $i)
		{
			unset($icon);
			$count++;

			if (!@preg_match($i->pattern, $string, $matches)) continue;
			if (isset($i->icon)) $icon = $i->icon;
			if (!isset($i->versions)) break;

			foreach($i->versions as $attrs) $vp = (int) $attrs['pattern'];

			switch (true) 
			{
				case (isset($i->versions->id)) :
					foreach($i->versions->id as $ver)
					{
						if (@preg_match($ver->pattern, $matches[$vp]))
						{
							$version = (string) $ver->name;
							if (isset($ver->icon)) $icon = (string) $ver->icon;
							break;
						}
					}
				break;
				case (!empty($i->versions)) :
					@preg_match($i->versions, $string, $matches);
					if (isset($matches[$vp])) $version = $matches[$vp];
				break;
				default :
					$version = (string) $matches[$vp];
				break;
			}
			break;
		}

		$item->string = $string;
		if (isset($i->name)) { $item->name = (string) $i->name; }
		if (isset($version)) { $item->version = (string) $version; }
		if (isset($i->link)) { $item->link = (string) $i->link; }
		if (isset($icon))    { $item->icon = (string) $icon; $item->icon_path = "{$this->img_path}/{$item->icon}"; }

		if (isset($item->name))	$item->full_name = (isset($item->version)) ? "{$item->name} {$item->version}" : $item->name;
		else					$item->name = $item->full_name = '';
		$item->count = $count;

		return $item;
	}
}