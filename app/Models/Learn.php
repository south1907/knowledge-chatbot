<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Learn extends Model
{
    protected $table = "learns";

    public $timestamps = false;

    public function word()
    {
    	return $this->belongsTo('App\Models\Word', 'word_id');
    }
}
