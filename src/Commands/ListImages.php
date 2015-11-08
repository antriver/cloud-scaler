<?php

namespace Tmd\CloudScaler\Commands;

use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tmd\CloudScaler\Providers\Host\DigitalOcean;

/**
 * Command to display the images on DigitalOcean that can be usd.
 *
 * @package Tmd\CloudScaler
 */
class ListImages extends ServiceCommand
{
    /**
     * Set the command name and inputs.
     */
    protected function configure()
    {
        $this->setName('digitalocean:listimages')
            ->setDescription("List all available images on DigitalOcean.");
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
        /** @var DigitalOcean $digitalOceanProvider */
        $digitalOceanProvider = $this->serviceManager->getHostProvider('digitalocean');
        $images = $digitalOceanProvider->getApi()->image()->getAll();

        $table = $this->getHelper('table');
        $table->setHeaders(array('ID', 'Name', 'Created At', 'Distribution', 'Regions'));
        $data = [];
        foreach ($images as $image) {
            $data[] = [
                $image->id,
                $image->name,
                date('r', strtotime($image->createdAt)),
                $image->distribution,
                implode(', ', $image->regions)
            ];
        }
        $table->setRows($data);
        $table->render($output);
    }
}
