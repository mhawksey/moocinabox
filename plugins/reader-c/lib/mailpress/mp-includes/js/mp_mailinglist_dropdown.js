var mp_ml_select = {

	ml : null,

	init : function() {
		mp_ml_select.ml = jQuery('#' + mp_ml_select_L10n.select);

		mp_ml_select.ml.change( function() { return mp_ml_select.submit(); });
		jQuery('form#' + mp_ml_select_L10n.form).submit( function() { return mp_ml_select.submit(); });

	},

	submit : function() {
		return (-1 == mp_ml_select.ml.val()) ? mp_ml_select.ko() : mp_ml_select.ok();
	},

	ok : function() {
		mp_ml_select.ml.css('background-color', '');
                return true;
	},

	ko : function() {
		alert(mp_ml_select_L10n.error);
		mp_ml_select.ml.css('background-color', '#fdd');
		return false;
	}
};
jQuery(document).ready( function() { mp_ml_select.init(); });