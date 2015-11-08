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
     * Default values for DNS records.
     * Should contain 'ttl' and any other provider specific values.
     *
     * @var array[]
     */
    public $dnsDefaults;

    /**
     * After creating a host, create these DNS records pointing to it.
     * %d in the string will be replaced with the instance number.
     * Array should be in the format:
     * 'domain1.com' => [
     *      'subdomain',
     *      'subdomain%d'
     * ],
     * 'domain2.com' => [
     *      'server%d'
     * ]
     *
     * @var array[][]
     */
    public $dnsRecords = [];

    /**
     * Array of provider specific information to use when creating a host.
     * For DigitalOcean:
     *      'region', 'size', and 'image'
     *
     * @var array
     */
    public $hostDefaults = [];

    /**
     * A regular expression to pick this service's host's hostnames out of all the hosts at the provider.
     *
     * @var string
     */
    public $hostnamePattern;

    /**
     * The hostname to use for new hosts.
     * %d in the string will be replaced with the instance number.
     *
     * @var string
     */
    public $hostnameTemplate;


    /**
     * Minimum number of hosts to keep running.
     *
     * @var int
     */
    public $minHosts = 1;

    /**
     * Maximum number of hosts to spawn.
     *
     * @var int
     */
    public $maxHosts;

    /**
     * Name of the service.
     *
     * @var string
     */
    public $name;

    /**
     * If specified, after creating a host it won't be considered ready until the following
     * URL returns a 200 response code. %s in the string will be replaced with the host's first public  IP address.
     *
     * @var string
     */
    public $testUrl;

    /**
     * Any additional headers to set when making the request to $testUrl (key => value pairs)
     *
     * @var array
     */
    public $testUrlHeaders = [];

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

        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }

        $this->dnsProvider = $dnsProvider;
        $this->hostProvider = $hostProvider;
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
