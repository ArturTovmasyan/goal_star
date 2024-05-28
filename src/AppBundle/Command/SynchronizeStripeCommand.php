<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 6/27/16
 * Time: 11:41 AM
 */

namespace AppBundle\Command;

use LB\PaymentBundle\Entity\Subscriber;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SynchronizeStripeCommand
 * @package AppBundle\Command
 */
class SynchronizeStripeCommand extends ContainerAwareCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('lv:synchronize:stripe')
            ->setDescription('Synchronize plans with stripe')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // starting
        $output->writeln("<info>Starting ... </info>");


        // get subscriber service
        $stripeService = $this->getContainer()->get('lb.stripe');

        // synchronize plans
//        $stripeService->synchronizePlans();
        $stripeService->addNewPlans();


        $output->writeln("<info>Finish ..</info>");

    }
}