<?php

namespace CanGelis\DataModels;

class JsonModel extends DataModel implements \JsonSerializable
{
    /**
     * @var array
     */
    protected $data;

    /**
     * DataModel constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

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
     * Make an array
     *
     * @return array
     */
    public function toArray()
    {
        $data = $this->data;

        // apply modified relationships
        foreach ($this->relations as $relationAttribute => $relation) {
            list($relationType, $attribute) = explode("-", $relationAttribute);
            $data[$attribute] = $relation->toArray();
        }

        foreach ($this->attributeValues as $attribute => $value) {
            $data[$attribute] = $this->uncastValue($attribute, $value);
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * @inheritDoc
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * @inheritDoc
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * @inheritDoc
     */
    public function __unset($name)
    {
        unset($this->data[$name]);
        unset($this->relations[$name]);
        unset($this->attributeValues[$name]);
    }

    /**
     * Make item a data model
     *
     * @param array|\CanGelis\DataModels\DataModel $item
     * @param string $class
     *
     * @return \CanGelis\DataModels\DataModel
     */
    protected function getItemAsObject($item, $class)
    {
        if (is_array($item)) {
            return new $class($item);
        }

        if (is_object($item) && get_class($item) == $class) {
            return $item;
        }

        throw new \InvalidArgumentException('Expected array or ' . $class . ' but ' . gettype($item) . ' given');
    }

    /**
     * @inheritDoc
     */
    protected function setHasOne($relation, $value)
    {
        return $this->getItemAsObject($value, $this->hasOne[$relation]);
    }

    /**
     * @inheritDoc
     */
    protected function setHasMany($relation, $value)
    {
        if (is_array($value)) {
            $collection = $this->makeCollection([]);
            foreach ($value as $item) {
                $collection->add($this->getItemAsObject($item, $this->hasMany[$relation]));
            }
            return $collection;
        }

        if ($value instanceof DataCollection) {
            return $value;
        }

        throw new \InvalidArgumentException('Expected array or DataCollection but ' . gettype($value) . ' given');
    }

    /**
     * @inheritDoc
     */
    protected function resolveHasManyRelationship($relation)
    {
        $items = [];

        if (array_key_exists($relation, $this->data) && is_array($this->data[$relation])) {
            $items = $this->data[$relation];
        }

        unset($this->data[$relation]);

        return $this->makeCollection(
            array_map(function ($item) use ($relation) {
                return $this->getItemAsObject($item, $this->hasMany[$relation]);
            }, $items)
        );
    }

    /**
     * @inheritDoc
     */
    protected function resolveHasOneRelationship($relation)
    {
        if (is_array($this->data[$relation])) {
            $model = new $this->hasOne[$relation]($this->data[$relation]);
            unset($this->data[$relation]);
            return $model;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    protected function hasAttribute($attribute)
    {
        return array_key_exists($attribute, $this->data);
    }

    /**
     * @inheritDoc
     */
    protected function getAttribute($attribute)
    {
        return $this->data[$attribute];
    }

    /**
     * @inheritDoc
     */
    protected function onLoadAttribute($attribute)
    {
        unset($this->data[$attribute]);
    }
}
