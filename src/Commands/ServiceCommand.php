<?php

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
