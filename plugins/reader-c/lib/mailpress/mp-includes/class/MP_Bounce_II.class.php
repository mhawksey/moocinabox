<?php
class MP_Bounce_II extends MP_bounce_
{
	public $option_name 	= MailPress_bounce_handling_II::option_name;
	public $option_name_pop3= MailPress_bounce_handling_II::option_name_pop3;
	public $meta_key 		= MailPress_bounce_handling_II::meta_key;

	public $class		= __CLASS__;
	public $log_name 		= 'mp_process_bounce_handling_II';
	public $log_option_name = 'bounce_handling_II';
	public $log_title 	= 'Bounce Handling II Report (Bounce in mailbox : %1$s )';

	public $cron_name 	= 'MailPress_schedule_bounce_handling_II';

	private $subject_regex 	= '';
	private $bounce_subject = array(
'**Message you sent blocked by our bulk email filter**',
'delayed 24 hours',
'delayed 48 hours',
'delayed 72 hours',
'Delivery Failure',
'Delivery is delayed',
'delivery notification',
'Delivery Notification: Delivery has failed',
'delivery report',
'Delivery Status Notification',
'Delivery unsuccessful',
'delivery_failure',
'error delivering mail',
'Expired Delivery Retry Notification',
'failure delivery',
'Failure Notice',
'Mail could not be delivered',
'mail delivery error',
'Mail delivery failed',
'Mail Delivery Problem',
'mail not delivered',
'mail status report',
'Mail System Error - Returned Mail',
'Marked As Spam:',
'Message status - undeliverable',
'Non delivery report',
'Nondeliverable mail',
'Returned mail',
'Undeliverable Mail',
'Undeliverable message',
'Undeliverable:',
'Undelivered Mail Returned to Sender',
'Warning: could not send message',
        );

	private $bounce_body 	= array (
' is FULL',
'522_mailbox_full',
'account is full',
'Can\'t create output',
'Delivery failed: Over quota',
'disk full',
'does not have enough space',
'exceed mailbox quota',
'exceed maximum allowed storage',
'exceed the quota for the mailbox',
'exceed the quota for the mailbox',
'exceeded his/her quota',
'exceeded storage allocation',
'exceeded the space quota',
'exceeds allowed message count',
'exceeds size limit',
'File too large',
'Inbox is full',
'incoming mailbox for user ',
'insufficient disk space',
'mail box space not enough',
'Mailbox disk quota exceeded',
'mailbox full',
'Mailbox has exceeded the limit',
'Mailbox has exceeded the limit',
'mailbox is full',
'mailbox is full',
'mailbox_quota_exceeded',
'maildir has overdrawn his diskspace quota',
'mailfolder is full',
'message is larger than the space available',
'Message would exceed ',
'message would exceed quota',
'not able to receive any more mail',
'Not enough storage space',
'over disk quota',
'Over quota',
'over quota',
'over the allowed quota',
'over the maximum allowed mailbox size',
'over the maximum allowed number of messages',
'over the storage quota',
'over their disk quota',
'Quota exceed',
'Quota exceeded',
'quota for the mailbox',
'Quota violation',
'recipient exceeded dropfile size quota',
'Recipient exceeded email quota',
'recipient storage full',
'Requested mailbox exceeds quota',
'Status: 5.2.2',
'Storage quota reached',
'The incoming mailbox for user',
'The user has not enough diskspace available',
'The user\'s space has used up.',
'too many messages in this mailbox',
'too many messages on this mailbox',
'User account is overquota',
'user has full mailbox',
'user is invited to retry',
'user is over quota',
'user is over their quota',
'User is overquota',
'User mailbox exceeds allowed size',
'user overdrawn his diskspace quota',
					/*  */
'errno=28',
					/* blocked content */ 
' This message has been blocked because it contains FortiSpamshield blocking URL',
'5.7.1 Content-Policy reject',
'5.7.1 Message cannot be accepted, spam rejection',
'5.7.1 reject content',
'5.7.1 URL/Phone Number Filter',
'550 POSSIBLE SPAM',
'550 Protocol violation',
'550 Rule imposed mailbox access for',
'554 Transaction failed',
'appears to be spam',
'black listed url host',
'Blacklisted',
'blocked because it contains FortiGuard - AntiSpam blocking URL',
'Blocked for abuse',
'Blocked for spam',
'blocked using ',
'Connection refused due to abuse',
'considered unsolicited bulk e-mail',
'Denied by policy',
'email is considered spam',
'envelope sender is in my badmailfrom',
'Error: content rejected',
'extremely high on spam scale',
'has exceeded maximum attachment count limit',
'headers consistent with spam',
'high spam probability',
'HTML tag unacceptable',
'is not accepting mail from this sender',
'is on RBL list',
'is refused. See http://spamblock.outblaze.com',
'listed in multi.surbl.org',
'Mail appears to be unsolicited',
'Mail contained a URL rejected by SURBL',
'Mail From IP Banned',
'mail server is currently blocked',
'Message cannot be accepted, content filter rejection',
'message contains potential spam',
'Message contains unacceptable attachment',
'message content rejected',
'Message Denied: Restricted attachment',
'message filtered',
'message held before permitting delivery',
'Message held for human verification',
'Message identified as SPAM',
'message looks like a spam',
'message looks like spam',
'Message rejected because of unacceptable content',
'not accepting mail with attachments or embedded images',
'not accepting mail with attachments or embedded images',
'on spam scale',
'rejected as bulk',
'rejected by an anti-spam',
'rejected by anti-spam',
'rejected for policy reasons',
'sender denied',
'Sender is on domain\'s blacklist',
'Spam detected',
'Spam origin',
'Spam rejected',
'spamblock',
'they are not accepting mail',
'This message does not comply with required standards',
'This message has been flagged as spam',
'this message scored ',
'You have been blocked by the recipient',
'Your e-mail was rejected for policy reasons on this gateway',
					/* remote configuration error */
' but connection died',
'4.3.2 service shutting down',
'5.7.1 Transaction failed',
'542 Rejected',
'550 authentication required',
'550 System error',
'all relevant MX records point to non-existent hosts',
'Can\'t open mailbox',
'cannot store document',
'CNAME lookup failed temporarily',
'Command died with status',
'Command rejected',
'Command time limit exceeded',
'Connection refused',
'Connection timed out',
'delivery expired (message too old)',
'Delivery failed 1 attempt',
'delivery temporarily suspended',
'delivery time expired',
'delivery was refused',
'Error in processing',
'error on maildir delivery',
'Error opening input/output file',
'failed on DATA command',
'Failed, 4.4.7 (delivery time expired)',
'has exceeded the max emails per hour',
'has installed an invalid MX record with an IP address instead of a domain name on the right hand side.',
'Hop count exceeded',
'internal server error',
'internal software error',
'loop count exceeded',
'loops back to myself',
'maildir delivery failed',
'malformed or unexpected name server reply',
'not capable to receive mail',
'operation timed out',
'Please receive your mail before sending',
'Remote sending only allowed with authentication',
'Resources temporarily not available',
'Resources temporarily unavailable',
'several matches found in domino',
'sorry, that domain isn\'t in my list of allowed rcpthosts',
'sorry, that domain isn\'t in my list of allowed rcpthosts',
'temporarily deferred',
'Temporary error on maildir delivery',
'temporary failure',
'temporary problem',
'The host does not have any mail exchanger',
'this message has been in the queue too long',
'This message is looping',
'timed out while receiving the initial server greeting',
'TLS connect failed: timed out',
'Too many results returned',
'unable to connect successfully to the destination mail server',
'Unable to create a dot-lock',
'unable to deliver a message to',
'Undeliverable message',
'unreachable for too long',
'user path does not exist',
'user path no exist',
'your "received:" header counts',
					/* local configuration error */
'Address does not pass the Sender Policy Framework',
'but sender was rejected',
'could indicate a mail loop',
'Could not complete sender verify callout',
'lost connection with',
'Mail only accepted from IPs with valid reverse lookups',
'Name service error',
'only accepts mail from known senders',
'Remote host said: 542 Rejected',
'Remote host said: 554 Failure',
'SC-001 Mail rejected by Windows Live Hotmail for policy reasons.',
'sender id (pra) not permitted',
'Sender verification error',
					/* local condition error */
'does not have a valid PTR record associated with it.',
'You will need to add a PTR record (also known as reverse lookup) before you are able to send email into the iiNet network.',
					/* inactive */
'refused to talk to me: 452 try later',
					/* inactive */
' is disabled',
'Account closed due to inactivity',
'account deactivated',
'account expired',
'Account has been suspended',
'account has been temporarily suspended',
'Account inactive as unread',
'Account inactive',
'account is locked',
'account is not active',
'Blocked address',
'deactivated mailbox',
'disabled due to inactivity',
'disabled mailbox',
'extended inactivity new mail is not currently being accepted',
'inactive on this domain',
'inactive user',
'Mailaddress is administratively disabled',
'Mailaddress is administrativley disabled',
'Mailbox currently suspended',
'Mailbox disabled',
'mailbox temporarily disabled',
'Mailbox_currently_suspended',
'message refused',
'not an active address',
'permission denied',
'recipient never logged onto',
'said: 550 5.2.1',
'Sorry, I wasn\'t able to establish an SMTP connection',
'Status: 5.2.1',
'this account has been disabled or discontinued',
'This account is not allowed',
'unavailable to take delivery of the message',
'user account disabled',
'user account is expired',
'User hasn\'t entered during last ',
'User is inactive',
'user mailbox is inactive',
'user mailbox is inactive',
					/* box doesnt exist */
' does not exist',
'_does_not_exist_here',
'> does not exist',
'550 5.1.1 User unknown',
'550 5.1.1',
'554 delivery error: This user doesn\'t have',
'account closed',
'account does not exist',
'address doesn\'t exist',
'Address invalid',
'address is no longer active',
'address is not valid',
'Address rejected',
'Addressee unknown',
'bad address ',
'bad destination email address',
'can\'t create user output file',
'deactivated due to abuse',
'Delivery to the following recipient failed permanently',
'Delivery to the following recipients failed',
'destination addresses were unknown',
'Destination server rejected recipients',
'does not have an email',
'does not like recipient',
'doesn\'t have an account',
'doesn\'t_have_a_yahoo',
'email has changed',
'email name is not found',
'I am no longer with',
'I have now left ',
'invalid address',
'invalid domain mailbox user',
'invalid e-mail address',
'Invalid final delivery user',
'invalid mailbox',
'Invalid or unknown virtual user',
'invalid recipient',
'Invalid User',
'isn\'t in my list of allowed recipients',
'mail receiving disabled',
'mailbox is currently unavailable',
'mailbox is not valid',
'mailbox not available',
'mailbox not found',
'mailbox unavailable',
'mailbox unavailable',
'no existe',
'no longer available',
'no longer in use',
'No mailbox here by that name',
'no recipients',
'No such account ',
'no such address',
'No such mailbox',
'No such recipient',
'No such user here',
'no such user here',
'No such user',
'No such virtual user here',
'no users here by that name',
'no valid recipients',
'non esiste',
'not a recognised email account',
'not a valid email account',
'not a valid mailbox',
'Not a valid recipient',
'not a valid user',
'not have a final email delivery point',
'not known at this site',
'not listed in domino directory',
'not our customer',
'Permanent error in automatic homedir creation',
'permanent fatal delivery',
'permanent fatal errors',
'Please check the recipients e-mail address',
'Recipient address rejected',
'recipient is invalid',
'Recipient no longer on server',
'Recipient not allowed',
'recipient not found',
'recipient rejected',
'Recipient unknown',
'recipient\'s account is disabled',
'recipients are invalid',
'Remote host said: 553',
'retry time not reached for any host after a long failure period',
'retry timeout exceeded',
'said: 553 sorry,',
'server doesn\'t handle mail for that user',
'sorry, no mailbox',
'Status: 5.1.1',
'The following recipients are unknown',
'The mailbox is not available on this system',
'The recipient cannot be verified',
'The recipient name is not recognized',
'There is no user by that name',
'This address does not receive mail',
'This address is no longer valid',
'This address no longer accepts mail',
'This Gmail user does not exist',
'This is a permanent error. The following address',
'This recipient e-mail address was not found',
'This user doesn\'t have a ',
'This user doesn\'t have a yahoo',
'this user doesn\'t have a yahoo.com account',
'Unable to chdir to maildir',
'Unable to find alias user',
'unable to validate recipient',
'unavailable mailbox',
'undeliverable to the following',
'Unknown account ',
'Unknown address error',
'unknown address or alias',
'unknown address',
'Unknown destination address',
'unknown email address',
'Unknown local part',
'Unknown local-part',
'unknown or illegal alias',
'unknown recipient',
'unknown user account',
'unknown user',
'unrouteable address',
'Unrouteable address',
'User Does Not Exist',
'user invalid',
'user is no longer available',
'User is unknown',
'user not found',
'User not known',
'User reject the mail',
'User unknown in local recipient table',
'User unknown in virtual alias table',
'User unknown in virtual mailbox table',
'User unknown in virtual mailbox',
'user unknown',
'user_unknown',
'Your email has not been delivered',
'Your e-mail has not been delivered',
'Your mail has not been delivered',
					/* domain doesnt exist */
'address does not exist',
'an MX or SRV record indicated no SMTP service',
'bad destination host',
'Cannot resolve the IP address of the following domain',
'Domain does not exist, please check your spelling',
'Domain must resolve',
'Domain not used for mail',
'host not found',
'Host or domain name not found',
'I couldn\'t find a mail exchanger or IP address',
'I couldn\'t find any host by that name',
'I couldn\'t find any host named',
'illegal host/domain',
'message could not be delivered for \d+ days',
'name or service not known',
'no matches to nameserver query',
'no route to host',
'No such domain at this location',
'no such domain',
'unrouteable mail domain',
/* relay error */
'5.7.1 Unable to deliver to ',
'554 denied',
'access denied',
'Although I\'m listed as a best-preference MX or A for that host',
'Authentication required for relay',
'Cannot relay',
'dns loop',
'is currently not permitted to relay',
'loop: too many hops',
'mail server permanently rejected message',
'message could not be delivered',
'not a gateway',
'not permitted to relay through this server',
'relay not permitted',
'relaying denied',
'relaying disallowed',
'Relaying is prohibited',
'relaying mail to',
'Relaying not allowed',
'Sender verify failed',
'they are not accepting mail from',
'This mail server requires authentication when attempting to send to a non-local e-mail address.',
'This system is not configured to relay mail',
'too many hops, this message is looping',
'Unable to relay for',
'we do not relay',
					/* invalid email */
'550_Invalid_recipient',
'bad address syntax',
'Bad destination mailbox address',
'domain missing or malformed',
'Invalid Address',
'not our customer',
        );

	function __construct()
	{
		foreach ($this->bounce_subject as $k => $v) $this->bounce_subject[$k] = preg_quote($v, '~');
		$this->subject_regex = '~(' . implode('|', $this->bounce_subject) . ')~i';

		parent::__construct();
	}

	function is_bounce()
	{
		$tags = array('Subject', 'X-MailPress-blog-id', 'X-MailPress-mail-id', 'X-MailPress-user-id');
		$this->pop3->get_headers_deep($this->message_id, $tags);

		$blog_id = $this->get_tag('X-MailPress-blog-id', 1);
		if (false === $blog_id) return false;
		global $wpdb;
		if ($blog_id != $wpdb->blogid) return false;

		$mail_id = $this->get_tag('X-MailPress-mail-id', 1);
		if (false === $mail_id) return false;

		$mp_user_id = $this->get_tag('X-MailPress-user-id', 1);
		if (false === $mp_user_id)
		{
			if (!$mail = MP_Mail::get($mail_id)) return false;

			if (!is_email($mail->toemail)) return false;

			$mp_user_id = MP_User::get_id_by_email($mail->toemail);
                
			if (!$mp_user_id) return false;
		}

		// detect message is bounce

		$subject = $this->get_tag('Subject');
		$subject = trim($subject);
		$subject = strtolower($subject);
		if (empty($subject) || !preg_match($this->subject_regex, $subject)) return false;

		if (!$this->parse_body($this->get_body(0))) return false;

		$this->process_mailbox_status();

		return array($mail_id, $mp_user_id, "mail : $mail_id & user : $mp_user_id");
	}

	function parse_body($body)
	{
		$status = array();
		if (preg_match('/(--[^\s]*?)\sContent-Type\s*?\:\s*?message\/delivery-status\s(.*?)\1/is', $body, $matches)) 
		{
			if (3 == count($matches)) preg_match('/Status\s*?\:\s*?([2|4|5]+)\.(\d{1,3}).(\d{1,3})/is', $matches[2], $status);
			unset($matches);
		}

		if (4 == count($status)) 
		{
			$bounce = false;
			switch ($status[1]) 
			{
				case 2:
					$bounce = true;
					break;
				case 4:
					$bounce = $this->RFC_3463_4($status[2], $status[3]);
					break;
				case 5:
					if (5 == $status[2] && 0 == $status[3]) break;
					$bounce = $this->RFC_3463_5($status[2], $status[3]);
					break;
			}
			if ($bounce) return true;
		}
		// -----

		foreach ($this->bounce_body as $rule) if (preg_match('%' . preg_quote($rule) . '%is', $body)) return true;

		return false;
	}

	// RFC 3463 - Enhanced Mail System Status Codes

	function RFC_3463_4($code, $subcode)
	{
		$bounce = false;
		if ((5 == $code) && (3 == $subcode)) $bounce = true;
		return $bounce;
	}

	function RFC_3463_5($code, $subcode)
	{
		$bounce = false;
		switch ($code)
		{
			case '1': 
				if (in_array($subcode, array('0','1','2','3','4','5','6'))) $bounce = true;
				break;
			default :
				if (in_array($code, array('2','3','4','5','6','7'))) $bounce = true;
				break;
		}
		return $bounce;
	}
}