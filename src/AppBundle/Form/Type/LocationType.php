<?php
/**
 * Created by PhpStorm.
 * User: pc-4
 * Date: 10/27/15
 * Time: 5:30 PM
 */
namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

/**
 * Class LocationType
 * @package AppBundle\Form\Type
 */
class LocationType extends AbstractType
{
    /**
     * @return null|string|\Symfony\Component\Form\FormTypeInterface
     */
    public function getParent()
    {
        return 'text';
    }
    /**
     * @return string
     */
    public function getName()
    {
        return 'location_type';
    }
}
