<?php
namespace App\Helpers\Intent;

use App\Models\Music;

class MusicIntentHelper extends IntentHelper
{
	public static function process($session, $PID, $page_id, $sentence) {
		$result = 'anh xin giơ tay rút lui thôi';

		$type = 'NAME_SONG';

		if ($session->addition != null) {
			$type = $session->addition;
		}

		if ($type == 'INFO_SONG') {
			$music = Music::where('name', 'like', '%' . $sentence . '%')->first();

			if ($music) {
				$result = $music->name;
			}

		} else {
			$music = Music::where('content', 'like', '%' . $sentence . '%')->first();

			if ($music) {
				
				if ($type == 'NAME_SONG') {
					$result = $music->name;
				}
				if ($type == 'NEXT_SENTENCE') {
					$content = $music->content;
					$content_split = explode("\n", $content);

					$count = 0;
					$max_sen = count($content_split);

					foreach ($content_split as $sen) {

						$count += 1;

						$check = strpos($sen, $sentence);
						if ($check !== FALSE) {
							if (strlen($sen) > $check + strlen($sentence)) {
								$result = substr($sen, $check + strlen($sentence));
								break;
							} else {
								if ($count < $max_sen) {
									$result = $content_split[$count];
								} else {
									$result = 'Hết';
								}
							}
						}
					}
				}
			}
		}
		
		return [
			[
				'id'	=>	null,
				'type'	=>	'text',
				'message'	=>	$result
			],
			[
				'id'	=>	null,
				'type'	=>	'audio',
				'message'	=>	$result
			]
		];
	}
}