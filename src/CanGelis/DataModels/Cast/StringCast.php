<?php

namespace CanGelis\DataModels\Cast;

class StringCast extends AbstractCast
{
    /**
     * @inheritDoc
     */
    public function cast($value)
    {
        return (string) $value;
    }
}
