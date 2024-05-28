<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 9/19/16
 * Time: 12:09 PM
 */
namespace AppBundle\Command;
use AppBundle\Entity\State;
use Symfony\Component\Config\FileLocator;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;


/**
 * Class StateCommand
 * @package AppBundle\Command
 */
class NotificationSwitchCommand extends ContainerAwareCommand
{

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('lv:notify:switches')
            ->setDescription('Set default notification switches')
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
        $output->writeln("<info>Starting Process</info>");

        // get entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();


        // get all users
        $users = $em->getRepository("LBUserBundle:User")->findAll();


        // loop for states
        foreach ($users as $user){
            $user->setNotificationFavoriteSwitch(true);
            $user->setNotificationLikeSwitch(true);
            $user->setNotificationMessagesSwitch(true);
            $user->setNotificationViewsSwitch(true);
            $em->persist($user);
        }

        $em->flush();

        $output->writeln("<info>Success </info>");

    }
}
