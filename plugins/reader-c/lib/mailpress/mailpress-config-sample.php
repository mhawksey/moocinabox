<?php
/**
 * Optional config file to 
	** rename mp-content folder :
		*** rename mp-content folder to the new folder name (e.g. : mailpress-content)
		*** create mailpress-config.php file from this file inside mailpress folder, rename "mp-content" (e.g. : replace 'mp-content' by 'mailpress-content').
		*** check under Wp Admin : Mails > Themes, that themes are located with the right path.
	** place mp-content folder outside mailpress folder
		*** copy mp-content folder in the mailpress parent directory with a new folder name (e.g. : mailpress-content)
		*** create mailpress-config.php file from this file inside mailpress parent directory, rename "mp-content" (e.g. : replace 'mp-content' by 'mailpress-content').
		*** check under Wp Admin : Mails > Themes, that themes are located with the right path.

 	** define use of wp_enqueue_script for mailpress subscription form >> requires : wp_print_scripts(); in your wordpress theme footer !! .

 	** define log for debug purpose only, uncomment appropriate line.
*/

// 0.

//define ('MP_Ip_ipinfodb_ApiKey', 	'<- get your api key here : http://www.ipinfodb.com/register.php ->');

//define ('MP_Ip_quova_ApiKey', 	'<- get your api key here : http://developer.quova.com/member/register ->');
//define ('MP_Ip_quova_Secret', 	'<- get your secret  here : http://developer.quova.com/member/register ->');

// 1.

/** Folder name of MailPress 'mp-content'. */
//define ('MP_CONTENT_FOLDER', 	'mp-content');

// 2.

/** MailPress subscription form wp_enqueue_script (uncomment if necessary) */
/** requires : wp_print_scripts(); in your wordpress theme footer !! */
//define ('MP_wp_enqueue_script', true);

// 3.

/** MailPress dev log (uncomment if necessary) */
//define ('MP_DEBUG_LOG', true);





/* That's all, stop editing! Check right path under Mails > Themes and Happy mailing. */

if ( defined('MP_CONTENT_FOLDER') )
{
	/** Absolute path to the MailPress 'mp-content' folder. */
	define ('MP_CONTENT_DIR', 	dirname(__FILE__) . '/' . MP_CONTENT_FOLDER . '/' );

	/** Relative path to the MailPress 'mp-content' folder. */
	if ( MP_FOLDER != basename(dirname(__FILE__)) )
		define ('MP_PATH_CONTENT', 	dirname(MP_PATH) . '/' 	. MP_CONTENT_FOLDER . '/' );
}