<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Zalo\ZaloHelper;
use App\Helpers\Zalo\ZaloDecode;

class TestController extends Controller
{
    public function index() {
    	return response()->json([
    		'name'	=>	'api',
    		'description'	=>	'Test Something'
    	]);
    }

    public function zalo(Request $request) {
        if ($request->has('phone')){
            $phone = $request->input('phone');

            $data = ZaloHelper::getUserByPhone($phone);
            return response()->json([
                'status'    =>  'SUCCESS',
                'data'    =>  $data
            ]);
        } else {
            return response()->json([
                'status'    =>  'FAIL',
                'answer'    =>  'No phone'
            ]);
        }
    }
}
