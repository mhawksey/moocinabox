<?php
class MP_oembed_provider_Viddler extends MP_oembed_provider_
{
	public $id = 'Viddler';

	function data2html( $html, $data, $url )
	{
		switch ($data->type)
		{
			case 'video' :
				$html  = "<a target='_blank' href=\"" . esc_url($data->url) . "\"";
				$html .= " title=\"" . esc_html($data->title) . "\"";
				$html .= ">";

				$html .= "<img";
				$html .= " width='{$data->width}px'";
				$html .= " height='{$data->height}px'";
				$html .= " src='{$data->thumbnail_url}'";
				$html .= " title=\"" . esc_html($data->title) . "\" alt=\"" . esc_html($data->title) . "\"";
				$html .= " />";

				$html .= "</a>";
			break;
		}
		return $html;
	}
}
new MP_oembed_provider_Viddler();
