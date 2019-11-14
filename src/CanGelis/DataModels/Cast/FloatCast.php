<?php

namespace CanGelis\DataModels\Cast;

class FloatCast extends AbstractCast
{
    /**
     * @inheritDoc
     */
    public function cast($value)
    {
        return (float) $value;
    }
}
