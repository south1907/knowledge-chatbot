<?php
namespace App\Helpers;

use App\Helpers\TTS\GoogleTTS;
use App\Helpers\TTS\PHPMP3;

use App\Models\Music;

class CommandIntentHelper
{

	public static function command($message) {

		$result = [
			[
				'id'	=>	null,
				'type'	=>	'text',
				'message'	=>	'Em không đấy'
			]
		];

		try {
			$pat = "(nói rằng|nói là|nói|bảo rằng|bảo là|bảo) (.*) đi";
			$pat = "/" . $pat . "/";
			if (preg_match($pat, $message, $matches, PREG_OFFSET_CAPTURE)) {
				$content = $matches[2][0];

				$content = trim($content);

				if ($content) {

					$result = [
						[
							'id'	=>	null,
							'type'	=>	'text',
							'message'	=>	$content
						],
						[
							'id'	=>	null,
							'type'	=>	'audio',
							'message'	=>	$content
						]
					];
				}
			} else {
				return null;
			}
		} catch (\Exception $e) {
			
		}

		return $result;
	}
}