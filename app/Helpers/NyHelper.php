<?php

namespace App\Helpers;
use GuzzleHttp\Client;
use App\Models\Intent;
use App\Models\Answer;
use App\Models\Session;
use App\Models\Word;
use App\Models\Fb\FbAnswer;
use App\Models\Fb\TextMessage;
use App\Models\Fb\ButtonMessage;
use App\Models\Fb\ButtonTemplate;

class NyHelper extends KnowledgeHelper
{
	public static function answer($query, $page_id, $PID) {

		$result = [
			'id'		=> 	null,
			'type'		=>	'text',
			'message'	=> 'I love you'
		];

		$current_intent = null;

		$session = Session::where('PID', $PID)->whereNull('expired_at')->first();

		if (array_key_exists('type', $query)) {
			if ($query['type'] == 'text') {
				$message = mb_strtolower($query['content']);

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
					$answers = $current_intent->answers->toArray();

					$answers = array_filter($answers, function($item) use (&$page_id)  {
					  	return $item['page_id'] == $page_id && $item['state'] == NULL;
					});

					if (count($answers) > 0) {
						$rand = array_rand($answers);

						$result = $answers[$rand];

						static::updateSession($session, $PID, $current_intent->name, NULL, $page_id);
					}
				} else {
					if ($session && $session->intent_name == 'learn_word' && $session->addition == 'CUSTOM') {

						// TODO: check limit word by PID --> spam
						$word = $query['content'];
						$word_split = explode(";", $word);
						if (strpos($word, ';') !== false && count($word_split) > 2) {

							if (static::isJapanese($word_split[0])) {
								$newWord = new Word;
								$newWord->word = trim($word_split[0]);
								$newWord->name_word = trim($word_split[1]);
								$newWord->means = trim($word_split[2]);
								$newWord->language = 'JA';
								$newWord->page_id = $page_id;
								$newWord->created_by_PID = $PID;

								$newWord->save();

								$addition = 'SUCCESS';
								$result = static::getAnswerDb($session->intent_name, $addition, $page_id);
							} else {
								$addition = 'NOT_JAPANESE';
								$result = static::getAnswerDb($session->intent_name, $addition, $page_id);
							}
						} else {
							$addition = 'ERROR_FORMAT';
							$result = static::getAnswerDb($session->intent_name, $addition, $page_id);
						}
					}

					// TODO Process with SYSTEM, need find lesson --> create slot
				}
			}

			// process postback
			if ($query['type'] == 'postback') {
				$payload = $query['content'];

				if (strpos($payload, 'INTENT::') !== false) {
					$intent_string = explode("::", $payload)[1];
					$intent_split = explode("|", $intent_string);

					$intent_name = $intent_split[0];
					$intent_addition = NULL;

					if (count($intent_split) == 2) {
						$intent_addition = $intent_split[1];
					}

					// end session
					if ($intent_string == 'learn_word|END') {
						$session->expired_at = date('Y-m-d H:i:s');
						$session->save();
					} else {
						static::updateSession($session, $PID, $intent_name, $intent_addition);
					}

					$result = static::getAnswerDb($intent_name, $intent_addition, $page_id);
				}
			}
		}

		return $result;
	}

	public static function updateSession($session, $PID, $intent_name, $addition) {

		if ($session) {
			$session->intent_name = $intent_name;
			$session->addition = $addition;
			$session->started_at = date('Y-m-d H:i:s');
			$session->save();
		} else {
			$session = new Session;
			$session->PID = $PID;
			$session->intent_name = $intent_name;
			$session->addition = $addition;

			$session->save();
		}
	}

	public static function getAnswerDb($intent_name, $state, $page_id) {
		$result = null;

		$answers = Answer::where([
			'intent_name' => $intent_name,
			'state' => $state,
			'page_id' => $page_id
		])->get()->toArray();

		if (count($answers) > 0) {
			$rand = array_rand($answers);

			$result = $answers[$rand];
		}

		return $result;
	}

	public static function isKanji($str) {
	    return preg_match('/[\x{4E00}-\x{9FBF}]/u', $str) > 0;
	}

	public static function isHiragana($str) {
	    return preg_match('/[\x{3040}-\x{309F}]/u', $str) > 0;
	}

	public static function isKatakana($str) {
	    return preg_match('/[\x{30A0}-\x{30FF}]/u', $str) > 0;
	}

	public static function isJapanese($str) {
	    return static::isKanji($str) || static::isHiragana($str) || static::isKatakana($str);
	}

}
?>