<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 1/21/16
 * Time: 6:26 PM
 */

namespace AppBundle\Command;

use Buzz\Browser;
use LB\UserBundle\Entity\File;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Client;

/**
 * Class UpdateUserCommand
 * @package AppBundle\Command
 */
class NoImagesCommand extends ContainerAwareCommand
{

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('lv:no:Images')
            ->setDescription('Get images name')
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
        $files = $em->getRepository("LBUserBundle:File")->findAll();

        // get users
        $files = $em->createQuery(" SELECT f
                            FROM LBUserBundle:File f
                            JOIN f.user u
                            ")
            ->getResult()
        ;

        // check users
        if($files){

            $filePath = __DIR__ . '/Files/physicallyImage.txt';

            // create file
            $myFile = fopen($filePath, "w");

            // loop for users
            foreach($files as $key => $file){

                // check path
                $path =$file->getAbsolutePath();

                // check is file exist
                if(!file_exists($path)){

                    $file->getUser() ? $userId = $file->getUser()->getId() : $userId = null;
                    $file->getUser() ? $oldId = $file->getUser()->getOldId() : $oldId = null;
                    $file->getUser() ? $uiId = $file->getUser()->getUId() : $uiId = null;
                    $file->getUser() ? $username = $file->getUser()->getUsername() : $username = null;

                    $clientName = $file->getClientName();
                    $name = $file->getName();
                    $filePath= $file->getPath();

                    $txt = "userId = $userId \n";
                    $txt .= "oldId = $oldId \n";
                    $txt .= "uid = $uiId \n";
                    $txt .= "clientName = $clientName \n";
                    $txt .= "name = $name \n";
                    $txt .= "path = $filePath \n";
                    $txt .= "username = $username \n";

                    fwrite($myFile, $txt);
                    $txt = "============================================================================================\n";
                    fwrite($myFile, $txt);

                }

                $output->writeln("<info>$key</info>");
            }

            fclose($myFile);
        }

        $output->writeln("<info>Success creating users</info>");

    }
}