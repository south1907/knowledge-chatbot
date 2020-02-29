<?php

namespace App\Helpers;
use GuzzleHttp\Client;
use App\Models\Intent;
use App\Models\Answer;
use App\Models\Session;
use App\Models\Word;
use App\Models\Learn;
use App\Models\Fb\FbAnswer;
use App\Models\Fb\TextMessage;
use App\Models\Fb\ButtonMessage;
use App\Models\Fb\ButtonTemplate;

class NyHelper extends KnowledgeHelper
{
	public static function answer($query, $page_id, $PID) {

		$result = [];

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

						$result[] = $answers[$rand];

						static::updateSession($session, $PID, $current_intent->name, NULL, NULL);
					}
				} else {
					if ($session && $session->intent_name == 'learn_word' && $session->addition == 'CUSTOM') {

						// TODO: check limit word by PID --> spam
						$word = $query['content'];
						$word_split = explode(";", $word);
						if (strpos($word, ';') !== false && count($word_split) > 2) {

							if (static::isJapanese($word_split[0])) {

								// TODO: check word if exists
								$newWord = new Word;
								$newWord->word = trim($word_split[0]);
								$newWord->name_word = trim($word_split[1]);
								$newWord->means = trim($word_split[2]);
								$newWord->language = 'JA';
								$newWord->page_id = $page_id;
								$newWord->created_by_PID = $PID;

								$newWord->save();

								$addition = 'SUCCESS';
								$result[] = static::getAnswerDb($session->intent_name, $addition, $page_id);
							} else {
								$addition = 'NOT_JAPANESE';
								$result[] = static::getAnswerDb($session->intent_name, $addition, $page_id);
							}
						} else {
							$addition = 'ERROR_FORMAT';
							$result[] = static::getAnswerDb($session->intent_name, $addition, $page_id);
						}
					}

					// TODO Process with SYSTEM, need find lesson --> create slot
					if ($session && $session->intent_name == 'learn_word' && ($session->addition == 'SYSTEM' || $session->addition ==  'WAIT_LESSON')) {

						$sentence = $query['content'];
						$find = 
						$re = '/(bài|bài số) (\d)/m';

						preg_match_all($re, $sentence, $matches_lesson, PREG_SET_ORDER, 0);

						$number_lesson = 0;
						if (count($matches_lesson)) {
							$number_lesson = $matches_lesson[0][2];
						}

						if($number_lesson) {
							// init word to learn
							$words = Word::where([
								'lesson'	=>	$number_lesson,
								'language'	=>	'JA'
							])->get();

							// delete all status NEW of PID
							$learn_news = Learn::where([
								'status'	=>	'NEW',
								'lesson'	=>	$number_lesson,
								'PID'		=>	$PID
							])->delete();

							foreach ($words as $w) {
								$learn = new Learn;
								$learn->PID = $PID;
								$learn->word_id = $w->id;
								$learn->page_id = $page_id;
								$learn->lesson = $number_lesson;
								$learn->status = 'NEW';
								$learn->save();
							}

							$addition = 'TYPE_IMPORT_WORD';
							$slot = $session->slot . ':' . $number_lesson;
							$result[] = static::getAnswerDb($session->intent_name, $addition, $page_id);
							static::updateSession($session, $PID, $session->intent_name, $addition, $slot);

						} else {
							$addition = 'WAIT_LESSON';
							$result[] = static::getAnswerDb($session->intent_name, $addition, $page_id);
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

					if ($intent_string == 'learn_word|ALL_WORD') {

						if (array_key_exists('lesson', $data_slot)) {
							// update all word of PID, lesson, status = NEW
							Learn::where([
								'status'	=>	'NEW',
								'lesson'	=>	$data_slot['lesson'],
								'PID'		=>	$PID
							])->update(['status' => 'LEARNING']);

							$intent_string = 'learn_word|END';
						}
					}

					$intent_split = explode("|", $intent_string);

					$intent_name = $intent_split[0];
					$intent_addition = NULL;

					if (count($intent_split) == 2) {
						$intent_addition = $intent_split[1];
					}

					$answerDb = static::getAnswerDb($intent_name, $intent_addition, $page_id);
					if ($answerDb) {
						$result[] = $answerDb;
					}
					// process confirm
					if (strpos($intent_string, 'learn_word|confirm_word') !== false) {
						$split_confirm = explode(":", $intent_string);
						$yes_or_no = $split_confirm[1];
						$id_learn = $split_confirm[2];

						$learn_confirm = Learn::find($id_learn);

						if ($yes_or_no == 'yes') {
							$learn_confirm->status = 'LEARNING';
						} else {
							$learn_confirm->status = 'CANCEL';
						}

						$learn_confirm->save();

						$intent_string = 'learn_word|CHOICE_WORD';
					}

					if ($intent_string == 'learn_word|CHOICE_WORD') {
						$learn_word_confirm = Learn::where([
							'status'	=>	'NEW',
							'lesson'	=>	$data_slot['lesson'],
							'PID'		=>	$PID
						])->with('word')->first();

						if ($learn_word_confirm) {
							$word_confirm = $learn_word_confirm->word;

							$message_word = $word_confirm->word;
							$message_word .= ' - ' . $word_confirm->name_word;
							$message_word .= ' - ' . $word_confirm->means;
							$message_word .= "\nPhát âm: " . $word_confirm->pronounce;
							$message_word .= "\nMẹo nhớ: " . $word_confirm->tip_memory;
							$message_word .= "\nTừ: " . $word_confirm->addition;
							$result[] = [
								'id'	=>	null,
								'type'	=>	'text',
								'message'	=>	$message_word
							];

							// confirm payload 
							$result[] = [
								'id'	=>	null,
								'type'	=>	'button',
								'message'	=>	'Học từ này chứ?',
								'buttons' => json_encode([
									[
										"type"		=> "postback",
										"title"		=> "Có",
										"payload"	=> "INTENT::learn_word|confirm_word:yes:" . $learn_word_confirm->id
									],
									[
										"type"		=> "postback",
										"title"		=> "Không",
										"payload"	=> "INTENT::learn_word|confirm_word:no:" . $learn_word_confirm->id
									]
								])
							];
						} else {
							$intent_string = 'learn_word|END';
							$answerDb = static::getAnswerDb('learn_word', 'END', $page_id);
							if ($answerDb) {
								$result[] = $answerDb;
							}
						}
					}

					if ($intent_string == 'learn_word|END') {
						$session->expired_at = date('Y-m-d H:i:s');
						$session->save();
					} else {
						$slot = null;
						if (array_key_exists('slot', $result[0])) {
							$slot = $result[0]['slot'];
						}
						static::updateSession($session, $PID, $intent_name, $intent_addition, $slot);
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

	public static function updateSession($session, $PID, $intent_name, $addition, $slot = null) {

		// TODO: add expired_at adn where condition
		if ($session) {
			$session->started_at = date('Y-m-d H:i:s');
		} else {
			$session = new Session;
			$session->PID = $PID;
		}


		$session->intent_name = $intent_name;
		$session->addition = $addition;
		if ($slot) {
			$session->slot = $slot;
		}
		$session->save();
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