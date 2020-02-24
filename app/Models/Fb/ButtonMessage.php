<?php

namespace App\Models\Fb;

class ButtonMessage
{
	public $templateType;

	public $text;

	public $buttons;

	public function __construct($templateType, $text, $buttons) {
		$this->templateType = $templateType;
		$this->text = $text;
		$this->buttons = $buttons;
	}

	// function isJson($string) {
	// 	json_decode($string);
	// 	return (json_last_error() == JSON_ERROR_NONE);
	// }
}
