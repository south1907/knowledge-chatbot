<?php

namespace App\Helpers;
use GuzzleHttp\Client;
use App\Helpers\Zalo\ZaloEncode;
use App\Helpers\Zalo\ZaloDecode;

class CurlHelper
{
	public static function send($url, $data) {

		$client = new Client([
		    'headers' => [ 'Content-Type' => 'application/json' ]
		]);
        try {
            $response = $client->post($url,
                ['body' => $data]
            );

            return $response;
        } catch (\Exception $e){}
	}

	public static function post($url, $data, $headers = null) {

		$client = new Client([
		    'headers' => $headers
		]);

        return $client->post($url,
            ['body' => $data]
        );
	}

	public static function get($url, $data, $headers = null) {

		$client = new Client([
		    'headers' => $headers
		]);

		$response = $client->get($url,
		    ['query' => $data]
		);

		return $response;
	}

	public static function getZalo($url, $data) {
		$json_data = json_encode($data);
		$encode_req_zalo = ZaloEncode::doAES($json_data);

		$last_req_data = [
			'zpw_ver'	=>	47,
			'zpw_type'	=>	30,
			'params'	=>	$encode_req_zalo
		];

		$headers = [
			'Accept-Encoding'	=>	'gzip, deflate',
			'Authority'	=>	'friend-wpa.chat.zalo.me',
			'User-Agent'	=>	'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.132 Safari/537.36',
			'Cookie'	=>	'zpw_sek=' . env('ZPW_SEK_ZALO', '')
		];

		$response = self::get($url, $last_req_data, $headers);

		$response = json_decode($response->getBody()->getContents(), true);

		$result = null;

		if ($response['error_code'] == 0) {
			$str = $response['data'];

			$decode_str = ZaloDecode::doAES($str);

			$result = json_decode($decode_str, true);
		}

		return $result;
	}

	public static function requestWit($message) {

        $CLIENT_WIT = env("CLIENT_WIT", "");
        $client = new Client(['headers' => ['Authorization' => 'Bearer ' . $CLIENT_WIT]]);
        $response = $client->request('GET', 'https://api.wit.ai/message', ['query' => ['q' => $message]]);

        $body = $response->getBody();
        $obj = json_decode($body, true);
        return $obj['entities'];
    }

    public static function sendWebFacebook($pageId, $PSID, $text) {

        $data = [
            'entry' => [
                [
                    'id' => $pageId,
                    'time' => 1582570011269,
                    'messaging' => [
                        [
                            'sender' => [
                                'id' => $PSID,
                            ],
                            'recipient' => [
                                'id' => $pageId,
                            ],
                            'message' => [
                                'mid' => 'mid.' . time(),
                                'text' => $text,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $dataJson = json_encode($data);

        $url = env('APP_URL') . '/api/v1/webhook';
        $headers = [
            'content-type'	=>	'application/json'
        ];

        $response = CurlHelper::post($url, $dataJson, $headers);
        return json_decode($response->getBody()->getContents(), true);
    }

    public static function answerFromGPT($message, $conversation) {
        $GPT_API_URL = env("GPT_API_URL", "");
        $data = [
            "system_message"    =>  $message,
            "conversation"      =>  $conversation
        ];
        $dataJson = json_encode($data);
        $headers = [
            'content-type'	=>	'application/json'
        ];
        try {
            $response = CurlHelper::post($GPT_API_URL, $dataJson, $headers);

            $body = $response->getBody();
            $obj = json_decode($body, true);
            return $obj['response'];
        } catch (\Exception $e){}
        return null;
    }

}
?>
