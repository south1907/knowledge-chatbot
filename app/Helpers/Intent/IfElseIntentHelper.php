<?php
namespace App\Helpers\Intent;

use App\Models\Fixed;

class IfElseIntentHelper
{
	public static function find($message, $PID, $page_id) {
		$find = Fixed::where([
					'status'		=>	1,
					'PID'			=>	$PID,
					'page_id'		=>	$page_id,
					'message_if'	=>	$message
				])->first();

		if ($find) {
			$result = [
				[
					'id'	=>	null,
					'type'	=>	'text',
					'message'	=>	$find->message_then
				],
				[
					'id'	=>	null,
					'type'	=>	'audio',
					'message'	=>	$find->message_then
				]
			];

			return $result;
		} else {
			return null;
		}
	}
	public static function process($message, $PID, $page_id) {

		$result = [
			[
				'id'	=>	null,
				'type'	=>	'text',
				'message'	=>	'Em không đấy'
			]
		];

		try {
			$flag = false;

			$pat1 = "nếu (bố|mẹ|anh|em|con|bác|chú) (bảo|nói) là (.*) thì (bố|mẹ|anh|em|con|bác|chú) (bảo|nói) là (.*) thì .* (bố|mẹ|anh|em|con|bác|chú) (.*) cho";

			$pat2 = "nếu (bố|mẹ|anh|em|con|bác|chú|ta) (bảo|nói) là (.*) thì (bố|mẹ|anh|em|con|bác|chú|nhà ngươi|ngươi) (bảo|nói) là (.*) nhé";


			$pat1 = "/" . $pat1 . "/";
			$pat2 = "/" . $pat2 . "/";

			$message_promise = null;

			if (preg_match($pat1, $message, $matches, PREG_OFFSET_CAPTURE)) {

				$s1 = $matches[1][0];

				$message_if = trim($matches[3][0]);

				$message_then = trim($matches[6][0]);

				$message_then = trim($message_then, 'nha');
				$message_then = trim($message_then, 'nhé');
				$message_then = trim($message_then);


				$s2 = $matches[4][0];

				$message_promise = $matches[8][0];

				$content = $s1 . ' nhớ ' . $message_promise . ' cho ' . $s2 . ' đấy nhé';

				$result = [
					[
						'id'	=>	null,
						'type'	=>	'text',
						'message'	=>	$content
					],
					[
						'id'	=>	null,
						'type'	=>	'audio',
						'message'	=>	$content
					]
				];

				$flag = true;
			}

			if (!$flag) {
				if (preg_match($pat2, $message, $matches, PREG_OFFSET_CAPTURE)) {
					$s1 = $matches[1][0];

					$message_if = trim($matches[3][0]);

					$message_then = trim($matches[6][0]);

					$s2 = $matches[4][0];

					$result = [
						[
							'id'	=>	null,
							'type'	=>	'text',
							'message'	=>	'ok'
						]
					];

					$flag = true;
				}
			}

			if ($flag) {

				$exist = Fixed::where([
					'status'		=>	1,
					'PID'			=>	$PID,
					'page_id'		=>	$page_id,
					'message_if'	=>	$message_if
				])->first();

				$flagNew = false;

				if ($exist) {

					if ($exist->message_then != $message_then) {
						// neu then khac thi update cai cu va tao cai moi
						$exist->status = 0;
						$exist->save();

						$flagNew = true;
					}
				} else {
					$flagNew = true;
				}

				if ($flagNew) {
					$fixed = new Fixed;
					$fixed->PID = $PID;
					$fixed->message_if = $message_if;
					$fixed->message_then = $message_then;
					$fixed->message_promise = $message_promise;
					$fixed->page_id = $page_id;
					$fixed->status = 1;

					$fixed->save();
				}

				return $result;
			} else {
				return null;
			}

		} catch (\Exception $e) {
			
		}

		return $result;
	}
}