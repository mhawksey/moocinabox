=== MailPress ===
Contributors: andre renaut
Donate link: http://blog.mailpress.org
Tags: mail, mails, comments, comment, subscribe, newsletters, newsletter, Wordpress, Plugin, swiftmailer, post, posts, smtp, sendmail, phpmail, notification, notifications, mail, mails, contact form
Requires at least: 3.6
Tested up to: 3.6
Stable tag: 5.4

The WordPress mailing platform 

== Description ==

1. Style your html and plain text mails with dedicated themes and templates.
1. Double opt-in subscription.
1. Subscriptions to Comments, Newsletters/Post notifications and even to Mailing lists.
1. Newsletters/Post notifications on a per post, daily, weekly, monthly basis.
1. Optional : full control on all mails sent by WordPress.


**Never use WordPress automatic upgrade** : if you have any customized file, WordPress will delete them !

**Follow Installation or Upgrade guidelines**
**Follow Installation or Upgrade guidelines**
**Follow Installation or Upgrade guidelines**

Some technical stuff :

1. Switmailer 5.0.1
1. jQuery 1.7.2 supported.
1. Google Map API V3 supported.
1. Code Mirror 0.9 (2.2)

Please report any bug in the mailpress google group http://groups.google.com/group/mailpress
starting your subject title with : "(MailPress 5.2)".

Thank you

== Installation ==

1. Make sure you have already installed WordPress 3.4 or above.
1. Unzip the MailPress package.
1. Upload the mailpress folder into wp-content/plugins.
1. Make sure the wp-content/plugins/mailpress/tmp folder is writable.
1. Log in to your WordPress admin and point your browser to "Plugins" page.
1. Activate MailPress plugin.
1. Point your browser to "Plugins > MailPress Add-ons" and activate required/desired add-ons (not all of them ! know what you are doing !).
1. Point your browser to "Settings > MailPress", fill and save the settings for each tab (General, (Connection to your mail server), Test, ... add-ons).
1. Once everything is installed, use the Test tab in "Settings > MailPress" to validate your settings.

**Never use WordPress automatic upgrade** : if you have any customized file, WordPress will delete them !

** Upgrade Notice **

1. Point your browser to “Plugins > MailPress Add-ons” page and deactivate all add ons.
1. Point your browser to “Plugins” page and deactivate MailPress plugin.
1. Save or Back-up mailpress/tmp folder + your MP theme + any customized file (step 4 to 8):
2. standards newsletters xml files have their path changed : mailpress/mp-content/advanced/newsletters/post/ (formerly mailpress/mp-content/advanced/newsletters/)
2. post newsletter xml file has its path & name changed : mailpress/mp-content/advanced/newsletters/post/post.xml (formerly mailpress/mp-content/advanced/newsletters/new_post.xml)
2. category newsletters xml file(s) have their path changed : mailpress/mp-content/advanced/newsletters/post/categories (formerly mailpress/mp-content/advanced/newsletters/categories)
2. scheduler id "post_cat" is renamed in "post_category"
2. processor id "now" is renamed in "post"
1. Delete wp-content/plugins/mailpress folder.
1. Unzip the MailPress package.
1. Upload the mailpress folder into wp-content/plugins.
1. Make sure the wp-content/plugins/mailpress/tmp folder is writable.
1. Restore mailpress/tmp folder + your MP theme + any customized file (read step 3 for any mailpress/mp-content/advanced/newsletters/ restore)
1. Refresh "Plugins" page and activate MailPress plugin
1. Activate MailPress previous add-ons (Plugins > MailPress Add-ons)

MailPress themes and templates do not need to be changed if customized in a previous MailPress release. 

== Upgrade Notice ==

**Never use WordPress automatic upgrade** : if you have any customized file, WordPress will delete them !

see Upgrade Notice in Installation section above

MailPress themes and templates do not need to be changed if customized in a previous MailPress release. 

**Never use WordPress automatic upgrade** : if you have any customized file, WordPress will delete them !

== Frequently Asked Questions ==

* see tutorials page http://blog.mailpress.org/tutorials/

== Screenshots ==

1. none

== Changelog ==

** 5.4 ** 05/08/2013

* Swiftmailer 5.0.1

* bug fix : fixing wpList changes, new html5 attribute data-wp-lists introduced in WP 3.5 (MailPress_Post add-on, drafts list)

** 5.3  ** 06/14/2012

** 5.2.1 **	12/25/2011

**Please Donate** https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=andre%2erenaut%40gmail%2ecom&lc=US&item_name=MailPress&item_number=gg&amount=5%2e00&currency_code=EUR&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHostedGuest
