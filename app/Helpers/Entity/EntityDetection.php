<?php
namespace App\Helpers\Entity;

use App\Helpers\CurlHelper;
use App\Models\Hero;
use App\Models\NarutoCharacter;
use App\Models\Recipe;
use App\Models\TarotCard;
use GuzzleHttp\Client;

use Illuminate\Support\Facades\Storage;

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
        $characters = NarutoCharacter::where('name', 'like', '%'. $sentence .'%')
            ->orWhere('fullname', 'like', '%'. $sentence .'%')
            ->get()->toArray();
        return EntityDetection::findMainCharacter($characters);
    }

    public static function findTarotCard($sentence) {
        $card = TarotCard::where('name', 'like', $sentence .'%')
            ->orWhere('name_translate', $sentence)
            ->first();
        if ($card) {
            return $card->toArray();
        }
        return null;
    }

    public static function findTarotCardByLevel($level) {
	    $mapLevel = [
	        '1, một' =>  'ace',
	        '2, hai' =>  'two',
	        '3, ba' =>  'three',
	        '4, bốn' =>  'four',
	        '5, năm' =>  'five',
	        '6, sáu' =>  'six',
	        '7, bảy, bẩy' =>  'seven',
	        '8, tám' =>  'eight',
	        '9, chín' =>  'nine',
	        '10, mười' =>  'ten',
	        'kị sĩ, kỵ sỹ, kị kỹ, kỵ sĩ' =>  'knight',
	        'vua' =>  'king',
	        'hoàng hậu, nữ hoàng' =>  'queen',
        ];
	    foreach ($mapLevel as $key => $value) {
            if (strpos($key, $level) !== false) {
                $level = $value;
                break;
            }
        }
        $cards = TarotCard::where('level', $level)
            ->get()->toArray();
        return $cards;
    }

    public static function queryWit($message) {

        $entities = CurlHelper::requestWit($message);

        $intent = null;
        $datetime = null;
        $number = null;
        if (count($entities) > 0) {
            if (array_key_exists('intent_entity:intent_entity', $entities)) {
                $intent = $entities['intent_entity:intent_entity'][0]['value'];
            }

            if (array_key_exists('wit$datetime:datetime', $entities)) {
                $datetime = $entities['wit$datetime:datetime'][0]['value'];
            }

            if (array_key_exists('wit$number:number', $entities)) {
                $number = $entities['wit$number:number'][0]['value'];
            }
        }

        return [
            'intent'    =>  $intent,
            'datetime' =>  $datetime,
            'number' =>  $number,
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

    public static function findRecipes($sentence) {
        $recipes = [];
        $detectEntity = self::detectWordCook($sentence);
        if ($detectEntity) {
            $arrayWhere = [];
            foreach ($detectEntity as $entity) {
                $arrayWhere[] = ['name', 'like', '%'. $entity .'%'];
            }
            $findRecipes = Recipe::where($arrayWhere);
            $recipes = $findRecipes->inRandomOrder()->limit(5)->get()->toArray();

            if (!$recipes) {
                $findRecipes = Recipe::where('name', 'like', '%'. $detectEntity[0] .'%');
                $count = 0;
                foreach ($detectEntity as $entity) {
                    $count += 1;
                    if ($count == 1) {
                        continue;
                    }
                    $findRecipes = $findRecipes->orWhere('name', 'like', '%'. $entity .'%');
                }
                $recipes = $findRecipes->inRandomOrder()->limit(5)->get()->toArray();
            }
        }
        return $recipes;
    }

    public static function detectWordCook($sentence) {
        $results = [];
        $arrWord = [];
        if (Storage::exists('cook_dictionaries.txt')) {
            $contentSystem = Storage::get('cook_dictionaries.txt');

            if ($contentSystem != '') {
                $arrWord = explode("\n", $contentSystem);
            }
        }

        if ($arrWord && $sentence) {
            $splitSentence = explode(" ", $sentence);
            foreach ($splitSentence as $word) {
                if (in_array($word, $arrWord)) {
                    $results[] = $word;
                }
            }
        }
        return $results;
    }
}
