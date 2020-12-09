<?php

namespace App\Helpers;
use App\Helpers\Entity\EntityDetection;
use App\Models\Fb\ElementTemplate;
use App\Models\NarutoCharacter;

class PaHelper extends KnowledgeHelper
{
	public static function answer($query, $page_id, $PID) {

		$random_string = [
			'Please chat name of Organization',
			'Please chat: Facebook or Youtube',
            'I only understand organization Facebook or Youtube'
		];

		$result = [];

        if (array_key_exists('type', $query)) {
            // xu ly text
            if ($query['type'] == 'text') {
                $message = mb_strtolower($query['content']);
                $image = '';
                $location = '';
                $link = '';
                print_r($message);
                if (strpos($message, "facebook") !== false) {
                    $image = 'https://www.facebook.com/images/fb_icon_325x325.png';
                    $location = 'Menlo Park, CA';
                    $link = 'https://www.facebook.com/facebook/';
                }
                if (strpos($message, "youtube") !== false) {
                    $image = 'https://www.youtube.com/img/desktop/yt_1200.png';
                    $location = '901 Cherry Avenue in San Bruno';
                    $link = 'https://www.facebook.com/youtube/';
                }

                if ($location) {
                    $result[] = [
                        'id'	=>	null,
                        'type'	=>	'text',
                        'message'	=>	'Information of ' . $query['content']
                    ];

                    $result[] = [
                        'id'	=>	null,
                        'type'	=>	'image',
                        'url'	=>	$image
                    ];

                    $result[] = [
                        'id'	=>	null,
                        'type'	=>	'text',
                        'message'	=>	'Location: ' . $location . '\n' . 'Page: ' . $link
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
