<?php

/*
 * This file is part of the CloudScaler package.
 *
 * (c) Anthony Kuske <www.anthonykuske.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tmd\CloudScaler\Commands;

use Symfony\Component\Console\Command\Command;
use Tmd\CloudScaler\ServiceManager;

/**
 * Base for commands.
 *
 * @package Tmd\CloudScaler
 */
abstract class ServiceCommand extends Command
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @param ServiceManager $serviceManager
     */
    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        parent::__construct();
    }
}
