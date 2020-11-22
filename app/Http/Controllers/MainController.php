<?php

namespace App\Http\Controllers;

use App\Helpers\NaHelper;
use Illuminate\Http\Request;
use App\Helpers\XSMBHelper;
use App\Helpers\NyHelper;
use App\Helpers\LQHelper;

class MainController extends Controller
{
    public function index() {
    	return response()->json([
    		'name'	=>	'api',
    		'description'	=>	'knowledge'
    	]);
    }

    public function answerXSMB(Request $request) {
    	if ($request->has('query')){
    		$query = $request->input('query');

    		$answer = XSMBHelper::answer($query);
    		return response()->json([
	    		'status'	=>	'SUCCESS',
	    		'answer'	=>	$answer
	    	]);
    	} else {
    		return response()->json([
	    		'status'	=>	'FAIL',
	    		'answer'	=>	'No query'
	    	]);
    	}
    }

    public function answerNy(Request $request) {
        if ($request->has('query')){
            $query = $request->input('query');

            $answer = NyHelper::answer($query);
            return response()->json([
                'status'    =>  'SUCCESS',
                'answer'    =>  $answer
            ]);
        } else {
            return response()->json([
                'status'    =>  'FAIL',
                'answer'    =>  'No query'
            ]);
        }
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

            $NY_PAGE_ID = env("NY_PAGE_ID", "");
            $XSMB_PAGE_ID = env("XSMB_PAGE_ID", "");
            $LQ_PAGE_ID = env("LQ_PAGE_ID", "");
            $NA_PAGE_ID = env("NA_PAGE_ID", "");

            if ($id_page == $NY_PAGE_ID) {
                NyHelper::sendAnswer($input);
            }

            if ($id_page == $XSMB_PAGE_ID) {
                XSMBHelper::sendAnswer($input);
            }

            if ($id_page == $LQ_PAGE_ID) {
                LQHelper::sendAnswer($input);
            }

            if ($id_page == $NA_PAGE_ID) {
                NaHelper::sendAnswer($input);
            }

        }

        return;
    }
}
