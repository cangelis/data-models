<?php

namespace CanGelis\DataModels\Cast;

class IntegerCast extends AbstractCast
{
    /**
     * @inheritDoc
     */
    public function cast($value)
    {
        return (int) $value;
    }
}
