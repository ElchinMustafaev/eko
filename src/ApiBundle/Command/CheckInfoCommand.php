<?php

namespace ApiBundle\Command;

use ApiBundle\Entity\AllInfo;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckInfoCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('check:info')
            ->setDescription('...')
            ->addOption('min_cost', null, InputOption::VALUE_REQUIRED, 'min cost')
            ->addOption('max_cost', null, InputOption::VALUE_REQUIRED, 'max cost')
            ->addOption('min_q', null, InputOption::VALUE_REQUIRED, 'min quantity')
            ->addOption('max_q', null, InputOption::VALUE_REQUIRED, 'max quantity')
            ->addOption('p', null, InputOption::VALUE_REQUIRED, 'percent')

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $min_c = $input->getOption('min_cost');
            $max_c = $input->getOption('max_cost');
            $min_q = $input->getOption('min_q');
            $max_q = $input->getOption('max_q');
            $percent = $input->getOption('p');

            $ops_helper = $this->getContainer()->get("api.ops.helper");

            $return_from_db = $ops_helper->getInfoFromDb($min_c, $max_c, $min_q, $max_q);

            $em = $this->getContainer()->get('doctrine')->getManager();
            $i = 0;
            foreach ($return_from_db as $key => $value) {
                $return = $ops_helper->getInfoFromCsGoBack($value['name'], "");
                $return = $return['result'];
                $info = $ops_helper->EqualPrice($value, $return, $percent);
                $i++;
                if (!empty($info)) {
                    $all_info = new AllInfo();

                    $all_info->setTime(time());
                    $all_info->setName($info['name']);
                    $all_info->setCost($info['cost']);
                    $all_info->setPercent($percent);

                    $em->persist($all_info);
                    $em->flush();
                }
            }
            $output->writeln('search ' . $i . 'min_cost = ' . $min_c . ' max_cost = ' . $max_c . "\n");
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            $output->writeln($e->getFile());
            $output->writeln($e->getLine());
        }
    }

}
