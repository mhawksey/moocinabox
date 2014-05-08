=== mp-content/advanced folders ===

* dashboard
	- mails_activity.xml : called in mp-includes/class/options/dashboard/widgets/mails_activity.php : for mails pie chart

* forms
	- templates
		_ *.xml : templates and subtemplates of form and form fields.

* html2txt (mandatory)
	- *.xml : called in mp-includes/class/MP_Html2txt.class.php : to convert html into plaintext 

* newsletters
	- *.xml : newsletters descriptions
	- categories
		_ categories.xml : categories newsletters descriptions

* scripts
	- deregister.xml (optional): called in mp-includes/class/MP_Admin_page.class.php : to deregister some external scripts abusively called on mp admin pages

* subscription-form
	- style.css : called in mp-includes/class/MP_Widget.class.php : contains the widget subscription form css
	- iframes
		_ facebook (prototype)
			+ read the readme.txt.
		_ std
			+ read the readme.txt.