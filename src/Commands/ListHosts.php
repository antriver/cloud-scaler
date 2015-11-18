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

use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tmd\CloudScaler\Models\Host;

/**
 * Command to display all hosts that exist at the host providers that belong to any of the services.
 *
 * @package Tmd\CloudScaler
 */
class ListHosts extends ServiceCommand
{
    /**
     * Set the command name and inputs.
     */
    protected function configure()
    {
        $this->setName('listhosts')
            ->setDescription("List all hosts that exist at the provider for a service.")
            ->addArgument('service', InputArgument::OPTIONAL, 'Only show hosts for this service.')
            ->addOption('load', null, InputOption::VALUE_NONE, 'Display the load average of hosts.');
    }

    /**
     * Run the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $hosts = [];

        if ($serviceName = $input->getArgument('service')) {
            $service = $this->serviceManager->getService($serviceName);
            $hosts = array_merge($hosts, $service->getHostProvider()->getHostsForService($service));
        } else {
            $services = $this->serviceManager->getAllServices();
            foreach ($services as $service) {
                /** @var Host[] $hosts */
                $hosts = array_merge($hosts, $service->getHostProvider()->getHostsForService($service));
            }
        }

        $table = $this->getHelper('table');
        $cols = array('Service', 'Instance', 'Hostname', 'IP', 'State');

        if ($showLoad = $input->getOption('load')) {
            $cols[] = 'Load';
        }

        $table->setHeaders($cols);
        $rows = [];

        foreach ($hosts as $host) {
            $ipStrings = $host->getPublicIpStrings();

            $row = [
                $host->getService()->name,
                $host->instance,
                $host->getHostname(),
                implode(', ', $ipStrings),
                $host->getState()
            ];

            if ($showLoad) {
                $row[] = implode(', ', $host->getLoad());
            }

            $rows[] = $row;
        }

        $table->setRows($rows);
        $table->render($output);
    }
}
