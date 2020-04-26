<?php

namespace App\Helpers;
use App\Helpers\Entity\EntityDetection;
use App\Models\Hero;
use App\Models\Skill;
use App\Models\Skin;
use App\Models\Fb\ElementTemplate;

class LQHelper extends KnowledgeHelper
{
	public static function answer($query, $page_id, $PID) {
		
		$random_string = [
			'Thà sống chứ không chịu hy sinh',
			'Chơi game 2 tiếng mỗi ngày để giữ gìn sức khỏe',
			'Chỉ cần chiến thắng, bạn bè không quan trọng',
			'Chỉ cần bạn có mặt, thắng thua không quan "tọng"',
			'Thà chết chứ không chịu hy sinh',
			'Thà chết chứ không thể sống một mình',
			'Thà giàu mà sướng còn hơn nghèo mà khổ',
			'Chỉ cần bạn bên cạnh tôi, thì tôi sẽ luôn ở bên cạnh bạn',
			'Chỉ cần bạn có mặt, thắng thua không quan trọng'
		];

		$result = [];
		
		if (array_key_exists('type', $query)) {
			// xu ly text
			if ($query['type'] == 'text') {
				$message = mb_strtolower($query['content']);
				$heros = EntityDetection::findHeros($message);

				if (count($heros) > 0) {
					$hero = $heros[0];
					$text = "Tên tướng: " . $hero['name'];
					$text .= "\nLoại: " . $hero['hero_type_name'];

					$result[] = [
						'id'	=>	null,
						'type'	=>	'text',
						'message'	=>	$text
					];

					$result[] = [
						'id'	=>	null,
						'type'	=>	'image',
						'url'	=>	$hero['image']
					];

					$result[] = [
						'id'	=>	null,
						'type'	=>	'button',
						'message'	=>	'Thông tin thêm',
						'buttons' => json_encode([
							[
								"type"		=> "postback",
								"title"		=> "Tiểu sử",
								"payload"	=> "LQ::story|" . $hero['id']
							],
							[
								"type"		=> "postback",
								"title"		=> "Kỹ năng",
								"payload"	=> "LQ::skill|" . $hero['id']
							],
							[
								"type"		=> "postback",
								"title"		=> "Trang phục",
								"payload"	=> "LQ::skin|" . $hero['id']
							]
						])
					];
				}
			}

			// xu ly postback
			if ($query['type'] == 'postback') {
				$payload = $query['content'];
				$id = explode("|", $payload)[1];

				if (strpos($payload, 'LQ::story') !== false) {
					$hero = Hero::find($id);

					$result[] = [
						'id'	=>	null,
						'type'	=>	'text',
						'message'	=>	$hero['story']
					];
				}

				if (strpos($payload, 'LQ::skill') !== false) {
					$skills = Skill::where('hero_id', $id)->get()->toArray();

					$elements = [];

					$arrTitle = ['Nội tại', 'Chiêu 1', 'Chiêu 2', 'Chiêu cuối'];
					$count = 0;
					foreach ($skills as $skill) {

						$buttons = [
							[
								"type"		=> "postback",
								"title"		=> "Xem chi tiết",
								"payload"	=> "LQ::detail_skill|" . $skill['id']
							]
						];

						$el = new ElementTemplate($arrTitle[$count] . ' - ' . $skill['name'], $skill['image'], $buttons);
						$elements[] = $el;
						$count += 1;
					}

					$result[] = [
						'id'	=>	null,
						'type'	=>	'generic',
						'elements'	=>	$elements
					];
				}

				if (strpos($payload, 'LQ::detail_skill') !== false) {
					$skill = Skill::find($id);

					$result[] = [
						'id'	=>	null,
						'type'	=>	'text',
						'message'	=>	$skill['description']
					];
				}

				if (strpos($payload, 'LQ::skin') !== false) {
					$skins = Skin::where('hero_id', $id)->get()->toArray();

					$elements = [];
					$count = 1;

					foreach ($skins as $skin) {

						$buttons = [
							[
								"type"		=> "web_url",
								"url"		=> "https://google.com",
								"title"		=> "Xem chi tiết"

							]
						];
						if (!isset($skin['name'])) {
							$skin['name'] = 'Trang phục ' . $count;
						}

						$el = new ElementTemplate($skin['name'], $skin['image'], $buttons);
						$elements[] = $el;
						$count += 1;

						if ($count > 10) {
							break;
						}
					}

					$result[] = [
						'id'	=>	null,
						'type'	=>	'generic',
						'elements'	=>	$elements
					];
				}
			}
		}

		if (count($result) == 0) {

			$rand = array_rand($random_string);

			$mes = $random_string[$rand];
			
			$result = [
				[
					'id'	=>	null,
					'type'	=>	'text',
					'message'	=>	$mes
				]
			];
		}

		return $result;
	}

}
?>