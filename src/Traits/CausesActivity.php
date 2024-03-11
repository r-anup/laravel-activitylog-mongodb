<?php

namespace Spatie\Activitylog\Traits;

use MongoDB\Laravel\Relations\MorphMany;
use Spatie\Activitylog\ActivitylogServiceProvider;

trait CausesActivity
{
    public function actions(): MorphMany
    {
        return $this->morphMany(
            ActivitylogServiceProvider::determineActivityModel(),
            'causer'
        );
    }
}
