<?php

namespace CanGelis\DataModels;

class DataModel
{
    /**
     * @var array
     */
    protected $data;

    /**
     * Casts for the attribute values
     *
     * @var array
     */
    protected $casts = [];

    /**
     * Default values for the attributes that doesn't exist at all
     *
     * @var array
     */
    protected $defaults = [];

    /**
     * Has many relationships
     *
     * @var array
     */
    protected $hasMany = [];

    /**
     * Has one relationships
     *
     * @var array
     */
    protected $hasOne = [];

    /**
     * Initialized relations
     *
     * @var array
     */
    private $relations = [];

    /**
     * Initialized attributes
     *
     * @var array
     */
    private $attributeValues = [];

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
        return $data;
    }

    /**
     * Accessor for the json object
     *
     * @param string $attribute
     *
     * @return mixed
     */
    public function __get($attribute)
    {
        // resolve has many relationship
        if (array_key_exists($attribute, $this->hasMany)) {
            return $this->getHasManyValue($attribute);
        }

        // resolve has one relationship
        if (array_key_exists($attribute, $this->hasOne)) {
            return $this->getHasOneValue($attribute);
        }

        // return if it was accessed before
        if (array_key_exists($attribute, $this->attributeValues)) {
            return $this->attributeValues[$attribute];
        }

        if (array_key_exists($attribute, $this->data)) {
            return $this->attributeValues[$attribute] = $this->castValue($attribute, $this->data[$attribute]);
        }

        if (array_key_exists($attribute, $this->getDefaults())) {
            return $this->attributeValues[$attribute] = $this->castValue($attribute, $this->getDefaults()[$attribute]);
        }

        return $this->attributeValues[$attribute] = null;
    }

    /**
     * Set the value
     *
     * @param string $attribute
     * @param mixed $value
     *
     * @throws \InvalidArgumentException
     */
    public function __set($attribute, $value)
    {
        if (array_key_exists($attribute, $this->hasOne)) {
            $this->setHasOneValue($attribute, $value);
        } elseif (array_key_exists($attribute, $this->hasMany)) {
            $this->setHasManyValue($attribute, $value);
        } else {
            $this->data[$attribute] = $this->uncastValue($attribute, $value);
            unset($this->attributeValues[$attribute]);
        }
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
    }

    /**
     * Get has many relationship value
     *
     * @param mixed $attribute
     *
     * @return \CanGelis\DataModels\DataCollection
     */
    protected function getHasManyValue($attribute)
    {
        if (isset($this->relations['hasMany-' . $attribute])) {
            return $this->relations['hasMany-' . $attribute];
        }

        $items = [];
        if (array_key_exists($attribute, $this->data) && is_array($this->data[$attribute])) {
            $items = $this->data[$attribute];
        }

        return $this->relations['hasMany-' . $attribute] = $this->makeCollection(
            array_map(function ($item) use ($attribute) {
                return $this->getItemAsObject($item, $this->hasMany[$attribute]);
            }, $items)
        );
    }

    /**
     * Make the value array if it is an datamodel already
     *
     * @param \CanGelis\DataModels\DataModel|array $item
     *
     * @return array
     */
    protected function getItemAsArray($item)
    {
        if ($item instanceof DataModel) {
            return $item->toArray();
        }

        if (is_array($item)) {
            return $item;
        }

        throw new \InvalidArgumentException('Expected array or DataModel but ' . gettype($item) . ' given');
    }

    /**
     * Make item an data model
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

        if (get_class($item) == $class) {
            return $item;
        }

        throw new \InvalidArgumentException('Expected array or ' . $class . ' but ' . gettype($item) . ' given');
    }

    /**
     * Get the has one relationship value
     *
     * @param mixed $attribute
     *
     * @return \CanGelis\DataModels\DataModel|null
     */
    protected function getHasOneValue($attribute)
    {
        if (isset($this->relations['hasOne-' . $attribute])) {
            return $this->relations['hasOne-' . $attribute];
        }

        if (is_array($this->data[$attribute])) {
            return $this->relations['hasOne-' . $attribute] = new $this->hasOne[$attribute]($this->data[$attribute]);
        }

        return $this->relations['hasOne-' . $attribute] = null;
    }

    /**
     * Set has one value
     *
     * @param string $attribute
     * @param array|\CanGelis\DataModels\DataModel $value
     */
    protected function setHasOneValue($attribute, $value)
    {
        $this->data[$attribute] = $this->getItemAsArray($value);
        unset($this->relations['hasOne-' . $attribute]);
    }

    /**
     * Set has many value
     *
     * @param string $attribute
     * @param \CanGelis\DataModels\DataCollection $value
     */
    protected function setHasManyValue($attribute, $value)
    {
        if (is_array($value)) {
            $this->data[$attribute] = array_map(function ($item) {
                return $this->getItemAsArray($item);
            }, $value);
        } elseif ($value instanceof DataCollection) {
            $this->data[$attribute] = $value->toArray();
        } else {
            throw new \InvalidArgumentException('Expected array or DataCollection but ' . gettype($value) . ' given');
        }

        unset($this->relations['hasMany-' . $attribute]);
    }

    /**
     * Default values for the attributes that doesn't exist
     * in the data, don't hesitate to override this if you have
     * more complex defaults logic
     *
     * @return array
     */
    protected function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * Cast an attribute value
     *
     * @param string $attribute
     * @param string $value
     *
     * @return mixed
     */
    protected function castValue($attribute, $value)
    {
        if (!array_key_exists($attribute, $this->casts)) {
            return $value;
        }

        /**
         * @var \CanGelis\DataModels\Cast\AbstractCast $caster
         */
        $caster = new $this->casts[$attribute]();

        return $caster->cast($value);
    }

    /**
     * Revert casted value back to the serialiazable form
     *
     * @param string $attribute
     * @param mixed $value
     *
     * @return mixed
     */
    protected function uncastValue($attribute, $value)
    {
        if (!array_key_exists($attribute, $this->casts)) {
            return $value;
        }

        /**
         * @var \CanGelis\DataModels\Cast\AbstractCast $caster
         */
        $caster = new $this->casts[$attribute]();

        return $caster->uncast($value);
    }

    /**
     * Make a new collection
     *
     * @param array $items
     *
     * @return \CanGelis\DataModels\DataCollection
     */
    protected function makeCollection($items)
    {
        return new DataCollection($items);
    }
}
