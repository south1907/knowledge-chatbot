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
                } else {
                    $cards = EntityDetection::findTarotCardByLevel($message);

                    if (count($cards) > 0) {
                        foreach ($cards as $card) {
                            $listReply[] = [
                                "content_type"		=> "text",
                                "title"		=> $card['name'],
                                "payload"	=> "TA::card|" . $card['id']
                            ];
                        }

                        $result[] = [
                            'id'	=>	null,
                            'type'	=>	'quick_reply',
                            'message'	=>	'Các là bài',
                            'quick_replies' => json_encode($listReply)
                        ];
                    }
                }
            }

            if ($query['type'] == 'postback') {
                $payload = $query['content'];
                $id = explode("|", $payload)[1];
                $contentPayload = explode("|", $payload)[0];
                $card = TarotCard::find($id)->toArray();

                if (strpos($payload, 'TA::card') !== false) {
                    $result = self::getAnswerCard($card);
                }

                if (strpos($payload, 'TA::meaning_reverse') !== false) {
                    $result[] = [
                        'id'	=>	null,
                        'type'	=>	'text',
                        'message'	=>	$card['meaning_reverse']
                    ];
                }
                if (strpos($payload, 'TA::meaning') !== false) {
                    $typePayload = explode("::", $contentPayload)[1];

                    if (array_key_exists($typePayload, $card)) {
                        $summary = $card[$typePayload];
                        $result[] = [
                            'id'	=>	null,
                            'type'	=>	'text',
                            'message'	=>	$summary
                        ];
                    }
                    if ($typePayload == 'meaning_summary') {
                        $listMeaning = ['meaning_job', 'meaning_love', 'meaning_money', 'meaning_heath'];
                        $listMeaningTitle = ['Công việc', 'Tình yêu', 'Tài chính', 'Sức khỏe'];
                        $listReply = [];
                        $count = 0;
                        foreach ($listMeaning as $m) {
                            if (array_key_exists($m, $card)) {
                                $listReply[] = [
                                    "content_type"		=> "text",
                                    "title"		=> $listMeaningTitle[$count],
                                    "payload"	=> "TA::". $m ."|" . $card['id']
                                ];
                            }
                            $count += 1;
                        }
                        $result[] = [
                            'id'	=>	null,
                            'type'	=>	'quick_reply',
                            'message'	=>	'Chi tiết theo lĩnh vực',
                            'quick_replies' => json_encode($listReply)
                        ];
                    }
                }
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
            $summary = $card['summary'];
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
                    "title"		=> "Lá bài xuôi",
                    "payload"	=> "TA::meaning_summary|" . $card['id']
                ],
                [
                    "type"		=> "postback",
                    "title"		=> "Lá bài ngược",
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
