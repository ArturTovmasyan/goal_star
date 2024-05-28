<?php
/**
 * Created by PhpStorm.
 * User: pc-4
 * Date: 10/29/15
 * Time: 6:18 PM
 */
namespace LB\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FileType
 * @package LB\UserBundle\Form
 */
class FileType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', 'file')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'LB\UserBundle\Entity\File',
        ));
    }

    public function getName()
    {
        return 'lb_file_type';
    }
}