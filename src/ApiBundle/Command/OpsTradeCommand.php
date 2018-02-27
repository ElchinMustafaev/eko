<?php

namespace ApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OpsTradeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ops:trade')
            ->setDescription('bye items')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $ops_helper = $this->getContainer()->get("api.ops.helper");

            $record_from_db = $ops_helper->getInfoToBye();

            while (!empty($record_from_db)) {
                $item = $ops_helper->searchItem($record_from_db["cost"], 2, $record_from_db['name'], "730_2");
                $output->writeln('remove: ' . $record_from_db['name'] . ' - ' . $ops_helper->removeRecord($record_from_db));
                if (!empty($item['response']['sales'])) {
                    $output->writeln($ops_helper->opsByeItem($item['response']));
                }

                $record_from_db = $ops_helper->getInfoToBye();
            }
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
    }

}
