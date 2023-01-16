<?php

namespace App\Helpers;

class DialyHelper extends KnowledgeHelper
{
	public static function answer($query, $page_id, $PID) {

		$random_string = [
			'Tôi yêu Boardbame',
		];

		$result = [];

        if (array_key_exists('type', $query)) {
            // xu ly text
            if ($query['type'] == 'text') {
                $message = mb_strtolower($query['content']);

            }

            if ($query['type'] == 'postback') {
                // xu ly postback
            }
        }
		if (count($result) == 0) {
			$rand = array_rand($random_string);
			$mes = $random_string[$rand];
			$result = [
				[
					'id'	=>	null,
					'type'	=>	'text',
					'message'	=>	$mes
				]
			];
		}

		return $result;
	}

}
?>
