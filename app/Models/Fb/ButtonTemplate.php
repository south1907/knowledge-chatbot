<?php

namespace App\Models\Fb;

class ButtonTemplate
{
	/*
		Postback Button
		{
		  "type": "postback",
		  "title": "<BUTTON_TEXT>",
		  "payload": "<STRING_SENT_TO_WEBHOOK>"
		}

		URL Button
		{
		  "type": "web_url",
		  "url": "<URL_TO_OPEN_IN_WEBVIEW>",
		  "title": "<BUTTON_TEXT>",
		}

		Call Button
		{
		  "type":"phone_number",
		  "title":"<BUTTON_TEXT>",
		  "payload":"<PHONE_NUMBER>"
		}
	*/
	public $type;

	public $url;

	public $title;

	public $payload;

	public function __construct($type, $title, $payload = null) {
		$this->type = $type;
		$this->title = $title;
		$this->payload = $payload;
	}
}
