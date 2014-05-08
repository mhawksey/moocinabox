<?php
class MP_oembed_provider_Twitter extends MP_oembed_provider_
{
	public $id = 'Twitter';

	function data2html( $html, $data, $url )
	{
		switch ($data->type)
		{
			case 'rich' :

				$data->author_id = substr($data->author_url, strrpos($data->author_url, '/') + 1);

				$data->status_id = substr($data->url, strrpos($data->url, '/') + 1);

				preg_match_all("/data-datetime=\"([^`]*?)\"/", $data->html, $matched, PREG_SET_ORDER);
				$data->datetime = $matched[0][1];

				preg_match_all("'<p>(.*?)</p>'si", $data->html, $matches, PREG_SET_ORDER);
				$data->text = $matches[0][1];

				$bg_url = ( (defined('WP_SITEURL')) ? WP_SITEURL : site_url() ) . "/wp-content/plugins/mailpress/mp-includes/class/options/oembed/providers/Twitter.png";

				$a = "float:left;cursor: pointer;text-decoration: none;font:12px/16px Arial,sans-serif;";
				$b = "float:left;font-weight:normal;color:#999;";
				$i = "display: block;float:left;font-style:normal;cursor: pointer;background-attachment: scroll;background-clip: border-box;background-color: transparent;background-image: url({$bg_url});background-origin: padding-box;background-repeat: repeat-x;background-size: 49px 137px;";
				$html = "<div style='margin:7px 0;color:#333;font:14px/16px Arial,sans-serif;background: none repeat scroll 0 0 #FFF;width: 550px!important;'><div style='border-color:#EEE #DDD #BBB;border-radius:5px 5px 5px 5px;border-style:solid;border-width:1px;bottom: auto;box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);width: 500px!important;'><table style='margin:0;padding:0;border-collapse:collapse;width:100%;'><tbody><tr><td rowspan='2' height='32px' width='32px' style='padding:10px 0 0 12px;width:32px;heigth:32px;'><a href='https://twitter.com/{$data->author_id}' style=''><img src='https://api.twitter.com/1/users/profile_image/{$data->author_id}' alt='' height='32px' width='32px' style='border-radius:5px;' /></a></td><td width='100%' style='padding:10px 12px 0 10px;'><a class='mp_twitter_author_id' href='https://twitter.com/{$data->author_id}' style='color:#333;cursor:pointer;font: bold 14px/18px Arial,sans-serif;width:100%;text-decoration:none;'>{$data->author_name}</a></td></tr><tr><td style='padding:0 12px 0 10px;'><a href='https://twitter.com/{$data->author_id}' style='color:#999;cursor:pointer;font:12px/16px Arial,sans-serif;text-decoration:none;'>@{$data->author_id}</a></td></tr><tr><td class='mp_twitter_link' colspan='2' style='padding:7px 17px 6px 12px;color:#333;font:16px/22px Georgia,Palatino,serif;width:100%;'>{$data->text}</td></tr><tr><td colspan='2' style='margin:0;padding:0;'><table style='margin:0;padding:0;border-collapse:collapse;' width='100%'><tr><td class='mp_twitter_link' style='padding:2px 0 12px 12px;'><a class='mp_twitter_date' href='{$data->url}' style='color:#999;font: 12px/13px Arial,sans-serif;text-decoration: none;'><span title='" . mysql2date( 'j M Y, H:i:s (e)', $data->datetime) . "'>" . mysql2date( 'j M y', $data->datetime) . "</span></a></td>	<td class='mp_twitter_link' style='padding:2px 12px 12px 0;font-size:12px;'><ul style='float:right;margin:0; padding:0;list-style: none outside none;marks: none;text-align:left;display:block;heigth:16px;'><li style='float:left;display: block;text-align:left;'><a href='https://twitter.com/intent/tweet?in_reply_to={$data->status_id}' title=\"" . esc_attr(__('Reply', MP_TXTDOM)) . "\" style='{a}'><i style='{$i}margin: 1px 5px 0 8px;background-position: 0 -30px;height: 13px;width: 18px;'></i><b style='{$b}'>" . __('Reply', MP_TXTDOM) . "</b></a></li>	<li style='float:left;display: block;text-align:left;'><a href='https://twitter.com/intent/retweet?tweet_id={$data->status_id}' title=\"" . esc_attr(__('Retweet', MP_TXTDOM)) . "\" style='{a}'><i style='{$i}margin: 1px 5px 0 8px;background-position: 0 -48px;height: 12px;width: 22px;'></i><b style='{$b}'>" . __('Retweet', MP_TXTDOM) . "</b></a></li>	<li style='float:left;display: block;text-align:left;'><a href='https://twitter.com/intent/favorite?tweet_id={$data->status_id}' title=\"" . esc_attr( __('Favorite', MP_TXTDOM)) . "\" style='{a}'><i style='{$i}margin: -1px 5px 0 8px;background-position: 0 -66px;height: 15px;width: 16px;'></i><b style='{$b}'>" . __('Favorite', MP_TXTDOM) . "</b></a></li>	</ul>	</td>	</tr>	</table></td></tr></tbody></table></div></div>";

			break;
		}
		return $html;
	}
}
new MP_oembed_provider_Twitter();
