<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

/**
 * @property string $name
 * @property string $description
 * @property bool $active
 * @property string $_id
 */
class Project extends Eloquent
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'active', '_id',
    ];
    protected $appends = ['owner'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'updated_at', 'created_at', 'user_id', 'aliases'
    ];


    public function things()
    {
        return $this->hasMany(Thing::class);
    }

    public function scenarios()
    {
        return $this->hasMany(Scenario::class);
    }

    public function codecs()
    {
        return $this->hasMany(Codec::class);
    }

    public function getOwnerAttribute()
    {
        $user = $this->user()->first();
        if ($user)
            return $user;
        return null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function activeScenario(Scenario $scenario)
    {
        $other_scenarios = $scenario->project()->first()->scenarios()->get();
        foreach ($other_scenarios as $s) {
            if ($s['is_active'] == true) {
                $s['is_active'];
                $s['is_active'] = false;
                $s->save();
            }
        }
        $scenario['is_active'] = true;
        $scenario->save();
    }
}
