<?php

namespace CanGelis\DataModels;

abstract class DataModel
{
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
    protected $relations = [];

    /**
     * Initialized attributes
     *
     * @var array
     */
    protected $attributeValues = [];

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

        if ($this->hasAttribute($attribute)) {
            return $this->loadAttribute($attribute, $this->getAttribute($attribute));
        }

        if (array_key_exists($attribute, $this->getDefaults())) {
            return $this->loadAttribute($attribute, $this->getDefaults()[$attribute]);
        }

        return $this->loadAttribute($attribute, null);
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
            $this->loadAttribute($attribute, $value);
        }
    }

    /**
     * Load the attribute
     *
     * @param string $attribute
     * @param mixed $value
     *
     * @return mixed
     */
    protected function loadAttribute($attribute, $value)
    {
        $this->attributeValues[$attribute] = $this->castValue($attribute, $value);
        // if the value is already in the source data
        // it should be unset since we load the value into attributeValues
        // to avoid duplication
        $this->onLoadAttribute($attribute);

        return $this->attributeValues[$attribute];
    }

    /**
     * Get has many relationship value
     *
     * @param mixed $relation
     *
     * @return \CanGelis\DataModels\DataCollection
     */
    protected function getHasManyValue($relation)
    {
        if ($this->isHasManyRelationLoaded($relation)) {
            return $this->getLoadedHasManyRelationValue($relation);
        }

        return $this->relations['hasMany-' . $relation] = $this->resolveHasManyRelationShip($relation);
    }

    /**
     * Get the already loaded has many relation value
     *
     * @param string $relation
     *
     * @return mixed
     */
    protected function getLoadedHasManyRelationValue($relation)
    {
        return $this->relations['hasMany-' . $relation];
    }

    /**
     * Get the already loaded has one relation value
     *
     * @param string $relation
     *
     * @return mixed
     */
    protected function getLoadedHasOneRelationValue($relation)
    {
        return $this->relations['hasOne-' . $relation];
    }

    /**
     * Returns true if the given has many relation is already loaded
     *
     * @param string $relation
     *
     * @return bool
     */
    protected function isHasManyRelationLoaded($relation)
    {
        return isset($this->relations['hasMany-' . $relation]);
    }

    /**
     * Returns true if the given has one relation is already loaded
     *
     * @param string $relation
     *
     * @return bool
     */
    protected function isHasOneRelationLoaded($relation)
    {
        return isset($this->relations['hasOne-' . $relation]);
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
        if ($this->isHasOneRelationLoaded($attribute)) {
            return $this->getLoadedHasOneRelationValue($attribute);
        }

        return $this->relations['hasOne-' . $attribute] = $this->resolveHasOneRelationship($attribute);
    }

    /**
     * Set has one value
     *
     * @param string $attribute
     * @param array|\CanGelis\DataModels\DataModel $value
     */
    protected function setHasOneValue($attribute, $value)
    {
        $this->relations['hasOne-' . $attribute] = $this->setHasOne($attribute, $value);
    }

    /**
     * Set has many value
     *
     * @param string $attribute
     * @param \CanGelis\DataModels\DataCollection $value
     */
    protected function setHasManyValue($attribute, $value)
    {
        $this->relations['hasMany-' . $attribute] = $this->setHasMany($attribute, $value);
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

    /**
     * Resolve has many relationship
     *
     * @param string $relation
     *
     * @return \CanGelis\DataModels\DataCollection
     */
    abstract protected function resolveHasManyRelationship($relation);

    /**
     * Resolve has one relationship
     *
     * @param string $relation
     *
     * @return \CanGelis\DataModels\DataModel
     */
    abstract protected function resolveHasOneRelationship($relation);

    /**
     * Set the has one value
     *
     * @param string $relation
     * @param mixed $value
     *
     * @return \CanGelis\DataModels\DataModel
     */
    abstract protected function setHasOne($relation, $value);

    /**
     * Set has many relation value
     *
     * @param string $relation
     * @param mixed $value
     *
     * @return \CanGelis\DataModels\DataCollection
     */
    abstract protected function setHasMany($relation, $value);

    /**
     * Returns true if the attribute exists
     *
     * @param string $attribute
     *
     * @return bool
     */
    abstract protected function hasAttribute($attribute);

    /**
     * Get the attribute value
     *
     * @param $attribute
     *
     * @return mixed
     */
    abstract protected function getAttribute($attribute);

    /**
     * Called when an attribute is loaded
     * When the value is loaded it can be deleted from the
     * source data so no duplication will occur during export
     *
     * @param string $attribute
     *
     * @return mixed
     */
    abstract protected function onLoadAttribute($attribute);
}
