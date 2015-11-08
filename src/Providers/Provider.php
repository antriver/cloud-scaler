<?php

/*
 * This file is part of the CloudScaler package.
 *
 * (c) Anthony Kuske <www.anthonykuske.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
