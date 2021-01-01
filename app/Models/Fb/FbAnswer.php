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

    public function setquickReplies($quickReplies) {
        $this->message['quick_replies'] = $quickReplies;
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
