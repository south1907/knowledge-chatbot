<?php

namespace App\Helpers;
use App\Models\Intent;
use App\Models\Session;

use App\Helpers\Intent\IfElseIntentHelper;
use App\Helpers\Intent\SingIntentHelper;
use App\Helpers\Intent\CommandIntentHelper;
use App\Helpers\Intent\MusicIntentHelper;
use App\Helpers\Intent\RecommendMusicHelper;

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

				$intents = Intent::with('answers')->where('page_id', $page_id)->get();

				$matches = null;

				foreach ($intents as $intent) {
					$sentences = $intent->sentences;
					if (!is_null($sentences)) {
						$list_sentence = explode(';', $sentences);
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

                        IntentHelper::updateSession($session, $PID, $current_intent->name, NULL, NULL);
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
