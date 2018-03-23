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
            ->setDescription('test command')
            ->addOption('p', null, InputOption::VALUE_REQUIRED, 'command')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $percent = $input->getOption("p");
            $ops_helper = $this->getContainer()->get("api.ops.helper");
            $ops_helper->bot("Я стартанул, мой баланс " . $ops_helper->getBalance() . ". И процент " . $percent, "-295278868");
            $return = $ops_helper->socketConnection($percent);
            $ops_helper->bot("Лол походу мне пизда: " . $return, "-295278868");
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            $output->writeln($e->getFile());
            $output->writeln($e->getLine());
        }
    }

}
