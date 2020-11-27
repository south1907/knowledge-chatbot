<?php
namespace App\Helpers\Entity;

use App\Helpers\CurlHelper;
use App\Models\Hero;
use App\Models\NarutoCharacter;
use GuzzleHttp\Client;

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

    public static function findCharacterNaruto($sentence) {
        $characters = NarutoCharacter::where('fullname_2', 'like', '%'. $sentence .'%')
            ->orWhere('fullname', 'like', '%'. $sentence .'%')
            ->get()->toArray();
        return EntityDetection::findMainCharacter($characters);
    }

    public static function queryWit($message) {

        $entities = CurlHelper::requestWit($message);

        $intent = null;
        $datetime = null;
        if (count($entities) > 0) {
            if (array_key_exists('intent_entity:intent_entity', $entities)) {
                $intent = $entities['intent_entity:intent_entity'][0]['value'];
            }

            if (array_key_exists('wit$datetime:datetime', $entities)) {
                $datetime = $entities['wit$datetime:datetime'][0]['value'];
            }
        }

        return [
            'intent'    =>  $intent,
            'datetime' =>  $datetime,
        ];
    }

    private static function checkContain($arr, $sentence) {

		$sentence = strtolower($sentence);
		foreach ($arr as $heroName) {
			$heroName = strtolower(trim($heroName));
			if(strpos($sentence, $heroName) !== false){
			    return true;
			}
		}

		return false;
	}

	private static function findMainCharacter($characters) {
	    $maxCount = 0;
	    $result = null;
        foreach ($characters as $cha) {
            $arrVal = array_values($cha);
            if (count(array_filter($arrVal)) > $maxCount) {
                $maxCount = count(array_filter($arrVal));
                $result = $cha;
            }
        }
        return $result;
    }
}
