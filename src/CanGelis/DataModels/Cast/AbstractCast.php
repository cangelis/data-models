<?php

namespace CanGelis\DataModels\Cast;

abstract class AbstractCast
{
    /**
     * The value is casted when it is accessed
     * So this is a good place to convert a date string into
     * a Carbon instance
     *
     * @param mixed $value
     *
     * @return mixed
     */
    abstract public function cast($value);

    /**
     * This method is used when the value is set
     * So this is good place to make the values
     * json compatible such as integer, string or bool
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function uncast($value)
    {
        return $value;
    }
}
