<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 2/24/16
 * Time: 4:52 PM
 */


namespace AppBundle\Admin;

use LSoft\AdBundle\Admin\AdsProviderAdmin;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;


/**
 * Class AdsProviderAdmin
 * @package LSoft\AdBundle\Admin
 */
class LSoftAdProviderAdmin extends AdsProviderAdmin
{
    public function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        // get container
        $container = $this->getConfigurationPool()->getContainer();

        // get object
        $object = $this->getSubject();

        // default value for zone check
        $zoneChecked = null;

        // check object
        if($object){

            // set data
            $zoneChecked = $object->getZone();
        }

        // get ads zone
        $zone = $container->getParameter('ads_zone');

        $formMapper
            ->add('domain', 'entity', array('class'=> "LSoftAdBundle:Domain"))
            ->add('ad')
            ->add('zone', 'choice', array('choices' => $zone,
                'data' => $zoneChecked,
                'choices_as_values' => true,
                'choice_label' => function ($allChoices) {
                    return $allChoices;
                },
                ))
        ;
    }
}