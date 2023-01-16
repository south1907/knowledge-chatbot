<?php

namespace App\Helpers;

use App\Models\Country;

class DialyHelper extends KnowledgeHelper
{
	public static function answer($query, $page_id, $PID) {

		$random_string = [
			'I love Dialy',
			'I love youuuuuuu, chụt chụt :))',
		];

        $random_string_correct = [
            'Niceeeeee :)',
            'Right',
            'Great job',
            'You are smart',
            'Chuẩn, đúng là vừa đẹp trai vừa giỏi dangggg :))',
            'Chuẩn, đúng là vừa xinh gái vừa giỏi dangggg :))',
            '10 điểm :D',
        ];

        $random_string_not_correct = [
            'Wrongggg',
            'You are not smart, but try your bét :))',
            'Sai lòi ra',
            'Bạn rất xinh gái, nhưng câu trả lời của bạn sai mất gòyyyyy',
            'Bạn rất đẹp trai, nhưng câu trả lời của bạn sai mất gòyyyyy',
            'Hồi xưa ai dạy bạn môn DIALY vậy :))',
        ];

		$result = [];

        if (array_key_exists('type', $query)) {
            // xu ly text
            if ($query['type'] == 'text') {
                $message = mb_strtolower($query['content']);

                if (strpos($message, 'start') !== false) {
                    $result = self::getStart();
                }

                if ($message == 'help') {
                    $result[] = [
                        'id' => null,
                        'type' => 'text',
                        'message' => 'Hello, I am Dialy.'
                    ];
                    $result[] = [
                        'id' => null,
                        'type' => 'text',
                        'message' => 'Typing "start" to start now'
                    ];
                }
            }

            if ($query['type'] == 'postback') {
                // xu ly postback
                $payload = $query['content'];

                if (strpos($payload, 'DIALY::start') !== false) {
                    $result = self::getStart();
                }

                if (strpos($payload, 'DIALY::answer') !== false) {
                    $checkAnswer = explode("|", $payload)[1];
                    $idCountryCorrect = explode("|", $payload)[2];
                    $country = Country::find($idCountryCorrect);
                    if ($checkAnswer == 'TRUE') {
                        $rand = array_rand($random_string_correct);
                        $mes = $random_string_correct[$rand];
                        $result = [
                            [
                                'id'	=>	null,
                                'type'	=>	'text',
                                'message'	=>	$mes
                            ]
                        ];
                    } else {
                        $rand = array_rand($random_string_not_correct);
                        $mes = $random_string_not_correct[$rand];
                        $result = [
                            [
                                'id'	=>	null,
                                'type'	=>	'text',
                                'message'	=>	$mes
                            ]
                        ];
                        $result[] = [
                            [
                                'id'	=>	null,
                                'type'	=>	'text',
                                'message'	=>	'Đáp án đúng là ' . $country['name']
                            ]
                        ];
                    }

                    $result[] = [
                        'id'	=>	null,
                        'type'	=>	'button',
                        'message'	=>	'More information',
                        'buttons' => json_encode([
                            [
                                "type"		=> "web_url",
                                "title"		=> "View more",
                                "url"	=> $country['link_country']
                            ],
                            [
                                "type"		=> "postback",
                                "title"		=> "New game",
                                "payload"	=> "DIALY::start"
                            ]
                        ])
                    ];
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

    private static function getStart(): array
    {
        $result = [];
        $result[] = [
            'id'	=>	null,
            'type'	=>	'text',
            'message'	=>	'Hello, I am Dialy. Try guess flag of countries'
        ];
        $numberResult = 4;
        $findRandom = Country::get()->random($numberResult);

        if ($findRandom) {
            $rand = rand(0, $numberResult - 1);
            $item = $findRandom[$rand];

            $result[] = [
                'id'	=>	null,
                'type'	=>	'image',
                'url'	=>	$item['link_flag']
            ];
            $listReply = [];
            foreach ($findRandom as $country) {
                $flagCheck = "FALSE";
                if ($country['id'] == $item['id']) {
                    $flagCheck = "TRUE";
                }
                $listReply[] = [
                    "content_type"		=> "text",
                    "title"		=> $country["name"],
                    "payload"	=> "DIALY::answer|" . $flagCheck . "|" . $item['id']
                ];
            }
            $result[] = [
                'id'	=>	null,
                'type'	=>	'quick_reply',
                'message'	=>	'What is the flag of country?',
                'quick_replies' => json_encode($listReply)
            ];

        } else {
            $result[] = [
                'id'	=>	null,
                'type'	=>	'text',
                'message'	=>	'Have a trouble, type "start" to restart'
            ];
        }
        return $result;
    }

}
?>
