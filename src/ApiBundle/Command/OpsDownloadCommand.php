<?php

namespace ApiBundle\Command;

use ApiBundle\Entity\Balance;
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
            ->setDescription('test command')
            ->addOption('p', null, InputOption::VALUE_REQUIRED, 'percent')
            ->addOption('mc', null, InputOption::VALUE_REQUIRED, 'min cost')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $logger = $this->getContainer()->get('monolog.logger.inputInfo');

            try {
                $percent = $input->getOption("p");
                $min_cost = $input->getOption("mc");
                $ops_helper = $this->getContainer()->get("api.ops.helper");
                $new_balance = $ops_helper->getBalance();


                $em = $this
                    ->getContainer()
                    ->get("doctrine")
                    ->getManager();

                $balance = $this
                    ->getContainer()
                    ->get("doctrine")
                    ->getRepository("ApiBundle:Balance")
                    ->findOneBy(
                        array(
                            "apiKey" => $this->getContainer()->getParameter("ops_api_key"),
                        )
                    );

                $ops_helper
                    ->bot(
                        "Я стартанул, мой баланс " . $new_balance . ". И процент " . $percent . ' минимальная стоимость: ' . $min_cost,
                        "-295278868"
                    );

                if (empty($balance)) {
                    $balance = new Balance();
                    $balance->setApiKey($this->getContainer()->getParameter("ops_api_key"));
                    $balance->setBalance($new_balance * 100);
                    $em->persist($balance);
                    $em->flush();
                } else {
                    $balance->setBalance($new_balance * 100);
                    $em->persist($balance);
                    $em->flush();
                }

                $return = $ops_helper->socketConnection($percent, $min_cost);
                $ops_helper->bot("Лол походу мне пизда: " . $return, "-295278868");
            } catch (\Exception $e) {
                $output_arr = array(
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine(),
                );
                $logger->error(json_encode($output_arr));
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

}
