<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\XSMBHelper;

class XSMBController extends Controller
{
    public function index() {
    	return response()->json([
    		'name'	=>	'api',
    		'description'	=>	'xsmb'
    	]);
    }

    public function answer(Request $request) {
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

    public function verifyWebhook (Request $request) {
    	/* validate verify token needed for setting up web hook */ 
    	
        $VERIFY_TOKEN = env("ACCESS_TOKEN", "");
    	// print_r( $request);
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
		XSMBHelper::sendAnswer($input);

        return;
    }
}
