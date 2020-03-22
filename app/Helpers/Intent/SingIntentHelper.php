<?php
namespace App\Helpers\Intent;

use App\Helpers\TTS\GoogleTTS;
use App\Helpers\TTS\PHPMP3;

use App\Models\Music;

class SingIntentHelper
{

	public static function sing($message) {

		$result = [
			[
				'id'	=>	null,
				'type'	=>	'text',
				'message'	=>	'Em không biết hát'
			]
		];

		try {
			$pat = "(hát bài|hát) (.*) đi";
			$pat = "/" . $pat . "/";
			if (preg_match($pat, $message, $matches, PREG_OFFSET_CAPTURE)) {
				$song = $matches[2][0];

				$song = trim($song);

				$music = Music::where('name', $song)->first();

				if ($music) {
					$nameMusic = $music->name;

					$content = $music->content;

					$split_content = explode("\n", $content);

					$content_current = "";

					$mp3 = new PHPMP3();
					foreach ($split_content as $sen) {
						$content_current .= $sen;

						if (mb_strlen($content_current) > 150) {
							$linkTTS = GoogleTTS::getLinkTTS($content_current);

							$tmpMp3 = new PHPMP3($linkTTS);
							$mp3->mergeBehind($tmpMp3);
							$content_current = '';
						}
					}

					if ($content_current != '') {
						$linkTTS = GoogleTTS::getLinkTTS($content_current);

						$tmpMp3 = new PHPMP3($linkTTS);
						$mp3->mergeBehind($tmpMp3);
					}
					
					$newPath = 'audio/' . $nameMusic . '.mp3';

					$mp3->save($newPath);
					$fullPath = env('APP_URL') . '/' .$newPath;

					$result = [
						[
							'id'	=>	null,
							'type'	=>	'text',
							'message'	=>	$nameMusic
						],
						[
							'id'	=>	null,
							'type'	=>	'audio',
							'message'	=>	'SONG::' . $nameMusic,
							'url'	=>	$fullPath
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