<?php

/*
 * This file is part of the CloudScaler package.
 *
 * (c) Anthony Kuske <www.anthonykuske.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tmd\CloudScaler\Providers\Dns;

use Tmd\CloudScaler\Models\DnsRecord;
use Tmd\CloudScaler\Models\IpAddress;
use Tmd\CloudScaler\Providers\Provider;

/**
 * Abstract provider of DNS services.
 *
 * @package Tmd\CloudScaler
 */
abstract class DnsProvider extends Provider
{
    /**
     * Add multiple DNS records.
     *
     * @param array $recordData
     *
     * @return DnsRecord[]
     */
    abstract public function addRecords($recordData);

    /**
     * Add a single DNS record.
     *
     * @param DnsRecord $record
     *
     * @return bool
     */
    public function addRecord(DnsRecord $record)
    {
        return $this->addRecords([$record]);
    }

    /**
     * Delete records pointing to any the given IP addresses from all the given domains.
     *
     * @param string[] $domains
     * @param IpAddress[] $ips
     */
    abstract public function deleteRecordsByIps(array $domains, array $ips);
}
