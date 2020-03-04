<?php

namespace App\Helpers;
use GuzzleHttp\Client;
use App\Models\Log;
use App\Models\User;
use App\Models\Fb\FbAnswer;
use App\Models\Fb\TextMessage;
use App\Models\Fb\ButtonMessage;
use App\Models\Fb\ButtonTemplate;

abstract class KnowledgeHelper
{
	//TODO add attribute user

	abstract public static function answer($query, $page_id, $PID);

	public static function sendAnswer($input) {
		date_default_timezone_set('Asia/Ho_Chi_Minh');

		$messaging = $input['entry'][0]['messaging'][0];
		if (isset($messaging['sender']['id'])) {

			$id_page = $input['entry'][0]['id'];

			$ACCESS_TOKEN = env("ACCESS_TOKEN_" . $id_page, "");
		
			$url = 'https://graph.facebook.com/v5.0/me/messages?access_token=' . $ACCESS_TOKEN;

			$sender = $messaging['sender']['id']; //sender facebook id
			$message = [];

			// normal message
			if (array_key_exists('message', $messaging)) {

				if (array_key_exists('text', $messaging['message'])) {
					$message = [
						'type'	=>	'text',
						'content'	=>	$messaging['message']['text'] //text that user sent
					];
				} else if ((array_key_exists('sticker_id', $messaging['message']))) {
					// sticker (like)
					$message = [
						'type'	=>	'icon',
						'content'	=>	$messaging['message']['attachments']['payload']['url'] //text that user sent
					];
				}
			} else if (array_key_exists('postback', $messaging)) {
				// postback message
				$message = [
					'type'	=>	'postback',
					'content'	=>	$messaging['postback']['payload'] //text that user sent
				];
			}

			$answers = static::answer($message, $id_page, $sender);


			$answer_id = null;
			if (count($answers) && array_key_exists('id', $answers[0])) {
				$answer_id = $answers[0]['id'];
			}
			// save log
			$log = new Log;
			$log->PID = $sender;
			$log->message = json_encode($answers);
			$log->page_id = $id_page;
			$log->answer_id = $answer_id;
			$log->save();

			// save user
			$check_exist = User::where(['PID' => $sender, 'page_id' => $id_page])->get();

			if(count($check_exist) == 0) {
				$user = new User;
				$user->PID = $sender;
				$user->page_id = $id_page;
				$user->save();
			}

			$objData = new FbAnswer($sender);

			foreach ($answers as $answer) {
				$jsonData = "";

				if ($answer['type'] == 'text') {
					$text_answers = explode("\n", $answer['message']);

					foreach ($text_answers as $ans) {
						if (!empty($ans)) {
							$objData->setTextMessage($ans);
							$jsonData = json_encode($objData);

							CurlHelper::send($url, $jsonData);
						}
					}
				}

				if ($answer['type'] == 'button') {
					$btn = new ButtonMessage('button', $answer['message'], json_decode($answer['buttons']));
					$objData->setButtonMessage($btn);
					$jsonData = json_encode($objData);
					CurlHelper::send($url, $jsonData);
				}
			}
		}
	}
}
?>