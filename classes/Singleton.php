<?php

namespace Ion;

/**
 * Class Singleton
 * @package Ion
 */
abstract class Singleton
{
    /**
     * Instance
     *
     * @var Singleton
     */
    protected static $instance;

    /**
     * Get instance
     *
     * @return Singleton
     */
    final public static function getInstance(): self
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}