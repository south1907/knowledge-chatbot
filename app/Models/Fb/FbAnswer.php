<?php

namespace App\Models\Fb;

class FbAnswer
{
	public $recipient;

	public $message;

	public function __construct($recipient) {
		$this->recipient = ['id' => $recipient];
	}
	public function setTextMessage($text) {
		$this->message['text'] = $text;
	}

	public function setButtonMessage($button) {
		$this->message = [
			'attachment'	=>	[
				'type'		=>	'template',
				'payload' 	=>	$button
			]
		];
	}

	public function setAudioMessage($audio) {
		$this->message = [
			'attachment'	=>	[
				'type'		=>	'audio',
				'payload' 	=>	$audio
			]
		];
	}
}
