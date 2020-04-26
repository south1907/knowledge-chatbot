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
		if (count($result) == 0) {
			$currentMax = 0;
			$tempResult = null;
			foreach ($heros as $hero) {
				$heroName = $hero['name'];
				$heroName = strtolower(trim($heroName));
				similar_text($heroName, $sentence, $perc);
				if ($perc >= 50 && $perc > $currentMax) {
					$currentMax = $perc;
					$tempResult = $hero;
				}
			}

			if ($tempResult != null) {
				$result[] = $tempResult;
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