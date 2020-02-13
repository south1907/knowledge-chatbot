<?php

namespace App\Helpers;
use GuzzleHttp\Client;

class XSMBHelper extends KnowledgeHelper
{
	public static function answer($query) {

		date_default_timezone_set('Asia/Ho_Chi_Minh');

		$result = 'Xin lỗi, tôi còn trẻ, tôi chưa thể trả lời những câu hỏi đó được...';

		$CLIENT_WIT = env("CLIENT_WIT", "");
		$client = new Client(['headers' => ['Authorization' => 'Bearer ' . $CLIENT_WIT]]);
		$response = $client->request('GET', 'https://api.wit.ai/message', ['query' => ['q' => $query]]);
		$body = $response->getBody();
		$obj = json_decode($body, true);

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

			if ($datetime != 'UNKNOWN') {
				$string_datetime = date("d/m/Y", strtotime($datetime));
			} else {
				$string_datetime = date("d/m/Y");
			}
			$query_time = str_replace('/', '-', $string_datetime);

			switch ($intent) {
				case 'UNKNOWN':
					$result = 'bạn hỏi khó quá';
					break;

				case 'check_xsmb':

					if (array_key_exists('number', $entities)) {
						$number = $entities['number'][0]['value'];

						$special = XSMBHelper::queryNumberSpecial($query_time);
						$xsmb = substr($special, 3);

						if ($xsmb != null) {
							if ($xsmb == $number) {
								$result = 'bạn đánh con ' . $number . ' thì chúc mừng bạn, bạn trúng cmnr';
							} else {
								$result = 'bạn đánh con ' . $number . ', tạch cmn rồi, đề về ' . $xsmb . ' cơ. Dù sao vẫn chúc mừng bạn, bạn nên bỏ sự nghiệp lô đề đi';
							}
						} else {
							$result = 'xin lỗi, tôi chưa có thông tin kết quả bạn nhé!';
						}
						

					} else {
						$result = 'bạn phải hỏi đánh con bao nhiêu chứ';
					}
					
					break;
				case 'query_xsmb':

				case 'query_xsmb_special':
					// TODO: request knowledge really. If time < 6h30 and query_time= = today --> no results --> DONE

					$homnay = date("d/m/Y", time());
					$homqua = date("d/m/Y", time() - 86400 * 1);
					$homkia = date("d/m/Y", time() - 86400 * 2);

					switch ($string_datetime) {
						case $homnay:
							$string_datetime = 'hôm nay ('. $string_datetime .')';
							break;
						case $homqua:
							$string_datetime = 'hôm qua ('. $string_datetime .')';
							break;
						case $homkia:
							$string_datetime = 'hôm kia ('. $string_datetime .')';
							break;
					}

					$current_hour = date('H:i');

					if (strpos($string_datetime, 'hôm nay') !== false && $current_hour < '18:35') {
						$result = 'giờ là ' . $current_hour . ' đề chưa quay, phải sau 6 rưỡi tối bạn ạ!';
					} else {
						$special = XSMBHelper::queryNumberSpecial($query_time);
						if ($special == null) {
							$result = 'Thời gian bạn hỏi không khả dụng';
						} else {
							$xsmb = substr($special, 3);
							$result = 'Giải đặc biệt là: ' . $special . '. Còn đề thì là ' . $xsmb . ' nhé';	
						}
					}
					
					break;
				
				case 'recommend': 
					$xsmb = rand (10, 99);
					// TODO: recommend use frequently
					$result = 'Tôi đoán đề hôm nay sẽ không phải là ' . $xsmb . '. Hehe';
					break;

				case 'help': 
					$result = "Tôi có thể đáp ứng các chức năng\n";
					$result .= "+ Tra cứu xsmb: hôm nay đề về bao nhiêu\n";
					$result .= "+ Kiểm tra xsmb: tôi đánh con 39 có trúng không\n";
					$result .= "+ Dự đoán xsmb: dự đoán kết quả xsmb hôm nay\n";
					break;
				default:
					$result = 'bạn hỏi khó quá';
					break;
			}
		}
		return $result;
	}

	private static function queryNumberSpecial($query_time) {
		$client = new Client();
		$response = $client->request('GET', 'https://xoso.com.vn/xsmb-'. $query_time .'.html');
		$body = $response->getBody();

		$postion = strpos($body, 'colorred xshover');
		if ($postion != null) {
			$start = $postion + 18;
			$result = substr($body, $start, 5);

			if (is_numeric($result)) {
				return $result;	
			} else {
				return null;
			}

		} else {
			return null;
		}		
	}
}
?>