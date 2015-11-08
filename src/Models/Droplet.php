<?php

namespace Tmd\CloudScaler\Models;

use DigitalOceanV2\DigitalOceanV2;
use DigitalOceanV2\Entity\Droplet as ApiDroplet;
use DigitalOceanV2\Entity\Network;

/**
 * A host on DigitalOcean.
 *
 * @package Tmd\CloudScaler
 */
class Droplet extends Host
{
    /**
     * @var DigitalOceanV2
     */
    private $api;

    /**
     * @var \DigitalOceanV2\Entity\Droplet
     */
    private $droplet;

    /**
     * @param \DigitalOceanV2\Entity\Droplet $droplet
     * @param DigitalOceanV2 $api
     */
    public function __construct(ApiDroplet $droplet, DigitalOceanV2 $api)
    {
        $this->setDroplet($droplet);
        $this->api = $api;
    }

    /**
     * Set the underlying droplet object.
     *
     * @param \DigitalOceanV2\Entity\Droplet $droplet
     */
    private function setDroplet(ApiDroplet $droplet)
    {
        $this->droplet = $droplet;

        $this->clearIps();
        if (is_array($droplet->networks)) {
            /** @var Network $network */
            foreach ($droplet->networks as $network) {
                if ($network->type === 'public') {
                    $this->publicIps[] = new IpAddress($network->version, $network->ipAddress);
                } elseif ($network->type === 'private') {
                    $this->privateIps[] = new IpAddress($network->version, $network->ipAddress);
                }
            }
        }
    }

    /**
     * @return \DigitalOceanV2\Entity\Droplet
     */
    public function getDroplet()
    {
        return $this->droplet;
    }

    /**
     * Returns the state of the droplet.
     *
     * @return string "new", "active", "off", or "archive".
     */
    public function getState()
    {
        return $this->droplet->status;
    }

    /**
     * Returns the droplets hostname.
     *
     * @return string
     */
    public function getHostname()
    {
        return $this->droplet->name;
    }

    /**
     * Returns if the droplet's status is "active".
     * Fetches fresh information from the API.
     *
     * @return bool
     */
    public function isReady()
    {
        $this->fresh();
        return $this->droplet->status === 'active';
    }

    /**
     * Fetch fresh droplet information from the API.
     *
     * @return bool
     */
    public function fresh()
    {
        if (!$this->droplet instanceof ApiDroplet) {
            return false;
        }
        if ($droplet = $this->api->droplet()->getById($this->droplet->id)) {
            $this->setDroplet($droplet);
            return true;
        }
        return false;
    }
}
