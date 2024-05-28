<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 3/9/16
 * Time: 11:17 AM
 */

namespace LB\UserBundle\Mailer;

use FOS\UserBundle\Mailer\Mailer as FosMailer;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;


/**
 * Class Mailer
 * @package LB\UserBundle\Mailer
 */
class Mailer extends  FosMailer
{
    protected $mailer;
    protected $router;
    protected $templating;
    protected $parameters;
    protected $container;

    /**
     * @param Container $container
     * @param RouterInterface $mailer
     * @param RouterInterface $router
     * @param EngineInterface $templating
     * @param array $parameters
     */
    public function __construct(Container $container, $mailer, RouterInterface $router, EngineInterface $templating, array $parameters)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->templating = $templating;
        $this->parameters = $parameters;
        $this->container = $container;

        parent::__construct($mailer, $router, $templating, $parameters);
    }


    /**
     * @param string $renderedTemplate
     * @param string $toEmail
     */
    protected function sendEmailMessage($renderedTemplate, $fromEmail, $toEmail)
    {
        // Render the email, use the first line as the subject, and the rest as the body
        $renderedLines = explode("\n", trim($renderedTemplate));
        $subject = $renderedLines[0];
        $body = implode("\n", array_slice($renderedLines, 1));

        $mandrill = $this->container->get('app.mandrill');

        $mandrill->sendEmail($toEmail, $toEmail, $subject, $body);
    }
}
