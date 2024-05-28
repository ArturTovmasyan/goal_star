<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 12/23/15
 * Time: 12:10 PM
 */

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Route\RouteCollection;

class StatisticAdmin extends Admin
{
    protected $baseRoutePattern = 'user-statistic';
    protected $baseRouteName = 'user-statistic';

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('list'));
    }
}