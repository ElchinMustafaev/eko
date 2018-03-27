<?php

namespace ApiBundle\Command;

use Monolog\Handler\LogglyHandler;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CsgobackTradeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('csgoback:trade')
            ->setDescription('...')
            ->addOption('min_cost', null, InputOption::VALUE_REQUIRED, 'min cost')
            ->addOption('max_cost', null, InputOption::VALUE_REQUIRED, 'max cost')
            ->addOption('percent', null, InputOption::VALUE_REQUIRED, 'percent')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $log = new Logger("CsGoBack");
            $log->pushHandler(new LogglyHandler('1827242c-b940-423b-ab53-cf4fc8a77d2a', Logger::INFO));

            $min_cost = $input->getOption('min_cost');
            $max_cost = $input->getOption('max_cost');
            $percent = $input->getOption('percent');

            $ops_helper = $this->getContainer()->get("api.ops.helper");
            $i = 0;
            $ops_helper->bot('Я стартанул и мой баланс сейчас ' . $max_cost, "-1001184076461");
            while (true) {
                $start_time = time();
                $result = $ops_helper->getTableFromCsGoBack();
                foreach ($result['result'] as $value) {
                    $csgoback_result = $ops_helper->equalPriceCsGoBack($value, $max_cost, $min_cost, $percent);
                    print_r($csgoback_result);
                    if ($csgoback_result != null) {
                        $item = $ops_helper->searchItem($csgoback_result['ops.cost'], 2, $csgoback_result['name'], "730_2");
                        $output->writeln("==========================================\n");
                        $log->addInfo("item find");
                        $output->writeln("==========================================\n");
                        if (!empty($item['response']['sales'])) {
                            $output->writeln("--------------------------------------\n");
                            $log->addInfo(json_encode(array(
                                        $ops_helper->opsByeItem($item['response']),
                                        "cs_buy",
                                    )
                                )
                            );
                            $output->writeln("--------------------------------------\n");
                            $max_cost = $ops_helper->getBalance();
                            //$ops_helper->bot('Хей парни, я купил вот эту шмотку ' . $csgoback_result['name'], "-1001184076461");
                            //$ops_helper->bot('И у меня осталось ' . $max_cost, "-1001184076461");
                            //sleep(1);
                        } else {
                            $output->writeln("--------------------------------------\n");
                            $output->writeln('SO SLOW');
                            $output->writeln("--------------------------------------\n");
                        }
                    }
                }
                print_r((time() - $start_time) / 60 . "\n");
                print_r(count($result['result']) . "\n");
                $i++;
                if ($i == 10) {
                    $old_cost = $max_cost;
                    $max_cost = $ops_helper->getBalance();
                    $i = 0;
                    if ($old_cost != $max_cost) {
                        $ops_helper->bot('Мой баланс сейчас ' . $max_cost, "-1001184076461");
                    }
                }
                print_r($max_cost . "\n");
                sleep(10);
            }
        } catch (\Exception $e) {

        }
    }

}
