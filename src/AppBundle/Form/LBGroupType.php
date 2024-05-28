<?php

namespace AppBundle\Form;

use AppBundle\Entity\LBGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Class LBGroupType
 * @package AppBundle\Form
 */
class LBGroupType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label' => 'group.form.name', 'required' => true, 'label_attr'=>array('class'=>'col-sm-5 col-md-4 control-label')))
            ->add('file', 'file', array('label' => 'group.form.group_image', 'required' => false, 'label_attr'=>array('class'=>'col-sm-5 col-md-4 control-label')))
            ->add('downloadLink', 'hidden', array('label' => 'group.form.group_image', 'required' => false, 'label_attr'=>array('class'=>'col-sm-5 col-md-4 control-label')))
            ->add('eventDate', 'datetime', array('date_widget' => "single_text", 'time_widget' => "single_text", 'label' => 'group.form.date', 'required' => true, 'label_attr'=>array('class'=>'col-sm-5 col-md-4 control-label')))
            ->add('joinLimit', 'integer', array('label' => 'group.form.limit_of_members', 'required' => false, 'label_attr'=>array('class'=>'col-sm-5 col-md-4 control-label')))
            ->add('description', 'textarea', array('label' => 'group.form.description', 'required' => true, 'label_attr'=>array('class'=>'col-sm-5 col-md-4 control-label')))
            ->add('address', 'hidden', array('required' => false, 'label_attr'=>array('class'=>'col-sm-5 col-md-4 control-label')))
            ->add('latitude', 'hidden', array('required' => false, 'label_attr'=>array('class'=>'col-sm-5 col-md-4 control-label')))
            ->add('longitude', 'hidden', array('required' => false, 'label_attr'=>array('class'=>'col-sm-5 col-md-4 control-label')))
            ->add('type', 'choice', array('label' => 'group.form.type', 'required' => true, 'label_attr'=>array('class'=>'col-sm-5 col-md-4 control-label'),
                'choices'=>array(LBGroup::GROUP_PUBLIC=>'group.form.public', LBGroup::GROUP_PRIVATE=>'group.form.private'),
                'placeholder' => 'group.form.choose_an_group_type'))
            ->add('submit', 'submit', array('label'=>'group.form.submit', 'attr'=>array('class'=>'btn btn-blue')))
        ;

    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'app_bundle_lbgroup_type';
    }
}
