<?php

namespace ApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OpsDownloadCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ops:test')
            ->setDescription('Download Skins Base')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $ops_helper = $this->getContainer()->get("api.ops.helper");
            print_r($ops_helper->socketConnection());
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            $output->writeln($e->getFile());
            $output->writeln($e->getLine());
        }
    }

}
