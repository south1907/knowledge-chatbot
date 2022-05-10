<?php

namespace App\Http\Controllers;

use App\Helpers\GameHelper;
use App\Helpers\NaHelper;
use Illuminate\Http\Request;
use App\Helpers\NyHelper;
use App\Helpers\LQHelper;
use App\Helpers\TaHelper;
use App\Helpers\AkiHelper;

class MainController extends Controller
{
    public function index() {
    	return response()->json([
    		'name'	=>	'api',
    		'description'	=>	'knowledge'
    	]);
    }

    public function verifyWebhook (Request $request) {
    	/* validate verify token needed for setting up web hook */

        $VERIFY_TOKEN = env("VERIFY_TOKEN", "");

    	$all = $request->all();
    	info(print_r($all, true));

		if ($request->has('hub_verify_token')) {
		    if ($request->get('hub_verify_token') === $VERIFY_TOKEN) {
		        echo $request->get('hub_challenge');
		        return;
		    } else {
		        echo 'Invalid Verify Token';
		        return;
		    }
		}

    }

    public function webhook(Request $request) {

		/* receive and send messages */
		$input = $request->all();
		info(print_r($input, true));

        if (isset($input['entry'][0]['id'])) {
            $id_page = $input['entry'][0]['id'];
            $LQ_PAGE_ID = env("LQ_PAGE_ID", "");
            $NA_PAGE_ID = env("NA_PAGE_ID", "");
            $TA_PAGE_ID = env("TA_PAGE_ID", "");
            $GAME_PAGE_ID = env("GAME_PAGE_ID", "");
            $AKI_PAGE_ID = env("AKI_PAGE_ID", "");

            switch ($id_page) {
                case $LQ_PAGE_ID:
                    LQHelper::sendAnswer($input);
                    break;
                case $NA_PAGE_ID:
                    NaHelper::sendAnswer($input);
                    break;
                case $TA_PAGE_ID:
                    TaHelper::sendAnswer($input);
                    break;
                case $GAME_PAGE_ID:
                    GameHelper::sendAnswer($input);
                    break;
                case $AKI_PAGE_ID:
                    AkiHelper::sendAnswer($input);
                    break;
                default:
                    NyHelper::sendAnswer($input);
            }
        }

        return;
    }
}
