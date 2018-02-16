<?php

namespace Decahedron\StickyLogging;

class StickyContext
{
    /**
     * This is the array in which we store all sticky context data.
     *
     * @var array
     */
    protected static $data = [];

    /**
     * Whether the sticky context should be attached to a record.
     *
     * @var bool
     */
    protected static $enabled = true;

    /**
     * Determine whether the sticky context is currently empty.
     *
     * @return bool
     */
    public static function isEmpty()
    {
        return empty(static::$data);
    }

    /**
     * Add context-loggable data to the sticky context.
     *
     * @param  string  $key
     * @param  mixed  $data
     */
    public static function add($key, $data)
    {
        static::$data[$key] = $data;
    }

    /**
     * Retrieve all data in the sticky context.
     *
     * @return array
     */
    public static function all()
    {
        return array_map(function ($value) {
            return is_callable($value) ? $value() : $value;
        }, static::$data);
    }

    /**
     * Disable sticky context logging.
     */
    public static function disable()
    {
        static::$enabled = false;
    }

    /**
     * Enable sticky context logging.
     */
    public static function enable()
    {
        static::$enabled = true;
    }

    /**
     * Determine whether sticky context logging has been disabled.
     *
     * @return bool
     */
    public static function disabled()
    {
        return ! static::$enabled;
    }

    /**
     * Clear all the sticky context data.
     */
    public static function flush()
    {
        static::$data = [];
    }
}