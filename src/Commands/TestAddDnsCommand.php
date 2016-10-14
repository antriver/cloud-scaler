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
class TestAddDnsCommand extends ServiceCommand
{
    protected function configure()
    {
        $this->setName('testadddns')
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

        $dnsProvider = $service->getDnsProvider();

        $recordData = [
            [
                'domain' => 'deleted.io',
                'type' => 'A',
                'name' => 'test',
                'value' => '127.0.0.1'
            ]
        ];

        $dnsProvider->addRecords($recordData);
    }
}
