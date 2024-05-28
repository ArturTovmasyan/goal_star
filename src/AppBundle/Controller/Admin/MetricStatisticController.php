<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 7/7/16
 * Time: 1:10 PM
 */

namespace AppBundle\Controller\Admin;

use FOS\RestBundle\Controller\Annotations\Route;
use Ob\HighchartsBundle\Highcharts\Highchart;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class MetricStatisticController
 * @package AppBundle\Controller\Admin
 */
class MetricStatisticController extends Controller
{

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        if (false === $this->admin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        return $this->render('AppBundle:Admin:metricStatistic.html.twig');
    }
}