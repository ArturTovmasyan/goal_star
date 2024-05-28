<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 11/9/15
 * Time: 10:48 AM
 */

namespace AppBundle\Admin;

use AppBundle\Entity\Location;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * Class AdAdmin
 * @package AppBundle\Admin
 */
class AdGeoAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('description', 'raw')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'delete' => array(),
                )
            ))
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name')
            ->add('radius', 'number', array('label'=>'Ad Radius(mile)'))
            ->add('description', 'ckeditor')
            ->add('file', 'icon_type', array('label' => "Image", 'required' => false))
            ->add('location_type', 'location_type', array('mapped' => false, 'required' => false))
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('name')
        ;
    }

    /**
     * @param mixed $object
     */
    public function prePersist($object)
    {
        $this->checkLocation($object);
    }

    /**
     * @param mixed $object
     */
    public function preUpdate($object)
    {
        $this->checkLocation($object);

        $object->uploadFile();
    }

    /**
     * @param $object
     */
    private function checkLocation(&$object)
    {
        // get location from form
        $locations = $this->getForm()->get('location_type')->getData();

        // get entity manager
        $em = $this->getConfigurationPool()->getContainer()->get('doctrine')->getManager();

        // get locations

        $locations = json_decode($locations);
        // get all locations
        $adGeoLocations = $object->getLocations();

        // check locations
        if($locations){
            $geo = array();
            // loop for location
            foreach($locations as $location){

                if(isset($location->id)){

                    $objLocation = $adGeoLocations[$location->id];
                }
                else{
                    $objLocation = new Location();
                    $object->addLocation($objLocation);
                }

                $objLocation->setLat($location->latitude);
                $objLocation->setLng($location->longitude);

                $em->persist($objLocation);

                $geo[]= $objLocation->getId();

            }

            foreach($adGeoLocations as $location)
            {
                if(in_array($location->getId(), $geo) == false)
                {
                    $em->remove($location);
                }
            }
            $em->flush();
        }
    }

}