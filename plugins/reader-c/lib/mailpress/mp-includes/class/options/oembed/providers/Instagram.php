<?php
class MP_oembed_provider_Instagram extends MP_oembed_provider_
{
	public $id = 'Instagram';

	function data2html( $html, $data, $url )
	{

		switch ($data->type)
		{
			case 'photo' :
				$html  = "<a target='_blank' href=\"" . esc_url($url) . "\"";
				if (isset($data->title))    	   $html .= " title=\"" . esc_html($data->title) . "\"";
				$html .= ">";

				$html .= "<img";
				if (isset($data->width))  $html .= " width='{$data->width}px'";
				if (isset($data->height)) $html .= " height='{$data->height}px'";
				if (isset($data->url))    $html .= " src='{$data->url}'";
				if (isset($data->title))    	    $html .= " title=\"" . esc_html($data->title) . "\" alt=\"" . esc_html($data->title) . "\"";
				$html .= " />";

				$html .= "</a>";
			break;
		}
		return $html;
	}
}
new MP_oembed_provider_Instagram();
