<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 6/9/16
 * Time: 12:52 PM
 */

namespace AppBundle\Model;

/**
 * Interface PaymentAware
 * @package AppBundle\Model
 */
interface PaymentAware
{
    /**
     * @return boolean
     */
    public function hasAccess();
}