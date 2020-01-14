<?php

namespace App\Helpers;
use GuzzleHttp\Client;

class XSMBHelper
{
	public static function answer($query) {

		$result = 'Xin lỗi, tôi còn trẻ, tôi chưa thể trả lời những câu hỏi đó được...';

		$CLIENT_WIT = env("CLIENT_WIT", "");
		$client = new Client(['headers' => ['Authorization' => 'Bearer ' . $CLIENT_WIT]]);
		$response = $client->request('GET', 'https://api.wit.ai/message', ['query' => ['q' => $query]]);
		$body = $response->getBody();
		$obj = json_decode($body, true);
		// print_r($obj);
		$entities = $obj['entities'];

		if (count($entities) > 0) {

			$intent = 'UNKNOWN';

			if (array_key_exists('intent', $entities)) {
				$intent = $entities['intent'][0]['value'];
			}

			$datetime = 'UNKNOWN';

			if (array_key_exists('datetime', $entities)) {
				$datetime = $entities['datetime'][0]['value'];
			}

			switch ($intent) {
				case 'UNKNOWN':
					$result = 'bạn hỏi khó quá';
					break;

				case 'check_xsmb':

					if (array_key_exists('number', $entities)) {
						$number = $entities['number'][0]['value'];

						$result = 'bạn đánh con ' . $number . ' thì chúc mừng bạn, bạn tạch cmnr';
					} else {
						$result = 'bạn phải hỏi đánh con bao nhiêu chứ';
					}
					
					break;
				case 'query_xsmb_special':
					$xsmb = rand (10, 99);
					// TODO: request knowledge really. If time < 6h30 and query_time= = today --> no results

					if ($datetime != 'UNKNOWN') {
						$string_datetime = date("d/m/Y", strtotime($datetime));
					} else {
						$string_datetime = 'hôm nay';
					}

					$homnay = date("d/m/Y", time());
					$homqua = date("d/m/Y", time() - 86400 * 1);
					$homkia = date("d/m/Y", time() - 86400 * 2);

					switch ($string_datetime) {
						case $homnay:
							$string_datetime = 'hôm nay';
							break;
						case $homqua:
							$string_datetime = 'hôm qua';
							break;
						case $homkia:
							$string_datetime = 'hôm kia';
							break;
					}

					$result = $string_datetime . ' đề về ' . $xsmb . '. Ra đê chứ???';
					
					break;
				
				case 'recommend': 
					$xsmb = rand (10, 99);
					// TODO: recommend use frequently
					$result = 'Tôi đoán đề hôm nay sẽ không phải là ' . $xsmb . '. Hehe';
					break;
				default:
					$result = 'bạn hỏi khó quá';
					break;
			}
		}
		return $result;
	}

	public static function sendAnswer($input) {

		$ACCESS_TOKEN = env("ACCESS_TOKEN", "");
		
		$url = 'https://graph.facebook.com/v5.0/me/messages?access_token=' . $ACCESS_TOKEN;

		if (isset($input['entry'][0]['messaging'][0]['sender']['id'])) {

			$sender = $input['entry'][0]['messaging'][0]['sender']['id']; //sender facebook id
			$message = '';
			if (array_key_exists('text', $input['entry'][0]['messaging'][0]['message'])) {
				$message = $input['entry'][0]['messaging'][0]['message']['text']; //text that user sent
			}
			$answer = XSMBHelper::answer($message);

			if (!empty($answer)) {
				$jsonData = '{
					"recipient":{
						"id":"' . $sender . '"
						},
						"message":{
							"text":"'. $answer .'"
						}
					}';
				CurlHelper::send($url, $jsonData);
			}
		}

	}
}
?>