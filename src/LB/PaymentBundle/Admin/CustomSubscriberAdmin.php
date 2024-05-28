<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 7/19/16
 * Time: 11:25 AM
 */

namespace LB\PaymentBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Route\RouteCollection;


/**
 * Class CustomSubscriberAdmin
 * @package LB\PaymentBundle\Admin
 */
class CustomSubscriberAdmin extends Admin
{
    protected $baseRoutePattern = 'custom-subscriber';
    protected $baseRouteName = 'custom-subscriber';

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('list', 'create', 'delete'));
    }
}