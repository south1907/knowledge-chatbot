<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\TTS\GoogleVoice;

class VoiceController extends Controller
{
    public function index(Request $request) {

        if ($request->has('message')) { 
            $data = GoogleVoice::getUrlAudio($request->get('message'));
            return $data;
        }
    }
}