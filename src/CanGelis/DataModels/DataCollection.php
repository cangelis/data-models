<?php

namespace CanGelis\DataModels;

class DataCollection implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @var array $items
     */
    protected $items;

    /**
     * JsonCollection constructor.
     *
     * @param array $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function toJson()
    {
        return json_encode($this->items);
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array_map(function ($item) {
            if ($item instanceof DataModel) {
                return $item->toArray();
            }
            return $item;
        }, $this->items);
    }

    /**
     * Add an item to the collection.
     *
     * @param \CanGelis\DataModels\DataModel $item
     *
     * @return $this
     */
    public function add(DataModel $item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Get the first item
     *
     * @param callable|null $callback
     * @param mixed         $default
     *
     * @return mixed
     */
    public function first(callable $callback = null, $default = null)
    {
        if (is_null($callback)) {
            $callback = function ($item) {
                return true;
            };
        }

        foreach ($this->items as $item) {
            if ($callback($item)) {
                return $item;
            }
        }

        return $default;
    }

    /**
     * @inheritDoc
     */
    public function filter(callable $callback)
    {
        return array_filter($this->items, $callback);
    }
}
