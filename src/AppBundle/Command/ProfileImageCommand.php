<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 7/27/16
 * Time: 2:01 PM
 */

namespace AppBundle\Command;

use LB\UserBundle\Entity\File;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ProfileImageCommand
 * @package AppBundle\Command
 */
class ProfileImageCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('lv:set:profile-image')
            ->setDescription('set profile image')
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

        // get users
        $users = $em->getRepository("LBUserBundle:User")->findAllWithProfileImage();

        $count = count($users);

        $output->writeln("<info>Counts of files $count</info>");

        // check users
        if($users){

            // loop for users
            foreach($users as $key => $user){

                $profileImage = $user->getProfileImage();
                if(!$profileImage && !$user->getSocialPhotoLink()){

                    // check file counts
                    $files = $user->getFiles();

                    // check files count
                    if(count($files) > 0){

                        $file = $files->first();

                        if($file instanceof File){
                            $user->setProfileImage($file);
                            $em->persist($file);
                            $em->persist($user);
                        }
                    }

                }
                $output->writeln("<info>$key</info>");
            }

            $em->flush();
        }

        $output->writeln("<info>Success creating users</info>");

    }
}