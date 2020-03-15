<?php

namespace App\Models\Fb;

class AudioMessage
{
	public $url;

	public $is_reusable;

	public function __construct($url, $is_reusable = true) {
		$this->url = $url;
		$this->is_reusable = $is_reusable;
	}
}
