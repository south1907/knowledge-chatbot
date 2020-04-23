<?php
namespace App\Helpers\Intent;

use App\Models\Music;

class MusicIntentHelper extends IntentHelper
{
	public static function process($session, $PID, $page_id, $sentence) {
		$result = [];

		$type = 'NAME_SONG';

		if ($session->addition != null) {
			$type = $session->addition;
		}

		if ($type == 'INFO_SONG') {
			$music = Music::where('name', 'like', '%' . $sentence . '%')->first();

			if ($music) {
				$result[] = [
						'id'	=>	null,
						'type'	=>	'button',
						'message'	=>	$music->name,
						'buttons' => json_encode([
							[
								"type"		=> "web_url",
								"url"		=> $music->youtube,
								"title"		=> "Nghe nhạc"

							],
							[
								"type"		=> "web_url",
								"url"		=> $music->link_origin,
								"title"		=> "Hợp âm"
							]
						])
					];
			}

		} else {
			$mesage = 'anh xin giơ tay rút lui thôi';
			$music = Music::where('content', 'like', '%' . $sentence . '%')->first();

			if ($music) {
				
				if ($type == 'NAME_SONG') {
					$mesage = $music->name;
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
								$mesage = substr($sen, $check + strlen($sentence));
								break;
							} else {
								if ($count < $max_sen) {
									$mesage = $content_split[$count];
								} else {
									$mesage = 'Hết';
								}
							}
						}
					}
				}
			}

			$result = [
				[
					'id'	=>	null,
					'type'	=>	'text',
					'message'	=>	$mesage
				],
				[
					'id'	=>	null,
					'type'	=>	'audio',
					'message'	=>	$mesage
				]
			];
		}
		
		return $result;
	}
}