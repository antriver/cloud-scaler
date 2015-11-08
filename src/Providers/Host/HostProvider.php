<?php

/*
 * This file is part of the CloudScaler package.
 *
 * (c) Anthony Kuske <www.anthonykuske.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tmd\CloudScaler\Providers\Host;

use Tmd\CloudScaler\Models\Host;
use Tmd\CloudScaler\Models\Service;
use Tmd\CloudScaler\Providers\Provider;

/**
 * Abstract provider of virtual servers.
 *
 * @package Tmd\CloudScaler
 */
abstract class HostProvider extends Provider
{
    /**
     * @param Service $service
     *
     * @return Host[]
     */
    abstract public function getHostsForService(Service $service);

    /**
     * Spin up a new host at the provider and return it.
     *
     * @param string $hostname
     * @param array $data
     *
     * @return Host
     */
    abstract public function launch($hostname, $data = array());

    /**
     * Destroy a host.
     *
     * @param Host $host
     *
     * @return bool
     */
    abstract public function destroy(Host $host);
}
