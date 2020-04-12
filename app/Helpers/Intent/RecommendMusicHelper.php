<?php
namespace App\Helpers\Intent;

use App\Models\Music;

class RecommendMusicHelper
{

	public static function recommend($message) {

		$result = null;

		try {
			$pat = "(hát bài|hát|nghe bài|nghe) gì";
			$pat = "/" . $pat . "/";
			if (preg_match($pat, $message, $matches, PREG_OFFSET_CAPTURE)) {

				$music = self::find($message);

				if ($music) {
					$link_music = $music->youtube;
					$link_music = str_replace('http:', 'https:', $link_music);
					$result[] = [
						'id'	=>	null,
						'type'	=>	'button',
						'message'	=>	$music->name,
						'buttons' => json_encode([
							[
								"type"		=> "web_url",
								"url"		=> $link_music,
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
				return null;
			}
		} catch (\Exception $e) {
			
		}

		return $result;
	}

	public static function find($message) {
		$message = strtolower($message);
		$message = preg_replace("/(?![.=$'€%-])\p{P}/u", "", $message);
		$message = strtolower($message);

		$one_word = ['vui', 'buồn', 'giận', 'chán', 'ghét', 'sợ', 'thích', 'yêu', 'ghê', 'khó', 'khóc', 'nhớ'];
		$two_word = ['đau khổ', 'chán ghét', 'bực mình', 'hận đời', 'chia tay', 'hạnh phúc', 'ngạc nhiên', 'ghê tởm', 'sợ hãi', 'cô đơn', 'một mình', 'đang yêu', 'giận giữ', 'bội bạc', 'phản bội', 'thương', 'từng yêu', 'bối rối', 'lo lắng', 'lo âu', 'khó chịu', 'thất vọng', 'tội lỗi', 'hi vọng', 'tổn thương', 'mong nhớ', 'oán hận', 'buồn rầu', 'hối hận', 'hối tiếc', 'tạm biệt'];

		$emotion = null;

		foreach ($two_word as $emo) {
			if(strpos($message, $emo) !== false){
			    $emotion = $emo;
			    break;
			}
		}

		if ($emotion == null) {
			foreach ($one_word as $emo) {
				if(strpos($message, $emo) !== false){
				    $emotion = $emo;
				    break;
				}
			}
		}
		if ($emotion) {
			$music = Music::where('emotion', 'like', '%' . $emotion . '%')->get()->random(1);
		} else {
			$music = Music::limit(100)->get()->random(1);
		}

		return $music[0];
	}
}