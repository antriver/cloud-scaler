<?php

namespace Tmd\CloudScaler\Models;

use Tmd\CloudScaler\Providers\Dns\DnsProvider;
use Tmd\CloudScaler\Providers\Host\HostProvider;

/**
 * A service represents a job being done by one or more hosts.
 * e.g. Serving a website is a service performed by multiple hosts.
 *
 * @package Tmd\CloudScaler
 */
class Service
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var array Data from the config.php file.
     */
    private $data;

    /**
     * @var DnsProvider
     */
    private $dnsProvider;

    /**
     * @var HostProvider
     */
    private $hostProvider;

    /**
     * @param string $name
     * @param array $data
     * @param DnsProvider $dnsProvider
     * @param HostProvider $hostProvider
     */
    public function __construct($name, $data, DnsProvider $dnsProvider, HostProvider $hostProvider)
    {
        $this->name = $name;
        $this->data = $data;
        $this->dnsProvider = $dnsProvider;
        $this->hostProvider = $hostProvider;
    }

    /**
     * Returns data from the service's configuration.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
        return null;
    }

    /**
     * Get the DNS provider for this service.
     *
     * @return DnsProvider
     */
    public function getDnsProvider()
    {
        return $this->dnsProvider;
    }

    /**
     * Get the host provider for this service.
     *
     * @return HostProvider
     */
    public function getHostProvider()
    {
        return $this->hostProvider;
    }

    /**
     * Check if the given host belongs to this service, by checking its hostname against this
     * service's hostnamePattern. Returns the instance number from the hostname on success else returns false.
     *
     * @param Host $host
     *
     * @return bool|int
     */
    public function isThisYourHost(Host $host)
    {
        $matches = [];
        if (preg_match($this->hostnamePattern, $host->getHostname(), $matches)) {
            return (int)$matches[1];
        }

        return false;
    }
}
