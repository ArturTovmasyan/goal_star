<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/5/16
 * Time: 3:41 PM
 */

namespace AppBundle\Command;
use LB\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class UpdateLookingForCommand extends ContainerAwareCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('lv:update:lookingFor')
            ->setDescription('Send message to all users')
            ->setDefinition(array(
                new InputArgument('type', InputArgument::REQUIRED, 'Type of command | 1 for lookingForTemp | 2 for lookingForTemp')
            ));
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
        $output->writeln("<info>Starting to send message</info>");

        $type = $input->getArgument('type');

        if($type == 1){
            $this->addToLookingForTemp();
        }

        if($type == 2){
            $this->addToLookingFor();
        }
        $output->writeln("<info>Success sending message</info>");
    }

    private function addToLookingForTemp()
    {
        // get entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();

        $filter = $em->getFilters();
        $filter->isEnabled('user_deactivate_filter') ? $filter->disable('user_deactivate_filter') : null;

        $users = $em->getRepository('LBUserBundle:User')->findAll();

        foreach ($users as $user){

            $lookingForArray = $user->getLookingFor();

            if(is_array($lookingForArray) && count($lookingForArray) > 0){

                $lookingFor = count($lookingForArray) > 1 ? User::BISEXUAL : reset($lookingForArray);
            }
            else{
                $lookingFor = User::BISEXUAL;
            }

            $user->setLookingForTemp($lookingFor);
            $em->persist($user);
        }

        $em->flush();
    }

    private function addToLookingFor()
    {
        // get entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();

        $filter = $em->getFilters();
        $filter->isEnabled('user_deactivate_filter') ? $filter->disable('user_deactivate_filter') : null;

        $users = $em->getRepository('LBUserBundle:User')->findAll();

        foreach ($users as $user){

            $lookingForArray = $user->getLookingForTemp();

            $user->setLookingFor($lookingForArray);
            $em->persist($user);
        }

        $em->flush();
    }
}