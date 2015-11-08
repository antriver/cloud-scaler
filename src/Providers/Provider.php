<?php

namespace Tmd\CloudScaler\Providers;

/**
 * Abstract provider.
 *
 * @package Tmd\CloudScaler
 */
abstract class Provider
{
    /**
     * @var string
     */
    protected $name = '';

    /**
     * Return the name of this provider.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
