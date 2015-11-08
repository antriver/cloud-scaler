<?php

return [

    // Settings for host / dns providers.
    'providers' => [
        'digitalocean' => [
            // Get your DigitalOcean "Personal Access Token" from the API page.
            'token' => 'YOURTOKENGOESHERE',
        ],

        'cloudflare' => [
            // The email address for your CloudFlare account.
            'email' => 'youremail@goeshere.com',

            // Your CloudFlare API key. Get it from the "Your Settings" page.
            'key' => 'keygoeshere',
        ],
    ],

    // Your services.
    'services' => [

        'web' => [ // Name of the service.

            // Currently only digitalocean is supported.
            'hostProvider' => 'digitalocean',

            // Currently only cloudflare is supported.
            'dnsProvider' => 'cloudflare',

            /**
             * Default values for DNS records.
             * Should contain 'ttl' and any other provider specific values.
             */
            'dnsDefaults' => [
                'ttl' => 1,
                'proxied' => true
            ],

            /**
             * After creating a host, create these DNS records pointing to it.
             * %d in the string will be replaced with the instance number.
             * Array should be in the format:
             * 'domain1.com' => [
             *      'subdomain' => [
             *          // Settings for this record
             *      ],
             *      'subdomain%d' => [
             *          // Settings for this record
             *      ]
             * ],
             * 'domain2.com' => [
             *      'server%d'  => [
             *          // Settings for this record
             *      ]
             * ]
             *
             * The settings can contain any provider-specific options
             */
            'dnsRecords' => [
                'themediadudes.com' => [
                    'www' => [
                        'proxied' => true, // Accelerate with CloudFlare
                        'ttl' => 1 // 1 = 'auto' on CloudFlare
                    ],
                    'web%d' => [
                        'proxied' => false,
                        'ttl' => 1
                    ]
                ]
            ],

            // Minimum number of hosts to keep running.
            'minHosts' => 1,

            // Maximum number of hosts to create.
            'maxHosts' => 5,

            /**
             * The hostname of new hosts. %d will be replaced with an integer.
             * web1.themediadudes.com
             * web2.themediadudes.com
             * etc.
             */
            'hostnameTemplate' => 'web%d.themediadudes.com',

            // A regular expression used to find hosts for this service among all of the hosts at the provider.
            'hostnamePattern' => '/^web(\d?)\.themediadudes\.com$/',
            /**
             * Array of provider specific information to use when creating a host.
             * For DigitalOcean:
             *      'region', 'size', and 'image'
             */
            'hostDefaults' => [
                'region' => 'nyc3',
                'size' => '512mb',
                'image' => 123456, // Find available images with the `console digitalocean:listimages` command
            ],

            /**
             * If specified, after creating a host it won't be considered ready until the following
             * URL returns a 200 response code. %s in the string will be replaced
             * with the host's first public IP address.
             * Leave blank to disable.
             */
            'testUrl' => 'http://%s/index.php',

            // Any additional headers to set when making the request to the testUrl
            'testUrlHeaders' => [
                'Host' => 'www.deleted.io'
            ],

        ]
    ]

];
