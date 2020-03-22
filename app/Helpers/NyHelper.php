<?php

namespace App\Helpers;
use App\Models\Intent;
use App\Models\Session;

use App\Helpers\Intent\SingIntentHelper;
use App\Helpers\Intent\CommandIntentHelper;

use App\Helpers\Intent\IntentHelper;
use App\Helpers\Intent\KanjiIntentHelper;

class NyHelper extends KnowledgeHelper
{
	public static function answer($query, $page_id, $PID) {
		
		$result = [];

		$current_intent = null;

		$session = Session::where('PID', $PID)->whereNull('expired_at')->first();

		if (array_key_exists('type', $query)) {
			if ($query['type'] == 'text') {
				$message = mb_strtolower($query['content']);

				$resultSing = SingIntentHelper::sing($message);
				if ($resultSing != null) {
					
					return $resultSing;
				}

				$resultCommand = CommandIntentHelper::command($message);
				if ($resultCommand != null) {
					
					return $resultCommand;
				}

				$intents = Intent::with('answers')->where('page_id', $page_id)->get();
				

				$matches = null;

				foreach ($intents as $intent) {
					$sentences = $intent->sentences;
					// print($sentences);
					if (!is_null($sentences)) {
						$list_sentence = explode(';', $sentences);
						// print_r($list_sentence);
						foreach ($list_sentence as $sen) {
							if ($sen == $message) {
								$current_intent = $intent;
								break;
							}
						}
					}

					$patterns = $intent->patterns;

					if (!is_null($patterns) && is_null($current_intent)) {
						$list_pattern = explode(';', $patterns);
						foreach ($list_pattern as $pat) {
							try {
								$pat = "/" . $pat . "/";
								if (preg_match($pat, $message, $matches, PREG_OFFSET_CAPTURE)) {
									$current_intent = $intent;
									break;
								}
							} catch (\Exception $e) {
								// print($e->getMessage());
							}
							
						}
					}
				}

				if (!is_null($current_intent)) {

					$is_special = false;

					if ($session && $session->intent_name == 'music_game') {
						if ($current_intent->name != 'stop_music_game') {

							$result = MusicIntentHelper::process($session, $PID, $page_id, $message);

							$is_special = true;
						}
					}

					if (!$is_special) {
						$answers = $current_intent->answers->toArray();

						$answers = array_filter($answers, function($item) use (&$page_id)  {
						  	return $item['page_id'] == $page_id && $item['state'] == NULL;
						});

						if (count($answers) > 0) {
							$rand = array_rand($answers);

							$result[] = $answers[$rand];

							IntentHelper::updateSession($session, $PID, $current_intent->name, NULL, NULL);
						}
					}
					
				} else {
					if ($session) {
						if ($session->intent_name == 'learn_word') {

							$result = KanjiIntentHelper::intentLearnWord($session, $PID, $page_id, $message);
						}

						if ($session->intent_name == 'review_word') {

							$result = KanjiIntentHelper::intentReviewWord($session, $PID, $page_id, $message);
						}

						if ($session->intent_name == 'music_game') {

							$result = MusicIntentHelper::process($session, $PID, $page_id, $message);
						}
					}

					

				}
			}

			// process postback
			if ($query['type'] == 'postback' && $session) {
				$payload = $query['content'];

				if (strpos($payload, 'INTENT::') !== false) {
					$intent_string = explode("::", $payload)[1];

					$session_slot = explode(';', $session->slot);
					$data_slot = [];
					foreach ($session_slot as $slot) {
						$split_slot = explode(':', $slot);
						if (count($split_slot) > 1) {
							$data_slot[$split_slot[0]] = $split_slot[1];
						}
					}

					$is_special = false;

					if (strpos($intent_string, 'learn_word|') !== false) {

						// process learn word: choice word want to learn
						$result = KanjiIntentHelper::learnWordPostback($session, $PID, $page_id, $data_slot, $intent_string);

						$is_special = true;
					}

					if (strpos($intent_string, 'review_word|') !== false) {

						// process review word: review word added in system (STATUS: LEARNING)
						$result = KanjiIntentHelper::reviewWordPostback($session, $PID, $page_id, $data_slot, $intent_string);

						$is_special = true;
					}

					// other intent
					if (!$is_special) {
						$intent_split = explode("|", $intent_string);

						$intent_name = $intent_split[0];
						$intent_addition = NULL;

						if (count($intent_split) == 2) {
							$intent_addition = $intent_split[1];
						}

						$answerDb = IntentHelper::getAnswerDb($intent_name, $intent_addition, $page_id);
						if ($answerDb) {
							$result[] = $answerDb;
						}

						IntentHelper::updateSession($session, $PID, $session->intent_name, $intent_addition, NULL);
					}
					
				}
			}
		}

		if (count($result) == 0) {
			$result = [
				[
					'id'	=>	null,
					'type'	=>	'text',
					'message'	=>	'I love you'
				]
			];
		}

		return $result;
	}

}
?>