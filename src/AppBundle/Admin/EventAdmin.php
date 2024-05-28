<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 9/19/16
 * Time: 12:17 PM
 */

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class EventAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('title')
            ->add('type',
                'doctrine_orm_string',
                array(),
                'choice',
                array('choices' => array(
                    0 => "Free Ticket",
                    1 =>"Paid Ticket",
                    2 => "Donation")
                )
            )
            ->add('status' , null, array('label' => 'Is Live'))
            ->add('metaDescription')
            ->add('content')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title')
            ->add('status', null, array(
                'editable' => true,
                'label' => 'Is Live'
            ))
            ->add('getStringType', null, array('label' => 'Ticket Sales'))
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
            ->add('status', null, array('label' => 'Make Event Live'))
            ->add('title')
            ->add('metaDescription')
            ->add('start', 'sonata_type_datetime_picker', ['format' => 'yyyy-MM-dd HH:mm:ss'])
            ->add('end', 'sonata_type_datetime_picker', ['format' => 'yyyy-MM-dd HH:mm:ss'])
            ->add('file', 'icon_type', array('label' => "Image", 'required' => false))
            ->add('content', 'ckeditor')
            ->add('type' , 'choice', array('choices' => array(
                0 => "Free Ticket",
                1 =>"Paid Ticket",
                2 => "Donation"
            )))
            ->add('price', null, array('attr' => array('min' => 0)))
            ->add('location_type', 'location_type', array('mapped' => false, 'required' => true))
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('title')
            ->add('getStringType', null, array('label' => 'Ticket Sales'))
            ->add('status', null, array('label' => 'Is Live'))
            ->add('metaDescription')
            ->add('content')
        ;
    }

    /**
     * @param mixed $object
     */
    public function prePersist($object)
    {
        $this->checkLocation($object);
        $object->uploadFile();
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
        
        $locations = json_decode($locations);

        // check locations
        if($locations && $locations[0]){


            $object->setCityLat($locations[0]->latitude);
            $object->setCity($locations[0]->address);
            $object->setCityLng($locations[0]->longitude);

        }
    }
}
