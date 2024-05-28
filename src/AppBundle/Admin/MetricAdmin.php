<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 7/7/16
 * Time: 1:07 PM
 */

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Route\RouteCollection;

/**
 * Class MetricAdmin
 * @package AppBundle\Admin
 */
class MetricAdmin extends Admin
{
    protected $baseRoutePattern = 'metric-statistic';
    protected $baseRouteName = 'metric-statistic';

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('list'));
    }
}