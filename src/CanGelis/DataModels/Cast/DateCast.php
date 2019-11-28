<?php

namespace CanGelis\DataModels\Cast;

use Carbon\Carbon;

class DateCast extends AbstractCast
{
    /**
     * @inheritDoc
     */
    public function cast($value)
    {
        return new Carbon($value);
    }

    /**
     * @inheritDoc
     */
    public function uncast($value)
    {
        if ($value instanceof Carbon) {
            return $value->toDateString();
        }
        return $value;
    }
}
