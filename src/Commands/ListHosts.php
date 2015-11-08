<?php

namespace Tmd\CloudScaler\Commands;

use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
            ->addArgument('service', InputArgument::OPTIONAL, 'Only show hosts for this service.');
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
        $table->setHeaders(array('Service', 'Instance', 'Hostname', 'IP', 'State'));
        $rows = [];

        foreach ($hosts as $host) {
            $ipStrings = $host->getPublicIpStrings();

            $rows[] = [
                $host->getService()->name,
                $host->instance,
                $host->getHostname(),
                implode(', ', $ipStrings),
                $host->getState()
            ];
        }

        $table->setRows($rows);
        $table->render($output);
    }
}
