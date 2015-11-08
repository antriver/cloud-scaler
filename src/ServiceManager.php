<?php

/*
 * This file is part of the CloudScaler package.
 *
 * (c) Anthony Kuske <www.anthonykuske.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tmd\CloudScaler;

use Exception;
use Tmd\CloudScaler\Models\Service;
use Tmd\CloudScaler\Providers\Dns\DnsProvider;
use Tmd\CloudScaler\Providers\Host\HostProvider;

/**
 * IoC container.
 *
 * @package Tmd\CloudScaler
 */
class ServiceManager
{
    /**
     * @var Service[]
     */
    private $services;

    /**
     * @var HostProvider[]
     */
    private $hostProviders = [];

    /**
     * @var DnsProvider[]
     */
    private $dnsProviders = [];

    /**
     * Setup all the available providers.
     *
     * @param $providers Array of settings from the config.php file (the 'providers' property in the file)
     */
    public function registerProviders($providers)
    {
        foreach ($providers as $providerName => $providerData) {
            switch ($providerName) {
                case 'digitalocean':
                    $this->hostProviders['digitalocean'] = new Providers\Host\DigitalOcean(
                        $providerData['token']
                    );
                    // TODO: DigitalOcean DNS provider too?
                    break;

                case 'cloudflare':
                    $this->dnsProviders['cloudflare'] = new Providers\Dns\CloudFlare(
                        $providerData['email'],
                        $providerData['key']
                    );
                    break;
            }
        }
    }

    /**
     * Returns a DnsProvider by its name.
     *
     * @param string $providerName
     *
     * @return DnsProvider
     * @throws Exception
     */
    public function getDnsProvider($providerName)
    {
        if (!isset($this->dnsProviders[$providerName])) {
            throw new Exception("Unknown DNS provider '{$providerName}'");
        }

        return $this->dnsProviders[$providerName];
    }

    /**
     * Returns a HostProvider by its name.
     *
     * @param string $providerName
     *
     * @return HostProvider
     * @throws Exception
     */
    public function getHostProvider($providerName)
    {
        if (!isset($this->hostProviders[$providerName])) {
            throw new Exception("Unknown host provider '{$providerName}'");
        }

        return $this->hostProviders[$providerName];
    }

    /**
     * Register all the defined services in the config file.
     *
     * @param array $services
     *
     * @return Service[]
     */
    public function registerServices($services)
    {
        foreach ($services as $serviceName => $serviceData) {
            $this->services[$serviceName] = new Service(
                $serviceName,
                $serviceData,
                $this->getDnsProvider($serviceData['dnsProvider']),
                $this->getHostProvider($serviceData['hostProvider'])
            );
        }

        return $this->services;
    }

    /**
     * Get a registered service object.
     *
     * @param string $serviceName
     *
     * @return Service
     * @throws Exception
     */
    public function getService($serviceName)
    {
        if (!isset($this->services[$serviceName])) {
            throw new Exception("Unknown service '{$serviceName}'");
        }

        return $this->services[$serviceName];
    }

    /**
     * Returns all the registered services.
     *
     * @return Service[]
     */
    public function getAllServices()
    {
        return $this->services;
    }
}
