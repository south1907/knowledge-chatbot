<?php

namespace App\Models\Fb;

class TextMessage
{
	public $text;

	public function __construct($text) {
		$this->text = $text;
	}
}
