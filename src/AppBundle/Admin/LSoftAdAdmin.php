<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/31/16
 * Time: 4:02 PM
 */

namespace AppBundle\Admin;

use AppBundle\Entity\LSoftAdManager;
use AppBundle\Form\LSoftAdManagerType;
use AppBundle\Services\AdsDataProvider;
use LSoft\AdBundle\Admin\AdAdmin;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Class LSoftAdAdmin
 * @package AppBundle\Admin
 */
class LSoftAdAdmin extends AdAdmin
{
    protected $formOptions = array(
        'cascade_validation' => true,
        'error_bubbling' => true
    );

    /**
     * @param string $name
     * @return null|string
     */
    public function getTemplate($name)
    {
        switch ($name) {
            case 'edit':
                return 'AppBundle:Admin:lsoft_ad_edit.html.twig';
                break;
            default:
                return parent::getTemplate($name);
                break;
        }
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        parent::configureFormFields($formMapper);

        $object = $this->getSubject();
        $adsManager = null;

        if($object->getId()){
            $em = $this->getConfigurationPool()->getContainer()->get('doctrine')->getManager();
            $adsManager = $em->getRepository("AppBundle:LSoftAdManager")->findOneBy(array('ad'=>$object->getId()));
        }
        $formMapper
            ->tab('Ad Manager')
                ->with('Ad Manager')
                    ->add('adManager', LSoftAdManagerType::class, array('mapped' => false, 'data' => $adsManager))
                ->end()
            ->end();
    }


    /**
     * {@inheritdoc}
     */
    public function preUpdate($object)
    {
        $em = $this->getConfigurationPool()->getContainer()->get('doctrine')->getManager();

        // get ad manager
        $adManager = $this->getForm()->get('adManager')->getData();

        if($this->isManagerEmpty($adManager)){
            if($adManager->getId() != null){
                $em->remove($adManager);
            }

        }else{
            $adManager->setAd($object);

            $em->persist($adManager);
        }

        apc_delete(AdsDataProvider::APC_CASH_ID);
        parent::preUpdate($object);
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
        $em = $this->getConfigurationPool()->getContainer()->get('doctrine')->getManager();

        // get ad manager
        $adManager = $this->getForm()->get('adManager')->getData();
        if(!$this->isManagerEmpty($adManager)){

            $adManager->setAd($object);
            $em->persist($adManager);
        }


        apc_delete(AdsDataProvider::APC_CASH_ID);

        parent::prePersist($object);
    }

    private function isManagerEmpty(LSoftAdManager $adManager){

        if($adManager->getCity() ||
            $adManager->getGender() ||
            $adManager->getMaxAge() ||
            $adManager->getMinAge()||
            $adManager->getInterests()->count() > 0

        ){
            return false;
        }

        return true;

    }

}