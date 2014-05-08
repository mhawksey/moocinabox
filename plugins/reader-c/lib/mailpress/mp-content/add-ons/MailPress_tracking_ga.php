<?php
if (class_exists('MailPress') && !class_exists('MailPress_tracking_ga'))
{
/*
Plugin Name: MailPress_tracking_ga
Plugin URI: http://blog.mailpress.org/tutorials/add-ons/tracking_ga/
Description: Tracking : mails activity to your site with <a href='http://www.google.com/support/googleanalytics/bin/answer.py?hl=en&amp;answer=55540'>google analytics</a> (<span style='color:red;'>beware !</span> Not compatible with <span style="color:#D54E21;">Tracking</span> add-on)
Version: 5.4
*/

class MailPress_tracking_ga
{
	function __construct()
	{
		if (class_exists('MailPress_tracking')) return;
// prepare mail
		add_filter('MailPress_mail',	array(__CLASS__, 'mail'), 8, 2);
	}

// prepare mail

	public static function mail($mail)
	{
		$utms = array(	'utm_source' 	=> 	'mailpress',
					'utm_medium' 	=> 	'email_link',
		//			'utm_term' 		=> 	'',
					'utm_content' 	=> 	"{$mail->theme}_{$mail->template}_{$mail->id}",
					'utm_campaign' 	=> 	date('c'),
		);
		$args = (isset($mail->utms) && is_array($mail->utms)) ? $mail->utms : $utms;

		$siteurl = site_url();
		$home    = home_url();


		$output = preg_match_all('/<a [^>]*href=[\'"]([^\'"]+)[\'"][^>]*>(.*?)<\/a>/is', $mail->html, $matches, PREG_SET_ORDER);

		$hrefs_txt = array();
		if ($matches)
		{
			foreach ($matches as $match)
			{
				if (strpos($match[1], 'mailto:') !== false) continue;
				if (strpos($match[1], $siteurl) === false && strpos($match[1], $home) === false) continue;

				$t_url  = $match[1];

				/* Strip any anchor reference off */
				$anchor 	= '';
				$hash_pos 	= strrpos($match[1], '#');

				if ($hash_pos !== false)
				{
					$t_url  = substr($match[1], 0, $hash_pos);
					$anchor = substr($match[1], $hash_pos);
			      }

				$sep = (strpos($t_url, '?')) ? '&' : '?';
				foreach ($args as $k => $v) {$t_url .= "{$sep}{$k}={$v}"; $sep = '&';}

				$t_url .= $anchor;

				$link = self::str_replace_count($match[1], $t_url, $match[0], 1);
				$mail->html = str_replace($match[0], $link, $mail->html);

				$hrefs_txt[$match[1]] = $t_url;
			}
		}

		if (!empty($hrefs_txt))
		{
			uksort($hrefs_txt, create_function('$a, $b', 'return strcmp(strlen($a), strlen($b));'));
			$hrefs_txt = array_reverse($hrefs_txt);
			$mail->plaintext = str_replace(array_keys($hrefs_txt), $hrefs_txt, $mail->plaintext);
		}
		return $mail;
	}

	public static function str_replace_count($search, $replace, $subject, $times=1) 
	{
		$subject_original=$subject;

		$len=strlen($search);
		$pos=0;
		for ($i=1;$i<=$times;$i++) 
		{
			$pos=strpos($subject, $search, $pos);
			if($pos!==false) 
			{
				$subject=substr($subject_original, 0, $pos);
				$subject.=$replace;
				$subject.=substr($subject_original, $pos+$len);
				$subject_original=$subject;
			}
			else
			{
				break;
			}
		}
		return($subject);
	}
}
new MailPress_tracking_ga();
}