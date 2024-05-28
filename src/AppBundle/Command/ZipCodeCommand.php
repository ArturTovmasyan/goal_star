<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/7/16
 * Time: 5:01 PM
 */

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ZipCodeCommand
 * @package AppBundle\Command
 */
class ZipCodeCommand extends ContainerAwareCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('lv:zip-code:update')
            ->setDescription('Update zip code')
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
        $output->writeln("<info>Starting to update users</info>");

        // get entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();

        $filter = $em->getFilters();
        $filter->isEnabled('user_deactivate_filter') ? $filter->disable('user_deactivate_filter') : null;


        // get users
        $users = $em->getRepository('LBUserBundle:User')->findUserWithoutZip();
//        $users = $em->getRepository('LBUserBundle:User')->findById(3);

        $count = count($users);
        $output->writeln("<info>count is $count</info>");

        if($users){

            $lvService = $this->getContainer()->get('app.luvbyrd.service');

            foreach($users as $key => $user){

                $output->writeln("<info>$key</info>");

                if(!$user->getZip()){
                    $zipCode = $user->getZipCode();
                    $output->writeln("<info>Zip code $zipCode</info>");

                    if($zipCode){

                        $zipObject = $lvService->getZipObjByZipCode($zipCode);

                        sleep(1);

                        if($zipObject){
                            $user->setZip($zipObject);
                            $em->persist($user);
                        }
                    }
                }

                if($key % 100 == 0){
                    $em->flush();
                }
            }

            $em->flush();

        }

        $output->writeln("<info>Success updating users</info>");

    }
}