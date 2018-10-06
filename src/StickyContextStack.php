<?php

namespace Decahedron\StickyLogging;

class StickyContextStack
{
    /**
     * An array of all sticky context data on the stack.
     *
     * @var array
     */
    protected $data = [];

    /**
     * @var int
     */
    static $recursionLimit = 5;

    /**
     * @var int
     */
    private $recursionCounter = 0;

    /**
     * Clear the stack of all messages.
     */
    public function flush()
    {
        $this->data = [];
    }

    /**
     * Retrieve all messages on the stack. Prevent recursion beyond the static limit.
     *
     * @return array
     */
    public function all()
    {
        $this->recursionCounter++;

        if ($this->recursionCounter <= static::$recursionLimit) {
            return array_map(function ($value) {
                return is_callable($value) ? $value() : $value;
            }, $this->data);
        }

        return [];
    }

    /**
     * Add context-loggable data to the sticky context.
     *
     * @param string         $key
     * @param callable|mixed $data
     */
    public function add(string $key, $data)
    {
        $this->data[$key] = $data;
    }

    /**
     * Check whether the stack is empty.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->data);
    }
}
