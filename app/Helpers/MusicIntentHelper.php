<?php
namespace App\Helpers;

use App\Models\Music;

class MusicIntentHelper extends IntentHelper
{
	public static function process($session, $PID, $page_id, $sentence) {
		$result = 'bài gì đó...';

		$music = Music::where('content', 'like', '%' . $sentence . '%')->first();

		if ($music) {
			$result = $music->name;
		}

		return [
			[
				'id'	=>	null,
				'type'	=>	'text',
				'message'	=>	$result
			]
		];
	}
}