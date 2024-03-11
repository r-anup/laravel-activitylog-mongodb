<?php

namespace Spatie\Activitylog\Test\Models;

use MongoDB\Laravel\Eloquent\Model;

class Article extends Model
{
    protected $table = 'articles';

    protected $guarded = [];

    public function User()
    {
        return $this->belongsTo(User::class);
    }
}
