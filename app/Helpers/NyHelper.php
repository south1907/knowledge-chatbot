<?php

namespace App\Helpers;
use GuzzleHttp\Client;

class NyHelper extends KnowledgeHelper
{
	public static function answer($query) {

		$CLIENT_WIT = env("CLIENT_WIT_NY", "");
		$client = new Client(['headers' => ['Authorization' => 'Bearer ' . $CLIENT_WIT]]);
		$response = $client->request('GET', 'https://api.wit.ai/message', ['query' => ['q' => $query]]);
		$body = $response->getBody();
		$obj = json_decode($body, true);

		$entities = $obj['entities'];

		$result = ':)';

		if (count($entities) > 0) {

			$intents = [];
			if (array_key_exists('intent', $entities)) {
				$intent = $entities['intent'][0]['value'];
				array_push($intents, $intent);
			}

			// process single intent
			if (count($intents) == 1) {
				$current_intent = $intents[0];

				switch ($current_intent) {

					case 'love_female':

						$sentences = [
							'Em cũng vậy, em cũng yêu em :p',
							'Em yêu anh <3',
							'Em không yêu anh đâu',
							'Đừng làm phiền em nữa',
							'Em cũng yêu anh nhứt luôn',
							'Thì sao',
							'Vl :))',
							'T đéo nhé :D',
							'Đm, vl :))',
							'ờ'
						];
						$rand = array_rand($sentences);

						$result = $sentences[$rand];
						
						break;

					case 'love_male':

						$sentences = [
							'Anh cũng vậy, anh cũng yêu anh :p',
							'Anh yêu em <3',
							'Anh không yêu em đâu',
							'Đừng làm phiền anh nữa',
							'Anh cũng yêu em nhứt luôn',
							'Thì sao',
							'Vl :))',
							'T đéo nhé :D',
							'Đm, vl :))',
							'ờ'
						];
						$rand = array_rand($sentences);

						$result = $sentences[$rand];
						
						break;

					case 'ask_love_female':

						$sentences = [
							'Ok, em đồng ý',
							'Ừ, yêu thì yêu',
							'Haha, ok anh yêu',
							'Yep!',
							'Em cần thời gian để suy nghĩ',
							'Em không yêu anh đâu, đừng yêu em nữa',
							'Đừng làm phiền em nữa',
							'Vl :))',
							'Hơi bất ngờ đấy nhé'
						];
						$rand = array_rand($sentences);

						$result = $sentences[$rand];
						
						break;
					case 'ask_love_male':

						$sentences = [
							'Ok, anh đồng ý',
							'Ừ, yêu thì yêu',
							'Haha, ok xxx yêu',
							'Yep!',
							'Anh cần thời gian để suy nghĩ',
							'Anh không yêu xxx đâu, đừng yêu anh nữa',
							'Đừng làm phiền anh nữa',
							'Vl :))',
							'Hơi bất ngờ đấy nhé'
						];
						$rand = array_rand($sentences);

						$result = $sentences[$rand];
						
						break;

					case 'break_up':
					
						$sentences = [
							'Ừ, chia tay thì chia tay',
							'Tại sao chứ',
							'Ok, tôi hiểu mà',
							'Ừ, biết nói sao giờ',
							'Mình làm lại nhé',
							'Ok, I\'m fine',
							'Ok...',
							'Đm, vl',
							':('
						];
						$rand = array_rand($sentences);

						$result = $sentences[$rand];
						
						break;

					case 'break_up_female':
					
						$sentences = [
							'Ok, em sẽ cố quên',
							'Ừ, em ổn mà',
							'Anh nghĩ quên anh mà dễ chắc',
							'Ừ, biết nói sao giờ',
							'Mình làm lại nhé',
							'Anh không sao đâu',
							'Buồn lắm, nhưng em ổn mà',
							'Ok...',
							'Đm, vl',
							':('
						];
						$rand = array_rand($sentences);

						$result = $sentences[$rand];
						
						break;

					case 'break_up_male':
					
						$sentences = [
							'Ok, anh sẽ cố quên',
							'Ừ, anh ổn mà',
							'Em nghĩ quên em mà dễ chắc',
							'Ừ, biết nói sao giờ',
							'Mình làm lại nhé',
							'Anh không sao đâu',
							'Buồn lắm, nhưng anh ổn mà',
							'Ok...',
							'Đm, vl',
							':('
						];
						$rand = array_rand($sentences);

						$result = $sentences[$rand];
						
						break;

					case 'bye':
					
						$sentences = [
							'Bye :))',
							'Hi, hẹn gặp lại',
							'Hihi',
							'Ừ, bye',
							'Tạm biệt',
							'Cho nhà người lui',
							'Cho khanh lui',
							'=))'
						];
						$rand = array_rand($sentences);

						$result = $sentences[$rand];
						
						break;

					case 'bye_never':
					
						$sentences = [
							'Ok, đừng nhìn mặt t nữa',
							'Đồ chó',
							'Con chó',
							'Ok, tốt thôi',
							'Ok, t block',
							'Cút đi',
							'Biến đi'
						];
						$rand = array_rand($sentences);

						$result = $sentences[$rand];
						
						break;

					case 'call':
					
						$sentences = [
							'Đây',
							'Hihi',
							':D'
						];
						$rand = array_rand($sentences);

						$result = $sentences[$rand];
						
						break;

					case 'call_female':
					
						$sentences = [
							'Em đây',
							'Dạ anh',
							'Vâng anh'
						];
						$rand = array_rand($sentences);

						$result = $sentences[$rand];
						
						break;

					case 'call_male':
					
						$sentences = [
							'Anh đây',
							'Anh nghe',
							'Ừ em'
						];
						$rand = array_rand($sentences);

						$result = $sentences[$rand];
						
						break;

					case 'talk':
					
						$sentences = [
							'Ừ',
							'Nói nghe',
							'Gì vậy'
						];
						$rand = array_rand($sentences);

						$result = $sentences[$rand];
						
						break;

					case 'talk_female':
					
						$sentences = [
							'Em nghe nè',
							'Dạ anh',
							'Vâng anh, có gì vậy ạ?'
						];
						$rand = array_rand($sentences);

						$result = $sentences[$rand];
						
						break;

					case 'call_male':
					
						$sentences = [
							'Ừ, anh nghe đây',
							'Anh nghe',
							'Ừ, có chuyện gì vậy em'
						];
						$rand = array_rand($sentences);

						$result = $sentences[$rand];
						
						break;

					case 'good_night':
					
						$sentences = [
							'Good night',
							'G9',
							'bye',
							'Ngủ ngon nha'
						];
						$rand = array_rand($sentences);

						$result = $sentences[$rand];
						
						break;

					case 'good_night_female':
					
						$sentences = [
							'Anh ngủ ngon nha',
							'Ngủ ngon nha anh',
							'Yep!'
						];
						$rand = array_rand($sentences);

						$result = $sentences[$rand];
						
						break;

					case 'good_night_male':
					
						$sentences = [
							'Ừ, Em ngủ ngon',
							'Bye em',
							'Mai gặp lại nhé'
						];
						$rand = array_rand($sentences);

						$result = $sentences[$rand];
						
						break;

					case 'why':
					
						$sentences = [
							'Có lẽ chúng ta không hợp nhau',
							'Đừng hỏi t tại sao',
							'Ai biết',
							'Haiz'
						];
						$rand = array_rand($sentences);

						$result = $sentences[$rand];
						
						break;

					case 'cursing':
					
						$sentences = [
							'Chửi cc',
							'Bậy vl',
							'Không yêu xin đừng nói lời cay đắng',
							'Đmm'
						];
						$rand = array_rand($sentences);

						$result = $sentences[$rand];
						
						break;

					case 'help': 
						$result = "Ny có thể\n";
						$result .= "+ Làm tình, cảm chúng ta đi lên\n";
						$result .= "+ Chúc ngủ ngon\n";
						$result .= "+ Nói lời yêu thương\n";
						$result .= "+ Kà kịa\n";
						break;

					default:
						# code...
						$result = 'Hì :))';
						break;
				}
			} else {
				$result = $query;
			}
		} else {
			$result = 'I love you';
		}

		return $result;

	}
}
?>