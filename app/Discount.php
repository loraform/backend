<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Discount extends Eloquent
{
    public $timestamps = false;
    protected $guarded = [
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'user_id'
    ];

}
