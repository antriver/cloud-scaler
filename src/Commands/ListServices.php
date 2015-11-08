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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to display all the configured services.
 *
 * @package Tmd\CloudScaler
 */
class ListServices extends ServiceCommand
{
    /**
     * Set the command name and inputs.
     */
    protected function configure()
    {
        $this->setName('listservices')
            ->setDescription("List all configured services.");
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
        $services = $this->serviceManager->getAllServices();

        $table = $this->getHelper('table');
        $table->setHeaders(array('Service', 'DNS Provider', 'Host Provider'));
        $rows = [];

        foreach ($services as $service) {
            $rows[] = [
                $service->name,
                $service->getDnsProvider()->getName(),
                $service->getHostProvider()->getName()
            ];
        }

        $table->setRows($rows);
        $table->render($output);
    }
}
