<?php

namespace App\Helpers;
use GuzzleHttp\Client;

class CurlHelper
{
	public static function send($url, $data) {

		$client = new Client([
		    'headers' => [ 'Content-Type' => 'application/json' ]
		]);

		$response = $client->post($url,
		    ['body' => $data]
		);

		return $response;
	}
}
?>