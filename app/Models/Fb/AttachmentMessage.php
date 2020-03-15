<?php

namespace App\Models\Fb;

class AttachmentMessage
{
	public $attachment_id;

	public function __construct($attachment_id) {
		$this->attachment_id = $attachment_id;
	}
}
