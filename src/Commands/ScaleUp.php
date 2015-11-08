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
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to add one host to a service.
 *
 * @package Tmd\CloudScaler
 */
class ScaleUp extends ServiceCommand
{
    protected function configure()
    {
        $this->setName('scaleup')
            ->setDescription("Add another host for a service.")
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

        $output->writeln("");

        $progress = new ProgressBar($output, 5);
        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progress->setMessage('Checking existing hosts');
        $progress->start();

        $currentHostCount = count($hostProvider->getHostsForService($service));

        if ($service->maxHosts && $currentHostCount >= $service->maxHosts) {
            throw new Exception(
                "There are already {$currentHostCount}/{$service->maxHosts} hosts for {$serviceName}."
            );
        }

        $newInstance = $currentHostCount + 1;
        $hostname = sprintf($service->hostnameTemplate, $newInstance);
        $host = $hostProvider->launch($hostname, $service->hostDefaults);
        $hostname = $host->getHostname(); // Just check it set the right name

        $progress->setMessage("Created host " . $hostname . " at " . $hostProvider->getName());
        $progress->advance();
        sleep(5);

        $progress->setMessage("Waiting for " . $hostname . " to be ready");
        $progress->advance();

        while (!$host->isReady()) {
            $lastState = $host->getState();
            $progress->setMessage(
                "Waiting for " . $hostname . " to be ready (Current sate: " . $lastState . ")"
            );
            $progress->display();
            sleep(10);
        }

        if (!empty($service->testUrl)) {
            $progress->setMessage("Testing host's HTTP response");
            $progress->advance();

            do {
                $lastResponse = $host->testHttp($service->testUrl, $service->testUrlHeaders);
                $progress->setMessage("Testing host's HTTP response (Current response: $lastResponse)");
                $progress->display();
                $lastResponse === 200 || sleep(5);

            } while ($lastResponse !== 200);
        }

        $dnsProvider = $service->getDnsProvider();
        $recordData = [];
        foreach ($host->publicIps as $ip) {
            foreach ($service->dnsRecords as $domain => $domainRecords) {
                foreach ($domainRecords as $record) {
                    $data = [
                        'domain' => $domain,
                        'type' => $ip->version === 6 ? 'AAAA' : 'A',
                        'name' => sprintf($record, $newInstance),
                        'value' => $ip->ip,
                    ];
                    $recordData[] = $data + $service->dnsDefaults;
                }
            }
        }

        $progress->setMessage("Adding " . count($recordData) . " DNS records to " . $dnsProvider->getName());
        $progress->advance();
        $dnsProvider->addRecords($recordData);

        $progress->setMessage('Done!');
        $progress->finish();

        $output->writeln("");
        $output->writeln("");

        $this->getApplication()->find('listhosts')->run(new ArrayInput(['service' => $serviceName]), $output);
    }
}
