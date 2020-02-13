<?php

namespace App\Helpers;
use GuzzleHttp\Client;

abstract class KnowledgeHelper
{
	abstract public static function answer($query);

	public static function sendAnswer($input) {

		if (isset($input['entry'][0]['messaging'][0]['sender']['id'])) {

			$id_page = $input['entry'][0]['id'];

			$ACCESS_TOKEN = env("ACCESS_TOKEN_" . $id_page, "");
		
			$url = 'https://graph.facebook.com/v5.0/me/messages?access_token=' . $ACCESS_TOKEN;

			$sender = $input['entry'][0]['messaging'][0]['sender']['id']; //sender facebook id
			$message = '';
			if (array_key_exists('text', $input['entry'][0]['messaging'][0]['message'])) {
				$message = $input['entry'][0]['messaging'][0]['message']['text']; //text that user sent
			}
			$answer = static::answer($message);

			// print($answer);

			$answers = explode("\n", $answer);
			// print_r($answers);
			foreach ($answers as $answer) {
				if (!empty($answer)) {
					$jsonData = '{
						"recipient":{
							"id":"' . $sender . '"
							},
							"message":{
								"text":"'. $answer .'"
							}
						}';
					CurlHelper::send($url, $jsonData);
				}
			}
		}
	}
}
?>