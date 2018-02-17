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
     * Clear the stack of all messages.
     */
    public function flush()
    {
        $this->data = [];
    }

    /**
     * Retrieve all messages on the stack.
     *
     * @return array
     */
    public function all()
    {
        return array_map(function ($value) {
            return is_callable($value) ? $value() : $value;
        }, $this->data);
    }

    /**
     * Add context-loggable data to the sticky context.
     *
     * @param  string  $key
     * @param  mixed  $data
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
