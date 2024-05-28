<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 11/9/15
 * Time: 10:48 AM
 */

namespace AppBundle\Admin;

use AppBundle\Entity\Tag;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;


class BlogAdmin extends Admin
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
            case 'preview':
                return 'AppBundle:Admin:preview_blog.html.twig';
                break;
            default:
                return parent::getTemplate($name);
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $query->orderBy($query->getRootAliases()[0] . '.created', 'DESC');
        return $query;
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title')
            ->add('slug')
            ->add('content')
            ->add('created')
            ->add('metaDescription')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title')
            ->add('slug')
            ->add('created')
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
        $tags = null;
        $object = $this->getSubject();

        if($object->getId()){
            $tags = $object->getTagsForInput();
        }

        $formMapper
            ->add('title')
            ->add('slug', null, array('required' => false))
            ->add('metaDescription')
            ->add('content', 'ckeditor')
            ->add('created', 'date', array(
                'pattern' => 'dd MMM Y G',
            ))
            ->add('tags', 'text', array( 'required' => false, 'mapped'=>false, 'attr'=>array(
                'name'=>'tag', 'class'=>'tokenfield', 'data-tokens' => $tags)))
            ->add('file', 'icon_type', array('label' => "Sharing image", 'required' => false))
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('title')
            ->add('slug')
            ->add('metaDescription')
            ->add('content')
            ->add('created')
        ;
    }

    public function preUpdate($object)
    {
        $object->uploadFile();
        $this->setTag($object);

    }

    public function prePersist($object)
    {
        $currentUser = $this->getConfigurationPool()->getContainer()->get('security.token_storage')->getToken()->getUser();

        $this->setTag($object);

        $object->setAuthor($currentUser);

        $object->uploadFile();
    }

    /**
     * @param $object
     */
    private function setTag(&$object)
    {
        $container = $this->getConfigurationPool()->getContainer();
        $em = $container->get('doctrine')->getManager();

        $dbTags = $em->getRepository('AppBundle:Tag')->findAllTagsName();
        $dbTags = array_change_key_case($dbTags, CASE_LOWER);

        // get tags
        $tags = $this->getTagsFromInput();

        $objectTags = $object->getTagsArray();

        // get new tags
        $addTags = array_diff($tags, $objectTags);

        // get remove tags
        $removeTags = array_diff($objectTags, $tags);

        // check new tags
        if($addTags){

            // loop for tags
            foreach($addTags as $tag){

                // check tag in db
                if(array_key_exists($tag, $dbTags)){
                    $tagObj = $dbTags[$tag];
                }
                else{
                    $tagObj = new Tag();
                    $tagObj->setName($tag);
                    $em->persist($tagObj);
                }

                $object->addTag($tagObj);
            }
        }

        // check remove tags
        if($removeTags){
            // loop for tags
            foreach($removeTags as $tag){
                // check tag in db
                if(array_key_exists($tag, $dbTags)){
                    $tagObj = $dbTags[$tag];
                    $object->removeTag($tagObj);

                }
            }
        }
    }

    /**
     * This function is used to get all tags form input
     *
     * @return array
     */
    private function getTagsFromInput()
    {
        // get tags data
        $tagData = $this->getForm()->get('tags')->getData();

        $tags = array();

        // check tag data
        if($tagData){
            // all to lowercase
            $tags = strtolower($tagData);

            // explode by comma
            $tags = explode(', ',  $tags);

            // get only unique data
            $tags = array_unique($tags);
        }



        return $tags;
    }



}