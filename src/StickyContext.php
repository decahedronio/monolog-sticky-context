<?php

namespace Decahedron\StickyLogging;

/**
 * Sticky Context.
 *
 * @method static void add(string $key, $value)
 * @see \Decahedron\StickyLogging\StickyContextStack
 */
class StickyContext
{
    /**
     * This is the array in which we store all sticky context data.
     *
     * @var \Decahedron\StickyLogging\StickyContextStack[]
     */
    protected static $stacks = [];

    /**
     * The default stack to which messages will be sent.
     *
     * @var string
     */
    protected static $defaultStack = 'sticky';

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
        foreach (static::$stacks as $stack) {
            if (! $stack->isEmpty()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retrieve all data in the sticky context.
     *
     * Note that all empty stacks will be excluded from the output.
     *
     * @return array
     */
    public static function all()
    {
        return array_map(function (StickyContextStack $stack) {
            return $stack->all();
        }, array_filter(static::$stacks, function (StickyContextStack $stack) {
            return ! $stack->isEmpty();
        }));
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
     *
     * You may optionally provide the name of a stack, in which
     * case only that stack's context data will be flushed.
     *
     * Note that when flushing a specific stack, the stack itself remains.
     *
     * @param string|null $stack
     */
    public static function flush(string $stack = null)
    {
        if ($stack) {
            static::$stacks[$stack]->flush();
        } else {
            static::$stacks = [];
        }
    }

    /**
     * Get or set the default stack that Sticky Context should use.
     *
     * @param string $stackName
     * @return string
     */
    public static function defaultStack(string $stackName = null)
    {
        if ($stackName) {
            // If the default stack has already been written to, we
            // will make sure to move the existing default stack
            // to the new stack name, as to not lose any data.
            if (static::hasStack(static::$defaultStack)) {
                static::$stacks[$stackName] = static::stack(static::$defaultStack);
                unset(static::$stacks[static::$defaultStack]);
            }

            static::$defaultStack = $stackName;
        }

        return static::$defaultStack;
    }

    /**
     * Retrieve a named stack.
     *
     * @param string $stack
     * @return \Decahedron\StickyLogging\StickyContextStack
     */
    public static function stack(string $stack)
    {
        if (! static::hasStack($stack)) {
            static::$stacks[$stack] = new StickyContextStack;
        }

        return static::$stacks[$stack];
    }

    /**
     * Proxy calls to the default stack.
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return static::stack(static::$defaultStack)->$name(...$arguments);
    }

    /**
     * Check if a given stack exists.
     *
     * @param $stack
     * @return bool
     */
    protected static function hasStack($stack)
    {
        return isset(static::$stacks[$stack]);
    }
}