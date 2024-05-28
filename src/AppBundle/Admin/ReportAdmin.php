<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class ReportAdmin extends Admin
{
    /**
     * @param string $context
     * @return \Sonata\AdminBundle\Datagrid\ProxyQueryInterface
     */
    public function createQuery($context = 'list')
    {
        // get container
        $container = $this->getConfigurationPool()->getContainer();
        $em = $container->get('doctrine')->getManager();

        $filters =$em->getFilters();
        $filters->isEnabled("user_deactivate_filter") ?  $filters->disable("user_deactivate_filter") : null;
        $query = parent::createQuery($context);
        return $query;
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
//            ->add('fromUser', null, array(), null,  array('query_builder' => function($er) {
//                return $er->createQueryBuilder('fu')
//                    ->addSelect('fcus')
//                    ->leftJoin('fu.customer', 'fcus')
//                    ;
//            }))
//            ->add('toUser', null, array(), null,  array('query_builder' => function($er) {
//                return $er->createQueryBuilder('tu')
//                    ->addSelect('tcus')
//                    ->leftJoin('tu.customer', 'tcus')
//                    ;
//            }))
            ->add('status')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('content')
            ->add('fromUser')
            ->add('toUser')
            ->add('status')
            ->add('created', null, array('pattern' => 'y-m-d'))
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
            ->add('fromUser', 'sonata_type_model_autocomplete',
                array(
                    'attr'=>array('class'=>'auto-class'),
                    'property' => 'username',
                    'placeholder' => 'Select the username'
                ))
            ->add('toUser', 'sonata_type_model_autocomplete',
                array(
                    'attr'=>array('class'=>'auto-class'),
                    'property' => 'username',
                    'placeholder' => 'Select the username'
                ))
            ->add('content')
            ->add('status')
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('fromUser')
            ->add('toUser')
            ->add('content')
            ->add('status')
            ->add('created')
        ;
    }
}
