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

use Exception;
use Tmd\CloudScaler\Providers\Host\HostProvider;

/**
 * A virtual server on some host.
 *
 * @package Tmd\CloudScaler
 */
abstract class Host
{
    /**
     * @var int
     */
    public $instance;

    /**
     * Public IP addresses.
     *
     * @var IpAddress[]
     */
    public $publicIps = [];

    /**
     * @var IpAddress[]
     */
    public $privateIps = [];

    /**
     * @var HostProvider
     */
    private $provider;

    /**
     * @var Service
     */
    private $service;

    /**
     * Returns the hostname of the host.
     *
     * @return string
     */
    abstract public function getHostname();

    /**
     * Returns a string describing the hosts's state (varies by provider).
     *
     * @return string
     */
    abstract public function getState();

    /**
     * Returns if this host is booted and ready to use.
     *
     * @return bool
     */
    public function isReady()
    {
        return false;
    }

    /**
     * Set the service this host belongs to.
     *
     * @param Service $service
     */
    public function setService(Service $service)
    {
        $this->service = $service;
    }

    /**
     * Get the service this host belongs to.
     *
     * @return Service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set the provider this host runs on.
     *
     * @return HostProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Get the provider this host runs on.
     *
     * @param HostProvider $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    /**
     * Set the instance number of this host.
     *
     * @param int $instance
     */
    public function setInstance($instance)
    {
        $this->instance = $instance;
    }

    /**
     * Get the load average for the server.
     *
     * @return float[]
     */
    public function getLoad()
    {
        $ssh = sprintf($this->service->ssh, $this->getHostname());
        $cmd = $ssh . " uptime | awk '{print $(NF-2)\" \"$(NF-1)\" \"$(NF-0)}'";
        $load = exec($cmd);
        $load = explode(',', $load);
        $load = array_map(function ($str) {
            return (float)trim($str);
        }, $load);
        return $load;
    }

    /**
     * Make an HTTP request to this host's first public IP address. Returns the HTTP status code.
     *
     * @param string $urlPattern
     * @param array $headers
     *
     * @throws Exception
     * @return int
     */
    public function testHttp($urlPattern, array $headers = array())
    {
        if (!empty($this->publicIps)) {
            $ip = $this->publicIps[0];
        } else {
            throw new Exception("Host has no public IP address to test.");
        }

        $url = sprintf($urlPattern, $ip->ip);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        foreach ($headers as $name => $value) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array($name . ': ' . $value));
        }
        curl_exec($curl);
        return curl_getinfo($curl, CURLINFO_HTTP_CODE);
    }

    /**
     * Return all the public IPs as an array of the actual IP addresses instead of IpAddress objects.
     *
     * @return string[]
     */
    public function getPublicIpStrings()
    {
        return array_map(function (IpAddress $ip) {
            return $ip->ip;
        }, $this->publicIps);
    }

    /**
     * Clear all IP addresses in the model. (Doesn't affect the actual server)
     */
    protected function clearIps()
    {
        $this->publicIps = [];
        $this->privateIps = [];
    }
}
