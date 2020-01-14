<?php

namespace App\Helpers;
use GuzzleHttp\Client;

class CurlHelper
{
	public static function send($url, $data) {
		/*initialize curl*/
		$ch = curl_init($url);
		
		/* curl setting to send a json post data */
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		$result = curl_exec($ch); // user will get the message
	}
}
?>