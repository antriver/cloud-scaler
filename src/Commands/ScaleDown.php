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
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to remove one more host from a service.
 *
 * @package Tmd\CloudScaler
 */
class ScaleDown extends ServiceCommand
{
    /**
     * Set the command name and inputs.
     */
    protected function configure()
    {
        $this->setName('scaledown')
            ->setDescription("Remove the latest host from a service.")
            ->addArgument('service', InputArgument::REQUIRED);
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
        $serviceName = $input->getArgument('service');
        $service = $this->serviceManager->getService($serviceName);
        $hostProvider = $service->getHostProvider();

        $this->getApplication()->find('listhosts')->run(new ArrayInput(['service' => $serviceName]), $output);

        $hosts = $hostProvider->getHostsForService($service);
        $currentHostCount = count($hosts);

        if (!$currentHostCount) {
            throw new Exception("There are no {$serviceName} hosts.");
        }

        if ($service->minHosts && $currentHostCount <= $service->minHosts) {
            throw new Exception("There are already the minimum allowed number of {$serviceName} hosts.");
        }

        ksort($hosts);
        /** @var \Tmd\CloudScaler\Models\Host $host */
        $host = array_pop($hosts);
        $hostname = $host->getHostname();
        $ipStrings = $host->getPublicIpStrings();

        $dialog = $this->getHelper('dialog');

        if (!$dialog->askConfirmation(
            $output,
            "<question>Destroy {$hostname} (IP " . implode(', ', $ipStrings) . ")?</question> ",
            false
        )) {
            return;
        }

        // Remove all DNS entries that pointed to this host
        $dnsProvider = $service->getDnsProvider();
        $domains = array_keys($service->dnsRecords);
        $ips = $host->publicIps;

        $output->writeln(
            'Removing DNS records from '
            . implode(' and ', $domains)
            . ' pointing to '
            . implode(' or ', $ipStrings)
            . '...'
        );

        $dnsProvider->deleteRecordsByIps($domains, $ips);

        // Then destroy
        $output->writeln("Destroying {$hostname}...");
        $hostProvider->destroy($host);

        $newCount = $currentHostCount - 1;
        while (count($hostProvider->getHostsForService($service)) > $newCount) {
            sleep(5);
        }
        $output->writeln("Success");

        $this->getApplication()->find('listhosts')->run(new ArrayInput(['service' => $serviceName]), $output);
    }
}
