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

use Tmd\CloudScaler\Exceptions\CloudFlareException;
use Tmd\CloudScaler\Models\CloudFlareDnsRecord;
use Tmd\CloudScaler\Models\IpAddress;

/**
 * Methods for abstracting the CloudFlare API implementation.
 *
 * @package Tmd\CloudScaler
 */
class CloudFlare extends DnsProvider
{
    /**
     * @var string
     */
    protected $name = 'cloudflare';

    /**
     * @var string
     */
    private $cloudFlareEmail;

    /**
     * @var string
     */
    private $cloudFlareKey;

    /**
     * Cache of zone identifiers for domain names.
     *
     * @var string[]
     */
    private $zoneIds = [];

    /**
     * @param string $email Your CloudFlare account email address.
     * @param string $key Your CloudFlare API key.
     *
     * @throws CloudFlareException
     */
    public function __construct($email, $key)
    {
        if (empty($email) || empty($key)) {
            throw new CloudFlareException('Please set a CloudFlare email and key');
        }

        $this->cloudFlareEmail = $email;
        $this->cloudFlareKey = $key;
    }

    /**
     * Add multiple DNS records.
     *
     * @param array $recordData
     *
     * @return CloudFlareDnsRecord[]
     * @throws CloudFlareException
     */
    public function addRecords($recordData)
    {
        $records = [];
        foreach ($recordData as $data) {
            $record = new CloudFlareDnsRecord($data);
            $zoneId = $this->getZoneId($record->domain);
            $this->request(
                'POST',
                'zones/' . $zoneId . '/dns_records',
                [
                    'type' => $record->type,
                    'name' => $record->name,
                    'content' => $record->value,
                    'ttl' => $record->ttl,
                    // Not in the docs but works to enable CloudFlare acceleration
                    // Alternative way: get the records after adding (to get the id)
                    // then update the record with proxied: true
                    'proxied' => $record->proxied
                ]
            );
        }
        return $records;
    }

    /**
     * Delete records pointing to any the given IP addresses from all the given domains.
     *
     * @param string[] $domains
     * @param IpAddress[] $ips
     *
     * @throws CloudFlareException
     */
    public function deleteRecordsByIps(array $domains, array $ips)
    {
        foreach ($domains as $domain) {
            // Get all records for this domain
            $zoneId = $this->getZoneId($domain);
            $records = $this->request('GET', 'zones/' . $zoneId . '/dns_records');
            foreach ($records->result as $record) {
                foreach ($ips as $ip) {
                    if ($record->content
                        && ($ip->version === 6 && $record->type === 'AAAA'
                            || $ip->version === 4 && $record->type === 'A')
                        && strcasecmp($record->content, $ip->ip) === 0
                    ) {
                        $this->deleteRecord($zoneId, $record->id);
                    }
                }
            }
        }
    }

    /**
     * Delete a single DNS record.
     *
     * @param string $zoneId
     * @param string $recordId
     *
     * @throws CloudFlareException
     */
    private function deleteRecord($zoneId, $recordId)
    {
        $this->request('DELETE', 'zones/' . $zoneId . '/dns_records/' . $recordId);
    }

    /**
     * Return the zone identifier for a domain name. (The first one if multiple exist)
     *
     * @param string $domain
     *
     * @return string
     * @throws CloudFlareException
     */
    private function getZoneId($domain)
    {
        if (isset($this->zoneIds[$domain])) {
            // Use the cached ID if we've got this domain before.
            return $this->zoneIds[$domain];
        }
        $response = $this->request('GET', 'zones', ['name' => $domain]);
        if (isset($response->result[0])) {
            $zoneId = $response->result[0]->id;
            // Cache for later
            $this->zoneIds[$domain] = $zoneId;
            return $zoneId;
        } else {
            throw new CloudFlareException("Unable to find zone for domain {$domain}");
        }
    }

    /**
     * A few different CloudFlare API libraries were tested, but they were half-baked or buggy.
     * So just do it ourselves until something better comes along, we're not doing that much anyway.
     *
     * @param string $type GET|POST|DELETE
     * @param string $endpoint
     * @param array $data
     *
     * @throws CloudFlareException
     * @return object
     */
    private function request($type, $endpoint, $data = [])
    {
        $url = 'https://api.cloudflare.com/client/v4/' . $endpoint;
        $headers = [
            "X-Auth-Email: {$this->cloudFlareEmail}",
            "X-Auth-Key: {$this->cloudFlareKey}",
        ];

        $curl = curl_init();

        if ($type === 'DELETE') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');

        } elseif ($type === 'POST') {
            $json = json_encode($data);
            $headers[] = "Content-Type: application/json";
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $json);

        } elseif ($type === 'GET') {
            $url .= '?' . http_build_query($data);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        $response = json_decode($response);

        if (!empty($response->errors)) {
            throw new CloudFlareException($response->errors[0]->message);
        } elseif (!$response || $response->success !== true) {
            throw new CloudFlareException("Error communicating with CloudFlare");
        }

        return $response;
    }
}
