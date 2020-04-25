<?php
namespace App\Helpers\Entity;

use App\Models\Hero;

class EntityDetection
{
	public static function findHeros($sentence) {
		$result = [];
		$heros = Hero::all()->toArray();

		foreach ($heros as $hero) {
			$heroName = $hero['name'];
			$arrCheck[] = $heroName;

			$otherNames = $hero['other_names'];
			if ($otherNames) {
				$otherNames = explode(';', $otherNames);

				$arrCheck = array_merge($arrCheck, $otherNames);
			}
			if (self::checkContain($arrCheck, $sentence)) {
				$result[] = $hero;
			}

		}

		return $result;
	}

	public static function checkContain($arr, $sentence) {

		$sentence = strtolower($sentence);
		foreach ($arr as $heroName) {
			$heroName = strtolower(trim($heroName));
			if(strpos($sentence, $heroName) !== false){
			    return true;
			}
		}

		return false;
	}
}