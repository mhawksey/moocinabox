<?php

class MP_Form_field_type_recaptcha extends MP_form_field_type_
{
	var $file	= __FILE__;

	var $id 	= 'recaptcha';

	var $category = 'composite';
	var $order	= 92;

	function submitted($field)
	{
		require_once('captcha/recaptchalib.php');

		$resp = recaptcha_check_answer ($field->settings['keys']['privatekey'], $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);

		if (!$resp->is_valid) 
		{
			// set the error code so that we can display it
			// $error = $resp->error;
			$field->submitted['on_error'] = $resp->error;
			return $field;
		}

		$field->submitted['value'] = 1;
		$field->submitted['text']  = __('ok', MP_TXTDOM);

		return $field;
	}

	function attributes_filter($no_reset)
	{
		if (!$no_reset) return;
		
		$this->attributes_filter_css();
	}

	function build_tag()
	{
		require_once('captcha/recaptchalib.php');

		$tag = recaptcha_get_html($this->field->settings['keys']['publickey'], (isset($this->field->submitted['on_error'])) ? $this->field->submitted['on_error'] : null);
		$id  = $this->get_id($this->field);

		$form_format =  '{{img}}';

		$form_formats = $this->get_formats($form_formats);

		$search[] = '{{img}}';		$replace[] = '%1$s';
		$search[] = '{{id}}'; 		$replace[] = '%2$s';

		$html = str_replace($search, $replace,  $form_format);

		return sprintf($html, $tag, $id);
	}
}
new MP_Form_field_type_recaptcha(__('ReCaptcha', MP_TXTDOM));