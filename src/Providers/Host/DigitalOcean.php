<?php

namespace Tmd\CloudScaler\Providers\Host;

use DigitalOceanV2\Adapter\GuzzleAdapter;
use DigitalOceanV2\DigitalOceanV2;
use Exception;
use Tmd\CloudScaler\Models\Droplet;
use Tmd\CloudScaler\Models\Host;
use Tmd\CloudScaler\Models\Service;

/**
 * Methods for abstracting the DigitalOcean API implementation.
 *
 * @package Tmd\CloudScaler
 */
class DigitalOcean extends HostProvider
{
    /**
     * @var string
     */
    protected $name = 'digitalocean';

    /**
     * @var DigitalOceanV2
     */
    private $api;

    /**
     * @param string $accessToken A "Personal Access Token" from the API page on DigitalOcean.
     */
    public function __construct($accessToken)
    {
        $adapter = new GuzzleAdapter($accessToken);
        $this->api = new DigitalOceanV2($adapter);
    }

    /**
     * Return all the droplets with hostnames matching the service.
     *
     * @param Service $service
     *
     * @return Droplet[]
     */
    public function getHostsForService(Service $service)
    {
        $droplet = $this->api->droplet();
        $droplets = $droplet->getAll();

        $hosts = [];
        foreach ($droplets as $droplet) {
            $host = new Droplet($droplet, $this->api);
            $host->setProvider($this);

            if (($instance = $service->isThisYourHost($host)) !== false) {
                $host->setService($service);
                $host->setInstance($instance);
                $hosts[] = $host;
            }
        }

        return $hosts;
    }

    /**
     * Spin up a new droplet on DigitalOcean.
     * @see \DigitalOceanV2\Api\Droplet::create()
     *
     * @param string $hostname
     * @param array $data
     *
     * @return Droplet|Host
     */
    public function launch($hostname, $data = array())
    {
        $droplet = $this->api->droplet()->create(
            $hostname,
            $data['region'],
            $data['size'],
            $data['image'],
            isset($data['backups']) ? (bool)$data['backups'] : false,
            isset($data['ipv6']) ? $data['ipv6'] : true,
            isset($data['privateNetworking']) ? $data['privateNetworking'] : true
        );

        return new Droplet($droplet, $this->api);
    }

    /**
     * Destroy a droplet.
     *
     * @param Host $host
     *
     * @return bool
     * @throws Exception
     */
    public function destroy(Host $host)
    {
        if (!$host instanceof Droplet) {
            throw new Exception(self::class . " can only destroy DigitalOcean droplets");
        }
        $this->api->droplet()->delete($host->getDroplet()->id);
        return true;
    }

    /**
     * Return the DigitalOcean API library object.
     *
     * @return DigitalOceanV2
     */
    public function getApi()
    {
        return $this->api;
    }
}
