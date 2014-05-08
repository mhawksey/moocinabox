// templates

function validateXML(xml)
{
	if (window.ActiveXObject)										// code for IE
	{
		var xmlDoc = new ActiveXObject('Microsoft.XMLDOM');
		xmlDoc.async = 'false';
		xmlDoc.loadXML(xml);
		if(xmlDoc.parseError.errorCode == 0) return true;

		alert('Error Code: ' + xmlDoc.parseError.errorCode + "\n" + 'Error Reason: ' + xmlDoc.parseError.reason + "\n" + 'Error Line: ' + xmlDoc.parseError.line);
		return false;
	}
	else if (document.implementation && document.implementation.createDocument)		// code for Mozilla, Firefox, Opera, etc.
	{
		var parser=new DOMParser();
		var xmlDoc=parser.parseFromString(xml, 'text/xml');

		if (xmlDoc.documentElement.nodeName != 'parsererror') return true;

		alert(xmlDoc.documentElement.childNodes[0].nodeValue);
		return false;
	}
	else return confirm('Your browser cannot verify if the xml is well formed.' + "\n" + 'You can loose your updates.' + "\n" + 'First copy your xml in your notepad.' + "\n" + 'Continue ?');
}

jQuery(document).ready(function(){ 
	jQuery('#submit_xml').click( function() {
		var xml = cm_editor.getCode();
		return validateXML(xml);
	 });
});


