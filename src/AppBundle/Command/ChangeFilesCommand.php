<?php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ChangeFilesCommand
 * @package AppBundle\Command
 */
class ChangeFilesCommand extends ContainerAwareCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('lv:change:files')
            ->setDescription('Change files and folder')
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

        // get users
        $users = $em->createQuery(" SELECT u
                            FROM LBUserBundle:User u
                            ")
            ->getResult()
        ;

        $cnt = count($users);

        $output->writeln("<info>count - $cnt</info>");

        if($users){ // check user
            foreach($users as $key => $user){ // loop for users

                $output->writeln("<info>$key</info>");

                $uid = $user->getUId(); // get user uid
                $files = $user->getFiles(); // get user files
                if($files){ // check files
                    foreach ($files as $file){ // loop files

                        $fileName = $file->getName(); // get file name

                        $oldFolder = $file->getDirForClone(); // get files
                        $newFolder = $file->getUploadRootDirForClone() . '/' . $uid ;

                        // check folder and create
                        if(!file_exists($newFolder)){
                            mkdir($newFolder, 0777, true);
                        }

                        $oldFile = $oldFolder . '/' . $fileName;
                        $newFile = $newFolder . '/' . $fileName;

                        if(file_exists($oldFile)){
                            rename($oldFile, $newFile); // move folder
                        }

                        $path = $uid . '/' . $fileName;
                        $file->setPath($path);
                        $em->persist($file);
                        $this->checkFolder($oldFolder);

                    }

                    if($key % 100 ==  0){
                        $em->flush();
                    }
                }
            }
            $em->flush();
        }

        $output->writeln("<info>Success creating users</info>");

        return 1;

    }

    /**
     * @param $path
     */
    private function checkFolder($path)
    {
        // check path
        if(strpos($path, '/web/uploads2') === false){
            return;
        }

        $paths = explode('/', $path);
        if(end($paths) == 'Images'){
            $imagesLink = implode('/', $paths);
            $this->removeFolder($imagesLink);
        }


        unset($paths[count($paths) - 1]);
        if(end($paths) != 'uploads2'){
            $usernameLink = implode('/', $paths);
            $this->removeFolder($usernameLink);
        }
    }

    /**
     * @param $path
     */
    private function removeFolder($path)
    {
        // check is dir empty
        if(is_dir($path) && $this->isDirEmpty($path)){
            rmdir($path);
        }
    }



    /**
     * @param $dir
     * @return bool|null
     */
    function isDirEmpty($dir)
    {
        if (!is_readable($dir)) return NULL;
        $handle = opendir($dir);
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * @param $file
     */
    private function clearCache($file)
    {
        // get li ip bundle configs
        $filterConfigurations = $this->getContainer()->get( 'liip_imagine.filter.configuration' );

        // get filters
        $filters = $filterConfigurations->all();
        // get filtar names
        $filters = array_keys($filters);

        $folder = $file->getWebPathFromCLI();

        // get cache manager
        $cacheManager = $this->getContainer()->get('liip_imagine.cache.manager');

        // clear file from cache
        $cacheManager->remove($file->getUploadDir(). '/' . $file->getPath(), $filters);

        $this->removeCacheFolder($filters, $folder);
    }

    /**
     * @param $filters
     * @param $folder
     */
    private function removeCacheFolder($filters, $folder)
    {
        $rootDir = $this->getContainer()->get('kernel')->getRootDir();
        $rootDir = $rootDir . '/../web/media/cache/';

        foreach ($filters as $filter){

            // get dur of cache
            $dir = $rootDir . $filter . $folder;

            if(file_exists($dir) && is_dir($dir) && $this->isDirEmpty($dir)){
                $this->remove($dir, $filter);
            }

        }
    }

    /**
     * @param $dir
     * @param $filter
     */
    private function remove($dir, $filter)
    {
        $paths = explode('/', $dir);
        if(end($paths) !== $filter){
            // check is dir empty
            if(file_exists($dir) && is_dir($dir) && $this->isDirEmpty($dir)){
                rmdir($dir);
                unset($paths[count($paths) - 1]);
                $newDir = implode('/', $paths);
                $this->remove($newDir, $filter);
            }
        }
    }

    /**
     * @param $path
     */
    public function generateCacheImages($path)
    {
        $filters = ['members', 'mobile_list', 'profile', 'box'];

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