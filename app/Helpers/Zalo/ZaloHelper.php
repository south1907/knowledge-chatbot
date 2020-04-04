<?php

namespace App\Helpers\Zalo;

use App\Helpers\CurlHelper;

class ZaloHelper
{
	public static function getUserByPhone($phone) {

		$req_data = [
			'phone'	=> $phone,
			'avatar_size'	=> 240
		];

		$url = 'https://friend-wpa.chat.zalo.me/api/friend/profile/get';

		$response = CurlHelper::getZalo($url, $req_data);
		print_r('heheh');
		print_r($response);
		die;
		return $resData;
	}
}

?>