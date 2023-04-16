<?php

namespace App\Helpers;

use App\Helpers\Entity\EntityDetection;
use App\Models\Fb\ElementTemplate;
use App\Models\Recipe;

class CookHelper extends KnowledgeHelper
{
	public static function answer($query, $page_id, $PID) {

		$random_string = [
			'Tôi yêu ăn :))',
		];

		$result = [];

        if (array_key_exists('type', $query)) {
            // xu ly text

            if ($query['type'] == 'text') {
                $message = mb_strtolower($query['content']);
                $detects = EntityDetection::findRecipes($message);

                if (count($detects) == 1) {
                    $result = self::getAnswerRecipe($detects[0]);
                }
                if (count($detects) > 1) {
                    $elements = [];

                    foreach ($detects as $item) {
                        $buttons = [
                            [
                                "type"		=> "postback",
                                "title"		=> "View",
                                "payload"	=> "COOK::detail|" . $item['id']
                            ]
                        ];

                        $el = new ElementTemplate($item['name'], $item['image'], $buttons);
                        $elements[] = $el;
                    }

                    $result[] = [
                        'id'	=>	null,
                        'type'	=>	'generic',
                        'elements'	=>	$elements
                    ];
                }
            }

            if ($query['type'] == 'postback') {
                // xu ly postback
                $payload = $query['content'];
                $id = explode("|", $payload)[1];

                if (strpos($payload, 'COOK::detail') !== false) {
                    $recipe = Recipe::find($id);
                    $result = self::getAnswerRecipe($recipe);
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

    private static function getAnswerRecipe($recipe) {
        $result = [];
        $text = $recipe['name'];

        $result[] = [
            'id'	=>	null,
            'type'	=>	'text',
            'message'	=>	$text
        ];

        $result[] = [
            'id'	=>	null,
            'type'	=>	'image',
            'url'	=>	$recipe['image']
        ];

        if (isset($recipe['summary'])) {
            $summary = "Summary: \n";
            $summary .= $recipe['summary'];

            $result[] = [
                'id'	=>	null,
                'type'	=>	'text',
                'message'	=>	$summary
            ];
        }

        if (isset($recipe['ingredients'])) {
            $strIngredients = "Ingredients: \n";
            $ingredients = json_decode($recipe['ingredients']);

            foreach ($ingredients as $ingredient) {
                $strIngredients .= " - " . $ingredient->name . "\n";
            }
            $result[] = [
                'id'	=>	null,
                'type'	=>	'text',
                'message'	=>	$strIngredients
            ];
        }

        if (isset($recipe['step_by_step'])) {
            $step = "Step by step: \n";
            $step .= $recipe['step_by_step'];

            $result[] = [
                'id'	=>	null,
                'type'	=>	'text',
                'message'	=>	$step
            ];
        }

        return $result;
    }
}
?>
