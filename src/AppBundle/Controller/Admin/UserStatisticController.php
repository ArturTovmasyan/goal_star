<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 12/22/15
 * Time: 7:55 PM
 */

namespace AppBundle\Controller\Admin;

use FOS\RestBundle\Controller\Annotations\Route;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class UserStatisticController
 * @package AppBundle\Controller\Admin
 */
class UserStatisticController extends Controller
{
    const WEEK = 1;
    const MONTH = 2;
    const YEAR = 3;

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        if (false === $this->admin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        // get entity manager
        $em = $this->container->get('doctrine')->getManager();

        $from = null;
        $to = null;
        $groupBy = self::YEAR;

        // get request
        $request = $this->container->get('request');

        if(!is_null($request->get('submit'))){
            $from = $request->get('from');
            $to = $request->get('to');
            $groupBy = $request->get('groupBy');
        }

        // get statistics
        $statistics = $em->getRepository("LBUserBundle:User")->getStatistics($from, $to, $groupBy);

        $labels = array();
        $data = array();

        // check statistics
        if($statistics){

            // loop for statistics
            foreach($statistics as $key => $value){

                // switch for gru
                switch($groupBy){
//                    case UserStatisticController::WEEK:
//                        $query
//                            ->addSelect('week(u.createdAt) as week')
//                            ->groupBy('week');
//                        break;
//                    case UserStatisticController::MONTH:
//                        $query
//                            ->addSelect('month(u.createdAt) as month')
//                            ->groupBy('month');
//                        break;
                    case UserStatisticController::YEAR:
                        $labels[] = $value['year'];
                        break;
                    default :
                        $labels[] = $key == "" ? 'Old User' : $key;
                        break;
                }



                $data[] = $value['CNT'];
            }
        }

        $selects = array(
//            self::WEEK => 'Weekly',
//            self::MONTH => 'Monthly',
            self::YEAR => 'Yearly',
        );

       return $this->render('AppBundle:Admin:userStatistic.html.twig', array(
           'labels'  => json_encode($labels), 'data' => json_encode($data),
           'from' => $from, 'to' => $to, 'groupBy' => $groupBy, 'selects' => $selects
       ));
    }
}