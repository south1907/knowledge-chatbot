<?php

namespace App\Models\Fb;

class ElementTemplate
{
	public $title;

	public $image_url;

	public $subtitle;

	public $buttons;
	
	public function __construct($title, $image_url, $buttons, $subtitle = null) {
		$this->title = $title;
		$this->image_url = $image_url;
		$this->subtitle = $subtitle;
		$this->buttons = $buttons;
	}
}
