<?php

namespace ApiBundle\Command;

use Monolog\Handler\LogglyHandler;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OpsEqualCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ops:equal')
            ->setDescription('Check percent')
            ->addOption('cost', null, InputOption::VALUE_REQUIRED, 'cost')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'name')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'item id')
            ->addOption('p', null, InputOption::VALUE_REQUIRED, 'percent')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $log = new Logger("Node_equal");
            $log->pushHandler(new LogglyHandler('1827242c-b940-423b-ab53-cf4fc8a77d2a', Logger::INFO));

            try {

                $ops_helper = $this->getContainer()->get("api.ops.helper");

                $cost = $input->getOption("cost");
                $name = urldecode($input->getOption("name"));
                $id = $input->getOption("id");
                $percent = $input->getOption('p');

                $return = $ops_helper->getInfoFromCsGoBack($name, "");
                $return = $return['result'];
                $equal_price = $ops_helper->EqualPrice($name, $cost, $return, $percent);
                if ($equal_price) {
                    $log->addInfo(json_encode(
                        array(
                                "equal_price" => $equal_price,
                                "id" => $id,
                            )
                        )
                    );
                    exit($id);
                } else {
                    exit();
                }
            } catch (\Exception $e) {
                $log->addError(
                    json_encode(
                        array(
                            $e->getMessage(),
                            $e->getFile(),
                            $e->getLine(),
                        )
                    )
                );
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

}
