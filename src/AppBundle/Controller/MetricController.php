<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 7/11/16
 * Time: 12:06 PM
 */
namespace AppBundle\Controller;

use LB\PaymentBundle\Entity\Subscriber;
use LB\UserBundle\Entity\User;
use LB\UserBundle\Entity\UserRelation;
use Ob\HighchartsBundle\Highcharts\Highchart;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sonata\AdminBundle\Util\ObjectAclManipulator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class MetricController
 * @package AppBundle\Controller
 */
class MetricController extends Controller
{
    const MONTH = 2;
    const YEAR = 3;
    const DAY = 4;

    /**
     * @param $from
     * @param $to
     * @param $groupBy
     * @param $yearForMonthly
     * @return Highchart
     */
    private function getRelationsData($from, $to, $groupBy, $yearForMonthly)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // check group by
        if($groupBy == self::YEAR){
            $from = null;
            $to = null;
            $yearForMonthly = null;
        }elseif ($groupBy == self::MONTH){
            $from = null;
            $to = null;
        }elseif ($groupBy == self::DAY){
            $yearForMonthly = null;
        }

        // empty data for statistic
        $relationStatistic = $em->getRepository('LBUserBundle:UserRelation')->findForMetric($from, $to, $groupBy, $yearForMonthly);

        $labels = array_keys($relationStatistic);

        $likes = array_map(function($item){
            return array_key_exists('Like', $item) ? (int)$item['Like'] : 0;

        }, $relationStatistic);

        $favorites = array_map(function($item){
            return array_key_exists('Favorite', $item) ? (int)$item['Favorite'] : 0;

        }, $relationStatistic);

        $friends = array_map(function($item){
            return array_key_exists('Friends', $item) ? (int)$item['Friends'] : 0;

        }, $relationStatistic);

        $messages = array_map(function($item){
            return array_key_exists('Messages', $item) ? (int)$item['Messages'] : 0;

        }, $relationStatistic);

        $series = array(
            array(
                'name'  => 'Likes',
                'type'  => 'line',
                'color' => '#3366CC',
                'data'  => array_values($likes),
            ),
            array(
                'name'  => 'Favorites',
                'type'  => 'line',
                'color' => '#DC3912',
                'data'  => array_values($favorites),
            ),
            array(
                'name'  => 'Connections',
                'type'  => 'line',
                'color' => '#FF9900',
                'data'  => array_values($friends),
            ),

            array(
                'name'  => 'Messages',
                'type'  => 'line',
                'color' => '#FF5577',
                'data'  => array_values($messages),
            ),
        );

        $ob = new Highchart();
        $ob->chart->renderTo('relationStatistic'); // The #id of the div where to render the chart
        $ob->title->text('User relation`s statistic');
        $ob->xAxis->categories($labels);
        $ob->series($series);

        return $ob;
    }


    /**
     * @Route(name="relation-statistic")
     * @Security("has_role('ROLE_ADMIN')")
     * @return array
     * @Template()
     * @param $from
     * @param $to
     * @param $select
     * @param $yearForMonthly
     * @return array
     */
    public function relationAction($from, $to, $select, $yearForMonthly)
    {
        // default group by
        $groupBy = $select ? $select : self::YEAR;

        // get all data
        $relationStatistic = $this->getRelationsData($from, $to, $groupBy, $yearForMonthly);

        $selects = array(
            self::DAY => 'Daily',
            self::MONTH => 'Monthly',
            self::YEAR => 'Yearly',
        );

        return array(
            'relationStatistic'  => $relationStatistic,
            'from' => $from, 'to' => $to,
            'groupBy' => $groupBy, 'selects' => $selects,
            'yearForMonthlySelected' => $yearForMonthly
        );
    }



    private function getUsersData($location, $distance, $count)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        $cityLocation = $em->getRepository('LBUserBundle:User')->getCityLatLng($location);
        // empty data for statistic
        $usersData = $em->getRepository('LBUserBundle:User')->findForMetric($location, $cityLocation, $distance);
        $manMessenger = $em->getRepository('LBUserBundle:User')
            ->findTopUsersByGenderAndType(User::MAN, ($location == '--'?null:$location), 'message', $cityLocation, $count, $distance);
        $womanMessenger = $em->getRepository('LBUserBundle:User')
            ->findTopUsersByGenderAndType(User::WOMAN, ($location == '--'?null:$location), 'message', $cityLocation, $count, $distance);
        $manLike = $em->getRepository('LBUserBundle:User')
            ->findTopUsersByGenderAndType(User::MAN, ($location == '--'?null:$location), 'like', $cityLocation, $count, $distance);
        $womanLike = $em->getRepository('LBUserBundle:User')
            ->findTopUsersByGenderAndType(User::WOMAN, ($location == '--'?null:$location), 'like', $cityLocation, $count, $distance);
        $manFavorite = $em->getRepository('LBUserBundle:User')
            ->findTopUsersByGenderAndType(User::MAN, ($location == '--'?null:$location), 'favorite', $cityLocation, $count, $distance);
        $womanFavorite = $em->getRepository('LBUserBundle:User')
            ->findTopUsersByGenderAndType(User::WOMAN, ($location == '--'?null:$location), 'favorite', $cityLocation, $count, $distance);
        $manVisitor = $em->getRepository('LBUserBundle:User')
            ->findTopUsersByGenderAndType(User::MAN, ($location == '--'?null:$location), 'visitor', $cityLocation, $count, $distance);
        $womanVisitor = $em->getRepository('LBUserBundle:User')
            ->findTopUsersByGenderAndType(User::WOMAN, ($location == '--'?null:$location), 'visitor', $cityLocation, $count, $distance);

        // default data for gender
        $genderSeries = array(
            array('name' => 'Man', 'y' => 0),
            array('name' => 'Woman', 'y' => 0),
            array('name' => 'Bisexual', 'y' => 0),
        );

        // default data for payment
        $paymentSeries = array(
            array('name' => 'iTunes', 'y' => 0),
            array('name' => 'Stripe', 'y' => 0),
            array('name' => 'No Payment', 'y' => 0),
        );

        // default data for gender payment
        $paymentGenderSeries = array(
            'man' => array(
                array('name' => 'iTunes', 'y' => 0),
                array('name' => 'Stripe', 'y' => 0),
                array('name' => 'No Payment', 'y' => 0)
            ),
            'bisexual' => array(
                array('name' => 'iTunes', 'y' => 0),
                array('name' => 'Stripe', 'y' => 0),
                array('name' => 'No Payment', 'y' => 0)
            ),
            'women' => array(
                array('name' => 'iTunes', 'y' => 0),
                array('name' => 'Stripe', 'y' => 0),
                array('name' => 'No Payment', 'y' => 0)
            )
        );

        $registrationSeries = array(
            array('name' => 'facebook', 'y' => 0),
            array('name' => 'twitter', 'y' => 0),
            array('name' => 'instagram', 'y' => 0),
            array('name' => 'email', 'y' => 0),
        );

        // default data for gender registration
        $registrationGenderSeries = array(
            'man' => array(
                array('name' => 'facebook', 'y' => 0),
                array('name' => 'twitter', 'y' => 0),
                array('name' => 'instagram', 'y' => 0),
                array('name' => 'email', 'y' => 0),
            ),
            'bisexual' => array(
                array('name' => 'facebook', 'y' => 0),
                array('name' => 'twitter', 'y' => 0),
                array('name' => 'instagram', 'y' => 0),
                array('name' => 'email', 'y' => 0),
            ),
            'women' => array(
                array('name' => 'facebook', 'y' => 0),
                array('name' => 'twitter', 'y' => 0),
                array('name' => 'instagram', 'y' => 0),
                array('name' => 'email', 'y' => 0),
            )
        );

        $deviceSeries = array(
            array('name' => 'Ios', 'y' => 0),
            array('name' => 'Android', 'y' => 0),
            array('name' => 'Web', 'y' => 0),
        );

        // default data for gender device
        $deviceGenderSeries = array(
            'man' => array(
                array('name' => 'Ios', 'y' => 0),
                array('name' => 'Android', 'y' => 0),
                array('name' => 'Web', 'y' => 0),
            ),
            'bisexual' => array(
                array('name' => 'Ios', 'y' => 0),
                array('name' => 'Android', 'y' => 0),
                array('name' => 'Web', 'y' => 0),
            ),
            'women' => array(
                array('name' => 'Ios', 'y' => 0),
                array('name' => 'Android', 'y' => 0),
                array('name' => 'Web', 'y' => 0),
            )
        );
        
        $interests = array();
        $ageArray = array();
        $paidArray = array();
        $now = new \DateTime();


        // loop for data
        foreach ($usersData as $key =>$data){

            $gender = 'bisexual';
            if($data['gender'] == User::MAN){
                $genderSeries[0]['y']++;
                $gender = 'man';
            }elseif ($data['gender'] == User::WOMAN){
                $genderSeries[1]['y']++;
                $gender = 'women';
            }
            elseif ($data['gender'] == User::BISEXUAL){
                $genderSeries[2]['y']++;
                $gender = 'bisexual';
            }
            
            if($data['registrationIds']){
                $registrationKeys = array_keys(json_decode($data['registrationIds'],true));

                if(in_array('ios',$registrationKeys)){
                    $deviceSeries[0]['y']++;
                    $deviceGenderSeries[$gender][0]['y']++;
                }

                if(in_array('android',$registrationKeys)){
                    $deviceSeries[1]['y']++;
                    $deviceGenderSeries[$gender][1]['y']++;
                }
            } else{
                $deviceSeries[2]['y']++;
                $deviceGenderSeries[$gender][2]['y']++;
            }
            // check interest
            $interestName = $data['interest'];
            if($interestName){
                if(array_key_exists($interestName, $interests)){
                    $interests[$interestName]['all'] = $interests[$interestName]['all'] + 1;
                    $interests[$interestName][$gender] = $interests[$interestName][$gender] + 1;
                }
                else{
                    $interests[$interestName] = array('all' => 1, 'man' => ($gender == 'man'?1:0), 'women' => ($gender == 'women'?1:0), 'bisexual' => ($gender == 'bisexual'?1:0));
                }
            }

            $paidName = $data['trialPeriod'];
            if($paidName && $paidName != '[]'){
                $paidName = array_keys(json_decode($paidName,true))[0];
                $paidName = array_key_exists($paidName, Subscriber::$PLAN)?Subscriber::$PLAN[$paidName]:$paidName;
                if(array_key_exists($paidName, $paidArray)){
                    $paidArray[$paidName]['all'] = $paidArray[$paidName]['all'] + 1;
                    $paidArray[$paidName][$gender] = $paidArray[$paidName][$gender] + 1;
                }
                else{
                    $paidArray[$paidName] = array('all' => 1, 'man' => ($gender == 'man'?1:0), 'women' => ($gender == 'women'?1:0), 'bisexual' => ($gender == 'bisexual'?1:0));
                }

                if(!$data['customer'] && !$data['simulatePeriod'] ){
                    $paymentSeries[0]['y']++;
                    $paymentGenderSeries[$gender][0]['y']++;
                } else{
                    $paymentSeries[1]['y']++;
                    $paymentGenderSeries[$gender][1]['y']++;
                }
            } else{
                $paymentSeries[2]['y']++;
                $paymentGenderSeries[$gender][2]['y']++;
            }

            if($data['facebook']){
                $registrationSeries[0]['y']++;
                $registrationGenderSeries[$gender][0]['y']++;
            }elseif ($data['twitter']){
                $registrationSeries[1]['y']++;
                $registrationGenderSeries[$gender][1]['y']++;
            }
            elseif ($data['instagram']){
                $registrationSeries[2]['y']++;
                $registrationGenderSeries[$gender][2]['y']++;
            } else{
                $registrationSeries[3]['y']++;
                $registrationGenderSeries[$gender][3]['y']++;
            }

            // check age
            $age = $data['age'];

            if($age instanceof \DateTime){

                $diff = date_diff($now, $age);
                $year = $diff->y;
                if(array_key_exists($year, $ageArray)){
                    $ageArray[$year]['all'] = $ageArray[$year]['all'] + 1;
                    $ageArray[$year][$gender] = $ageArray[$year][$gender] + 1;
                }
                else{
                    $ageArray[$year] = array('all' => 1, 'man' => ($gender == 'man'?1:0), 'women' => ($gender == 'women'?1:0), 'bisexual' => ($gender == 'bisexual'?1:0));
                }
            }
        }

        // check interests
        $interestSeries = array();
        $interestManSeries = array();
        $interestWomenSeries = array();
        foreach ($interests as $key => $interest){
            $interestSeries[] = array('name' => $key, 'y' => (array_key_exists('all', $interest)?$interest['all']:0));
            $interestManSeries[] = array('name' => $key, 'y' => (array_key_exists('man', $interest)?$interest['man']:0));
            $interestWomenSeries[] = array('name' => $key, 'y' => (array_key_exists('women', $interest)?$interest['women']:0));
        }

        // check age
        $ageSeries = array();
        $ageManSeries = array();
        $ageWomenSeries = array();
        foreach ($ageArray as $key => $age){
            $ageSeries[] = array('name' => $key .  " old year", 'y' => (array_key_exists('all', $age)?$age['all']:0));
            $ageManSeries[] = array('name' => $key .  " old year", 'y' => (array_key_exists('man', $age)?$age['man']:0));
            $ageWomenSeries[] = array('name' => $key .  " old year", 'y' => (array_key_exists('women', $age)?$age['women']:0));
        }

        // check paid
        $paidSeries = array();
        $paidManSeries = array();
        $paidWomenSeries = array();
        if(count($paidArray) > 0){
            foreach ($paidArray as $key => $paid){
                $paidSeries[] = array('name' => $key , 'y' => (array_key_exists('all', $paid)?$paid['all']:0));
                $paidManSeries[] = array('name' => $key , 'y' => (array_key_exists('man', $paid)?$paid['man']:0));
                $paidWomenSeries[] = array('name' => $key , 'y' => (array_key_exists('women', $paid)?$paid['women']:0));
            }
        } else{
            $paidSeries[] = array('name' => 'no paid service' , 'y' => 1);
            $paidManSeries[] = array('name' => 'no paid man service' , 'y' => 1);
            $paidWomenSeries[] = array('name' => 'no paid women service' , 'y' => 1);
        }
        
        if($paymentGenderSeries['man'][0]['y'] == 0 && $paymentGenderSeries['man'][1]['y'] == 0 && $paymentGenderSeries['man'][2]['y'] == 0){
            $paymentGenderSeries['man'] = [array('name' => 'no payment man user' , 'y' => 1)];
        }
        
        if($paymentGenderSeries['women'][0]['y'] == 0 && $paymentGenderSeries['women'][1]['y'] == 0 && $paymentGenderSeries['women'][2]['y'] == 0){
            $paymentGenderSeries['women'] = [array('name' => 'no payment women user' , 'y' => 1)];
        }

        if($registrationGenderSeries['man'][0]['y'] == 0 && $registrationGenderSeries['man'][1]['y'] == 0 
            && $registrationGenderSeries['man'][2]['y'] == 0 && $registrationGenderSeries['man'][3]['y'] == 0){
            $registrationGenderSeries['man'] = [array('name' => 'no registration man user' , 'y' => 1)];
        }

        if($registrationGenderSeries['women'][0]['y'] == 0 && $registrationGenderSeries['women'][1]['y'] == 0
          && $registrationGenderSeries['women'][2]['y'] == 0 && $registrationGenderSeries['women'][3]['y'] == 0
        ){
            $registrationGenderSeries['women'] = [array('name' => 'no registration women user' , 'y' => 1)];
        }

        if($deviceGenderSeries['man'][0]['y'] == 0 && $deviceGenderSeries['man'][1]['y'] == 0 && $deviceGenderSeries['man'][2]['y'] == 0){
            $deviceGenderSeries['man'] = [array('name' => 'no device man user' , 'y' => 1)];
        }

        if($deviceGenderSeries['women'][0]['y'] == 0 && $deviceGenderSeries['women'][1]['y'] == 0 && $deviceGenderSeries['women'][2]['y'] == 0){
            $deviceGenderSeries['women'] = [array('name' => 'no device women user' , 'y' => 1)];
        }
        

        // generate chart for interest
        $interestChart = $this->generatePeChart($interestSeries,
            "interestChart",
            "User`s interest statistic in $location",
            "count"
        );

        // generate chart for interest
        $interestManChart = $this->generatePeChart($interestManSeries,
            "interestManChart",
            "Man User`s interest statistic in $location",
            "count"
        );

        // generate chart for interest
        $interestWomenChart = $this->generatePeChart($interestWomenSeries,
            "interestWomenChart",
            "Women User`s interest statistic in $location",
            "count"
        );

        // generate chart for paid users
        $paidChart = $this->generatePeChart($paidSeries,
            "paidChart",
            "User`s paid statistic in $location",
            "count"
        );

        // generate chart for paid man users
        $paidManChart = $this->generatePeChart($paidManSeries,
            "paidManChart",
            "Man User`s paid statistic in $location",
            "count"
        );

        // generate chart for paid women users
        $paidWomenChart = $this->generatePeChart($paidWomenSeries,
            "paidWomenChart",
            "Women User`s paid statistic in $location",
            "count"
        );

        // generate chart for registration users
        $registerChart = $this->generatePeChart($registrationSeries,
            "registerChart",
            "User`s registration statistic in $location",
            "count"
        );

        // generate chart for registration man users
        $registerManChart = $this->generatePeChart($registrationGenderSeries['man'],
            "registerManChart",
            "Man User`s registration statistic in $location",
            "count"
        );

        // generate chart for registration women users
        $registerWomenChart = $this->generatePeChart($registrationGenderSeries['women'],
            "registerWomenChart",
            "Women User`s registration statistic in $location",
            "count"
        );

        // generate chart for device users
        $deviceChart = $this->generatePeChart($deviceSeries,
            "deviceChart",
            "User`s platform statistic in $location",
            "count"
        );

        // generate chart for device man users
        $deviceManChart = $this->generatePeChart($deviceGenderSeries['man'],
            "deviceManChart",
            "Man User`s platform statistic in $location",
            "count"
        );

        // generate chart for device women users
        $deviceWomenChart = $this->generatePeChart($deviceGenderSeries['women'],
            "deviceWomenChart",
            "Women User`s platform statistic in $location",
            "count"
        );

        // generate chart for gender
        $genderChart = $this->generatePeChart($genderSeries,
            "genderChart", "User`s gender statistic in $location",
            "count"
        );

        // generate chart for payment
        $paymentChart = $this->generatePeChart($paymentSeries,
            "paymentChart", "User`s payment statistic in $location",
            "count"
        );

        // generate chart for man payment
        $paymentManChart = $this->generatePeChart($paymentGenderSeries['man'],
            "paymentManChart", "Man User`s payment statistic in $location",
            "count"
        );

        // generate chart for women payment
        $paymentWomenChart = $this->generatePeChart($paymentGenderSeries['women'],
            "paymentWomenChart", "Women User`s payment statistic in $location",
            "count"
        );

        // generate chart for age
        $ageChart = $this->generatePeChart($ageSeries,
            "ageChart",
            "User`s age statistic in $location",
            "count"
        );

        // generate chart for man age
        $ageManChart = $this->generatePeChart($ageManSeries,
            "ageManChart",
            "Man User`s age statistic in $location",
            "count"
        );

        // generate chart for women age
        $ageWomenChart = $this->generatePeChart($ageWomenSeries,
            "ageWomenChart",
            "Women User`s age statistic in $location",
            "count"
        );

        $chartArray = array(
            'genderChart' => $genderChart,
            'interestChart' => $interestChart,
            'interestManChart' => $interestManChart,
            'interestWomenChart' => $interestWomenChart,
            'ageChart' => $ageChart,
            'ageManChart' => $ageManChart,
            'ageWomenChart' => $ageWomenChart,
            'paymentChart' => $paymentChart,
            'paymentManChart' => $paymentManChart,
            'paymentWomenChart' => $paymentWomenChart,
            'paidChart' => $paidChart,
            'paidManChart' => $paidManChart,
            'paidWomenChart' => $paidWomenChart,
            'registerChart' => $registerChart,
            'registerManChart' => $registerManChart,
            'registerWomenChart' => $registerWomenChart,
            'deviceChart' => $deviceChart,
            'deviceManChart' => $deviceManChart,
            'deviceWomenChart' => $deviceWomenChart,
            'manMessengers' => $manMessenger,
            'womanMessengers' => $womanMessenger,
            'manLikes' => $manLike,
            'womanLikes' => $womanLike,
            'manFavorite' => $manFavorite,
            'womanFavorite' => $womanFavorite,
            'manVisitor' => $manVisitor,
            'womanVisitor' => $womanVisitor,
            );

        return $chartArray;
    }

    /**
     * @param $data
     * @param $divId
     * @param $title
     * @param $tooltip
     * @return Highchart
     */
    private function generatePeChart($data, $divId, $title, $tooltip)
    {
        $series = array(
            array(
                'name'  => $tooltip,
                'type'  => 'pie',
                'color' => '#3366CC',
                'data'  => $data
            ),
        );
        $obChart = new Highchart();
        $obChart->plotOptions->pie(array(
            'allowPointSelect'  => true,
            'cursor'    => 'pointer',
            'dataLabels'    => array('enabled' => false),
            'showInLegend'  => true
        ));
        $obChart->chart->renderTo($divId); // The #id of the div where to render the chart
        $obChart->title->text($title);
        $obChart->series($series);

        return $obChart;
    }


    /**
     * @Route(name="user-statistic")
     * @Security("has_role('ROLE_ADMIN')")
     * @return array
     * @param $location
     * @param $distance
     * @param $count
     * @param $type
     * @param $gender
     * @Template()
     * @return array
     */
    public function userAction($location, $distance = 50, $count = 10, $type = 'messages', $gender = 'all')
    {
        // get entity manager
        $em = $this->get('doctrine')->getManager();

        // find all users location
        $locations = $em->getRepository("LBUserBundle:User")->findUserLocations();

        foreach ($locations as &$cityCountry){
            $city = explode(',', $cityCountry);
            $city = explode('/', $city[0]);
            $cityCountry = trim($city[0]);
        }
        $locations = array_unique($locations);
        
        // check location and est frist location if not exist
        $location = $location ? $location : reset($locations);

        // get all data
        $relationStatistic = $this->getUsersData($location, $distance, $count);

        return array(
            'relationStatistic'  => $relationStatistic,
            'selectedLocation' => $location,
            'selectedGender' => $gender,
            'distance' => $distance,
            'count' => $count,
            'selectedType' => $type,
            'locations' => $locations,
            'tabId' => 1
        );
    }
}