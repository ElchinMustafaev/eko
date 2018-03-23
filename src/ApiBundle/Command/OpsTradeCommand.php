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
            $logger = $this->getContainer()->get('monolog.logger.trade');

            try {
                $cost = $input->getOption("cost");
                $name = $input->getOption("name");
                $id = $input->getOption("id");
                $percent = $input->getOption('p');

                $ops_helper = $this->getContainer()->get("api.ops.helper");

                $return = $ops_helper->getInfoFromCsGoBack($name, "");
                $return = $return['result'];
                $output_info_about_trade = "false";
                $equal_price = $ops_helper->EqualPrice($name, $cost, $return, $percent);
            if ($equal_price) {
                $output = $ops_helper->opsByeItem_v2($id, $cost);
                $output_info_about_trade = $output;
            }
            $log_array = array(
                "cost" => $cost,
                "name" => $name,
                "id" => $id,
                "percent" => $percent,
                "equal price" => $equal_price,
                "output info" => $output_info_about_trade,
            );
            $logger->info(json_encode($log_array));

            } catch (\Exception $e) {
                $logger->error(json_encode(
                        array(
                            $e->getMessage(),
                            $e->getFile(),
                            $e->getLine()
                        )
                    )
                );
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
