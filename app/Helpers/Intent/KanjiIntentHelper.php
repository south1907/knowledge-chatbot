<?php
namespace App\Helpers\Intent;

use App\Models\Word;
use App\Models\Learn;


class KanjiIntentHelper extends IntentHelper
{
	public static function intentReviewWord($session, $PID, $page_id, $sentence) {
		$result = [];
		$flag_right = false;
		$split_addition = explode(':', $session->addition);

		if (count($split_addition) > 1) {
			$type_learn = $split_addition[0];
			$id_learn = $split_addition[1];

			$learn = Learn::find($id_learn);
			
			// addition, can update if next word
			$addition = $session->addition;
			$ask = '';

			// if find learn
			if ($learn) {

				$word = $learn->word;
				if (strpos($session->addition, 'MEANS:') !== false) {
					$ask = 'nghĩa';
					$means = array_map('trim', explode(',', $word->means));

					foreach ($means as $mean) {
						if (mb_strtolower($mean) == $sentence) {
							//flag right
							$flag_right = true;
							break;
						}
					}
				}

				if (strpos($session->addition, 'NAMEWORD:') !== false) {
					$ask = 'âm hán';
					$name_word = mb_strtolower($word->name_word);

					if ($name_word == $sentence) {
						//flag right
						$flag_right = true;
					}
				}

				if (strpos($session->addition, 'PRONOUNCE:') !== false) {
					$ask = 'phát âm';
					$pronounces = array_map('trim', explode(',', $word->pronounce));

					foreach ($pronounces as $pronounce) {
						if (mb_strtolower($pronounce) == $sentence) {
							//flag right
							$flag_right = true;
							break;
						}
					}
				}

				if ($flag_right) {
					// answer mean right
					$result[] = [
						'id'	=>	null,
						'type'	=>	'text',
						'message'	=>	'Bạn đã trả lời đúng!'
					];
					$result = array_merge($result, static::createInfoWord($word));

					// update status learn
					$learn->status = 'REVIEWED';
					$learn->save();

					// continue review
					$review_word = Learn::where([
						'status'	=>	'LEARNING',
						'PID'		=>	$PID
					])->with('word')->first();
					if ($review_word) {
						$word_review = $review_word->word;

						$message_word = "Từ " . $word_review->word . " " . $ask . " là gì?";

						$result[] = [
							'id'	=>	null,
							'type'	=>	'text',
							'message'	=>	$message_word
						];

						// set addition to update session addition
						$addition = $type_learn . ':' . $review_word->id;

					} else {
						$intent_string = 'review_word|DONE';
						$answerDb = static::getAnswerDb('review_word', 'DONE', $page_id);
						if ($answerDb) {
							$result[] = $answerDb;
						}

						$addition = 'DONE';
					}
				} else {
					// wrong
					$result[] = [
						'id'	=>	null,
						'type'	=>	'text',
						'message'	=>	'Sai rồi, vui lòng trả lời lại!'
					];

					//button view answers
					$result[] = [
						'id'	=>	null,
						'type'	=>	'button',
						'message'	=>	'Xem đáp án?',
						'buttons' => json_encode([
							[
								"type"		=> "postback",
								"title"		=> "Xem",
								"payload"	=> "INTENT::review_word|". $type_learn .":" . $id_learn
							]
						])
					];
				}
			}

			static::updateSession($session, $PID, $session->intent_name, $addition, NULL);
		}
		
		return $result;

	}

	public static function intentLearnWord($session, $PID, $page_id, $sentence) {
		$result = [];

		if ($session->addition == 'CUSTOM') {

			// TODO: check limit word by PID --> spam
			$word_split = explode(";", $sentence);
			if (strpos($sentence, ';') !== false && count($word_split) > 2) {

				if (static::isJapanese($word_split[0])) {

					// TODO: check word if exists
					$newWord = Word::where('word', $word_split[0])->first();

					if (!$newWord) {
						$newWord = new Word;
						$newWord->word = trim($word_split[0]);
						$newWord->name_word = trim($word_split[1]);
						$newWord->means = trim($word_split[2]);
						$newWord->language = 'JA';
						$newWord->page_id = $page_id;
						$newWord->created_by_PID = $PID;

						$newWord->save();
					}

					// create learning
					$learn = new Learn;
					$learn->PID = $PID;
					$learn->word_id = $newWord->id;
					$learn->page_id = $page_id;
					$learn->lesson = $newWord->lesson;
					$learn->status = 'LEARNING';
					$learn->save();

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
		if ($session->addition == 'SYSTEM' || $session->addition ==  'WAIT_LESSON') {

			$number_lesson = 0;

			if (is_numeric($sentence)) {
				$number_lesson = $sentence;
			}

			$re = '/(bài|bài số) (\d+)/im';

			preg_match_all($re, $sentence, $matches_lesson, PREG_SET_ORDER, 0);

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

		return $result;
	}

	public static function reviewWordPostback($session, $PID, $page_id, $data_slot, $intent_string) {
		$result = [];

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

		$id_learn_view = NULL;
		$type_learn_view = NULL;
		if ($intent_addition) {
			$addition_split = explode(':', $intent_addition);
			if (count($addition_split) > 1) {
				$id_learn_view = $addition_split[1];
				$type_learn_view = $addition_split[0];
			}
		}
		//view answer
		if ($id_learn_view) {
			$learn = Learn::find($id_learn_view);

			if ($learn) {
				$word_view = $learn->word;

				$result = array_merge($result, static::createInfoWord($word_view));

				// update status learn
				$learn->status = 'FAILED';
				$learn->save();

				// continue review
				$review_word = Learn::where([
					'status'	=>	'LEARNING',
					'PID'		=>	$PID
				])->with('word')->first();

				if ($review_word) {
					$word_review = $review_word->word;

					$message_word = "Từ " . $word_review->word . " nghĩa là gì nhỉ?";

					$result[] = [
						'id'	=>	null,
						'type'	=>	'text',
						'message'	=>	$message_word
					];

					// set addition to update session addition
					$intent_addition = $type_learn_view . ':' . $review_word->id;

				} else {
					$intent_string = 'review_word|DONE';
					$answerDb = static::getAnswerDb('review_word', 'DONE', $page_id);
					if ($answerDb) {
						$result[] = $answerDb;
					}

					$session->addition = 'DONE';
					$session->save();

				}

			}
		} else if ($intent_addition == 'RESET') {
			// reset review. Need to change status REVIEWED to LEARNING
			Learn::where([
				'status'	=>	'REVIEWED',
				'PID'		=>	$PID
			])->update(['status' => 'LEARNING']);

			$session->expired_at = date('Y-m-d H:i:s');
			$session->save();

		} else if ($intent_addition == 'COMPLETE') {
			// complete review. Need to change status REVIEWED to DONE
			
			Learn::where([
				'status'	=>	'REVIEWED',
				'PID'		=>	$PID
			])->update(['status' => 'DONE']);
			
			$session->expired_at = date('Y-m-d H:i:s');
			$session->save();

		} else {
			// when click postback to init type to start review
			$ask = '';
			if ($intent_addition == 'MEANS') {
				$ask = 'nghĩa';
			}

			if ($intent_addition == 'NAMEWORD') {
				$ask = 'âm hán';
			}

			if ($intent_addition == 'PRONOUNCE') {
				$ask = 'phát âm';
			}
			$learn_word = Learn::where([
				'status'	=>	'LEARNING',
				'PID'		=>	$PID
			])->with('word')->first();

			if ($learn_word) {
				$word_review = $learn_word->word;

				$message_word = "Từ " . $word_review->word . " " . $ask . " là gì nhỉ?";

				$result[] = [
					'id'	=>	null,
					'type'	=>	'text',
					'message'	=>	$message_word
				];

				// set intent_addtion to update session addition
				$intent_addition = $intent_addition . ':' . $learn_word->id;

			} else {
				$intent_string = 'review_word|DONE';
				$answerDb = static::getAnswerDb('review_word', 'DONE', $page_id);
				if ($answerDb) {
					$result[] = $answerDb;
				}

				$session->addition = 'DONE';
				$session->save();
			}
		}

		if ($intent_string == 'learn_word|END') {
			$session->expired_at = date('Y-m-d H:i:s');
			$session->save();
		} else {
			static::updateSession($session, $PID, $intent_name, $intent_addition, NULL);
		}

		return $result;
	}

	public static function learnWordPostback($session, $PID, $page_id, $data_slot, $intent_string) {

		$result = [];

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

			if ($yes_or_no == 'stop') {
				// update CANCEL in  all word of PID, lesson, status = NEW
				Learn::where([
					'status'	=>	'NEW',
					'lesson'	=>	$data_slot['lesson'],
					'PID'		=>	$PID
				])->update(['status' => 'CANCEL']);

				$intent_string = 'learn_word|END';
				$answerDb = static::getAnswerDb('learn_word', 'END', $page_id);
				if ($answerDb) {
					$result[] = $answerDb;
				}

			} else {
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
		}

		if ($intent_string == 'learn_word|CHOICE_WORD') {
			$learn_word_confirm = Learn::where([
				'status'	=>	'NEW',
				'lesson'	=>	$data_slot['lesson'],
				'PID'		=>	$PID
			])->with('word')->first();

			if ($learn_word_confirm) {
				$word_confirm = $learn_word_confirm->word;

				$result = array_merge($result, static::createInfoWord($word_confirm));
				
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
						],
						[
							"type"		=> "postback",
							"title"		=> "Dừng",
							"payload"	=> "INTENT::learn_word|confirm_word:stop"
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

	public static function createInfoWord($word) {
		$result = [];
		$message_word = $word->word;
		$message_word .= ' - ' . $word->name_word;
		$message_word .= ' - ' . $word->means;
		$message_word .= "\nPhát âm: " . $word->pronounce;
		$message_word .= "\nMẹo nhớ: " . $word->tip_memory;
		$message_word .= "\nTừ: " . $word->addition;
		$result[] = [
			'id'	=>	null,
			'type'	=>	'text',
			'message'	=>	$message_word
		];

		return $result;
	}
}