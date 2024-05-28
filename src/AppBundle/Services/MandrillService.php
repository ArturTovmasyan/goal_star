<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 2/9/16
 * Time: 12:55 PM
 */
namespace AppBundle\Services;

use LB\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\Container;


/**
 * Class MandrillService
 * @package AppBundle\Services
 */
class MandrillService
{
    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected  $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param $email
     * @param $name
     * @param $subject
     * @param $message
     * @throws \Exception
     * @throws \Mandrill_Error
     */
    public function sendEmail($email, $name, $subject, $message)
    {
        // get mandrill app key
        $mandrillAppKey = $this->container->getParameter('mandrill');

        //get from email in parameter
        $fromEmail = $this->container->getParameter('to_report_email');

        // get get environment
        $env = $this->container->get('kernel')->getEnvironment();

        // check environment
        if($env == "test" || $env == "dev"){
            return;
        }

        try {

            $mandrill = new \Mandrill($mandrillAppKey);
            $message = array(
                'html' => $message,
                'subject' => $subject,
                'from_email' => $fromEmail,
                'from_name' => 'luvbyrd',
                'to' => array(
                    array(
                        'email' => $email,
                        'name' => $name,
                        'type' => 'to'
                    )
                )
            );
            $async = false;
            $ip_pool = 'Main Pool';
            $mandrill->messages->send($message, $async, $ip_pool);

        } catch(\Mandrill_Error $e) {
            // Mandrill errors are thrown as exceptions
//            echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
            throw $e;
        }
    }
}