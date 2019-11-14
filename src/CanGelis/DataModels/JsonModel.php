<?php

namespace CanGelis\DataModels;

class JsonModel extends DataModel implements \JsonSerializable
{
    /**
     * Make an instance from a string
     *
     * @param string $json
     *
     * @return static
     */
    public static function fromString($json)
    {
        return new static(json_decode($json, true));
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return json_encode($this->toArray());
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->jsonSerialize();
    }
}