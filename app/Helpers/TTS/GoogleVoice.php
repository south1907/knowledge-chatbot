<?php

namespace App\Helpers\TTS;
use App\Helpers\CurlHelper;

class GoogleVoice
{
	public static function getUrlAudio($message) {
		$key = env('KEY_GOOGLE_CLOUD', '');

		$data = [
		   	"input" => [
				"text" => $message 
			], 
		   	"voice" => [
				"languageCode" => "vi-VN", 
				"name" => "vi-VN-Wavenet-A" 
			], 
		   	"audioConfig" => [
		       "audioEncoding" => "LINEAR16", 
		       "pitch" => 1, 
		       "speakingRate" => 1 
	    	]
	    ];

		$dataJson = json_encode($data);

		$headers = [
			'x-origin'	=>	'https://explorer.apis.google.com',
			'content-type'	=>	'application/json'
		];


		$url = 'https://texttospeech.googleapis.com/v1/text:synthesize?key='. $key .'&alt=json';

		$response = CurlHelper::post($url, $dataJson, $headers);

		$result = json_decode($response->getBody()->getContents(), true);

		$decode = base64_decode($result['audioContent']);
		$time = time();
		file_put_contents('audio/'.$time.'.mp3', $decode);

		$fullPath = env('APP_URL') . '/audio/'.$time.'.mp3';
		return $fullPath;
	}
}

?>