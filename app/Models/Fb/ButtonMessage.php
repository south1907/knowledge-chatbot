<?php

namespace App\Models\Fb;

class ButtonMessage
{
	public $template_type;

	public $text;

	public $buttons;

	public function __construct($template_type, $text, $buttons) {
		$this->template_type = $template_type;
		$this->text = $text;
		$this->buttons = $buttons;
	}

	// function isJson($string) {
	// 	json_decode($string);
	// 	return (json_last_error() == JSON_ERROR_NONE);
	// }
}
