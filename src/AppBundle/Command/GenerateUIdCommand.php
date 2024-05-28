<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 11/9/16
 * Time: 4:31 PM
 */

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class GenerateUIdCommand
 * @package AppBundle\Command
 */
class GenerateUIdCommand extends ContainerAwareCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('lv:uid:generate')
            ->setDescription('generate uid for users')
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
        $output->writeln("<info>Starting to create users</info>");

        // get entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();
        $filters = $em->getFilters();
        $filters->isEnabled("user_deactivate_filter") ?  $filters->disable("user_deactivate_filter") : null;





        $uIds = [];

        $appService = $this->getContainer()->get('app.luvbyrd.service');
        $start = 0;
        $count = 0;

        while ($users = $this->getUsers($start)){

            foreach($users as $key => $user){

                // check uid
                if(!$user->getUId()){

                    do {
                        $uid = $appService->generateUId();
                        $isUser = in_array($uid, $uIds);
                    } while ($isUser);

                    $user->setUId($uid);
                    $uIds[] = $uid;
                    $em->persist($user);
                    $output->writeln("<info>$count</info>");
                }
                $count ++ ;
            }
            $start = $start+100;
            $em->flush();
        }
        $output->writeln("<info>Success creating users</info>");

        return 1;

    }

    /**
     * @param $start
     * @return mixed
     */
    private function getUsers($start)
    {
        // get entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();
        $filters = $em->getFilters();
        $filters->isEnabled("user_deactivate_filter") ?  $filters->disable("user_deactivate_filter") : null;

        $count = 100;
        // get users
        $users = $em->createQuery(" SELECT u
                            FROM LBUserBundle:User u
                            ")
            ->setFirstResult($start)
            ->setMaxResults($count)
            ->getResult()
        ;

        return count($users) > 0 ? $users : null;
    }
}