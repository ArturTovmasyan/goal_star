<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 12/29/15
 * Time: 12:13 PM
 */

namespace LB\UserBundle\Admin;

use LB\UserBundle\Entity\User;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 * Class UserAdmin
 * @package LB\UserBundle\Admin
 */
class UserAdmin extends Admin
{
    /**
     * @var bool
     */
    public $supportsPreviewMode = true;
    /**
     * @param string $name
     * @return null|string
     */
    public function getTemplate($name)
    {
        switch ($name) {
            case 'list':
                return 'LBUserBundle:Admin:user_list.html.twig';
                break;
            default:
                return parent::getTemplate($name);
                break;
        }
    }


    /**
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function getFormBuilder()
    {
        $this->formOptions['data_class'] = $this->getClass();

        $options = $this->formOptions;
        $options['validation_groups'] = "Admin";

        $formBuilder = $this->getFormContractor()->getFormBuilder( $this->getUniqid(), $options);

        $this->defineFormBuilder($formBuilder);

        return $formBuilder;
    }

    public function prePersist($object)
    {
        parent::prePersist($object);

        $object->addRole("ROLE_ADMIN");
        $object->addRole("ROLE_SUPER_ADMIN");
        $object->addRole("ROLE_SONATA_ADMIN");

        $this->updatePassword($object);
    }

    public function getStateFilter($queryBuilder, $alias, $field, $value)
    {
        if (!array_key_exists('value', $value)) {
            return;
        }

        $state = $value['value'];

        if(!$state){
            return;
        }

        $queryBuilder->andWhere("TRIM(LOWER(SUBSTRING_INDEX(SUBSTRING_INDEX(".$alias .".city, ',',  2), ',', '-1'))) = :state");
        $queryBuilder->setParameter('state', $state);

        return true;
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $em = $this->getConfigurationPool()->getContainer()->get('doctrine');
        $states = $em->getRepository('AppBundle:State')->findAllWithAbbr();

        $datagridMapper
            ->add('id')
            ->add('uId')
            ->add('email')
            ->add('username')
            ->add('lastName')
            ->add('firstName')
            ->add('deactivate', 'doctrine_orm_choice', array(
                'label' => 'deactivate'),
                'choice',
                array(
                    'choices' => array(0 => 'No', 1 => 'Yes')
                ))
            ->add('city', null, array(), null,  array('attr'=>array(
                'google-places-autocomplete'=> '', 'place' => 'userLocation', 'data-types'=> "['(cities)']"

            )))
            ->add('state', 'doctrine_orm_callback', array(
                'callback'   => array($this, 'getStateFilter'),
                'field_type' => 'choice',
                'field_options' => array('choices'=> $states),
            ))
            ->add('I_am', 'doctrine_orm_choice', array(
                'label' => 'gender'),
                'choice',
                array(
                    'choices' => User::$GENDER_CHOICE
                ))
            ->add('zipCode')
            ->add('birthday', 'doctrine_orm_date_range',  array(
                    'label' => 'birthday',
                    'input_type' => 'text',
//                    'field_options' => array(
//                        'widget' => 'single_text'
//
//                    )
                )
            )
            ->add('interests', null, array(), null, array( 'multiple' => true))
            ->add('createdAt', 'doctrine_orm_date_range',  array(
                    'label' => 'createdAt',
                    'input_type' => 'text',
//                    'field_options' => array(
//                        'widget' => 'single_text'
//
//                    )
                )
            )
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('email')
            ->add('username')
            ->add('lastName')
            ->add('firstName')
            ->add('deactivate')
            ->add('createdAt')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'profile' => array('template' =>'LBUserBundle:Admin:profile.html.twig'),
                    'show' => array(),
                    'delete' => array(),
                    'deactivate' => array('template' =>'LBUserBundle:Admin:deactivate.html.twig')
                )
            ))
        ;
    }

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
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        // get container
        $container = $this->getConfigurationPool()->getContainer();

        $roles = $container->getParameter('security.role_hierarchy.roles');

        // get roles
        $rolesChoices = $this->flattenRoles($roles);

        $formMapper
            ->add('email')
            ->add('username')
            ->add('lastName')
            ->add('firstName')
            ->add('roles', 'choice', array(
                'choices'  => $rolesChoices,
                'multiple' => true
            ))
            ->add('plainPassword', 'repeated', array('first_name' => 'password',
                'required' => false,
                'second_name' => 'confirm',
                'type' => 'password',
                'invalid_message' => 'Passwords do not match',
                'first_options' => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password')))
            ->add('I_am', 'choice', array(
                'choices' => User::$GENDER_CHOICE_FOR_I_AM,
                'expanded' =>true,
                'required' => true
            ))
            ->add('looking_for', 'choice', array(
                'choices' => User::$GENDER_CHOICE,
                'expanded' =>true,
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
            ->add('uId')
            ->add('email')
            ->add('username')
            ->add('lastName')
            ->add('firstName')
        ;
    }

    public function getExportFields()
    {
        $fields = ['id', 'uId', 'email','username', 'firstName', 'lastName'];

        return $fields;
    }

    public function preUpdate($object)
    {
        parent::preUpdate($object);

        $this->updatePassword($object);
    }

    /**
     * {@inheritdoc}
     */
    public function getObject($id)
    {
        // get container
        $container = $this->getConfigurationPool()->getContainer();
        $em = $container->get('doctrine')->getManager();

        $filters =$em->getFilters();
        $filters->isEnabled("user_deactivate_filter") ?  $filters->disable("user_deactivate_filter") : null;

        $object = parent::getObject($id);
        return $object;
    }

    /**
     * @param $rolesHierarchy
     * @return array
     */
    private function flattenRoles($rolesHierarchy)
    {
        // empty values for
        $flatRoles = array();
        foreach($rolesHierarchy as $key=> $roles) {

            // check roles, don`t show role admin, and sonata entities hierarchies
            if(strpos($key, 'SONATA') === false && $key != "ROLE_ADMIN"){
                $flatRoles[$key] = $key;
            }
        }
        return $flatRoles;
    }

    /**
     * @param $object
     */
    private function updatePassword(User $object)
    {
        // get user manager
        $um = $this->getConfigurationPool()->getContainer()->get('fos_user.user_manager');

        // get plain password
        $plainPassword = $object->getPlainPassword();

        if($plainPassword){
            // update user
            $um->updateUser($object, false);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function preRemove($object)
    {
        // get entity manager
        $em = $this->getConfigurationPool()->getContainer()->get('doctrine')->getManager();

        // remove user relation
        $em->getRepository("LBUserBundle:UserRelation")->removeRelationByUser($object->getId());

        // remove user`s push
        $em->getRepository("LBUserBundle:UserPush")->removePushByUser($object->getId());

        // remove user ad location
        $em->getRepository("LBUserBundle:UserAdLocation")->deleteExistLocations($object);

        // remove user`s report
        $em->getRepository("AppBundle:Report")->removeReportByUser($object->getId());

        // remove user`s notification
        $em->getRepository("LBNotificationBundle:Notification")->removeNoteByUser($object->getId());

        // remove user`s messages
        $em->getRepository("LBMessageBundle:Message")->removeMessageByUser($object->getId());

        // remove groups members by user
        $em->getRepository("AppBundle:LBGroupMembers")->removeGroupMemberByUser($object->getId());

        // remove groups members by user
        $em->getRepository("AppBundle:LBGroupModerators")->removeGroupModeratorByUser($object->getId());

        // remove comments by user
        $em->getRepository("AppBundle:Comment")->removeCommentByUser($object->getId());

        // remove user`s groups
        $blogs = $em->getRepository("AppBundle:Blog")->findBy(array('author'=> $object->getId()));
        if($blogs){
            foreach($blogs as $blog){
                $em->remove($blog);
            }
        }
        // remove user`s groups
        $groups = $em->getRepository("AppBundle:LBGroup")->findBy(array('author'=> $object->getId()));
        if($groups){
            foreach($groups as $group){
                $em->remove($group);
            }
        }

        // remove user files
        $files = $object->getFiles();
        if($files){
            foreach($files as $file){
                $em->remove($file);
            }
        }

        // remove user interest
        $interests = $object->getInterests();
        if($interests){
            foreach($interests as $interest){
                $object->removeInterest($interest);
            }
        }
    }
}