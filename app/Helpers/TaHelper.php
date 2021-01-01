<?php

namespace App\Helpers;
use App\Helpers\Entity\EntityDetection;
use App\Models\Fb\ElementTemplate;
use App\Models\TarotCard;

class TaHelper extends KnowledgeHelper
{
	public static function answer($query, $page_id, $PID) {

		$random_string = [
			'Tôi yêu Tarot',
		];

		$result = [];

        if (array_key_exists('type', $query)) {
            // xu ly text
            if ($query['type'] == 'text') {
                $message = mb_strtolower($query['content']);
				$card = EntityDetection::findTarotCard($message);
                if ($card) {
                    $result = self::getAnswerCard($card);
                }
            }

            if ($query['type'] == 'postback') {
                $payload = $query['content'];
                $id = explode("|", $payload)[1];

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

	private static function getAnswerCard($card) {
	    $result = [];
        $text = "Lá bài: " . $card['name'];

        $result[] = [
            'id'	=>	null,
            'type'	=>	'text',
            'message'	=>	$text
        ];

        if (array_key_exists('image', $card)) {
            $image = $card['image'];
            $result[] = [
                'id'	=>	null,
                'type'	=>	'image',
                'url'	=>	$image
            ];
        }

        if (array_key_exists('summary', $card)) {
            $summary = $card['image'];
            $result[] = [
                'id'	=>	null,
                'type'	=>	'text',
                'message'	=>	$summary
            ];
        }

        $result[] = [
            'id'	=>	null,
            'type'	=>	'button',
            'message'	=>	'Ý nghĩa lá bài',
            'buttons' => json_encode([
                [
                    "type"		=> "postback",
                    "title"		=> "Lá Bài Xuôi",
                    "payload"	=> "TA::meaning|" . $card['id']
                ],
                [
                    "type"		=> "postback",
                    "title"		=> "Lá Bài Ngược",
                    "payload"	=> "TA::meaning_reverse|" . $card['id']
                ],
                [
                    "type"		=> "web_url",
                    "title"		=> "Xem thêm",
                    "url"	=> $card['link_origin']
                ]
            ])
        ];
        return $result;
    }

}
?>
