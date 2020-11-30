<?php

namespace App\Http\Controllers;

use App\Helpers\CurlHelper;
use App\Helpers\NaHelper;
use Illuminate\Http\Request;

class WebviewController extends Controller
{
    public function date(Request $request) {
		/* receive and send messages */
		$input = $request->all();
		info(print_r($input, true));
        $psid = $input['psid'];
        $date = $input['date'];
        $pageId = '101255648493211';
        return CurlHelper::sendWebFacebook($pageId, $psid, $date);
    }
}
