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

	public function setGenericMessage($generic) {
		$this->message = [
			'attachment'	=>	[
				'type'		=>	'template',
				'payload' 	=>	$generic
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

	public function setImageMessage($image) {
		$this->message = [
			'attachment'	=>	[
				'type'		=>	'image',
				'payload' 	=>	$image
			]
		];
	}

	public function setAttachmentMessage($type, $attachment) {
		$this->message = [
			'attachment'	=>	[
				'type'		=>	$type,
				'payload' 	=>	$attachment
			]
		];
	}
}
