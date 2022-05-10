<?php

namespace App\Helpers;
use App\Helpers\Entity\EntityDetection;
use App\Models\Fb\ElementTemplate;

class AkiHelper extends KnowledgeHelper
{
	public static function answer($query, $page_id, $PID) {

		$random_string = [
			'Tôi yêu Aki',
		];

		$result = [];

        if (array_key_exists('type', $query)) {
            // xu ly text
            if ($query['type'] == 'text') {
                $message = mb_strtolower($query['content']);

            }

            if ($query['type'] == 'postback') {
                $payload = $query['content'];

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
