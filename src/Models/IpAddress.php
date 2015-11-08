<?php

/*
 * This file is part of the CloudScaler package.
 *
 * (c) Anthony Kuske <www.anthonykuske.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tmd\CloudScaler\Models;

/**
 * An IP address.
 * This model exists to tell the difference between IPv4 and Ipv6 addresses.
 *
 * @package Tmd\CloudScaler
 */
class IpAddress
{
    /**
     * @var int 4 or 6
     */
    public $version;

    /**
     * @var string
     */
    public $ip;

    /**
     * @param int $version
     * @param string $ip
     */
    public function __construct($version, $ip)
    {
        $this->version = $version;
        $this->ip = $ip;
    }
}
