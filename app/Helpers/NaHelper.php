<?php

namespace App\Helpers;
use App\Helpers\Entity\EntityDetection;

class NaHelper extends KnowledgeHelper
{
	public static function answer($query, $page_id, $PID) {

		$random_string = [
			'Kẻ bỏ rơi bạn bè không bằng rác rưởi',
			'Đó chính là nhẫn đạo của ta'
		];

		$result = [];

        if (array_key_exists('type', $query)) {
            // xu ly text
            if ($query['type'] == 'text') {
                $message = mb_strtolower($query['content']);
                $character = EntityDetection::findCharacterNaruto($message);

                if ($character) {
                    $text = "Character: " . $character['fullname_2'];

                    $result[] = [
                        'id'	=>	null,
                        'type'	=>	'text',
                        'message'	=>	$text
                    ];

                    if (array_key_exists('avatar', $character)) {
                        $avatar = $character['avatar'];

                        $smallImage = explode('?', $avatar)[0] . '/scale-to-width-down/300';
                        $result[] = [
                            'id'	=>	null,
                            'type'	=>	'image',
                            'url'	=>	$smallImage
                        ];
                    }
                    $moreInfo = "";
                    $arrKeyInfo = ['summary'];
                    foreach ($arrKeyInfo as $key) {
                        if (array_key_exists($key, $character)) {
                            $moreInfo .= $key . ': ' . $character[$key] . "\n";
                        }
                    }
                    $result[] = [
                        'id'	=>	null,
                        'type'	=>	'text',
                        'message'	=>	$moreInfo
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
