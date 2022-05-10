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

                if ($message == 'start') {
                    $result[] = [
                        'id'	=>	null,
                        'type'	=>	'text',
                        'message'	=>	'Hello, I am Ây Ai Đây. Think about a real or fictional character. I will try to guess who it is'
                    ];
                    $start = self::start($PID);
                    if (array_key_exists('answers', $start)) {
                        $count = 0;
                        foreach ($start['answers'] as $ans) {
                            $listReply[] = [
                                "content_type"		=> "text",
                                "title"		=> $ans,
                                "payload"	=> "AKI::answer|" . $count
                            ];
                            $count += 1;
                        }

                        $result[] = [
                            'id'	=>	null,
                            'type'	=>	'quick_reply',
                            'message'	=>	'Choice: ',
                            'quick_replies' => json_encode($listReply)
                        ];
                    }

                }
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

	private static function start($userId) {
        $data = [
            'user_id'   =>  $userId
        ];
        $dataJson = json_encode($data);
        $CLIENT_AKI = env("AKI_API_URL", "") . '/start';
        $response = CurlHelper::send($CLIENT_AKI, $dataJson);

        return json_decode($response->getBody()->getContents(), true);
    }
}
?>
