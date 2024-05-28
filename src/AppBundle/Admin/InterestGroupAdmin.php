<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class InterestGroupAdmin extends Admin
{
    protected $datagridValues = array(
        '_page' => 1,
        '_sort_order' => 'ASC',
        '_sort_by' => 'position',
    );

    public $maxPosition;
    public $minPosition;

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->add('up', $this->getRouterIdParameter().'/up')
            ->add('down', $this->getRouterIdParameter().'/down');
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('name')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $container = $this->getConfigurationPool()->getContainer();
        $em = $container->get('doctrine')->getManager();
        $maxMinPositions = $em->getRepository('AppBundle:InterestGroup')->findMaxMinPositions();

        $this->maxPosition = $maxMinPositions['maxPos'];
        $this->minPosition = $maxMinPositions['minPos'];


        $listMapper
            ->addIdentifier('name')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'delete' => array(),
                    'up' => array('template' => 'SonataAdminBundle:CRUD:list__action_up.html.twig'),
                    'down' => array('template' => 'SonataAdminBundle:CRUD:list__action_down.html.twig'),
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
            ->add('interest', 'sonata_type_collection', array(), array(
                'edit' => 'inline',
                'inline' => 'table',
                'sortable'  => 'position'
            ))
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('name')
            ->add('interest')
        ;
    }

    public function prePersist($object)
    {
        foreach($object->getInterest() as $interest){
            $interest->setGroup($object);
        }

        $container = $this->getConfigurationPool()->getContainer();
        $em = $container->get('doctrine')->getManager();
        $maxMinPositions = $em->getRepository('AppBundle:InterestGroup')->findMaxMinPositions();

        $object->setPosition($maxMinPositions['maxPos'] + 1);
    }

    public function preUpdate($object)
    {
        $container = $this->getConfigurationPool()->getContainer();
        $em = $container->get('doctrine')->getManager();

        $interests = $em->getRepository('AppBundle:Interest')->findBy(array('group' => $object));
        foreach($interests as $interest){
            if (!$object->hasInterest($interest->getId())){
                $em->remove($interest);
            }
        }

        foreach($object->getInterest() as $interest){
            $interest->uploadFile();
            $interest->setGroup($object);
        }

        $em->flush();
    }
}
