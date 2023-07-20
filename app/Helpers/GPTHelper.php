<?php

namespace App\Helpers;

use App\Models\Log;
use Carbon\Carbon;

class GPTHelper extends KnowledgeHelper
{
	public static function answer($query, $page_id, $PID) {

		$random_string = [
			'Chào chị ạ, em có thể giúp gì cho chị hôm nay?',
			'Chào chị! Em tên là Jenny Vũ, có gì chị cần tư vấn không ạ?',
			'Chào chị, em là Jenny Vũ, nhân viên chăm sóc khách hàng của thẩm mỹ viện Mega Gangnam. Chị cần tư vấn về vấn đề gì ạ?',
		];

		$result = [];

        if (array_key_exists('type', $query)) {
            // xu ly text

            if ($query['type'] == 'text') {
                $message = mb_strtolower($query['content']);
                $todayStart = Carbon::now()->format('Y-m-d 00:00:00');
                $logs = Log::where('PID', $PID)->where('created_at', '>', $todayStart)->orderBy('created_at', 'ASC')->get();
                $conversation = [];
                foreach ($logs as $log ) {
                    $conversation[] = [
                        'user'  =>  json_decode($log->message, true)['content'],
                        'bot'  =>  json_decode($log->answer, true)[0]['message']
                    ];
                }
                $answerText = CurlHelper::answerFromGPT($message, $conversation);
                if ($answerText) {
                    $result = [
                        [
                            'id'	=>	null,
                            'type'	=>	'text',
                            'message'	=>	$answerText
                        ]
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
}
?>
