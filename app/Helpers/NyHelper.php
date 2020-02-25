<?php

namespace App\Helpers;
use GuzzleHttp\Client;
use App\Models\Intent;
use App\Models\Fb\FbAnswer;
use App\Models\Fb\TextMessage;
use App\Models\Fb\ButtonMessage;
use App\Models\Fb\ButtonTemplate;

class NyHelper extends KnowledgeHelper
{
	public static function answer($query, $page_id) {
		$query = strtolower($query);

		$result = [
			'id'		=> 	null,
			'type'		=>	'text',
			'message'	=> 'I love you'
		];

		$intents = Intent::with('answers')->where('page_id', $page_id)->get();
		
		$current_intent = null;

		$matches = null;

		foreach ($intents as $intent) {
			$sentences = $intent->sentences;
			// print($sentences);
			if (!is_null($sentences)) {
				$list_sentence = explode(';', $sentences);
				// print_r($list_sentence);
				foreach ($list_sentence as $sen) {
					if ($sen == $query) {
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
						if (preg_match($pat, $query, $matches, PREG_OFFSET_CAPTURE)) {
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
			  	return $item['page_id'] == $page_id;
			});

			if (count($answers) > 0) {
				$rand = array_rand($answers);

				$result = $answers[$rand];
			}
		}

		return $result;
	}
}
?>