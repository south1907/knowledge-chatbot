<?php

namespace App\Helpers;
use App\Helpers\Entity\EntityDetection;
use App\Models\Fb\ElementTemplate;
use App\Models\NarutoCharacter;

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

                $intent = null;
                $datetime = null;
                $res = EntityDetection::queryWit($message);
                if ($res['intent']) {
                    $intent = $res['intent'];
                }

                if ($res['datetime']) {
                    $datetime = $res['datetime'];
                }

                // khong co intent
                if ($intent == null) {
                    $character = EntityDetection::findCharacterNaruto($message);

                    if ($character) {
                        $result = self::getAnswerCharacter($character);
                    }
                } else if ($intent == 'birthday') {
                    // intent ngay nay la sinh nhat cua ai
                    if ($datetime) {
                        $string_datetime = date("F j", strtotime($datetime));

                        $characters = NarutoCharacter::where('birthday', $string_datetime)->get();

                        $elements = [];
                        $count = 1;

                        foreach ($characters as $character) {
                            $avatar = $character['avatar'];
                            $smallImage = explode('/revision', $avatar)[0];
                            $buttons = [
                                [
                                    "type"		=> "postback",
                                    "title"		=> "Information",
                                    "payload"	=> "NA::character|" . $character['id']
                                ],
                                [
                                    "type"		=> "web_url",
                                    "url"		=> $character['link_origin'],
                                    "title"		=> "View more"
                                ]
                            ];

                            $el = new ElementTemplate($character['fullname_2'], $smallImage, $buttons);
                            $elements[] = $el;
                            $count += 1;

                            if ($count > 10) {
                                break;
                            }
                        }

                        if (!empty($elements)) {
                            $result[] = [
                                'id'	=>	null,
                                'type'	=>	'generic',
                                'elements'	=>	$elements
                            ];
                        }
                    }
                }
            }

            if ($query['type'] == 'postback') {
                $payload = $query['content'];
                $id = explode("|", $payload)[1];
                $character = NarutoCharacter::find($id);
                if (strpos($payload, 'NA::summary') !== false) {
                    $result[] = [
                        'id'	=>	null,
                        'type'	=>	'text',
                        'message'	=>	$character['summary']
                    ];
                }
                if (strpos($payload, 'NA::family') !== false) {
                    $result[] = [
                        'id'	=>	null,
                        'type'	=>	'text',
                        'message'	=>	$character['family']
                    ];
                }
                if (strpos($payload, 'NA::character') !== false) {
                    $result = self::getAnswerCharacter($character);
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

	private static function getAnswerCharacter($character) {
	    $result = [];
        $text = "Character: " . $character['fullname_2'];

        $result[] = [
            'id'	=>	null,
            'type'	=>	'text',
            'message'	=>	$text
        ];

        if (array_key_exists('avatar', $character)) {
            $avatar = $character['avatar'];

            $smallImage = explode('/revision', $avatar)[0];
            $result[] = [
                'id'	=>	null,
                'type'	=>	'image',
                'url'	=>	$smallImage
            ];
        }
        $moreInfo = "";
        $arrKeyInfo = [
            'affiliation'   => 'Village',
            'nickname'   => 'Nickname',
            'sex'   => 'Sex',
            'birthday'   => 'Birthday',
            'blood_type'   => 'Blood',
        ];
        foreach ($arrKeyInfo as $key => $value) {
            if (array_key_exists($key, $character)) {
                $moreInfo .= $value . ': ' . $character[$key] . "\n";
            }
        }
        $result[] = [
            'id'	=>	null,
            'type'	=>	'text',
            'message'	=>	$moreInfo
        ];

        $result[] = [
            'id'	=>	null,
            'type'	=>	'button',
            'message'	=>	'More information',
            'buttons' => json_encode([
                [
                    "type"		=> "postback",
                    "title"		=> "Summary",
                    "payload"	=> "NA::summary|" . $character['id']
                ],
                [
                    "type"		=> "postback",
                    "title"		=> "Family",
                    "payload"	=> "NA::family|" . $character['id']
                ],
                [
                    "type"		=> "web_url",
                    "title"		=> "View more",
                    "url"	=> $character['link_origin']
                ]
            ])
        ];
        return $result;
    }

}
?>
