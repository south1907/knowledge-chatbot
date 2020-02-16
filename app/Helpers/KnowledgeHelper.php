<?php

namespace App\Helpers;
use GuzzleHttp\Client;
use App\Models\Log;
use App\Models\User;

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

			// save log
			$log = new Log;
			$log->PID = $sender;
			$log->message = $message;
			$log->page_id = $id_page;
			$log->answer = $answer;
			$log->save();

			// save user
			$check_exist = User::where(['PID' => $sender, 'page_id' => $id_page])->get();

			if(count($check_exist) == 0) {
				$user = new User;
				$user->PID = $sender;
				$user->page_id = $id_page;
				$user->save();
			}

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