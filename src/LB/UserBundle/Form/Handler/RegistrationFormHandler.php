<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/2/15
 * Time: 11:23 AM
 */

namespace LB\UserBundle\Form\Handler;

use FOS\UserBundle\Form\Handler\RegistrationFormHandler as BaseHandler;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class RegistrationFormHandler
 * @package LB\UserBundle\Form\Handler
 */
class RegistrationFormHandler extends BaseHandler
{

    /**
     * @var Container
     */
    protected $container;

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param UserManagerInterface $userManager
     * @param MailerInterface $mailer
     * @param TokenGeneratorInterface $tokenGenerator
     * @param $container
     */
    public function __construct(FormInterface $form, Request $request, UserManagerInterface $userManager,
                                MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator, Container $container)
    {
        // get parent  constructor
        parent::__construct($form, $request, $userManager, $mailer, $tokenGenerator);

        $this->container = $container;

    }

    /**
     * @param bool|false $confirmation
     * @param null $user
     * @param null $profileImageError
     * @param bool|false $reg
     * @return bool
     */
    public function process($confirmation = false, $user = null, $profileImageError = null, $reg = false)
    {
        if(!$user || !is_object($user) ){
            $user = $this->createUser();
            $this->form->setData($user);

        }

        if ('POST' === $this->request->getMethod()) {
            $this->form->bind($this->request);

            if ($this->form->isValid() && !$profileImageError) {

                // don`t flush if register
                if(!$reg){
                    $this->onSuccess($user, $confirmation);
                }
                return true;
            }
        }

        return false;
    }
}