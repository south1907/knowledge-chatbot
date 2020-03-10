<?php
namespace App\Helpers;

use App\Models\Answer;
use App\Models\Session;

class IntentHelper
{
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
}