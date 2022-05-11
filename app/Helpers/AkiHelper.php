<?php

namespace App\Helpers;
use App\Helpers\Entity\EntityDetection;
use App\Models\Fb\ElementTemplate;

class AkiHelper extends KnowledgeHelper
{
	public static function answer($query, $page_id, $PID) {
		$random_string = [
			'I am Ây Ai Đây.',
			'Typing "start" to start now',
			'Made by Heva',
			'Cyberbot from with love',
			'Have a nice day',
		];

		$result = [];

        if (array_key_exists('type', $query)) {
            // xu ly text
            if ($query['type'] == 'text') {
                $message = mb_strtolower($query['content']);

                if ($message == 'start') {
                    $result[] = [
                        'id'	=>	null,
                        'type'	=>	'text',
                        'message'	=>	'Hello, I am Ây Ai Đây. Think about a real or fictional character. I will try to guess who it is'
                    ];
                    $start = self::start($PID);
                    if ($start) {
                        if (array_key_exists('answers', $start)) {
                            $count = 0;
                            $listReply = [];
                            foreach ($start['answers'] as $ans) {
                                $listReply[] = [
                                    "content_type"		=> "text",
                                    "title"		=> $ans,
                                    "payload"	=> "AKI::answer|" . $count
                                ];
                                $count += 1;
                            }

                            $currentStep = $start['currentStep'] + 1;
                            $result[] = [
                                'id'	=>	null,
                                'type'	=>	'quick_reply',
                                'message'	=>	$currentStep . '. ' . $start['question'],
                                'quick_replies' => json_encode($listReply)
                            ];
                        }
                    } else {
                        $result[] = [
                            'id'	=>	null,
                            'type'	=>	'text',
                            'message'	=>	'Have a trouble, type "start" to restart'
                        ];
                    }

                }

                if ($message == 'help') {
                    $result[] = [
                        'id'	=>	null,
                        'type'	=>	'text',
                        'message'	=>	'Hello, I am Ây Ai Đây.'
                    ];
                    $result[] = [
                        'id'	=>	null,
                        'type'	=>	'text',
                        'message'	=>	'Typing "start" to start now'
                    ];
                }
            }

            if ($query['type'] == 'postback') {
                $payload = $query['content'];

                if (strpos($payload, 'AKI::donate') !== false) {
                    $result[] = [
                        'id'	=>	null,
                        'type'	=>	'text',
                        'message'	=>	'Love youuuuu ☺️☺️☺️'
                    ];
                }

                if (strpos($payload, 'AKI::answer') !== false) {
                    // xu ly cau tra loi
                    $numberAnswer = explode("|", $payload)[1];

                    $resAns = self::answerAki($PID, $numberAnswer);
                    if ($resAns) {
                        if ($resAns['guessCount'] > 0) {
                            // co ket qua

                            $result[] = [
                                'id'	=>	null,
                                'type'	=>	'text',
                                'message'	=>	'Name: ' . $resAns['answers'][0]['name']
                            ];

                            $result[] = [
                                'id'	=>	null,
                                'type'	=>	'image',
                                'url'	=>	$resAns['answers'][0]['absolute_picture_path']
                            ];
                            $result[] = [
                                'id'	=>	null,
                                'type'	=>	'button',
                                'message'	=>	'Let me know',
                                'buttons' => json_encode([
                                    [
                                        "type"		=> "web_url",
                                        "title"		=> "Feedback",
                                        "url"	=> 'https://docs.google.com/forms/d/e/1FAIpQLSf38jby1ae34rZRdbfZmKr4X8KkC-cKbFQkbEzGyXxmgXWT_g/viewform'
                                    ],
                                    [
                                        "type"		=> "postback",
                                        "title"		=> "Donate",
                                        "payload"	=> "AKI::donate"
                                    ]
                                ])
                            ];
                        } else {
                            // khong co ket qua, hoi tiep
                            $count = 0;
                            $listReply = [];
                            foreach ($resAns['answers'] as $ans) {
                                $listReply[] = [
                                    "content_type"		=> "text",
                                    "title"		=> $ans,
                                    "payload"	=> "AKI::answer|" . $count
                                ];
                                $count += 1;
                            }

                            $currentStep = $resAns['currentStep'] + 1;
                            $result[] = [
                                'id'	=>	null,
                                'type'	=>	'quick_reply',
                                'message'	=>	$currentStep . '. ' . $resAns['question'],
                                'quick_replies' => json_encode($listReply)
                            ];
                        }
                    } else {
                        $result[] = [
                            'id'	=>	null,
                            'type'	=>	'text',
                            'message'	=>	'Have a trouble, type "start" to restart'
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

	private static function start($userId) {
        $data = [
            'user_id'   =>  $userId
        ];
        $dataJson = json_encode($data);
        $CLIENT_AKI = env("AKI_API_URL", "") . '/start';
        $response = CurlHelper::send($CLIENT_AKI, $dataJson);
        if ($response) {
            return json_decode($response->getBody()->getContents(), true);
        }
        return null;
    }

    private static function answerAki($userId, $answer) {
        $data = [
            'user_id'   =>  $userId,
            'answer'   =>  $answer,
        ];
        $dataJson = json_encode($data);
        $CLIENT_AKI = env("AKI_API_URL", "") . '/answer';
        $response = CurlHelper::send($CLIENT_AKI, $dataJson);
        if ($response) {
            return json_decode($response->getBody()->getContents(), true);
        }
        return null;
    }
}
?>
