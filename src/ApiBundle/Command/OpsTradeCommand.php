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
            ->addOption('cost', null, InputOption::VALUE_REQUIRED, 'cost')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'name')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'item id')
            ->addOption('p', null, InputOption::VALUE_REQUIRED, 'percent')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $cost = $input->getOption("cost");
            $name = $input->getOption("name");
            $id = $input->getOption("id");
            $percent = $input->getOption('p');

            $ops_helper = $this->getContainer()->get("api.ops.helper");

            $return = $ops_helper->getInfoFromCsGoBack($name, "");
            $return = $return['result'];
            if ($ops_helper->EqualPrice($name, $cost, $return, $percent)) {
                $output = $ops_helper->opsByeItem_v2($id, $cost);
                return $output;
            }

        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
    }
}
