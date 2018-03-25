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

                $log = new Logger("Ops Trade Command");
                $log->pushHandler(new LogglyHandler('1827242c-b940-423b-ab53-cf4fc8a77d2a', Logger::INFO));


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
                    $item = $ops_helper->searchItem($cost / 100, 1, $name, "730_2");
                    if (!empty($item['response']['sales'])) {
                        $output_info_about_trade = $ops_helper->opsByeItem($item['response']);

                        if (json_decode($output_info_about_trade["status"]) == 2002) {
                            sleep(240);
                            $output_info_about_trade = $ops_helper->opsByeItem($item['response']);
                        }

                    } else {
                        $output_info_about_trade = "so slow";
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
