// STYLING FILE INPUTS 1.0 | Shaun Inman <http://www.shauninman.com/> | 2007-09-07
if (!window.SI) { var SI = {}; };
SI.Files =
{
	htmlClass : 'SI-FILES-STYLIZED',
	fileClass : 'file',
	wrapClass : 'cabinet',
	
	fini : false,
	able : false,
	
	stylize : function(elem)
	{
		if (!this.fini) { this.init(); };
		if (!this.able) { return; };

		elem.parentNode.file = elem;
	},
	
	stylizeById : function(id)
	{
		this.stylize(document.getElementById(id));
	},
	
	stylizeAll : function()
	{
		if (!this.fini) { this.init(); };
		if (!this.able) { return; };
		
		var inputs = document.getElementsByTagName('input');
		for (var i = 0; i < inputs.length; i++)
		{
			var input = inputs[i];
			if (input.type == 'file' && input.className.indexOf(this.fileClass) != -1 && input.parentNode.className.indexOf(this.wrapClass) != -1)
			{
				this.stylize(input);
			};
		};
	},

	init : function()
	{
		this.fini = true;
		
		var ie = 0 //@cc_on + @_jscript_version
		if (window.opera || (ie && ie < 5.5) || !document.getElementsByTagName) { return; } // no support for opacity or the DOM
		this.able = true;
		
		var html = document.getElementsByTagName('html')[0];
		html.className += (html.className != '' ? ' ' : '') + this.htmlClass;
	}
};