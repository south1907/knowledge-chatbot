<?php

namespace App\Helpers;
use GuzzleHttp\Client;
use App\Models\Intent;

class NyHelper extends KnowledgeHelper
{
	public static function answer($query) {
		$query = strtolower($query);

		$result = 'I love you';

		$intents = Intent::with('answers')->get();
		
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
			$rand = array_rand($answers);

			$result = $answers[$rand]['message'];
		}

		return $result;
	}
}
?>