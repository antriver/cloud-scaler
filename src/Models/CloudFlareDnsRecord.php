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
 * Represents a DNS record on CloudFlare.
 *
 * @package Tmd\CloudScaler
 */
class CloudFlareDnsRecord extends DnsRecord
{
    /**
     * CloudFlare acceleration enabled?
     * @var bool
     */
    public $proxied;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->proxied = !empty($data['proxied']) ? true : false;
        parent::__construct($data);
    }
}
