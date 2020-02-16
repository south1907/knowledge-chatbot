<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Intent extends Model
{
	protected $table = "intents";

    public $timestamps = false;

    public function answers()
    {
    	return $this->hasMany('App\Models\Answer', 'intent_id');
    }
}
