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
}
