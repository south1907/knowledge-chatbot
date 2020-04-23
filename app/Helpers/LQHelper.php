<?php

namespace App\Helpers;
use App\Models\Intent;

class LQHelper extends KnowledgeHelper
{
	public static function answer($query, $page_id, $PID) {
		
		$result = [];

		$current_intent = null;

		if (count($result) == 0) {
			$result = [
				[
					'id'	=>	null,
					'type'	=>	'text',
					'message'	=>	'Thà sống chứ không chịu hy sinh'
				]
			];
		}

		return $result;
	}

}
?>