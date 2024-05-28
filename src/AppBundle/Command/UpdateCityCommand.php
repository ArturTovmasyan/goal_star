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
 * Class UpdateCityCommand
 * @package AppBundle\Command
 */
class UpdateCityCommand extends ContainerAwareCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('lv:city:update')
            ->setDescription('Update city')
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

        $cities = array();

        // get entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();

        $filter = $em->getFilters();
        $filter->isEnabled('user_deactivate_filter') ? $filter->disable('user_deactivate_filter') : null;

        $defaultCity = array(
            'address' => "Denver, CO, USA",
            'loc' => array(
                'lat' => 39.7392358,
                'lng' => -104.990251
            ));


        // get users
        $users = $em->getRepository('LBUserBundle:User')->findAll();

        $count = count($users);
        $output->writeln("<info>count is $count</info>");

        if($users){

            $lvService = $this->getContainer()->get('app.luvbyrd.service');

            foreach($users as $key => $user){

                $output->writeln("<info>$key</info>");

                // get city
                $city = $user->getCity();

                if($city){

                    // check is already city exist
                    if(array_key_exists(strtolower($city), $cities)){
                        $defaultCity = $cities[strtolower($city)];
                    }else{
                        $data = $lvService->getLocationByCityName($city);

                        sleep(1);

                        if(is_array($data)){
                            $defaultCity = $data;
                            $cityName = strtolower($data['address']);
                            $cities[$cityName] = $defaultCity;
                        }
                    }
                }

                $user->setCity($defaultCity['address']);
                $user->setCityLng($defaultCity['loc']['lng']);
                $user->setCityLat($defaultCity['loc']['lat']);


                if($key % 100 == 0){
                    $em->flush();
                }
            }

            $em->flush();
        }

        $output->writeln("<info>Success updating users</info>");

    }
}