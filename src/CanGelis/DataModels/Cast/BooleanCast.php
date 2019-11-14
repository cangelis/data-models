<?php

namespace CanGelis\DataModels\Cast;

class BooleanCast extends AbstractCast
{
    /**
     * @inheritDoc
     */
    public function cast($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
