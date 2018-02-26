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
            ->setName('ops:download')
            ->setDescription('Download Skins Base')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $start = time();
            $ops_helper = $this->getContainer()->get("api.ops.helper");
            $return = $ops_helper->downloadOpsLowCost(730);
            $return = $ops_helper->writeInDb($return);
            $time = time() - $start;
            $output->writeln($time / 60);
            $output->writeln(json_encode($return));
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            $output->writeln($e->getFile());
            $output->writeln($e->getLine());
        }
    }

}
