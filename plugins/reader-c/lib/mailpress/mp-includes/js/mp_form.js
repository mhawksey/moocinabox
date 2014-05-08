var mp_form = {

	selectors : {
		submit  : 'div.MailPress input.mp_submit', 
		form    : 'form.mp-form', 
		formdiv : 'div.mp-formdiv', 
		loading : 'div.mp-loading', 
		message : 'div.mp-message'
	}, 

	init : function() {
		jQuery(mp_form.selectors.submit).click( function() { mp_form.ajax(jQuery(this).parents('.MailPress')); return false;} );
	}, 

	ajax : function(div) {
		var data = {};
		jQuery(mp_form.selectors.form+' [type!=submit]',  div).each(function(){
			data[ jQuery(this).attr('name') ] = jQuery(this).val();
		});
		jQuery(mp_form.selectors.formdiv, div).fadeTo(500,0);
	 	jQuery(mp_form.selectors.loading, div).fadeTo(500,1);

		//¤ ajax
		jQuery.ajax({
			data: data,
			beforeSend: null,
			type: "POST", 
			url: MP_Widget.url,
			success: mp_form.callback
			});
	}, 

	callback : function(r) {
	 	var mess  = jQuery('message',r).text();
	 	var email = jQuery('email',r).text();
	 	var name  = jQuery('name',r).text();
	 	var id    = jQuery('id',r).text();
		var div   = jQuery('#' + id);

		jQuery(mp_form.selectors.form+' [name=email]',  div).val(email);
		jQuery(mp_form.selectors.form+' [name=name]',  div).val(name);

	 	jQuery(mp_form.selectors.loading, div).fadeTo(500,0);
		jQuery(mp_form.selectors.message, div).html(mess).fadeTo(1000,1);

	 	setTimeout('mp_form.show("' + id + '")',2000);
	}, 

	show : function(id) {
		var div   = jQuery('#' + id);
	 	jQuery(mp_form.selectors.message, div).fadeTo(1000,0);
		jQuery(mp_form.selectors.formdiv, div).fadeTo(500,1);
	}
}
jQuery(document).ready( function() { mp_form.init(); } );