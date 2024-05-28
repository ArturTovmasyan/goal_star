<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 12/26/16
 * Time: 6:10 PM
 */

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GenerateCachCommand
 * @package AppBundle\Command
 */
class GenerateCachCommand extends ContainerAwareCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('lv:cache:generate')
            ->setDescription('Generate some caches')
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
        $users = $em->createQuery(" SELECT u, f
                            FROM LBUserBundle:User u
                            JOIN u.files as f
                            ORDER BY u.lastActivity DESC
                            ")
            ->getResult()
        ;

        $cnt = count($users);

        $output->writeln("<info>count - $cnt</info>");

        if($users){ // check user
            foreach($users as $key => $user){ // loop for users

                $output->writeln("<info>$key</info>");

                $file = $user->getProfileImage(); // get user files
                if($file){ // check files
                    $path = $file->getUploadDir() . '/' . $file->getPath();
                    $this->generateCacheImages($path);
                }
            }
        }

        $output->writeln("<info>Success creating users</info>");

        return 1;

    }


    /**
     * @param $path
     */
    public function generateCacheImages($path)
    {
        $filters = ['members', 'mobile_list', 'profile', 'box', 'mobile'];

        // get container
        $container = $this->getContainer();

        // get liip cache manager
        $cacheManager = $container->get('liip_imagine.cache.manager');

        // get liip filter manager
        $filterManager = $container->get('liip_imagine.filter.manager');

        // get data manager
        $dataManager = $container->get('liip_imagine.data.manager');

        // loop for configs
        foreach($filters as $filter){

            // check has http in path
            if(strpos($path, 'http') === false){

                // try to cache image
                try{

                    // get binary of filter
                    $binary = $dataManager->find($filter, $path);

                    // cache images
                    $cacheManager->store(
                        $filterManager->applyFilter($binary, $filter),
                        $path,
                        $filter);
                }
                catch(\Exception $e){
                    // catch
                }
            }
        }
    }

}