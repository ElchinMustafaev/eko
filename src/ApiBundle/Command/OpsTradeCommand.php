<?php

namespace ApiBundle\Command;

use Monolog\Handler\LogglyHandler;
use Monolog\Logger;
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
                $time_start = microtime(true);
                $cost = $input->getOption("cost");
                $name = urldecode($input->getOption("name"));
                $id = $input->getOption("id");
                $percent = $input->getOption('p');

                $ops_helper = $this->getContainer()->get("api.ops.helper");

                $return = $ops_helper->getInfoFromCsGoBack($name, "");
                $return = $return['result'];
                $output_info_about_trade = "false";
                $tag = "don't buy";
                $equal_price = $ops_helper->EqualPrice($name, $cost, $return, $percent);
            if ($equal_price) {
                $balance = $this
                    ->getContainer()
                    ->get("doctrine")
                    ->getRepository("ApiBundle:Balance")
                    ->findOneBy(
                        array(
                            "apiKey" => $this->getContainer()->getParameter("ops_api_key")
                        )
                    );
                if ($balance->getBalance() >= $cost) {
                    $output = $ops_helper->opsByeItem_v2($id, $cost);
                    $output_info_about_trade = $output;
                    $output = json_decode($output, 1);
                    if ($output["status"] == 2002) {
                        sleep(240);
                        $output = $ops_helper->opsByeItem_v2($id, $cost);
                        $output_info_about_trade = $output;
                    }

                    $balance->setBalance($ops_helper->getBalance() * 100);

                    $em = $this
                        ->getContainer()
                        ->get("doctrine")
                        ->getManager();
                    $em->persist($balance);
                    $em->flush();
                    $tag = "buy";
                } else {
                    $output_info_about_trade = "Not enough money";
                }
            }
            $log_array = array(
                "cost" => $cost,
                "name" => $name,
                "id" => $id,
                "percent" => $percent,
                "equal price" => $equal_price,
                "output info" => $output_info_about_trade,
                "tag" => $tag,
            );
            $logger->info(json_encode($log_array));

            $log = new Logger("Ops Trade Command");
            $log->pushHandler(new LogglyHandler('c08914a4-b0a9-469e-afad-b1443759875b', Logger::INFO));

            $log->addInfo(json_encode($log_array));

            $time_array = array(
                "masage" => microtime(true) - $time_start,
                "tag" => "time",
            );

            $log->addInfo(json_encode($time_array));
            return "skin with id: " . $id . " processed";
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
