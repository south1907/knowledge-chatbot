<?php

namespace App\Models\Fb;

class GenericMessage
{
	public $template_type;

	public $elements;

	public function __construct($template_type, $elements) {
		$this->template_type = $template_type;
		$this->elements = $elements;
	}
}
